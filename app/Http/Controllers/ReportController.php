<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display commerce reports for the selected period.
     */
    public function __invoke(Request $request, Team $currentTeam): Response
    {
        $from = $request->date('from') ?? now()->subDays(29)->startOfDay();
        $to = $request->date('to') ?? now()->endOfDay();

        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $sales = $currentTeam->sales()
            ->where('status', Sale::STATUS_CONFIRMED)
            ->whereBetween('sold_at', [$from, $to]);

        $purchases = $currentTeam->purchases()
            ->whereDate('purchased_at', '>=', $from->toDateString())
            ->whereDate('purchased_at', '<=', $to->toDateString());

        return Inertia::render('commerce/Reports', [
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => [
                'salesTotal' => (clone $sales)->sum('total'),
                'salesCount' => (clone $sales)->count(),
                'purchasesTotal' => (clone $purchases)->sum('total'),
                'purchasesCount' => (clone $purchases)->count(),
                'inventoryValue' => $currentTeam->products()
                    ->selectRaw('COALESCE(SUM(stock * purchase_price), 0) as value')
                    ->value('value'),
                'lowStockCount' => $currentTeam->products()
                    ->whereColumn('stock', '<=', 'minimum_stock')
                    ->count(),
            ],
            'dailySales' => $this->dailySales($currentTeam, $from, $to),
            'topProducts' => $this->topProducts($currentTeam, $from, $to),
            'lowStockProducts' => $currentTeam->products()
                ->with('category')
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->orderBy('stock')
                ->limit(12)
                ->get()
                ->map(fn ($product) => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'stock' => $product->stock,
                    'minimum_stock' => $product->minimum_stock,
                ]),
            'supplierPurchases' => $this->supplierPurchases($currentTeam, $from, $to),
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{date: string, total: string, count: int}>
     */
    private function dailySales(Team $team, Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return DB::table('sales')
            ->selectRaw('DATE(sold_at) as date, COALESCE(SUM(total), 0) as total, COUNT(*) as count')
            ->where('team_id', $team->id)
            ->where('status', Sale::STATUS_CONFIRMED)
            ->whereBetween('sold_at', [$from, $to])
            ->groupByRaw('DATE(sold_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total' => (string) $row->total,
                'count' => (int) $row->count,
            ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{product: string, sku: string, quantity: int, total: string}>
     */
    private function topProducts(Team $team, Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->selectRaw('products.name as product, products.sku, SUM(sale_items.quantity) as quantity, SUM(sale_items.subtotal) as total')
            ->where('sales.team_id', $team->id)
            ->where('sales.status', Sale::STATUS_CONFIRMED)
            ->whereBetween('sales.sold_at', [$from, $to])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('quantity')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'product' => $row->product,
                'sku' => $row->sku,
                'quantity' => (int) $row->quantity,
                'total' => (string) $row->total,
            ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{supplier: string, purchases: int, total: string}>
     */
    private function supplierPurchases(Team $team, Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return DB::table('purchases')
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->selectRaw('COALESCE(suppliers.name, "Sin proveedor") as supplier, COUNT(purchases.id) as purchases, SUM(purchases.total) as total')
            ->where('purchases.team_id', $team->id)
            ->whereDate('purchases.purchased_at', '>=', $from->toDateString())
            ->whereDate('purchases.purchased_at', '<=', $to->toDateString())
            ->groupBy('supplier')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'supplier' => $row->supplier,
                'purchases' => (int) $row->purchases,
                'total' => (string) $row->total,
            ]);
    }
}
