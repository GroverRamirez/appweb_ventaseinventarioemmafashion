<?php

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('owners can view commerce reports with aggregated data', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    $supplier = Supplier::factory()->create(['team_id' => $team->id, 'name' => 'Proveedor Central']);
    $product = Product::factory()->create([
        'team_id' => $team->id,
        'category_id' => null,
        'sku' => 'REP-001',
        'name' => 'Blusa reporte',
        'stock' => 1,
        'minimum_stock' => 2,
        'purchase_price' => 40,
        'sale_price' => 75,
    ]);

    $this
        ->actingAs($user)
        ->post(route('purchases.store', $team), [
            'supplier_id' => $supplier->id,
            'purchased_at' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_cost' => 40,
                ],
            ],
        ]);

    $this
        ->actingAs($user)
        ->post(route('sales.store', $team), [
            'sold_at' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 75,
                ],
            ],
        ]);

    $response = $this
        ->actingAs($user)
        ->get(route('reports.index', $team));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('commerce/Reports')
        ->where('summary.salesCount', 1)
        ->where('summary.purchasesCount', 1)
        ->has('dailySales', 1)
        ->has('topProducts', 1)
        ->has('lowStockProducts', 1)
        ->has('supplierPurchases', 1));
});
