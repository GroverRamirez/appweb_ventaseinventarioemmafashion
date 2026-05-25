<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
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
            'user_id' => User::factory(),
            'sold_at' => fake()->dateTimeBetween('-1 month'),
            'total' => fake()->randomFloat(2, 50, 600),
            'status' => Sale::STATUS_CONFIRMED,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
