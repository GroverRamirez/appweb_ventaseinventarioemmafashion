<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchases\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    /**
     * Display purchases.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('commerce/Purchases', [
            'products' => $currentTeam->products()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'sku', 'name', 'stock', 'purchase_price', 'sale_price']),
            'suppliers' => $currentTeam->suppliers()
                ->orderBy('name')
                ->get(['id', 'name']),
            'purchases' => $currentTeam->purchases()
                ->with(['supplier', 'user', 'items.product'])
                ->latest('purchased_at')
                ->paginate(12)
                ->through(fn (Purchase $purchase) => [
                    'id' => $purchase->id,
                    'purchased_at' => $purchase->purchased_at->toDateString(),
                    'supplier' => $purchase->supplier?->name,
                    'user' => $purchase->user->name,
                    'total' => $purchase->total,
                    'notes' => $purchase->notes,
                    'items' => $purchase->items->map(fn ($item) => [
                        'product' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_cost,
                        'subtotal' => $item->subtotal,
                    ]),
                ]),
        ]);
    }

    /**
     * Store a purchase and increase stock.
     */
    public function store(StorePurchaseRequest $request, Team $currentTeam): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $currentTeam) {
            $total = collect($validated['items'])->sum(
                fn (array $item) => (float) $item['quantity'] * (float) $item['unit_cost']
            );

            $purchase = $currentTeam->purchases()->create([
                'supplier_id' => $validated['supplier_id'] ?? null,
                'user_id' => $request->user()->id,
                'purchased_at' => $validated['purchased_at'],
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::where('team_id', $currentTeam->id)
                    ->whereKey($item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $subtotal = (float) $item['quantity'] * (float) $item['unit_cost'];

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                $product->update([
                    'stock' => $product->stock + (int) $item['quantity'],
                    'purchase_price' => $item['unit_cost'],
                ]);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Compra registrada y stock actualizado.']);

        return back();
    }
}
