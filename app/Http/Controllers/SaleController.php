<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sales\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    /**
     * Display sales and daily close.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('commerce/Sales', [
            'products' => $currentTeam->products()
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->orderBy('name')
                ->get(['id', 'sku', 'name', 'stock', 'sale_price']),
            'dailyClose' => [
                'date' => now()->toDateString(),
                'total' => $currentTeam->sales()
                    ->where('status', Sale::STATUS_CONFIRMED)
                    ->whereDate('sold_at', now())
                    ->sum('total'),
                'count' => $currentTeam->sales()
                    ->where('status', Sale::STATUS_CONFIRMED)
                    ->whereDate('sold_at', now())
                    ->count(),
            ],
            'sales' => $currentTeam->sales()
                ->with(['user', 'items.product'])
                ->latest('sold_at')
                ->paginate(12)
                ->through(fn (Sale $sale) => [
                    'id' => $sale->id,
                    'sold_at' => $sale->sold_at->toDateTimeString(),
                    'user' => $sale->user->name,
                    'status' => $sale->status,
                    'total' => $sale->total,
                    'notes' => $sale->notes,
                    'items' => $sale->items->map(fn ($item) => [
                        'product' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                    ]),
                ]),
        ]);
    }

    /**
     * Store a sale and decrease stock.
     */
    public function store(StoreSaleRequest $request, Team $currentTeam): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $currentTeam) {
            $lockedItems = [];
            $lockedProducts = [];
            $stockRemaining = [];
            $total = 0;

            foreach ($validated['items'] as $index => $item) {
                $productId = (int) $item['product_id'];
                $product = $lockedProducts[$productId] ??= Product::where('team_id', $currentTeam->id)
                    ->whereKey($productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stockRemaining[$productId] ??= $product->stock;

                if ($stockRemaining[$productId] < (int) $item['quantity']) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => "Stock insuficiente para {$product->name}. Disponible: {$stockRemaining[$productId]}.",
                    ]);
                }

                $stockRemaining[$productId] -= (int) $item['quantity'];
                $subtotal = (float) $item['quantity'] * (float) $item['unit_price'];
                $total += $subtotal;

                $lockedItems[] = [
                    'product' => $product,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ];
            }

            $sale = $currentTeam->sales()->create([
                'user_id' => $request->user()->id,
                'sold_at' => Carbon::parse($validated['sold_at']),
                'total' => $total,
                'status' => Sale::STATUS_CONFIRMED,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lockedItems as $item) {
                $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            foreach ($lockedProducts as $productId => $product) {
                $product->update([
                    'stock' => $stockRemaining[$productId],
                ]);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Venta registrada y stock actualizado.']);

        return back();
    }
}
