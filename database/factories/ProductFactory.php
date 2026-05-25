<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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
            'category_id' => Category::factory(),
            'sku' => strtoupper(Str::random(8)),
            'name' => fake()->randomElement(['Polera basica', 'Pantalon jean', 'Vestido casual', 'Blusa manga larga']),
            'model' => fake()->optional()->word(),
            'size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            'color' => fake()->safeColorName(),
            'location' => fake()->randomElement(['Estante', 'Deposito', 'Exhibicion']),
            'purchase_price' => fake()->randomFloat(2, 30, 120),
            'sale_price' => fake()->randomFloat(2, 50, 220),
            'stock' => fake()->numberBetween(0, 20),
            'minimum_stock' => fake()->numberBetween(1, 5),
            'is_active' => true,
        ];
    }
}
