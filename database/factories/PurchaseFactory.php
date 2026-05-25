<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'supplier_id' => Supplier::factory(),
            'user_id' => User::factory(),
            'purchased_at' => fake()->date(),
            'total' => fake()->randomFloat(2, 100, 900),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
