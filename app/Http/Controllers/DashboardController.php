<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Team;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the commerce dashboard.
     */
    public function __invoke(Team $currentTeam): Response
    {
        $today = Carbon::today();

        $confirmedSales = $currentTeam->sales()
            ->where('status', Sale::STATUS_CONFIRMED);

        return Inertia::render('Dashboard', [
            'metrics' => [
                'products' => $currentTeam->products()->count(),
                'lowStock' => $currentTeam->products()
                    ->whereColumn('stock', '<=', 'minimum_stock')
                    ->count(),
                'todaySales' => (clone $confirmedSales)
                    ->whereDate('sold_at', $today)
                    ->sum('total'),
                'monthSales' => (clone $confirmedSales)
                    ->whereBetween('sold_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])
                    ->sum('total'),
            ],
            'lowStockProducts' => $currentTeam->products()
                ->with('category')
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->orderBy('stock')
                ->limit(8)
                ->get()
                ->map(fn ($product) => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'stock' => $product->stock,
                    'minimum_stock' => $product->minimum_stock,
                ]),
            'recentSales' => $currentTeam->sales()
                ->with(['user', 'items.product'])
                ->latest('sold_at')
                ->limit(6)
                ->get()
                ->map(fn ($sale) => [
                    'id' => $sale->id,
                    'sold_at' => $sale->sold_at->toDateTimeString(),
                    'total' => $sale->total,
                    'user' => $sale->user->name,
                    'items' => $sale->items->map(fn ($item) => [
                        'product' => $item->product->name,
                        'quantity' => $item->quantity,
                    ]),
                ]),
        ]);
    }
}
