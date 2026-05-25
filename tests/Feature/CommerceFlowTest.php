<?php

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('products can be registered with a new category', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this
        ->actingAs($user)
        ->post(route('products.store', $team), [
            'category_name' => 'Poleras',
            'sku' => 'POL-001',
            'name' => 'Polera basica',
            'model' => 'Clasico',
            'size' => 'M',
            'color' => 'Negro',
            'location' => 'Estante',
            'purchase_price' => 45,
            'sale_price' => 80,
            'stock' => 3,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'team_id' => $team->id,
        'name' => 'Poleras',
    ]);

    $this->assertDatabaseHas('products', [
        'team_id' => $team->id,
        'sku' => 'POL-001',
        'stock' => 3,
    ]);
});

test('registering a purchase increases product stock', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    $supplier = Supplier::factory()->create(['team_id' => $team->id]);
    $product = Product::factory()->create([
        'team_id' => $team->id,
        'category_id' => null,
        'stock' => 2,
        'purchase_price' => 40,
        'sale_price' => 75,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('purchases.store', $team), [
            'supplier_id' => $supplier->id,
            'purchased_at' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_cost' => 42,
                ],
            ],
        ]);

    $response->assertRedirect();

    expect($product->fresh()->stock)->toBe(7)
        ->and($product->fresh()->purchase_price)->toBe('42.00');

    $this->assertDatabaseHas('purchases', [
        'team_id' => $team->id,
        'supplier_id' => $supplier->id,
        'total' => 210,
    ]);
});

test('registering a sale decreases product stock and records daily total', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    $product = Product::factory()->create([
        'team_id' => $team->id,
        'category_id' => null,
        'stock' => 4,
        'sale_price' => 90,
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('sales.store', $team), [
            'sold_at' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 90,
                ],
            ],
        ]);

    $response->assertRedirect();

    expect($product->fresh()->stock)->toBe(2);

    $this->assertDatabaseHas('sales', [
        'team_id' => $team->id,
        'total' => 180,
        'status' => 'confirmed',
    ]);
});

test('sales cannot be registered when stock is insufficient', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    $product = Product::factory()->create([
        'team_id' => $team->id,
        'category_id' => null,
        'stock' => 1,
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('sales.index', $team))
        ->post(route('sales.store', $team), [
            'sold_at' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 70,
                ],
            ],
        ]);

    $response->assertRedirect(route('sales.index', $team));
    $response->assertSessionHasErrors('items.0.quantity');

    expect($product->fresh()->stock)->toBe(1);
    $this->assertDatabaseCount('sales', 0);
});
