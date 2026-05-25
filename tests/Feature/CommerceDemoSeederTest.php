<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\CommerceDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('commerce demo seeder creates reusable commerce data', function () {
    $this->seed(CommerceDemoSeeder::class);

    $user = User::where('email', 'test@example.com')->firstOrFail();
    $team = $user->currentTeam;

    expect($team)->not->toBeNull()
        ->and($team->categories)->toHaveCount(6)
        ->and($team->suppliers)->toHaveCount(3)
        ->and($team->products)->toHaveCount(6)
        ->and($team->purchases)->toHaveCount(2)
        ->and($team->sales)->toHaveCount(4)
        ->and(Product::where('sku', 'DEMO-ACC-001')->value('stock'))->toBe(2)
        ->and((float) Sale::sum('total'))->toBeGreaterThan(0)
        ->and((float) Purchase::sum('total'))->toBeGreaterThan(0);
});

test('commerce demo seeder does not duplicate demo transactions', function () {
    $this->seed(CommerceDemoSeeder::class);
    $this->seed(CommerceDemoSeeder::class);

    $team = User::where('email', 'test@example.com')->firstOrFail()->currentTeam;

    expect($team->purchases()->count())->toBe(2)
        ->and($team->sales()->count())->toBe(4)
        ->and(Product::where('sku', 'DEMO-ACC-001')->value('stock'))->toBe(2);
});

test('commerce demo seeder seeds existing user teams', function () {
    $user = User::factory()->create([
        'email' => 'owner@example.test',
    ]);

    $this->seed(CommerceDemoSeeder::class);

    $team = $user->fresh()->currentTeam;

    expect($team->products()->count())->toBe(6)
        ->and($team->purchases()->count())->toBe(2)
        ->and($team->sales()->count())->toBe(4)
        ->and($team->products()->where('sku', 'DEMO-POL-001')->exists())->toBeTrue();
});
