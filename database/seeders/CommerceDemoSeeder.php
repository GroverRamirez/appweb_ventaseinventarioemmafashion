<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommerceDemoSeeder extends Seeder
{
    private const DEMO_NOTE_PREFIX = 'Datos demo comercio';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $users = User::query()->get();

            if ($users->isEmpty()) {
                $users = collect([$this->demoUser()]);
            }

            $users->each(fn (User $user) => $this->seedUserTeam($user));
        });
    }

    private function demoUser(): User
    {
        $user = User::where('email', 'test@example.com')->first();

        if ($user === null) {
            return User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $user->forceFill([
            'name' => 'Test User',
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        return $user;
    }

    private function seedUserTeam(User $user): void
    {
        $team = $this->teamForUser($user);

        $this->clearDemoTransactions($team);

        $categories = $this->categories($team);
        $suppliers = $this->suppliers($team);
        $products = $this->products($team, $categories);

        $this->createPurchase($team, $user, $suppliers['Textiles La Paz'], now()->subDays(18), [
            ['product' => $products['DEMO-POL-001'], 'quantity' => 18, 'unit_cost' => 42.50],
            ['product' => $products['DEMO-BLU-001'], 'quantity' => 12, 'unit_cost' => 55.00],
            ['product' => $products['DEMO-ACC-001'], 'quantity' => 15, 'unit_cost' => 18.00],
        ], self::DEMO_NOTE_PREFIX.' - compra inicial');

        $this->createPurchase($team, $user, $suppliers['Moda Andina'], now()->subDays(7), [
            ['product' => $products['DEMO-JEA-001'], 'quantity' => 16, 'unit_cost' => 88.00],
            ['product' => $products['DEMO-VES-001'], 'quantity' => 8, 'unit_cost' => 95.00],
            ['product' => $products['DEMO-CHA-001'], 'quantity' => 6, 'unit_cost' => 120.00],
        ], self::DEMO_NOTE_PREFIX.' - reposicion semanal');

        $this->createSale($team, $user, now()->subDays(5)->setTime(10, 30), [
            ['product' => $products['DEMO-POL-001'], 'quantity' => 3, 'unit_price' => 79.90],
            ['product' => $products['DEMO-ACC-001'], 'quantity' => 4, 'unit_price' => 39.90],
        ], self::DEMO_NOTE_PREFIX.' - venta mostrador');

        $this->createSale($team, $user, now()->subDays(2)->setTime(16, 15), [
            ['product' => $products['DEMO-JEA-001'], 'quantity' => 2, 'unit_price' => 155.00],
            ['product' => $products['DEMO-VES-001'], 'quantity' => 1, 'unit_price' => 189.00],
        ], self::DEMO_NOTE_PREFIX.' - venta tarde');

        $this->createSale($team, $user, now()->subDay()->setTime(11, 45), [
            ['product' => $products['DEMO-BLU-001'], 'quantity' => 4, 'unit_price' => 99.90],
            ['product' => $products['DEMO-ACC-001'], 'quantity' => 6, 'unit_price' => 39.90],
        ], self::DEMO_NOTE_PREFIX.' - venta redes');

        $this->createSale($team, $user, now()->setTime(9, 20), [
            ['product' => $products['DEMO-POL-001'], 'quantity' => 2, 'unit_price' => 79.90],
            ['product' => $products['DEMO-VES-001'], 'quantity' => 2, 'unit_price' => 189.00],
            ['product' => $products['DEMO-CHA-001'], 'quantity' => 1, 'unit_price' => 249.00],
            ['product' => $products['DEMO-ACC-001'], 'quantity' => 3, 'unit_price' => 39.90],
        ], self::DEMO_NOTE_PREFIX.' - venta hoy');
    }

    private function teamForUser(User $user): Team
    {
        $team = $user->currentTeam ?? $user->personalTeam();

        if ($team !== null) {
            return $team;
        }

        $team = Team::create([
            'name' => 'Emma Fashion Demo',
            'is_personal' => true,
        ]);

        $team->members()->attach($user, [
            'role' => TeamRole::Owner->value,
        ]);

        $user->switchTeam($team);

        return $team;
    }

    private function clearDemoTransactions(Team $team): void
    {
        $team->sales()
            ->where('notes', 'like', self::DEMO_NOTE_PREFIX.'%')
            ->delete();

        $team->purchases()
            ->where('notes', 'like', self::DEMO_NOTE_PREFIX.'%')
            ->delete();
    }

    /**
     * @return array<string, \App\Models\Category>
     */
    private function categories(Team $team): array
    {
        $rows = [
            'Poleras' => 'Prendas basicas para rotacion diaria.',
            'Pantalones' => 'Jeans y pantalones de moda.',
            'Vestidos' => 'Vestidos casuales y de temporada.',
            'Blusas' => 'Blusas para oficina y salidas.',
            'Chaquetas' => 'Abrigos ligeros y chaquetas.',
            'Accesorios' => 'Complementos para venta cruzada.',
        ];

        $categories = [];

        foreach ($rows as $name => $description) {
            $categories[$name] = $team->categories()->updateOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
        }

        return $categories;
    }

    /**
     * @return array<string, Supplier>
     */
    private function suppliers(Team $team): array
    {
        $rows = [
            'Textiles La Paz' => [
                'phone' => '76543210',
                'address' => 'Zona Max Paredes, La Paz',
                'notes' => 'Proveedor mayorista de prendas basicas.',
            ],
            'Moda Andina' => [
                'phone' => '71234567',
                'address' => 'Av. Buenos Aires, La Paz',
                'notes' => 'Proveedor de novedades y temporada.',
            ],
            'Distribuidora Emma' => [
                'phone' => '79887766',
                'address' => 'Centro comercial Uyustus',
                'notes' => 'Accesorios y reposicion rapida.',
            ],
        ];

        $suppliers = [];

        foreach ($rows as $name => $attributes) {
            $suppliers[$name] = $team->suppliers()->updateOrCreate(
                ['name' => $name],
                $attributes,
            );
        }

        return $suppliers;
    }

    /**
     * @param  array<string, \App\Models\Category>  $categories
     * @return array<string, Product>
     */
    private function products(Team $team, array $categories): array
    {
        $rows = [
            [
                'sku' => 'DEMO-POL-001',
                'category' => 'Poleras',
                'name' => 'Polera basica cuello redondo',
                'model' => 'Clasico',
                'size' => 'M',
                'color' => 'Negro',
                'location' => 'Estante A1',
                'purchase_price' => 42.50,
                'sale_price' => 79.90,
                'minimum_stock' => 4,
            ],
            [
                'sku' => 'DEMO-JEA-001',
                'category' => 'Pantalones',
                'name' => 'Jean mom fit azul',
                'model' => 'Mom Fit',
                'size' => '38',
                'color' => 'Azul',
                'location' => 'Estante B2',
                'purchase_price' => 88.00,
                'sale_price' => 155.00,
                'minimum_stock' => 3,
            ],
            [
                'sku' => 'DEMO-VES-001',
                'category' => 'Vestidos',
                'name' => 'Vestido casual floreado',
                'model' => 'Primavera',
                'size' => 'S',
                'color' => 'Rojo',
                'location' => 'Exhibicion',
                'purchase_price' => 95.00,
                'sale_price' => 189.00,
                'minimum_stock' => 2,
            ],
            [
                'sku' => 'DEMO-BLU-001',
                'category' => 'Blusas',
                'name' => 'Blusa manga larga satinada',
                'model' => 'Satin',
                'size' => 'L',
                'color' => 'Blanco',
                'location' => 'Estante C1',
                'purchase_price' => 55.00,
                'sale_price' => 99.90,
                'minimum_stock' => 4,
            ],
            [
                'sku' => 'DEMO-CHA-001',
                'category' => 'Chaquetas',
                'name' => 'Chaqueta corta ecocuero',
                'model' => 'Urban',
                'size' => 'M',
                'color' => 'Cafe',
                'location' => 'Perchero',
                'purchase_price' => 120.00,
                'sale_price' => 249.00,
                'minimum_stock' => 2,
            ],
            [
                'sku' => 'DEMO-ACC-001',
                'category' => 'Accesorios',
                'name' => 'Cinturon hebilla dorada',
                'model' => 'Dorado',
                'size' => 'U',
                'color' => 'Marron',
                'location' => 'Caja',
                'purchase_price' => 18.00,
                'sale_price' => 39.90,
                'minimum_stock' => 3,
            ],
        ];

        $products = [];

        foreach ($rows as $row) {
            $products[$row['sku']] = $team->products()->updateOrCreate(
                ['sku' => $row['sku']],
                [
                    'category_id' => $categories[$row['category']]->id,
                    'name' => $row['name'],
                    'model' => $row['model'],
                    'size' => $row['size'],
                    'color' => $row['color'],
                    'location' => $row['location'],
                    'purchase_price' => $row['purchase_price'],
                    'sale_price' => $row['sale_price'],
                    'stock' => 0,
                    'minimum_stock' => $row['minimum_stock'],
                    'is_active' => true,
                ],
            );
        }

        return $products;
    }

    /**
     * @param  array<int, array{product: Product, quantity: int, unit_cost: float}>  $items
     */
    private function createPurchase(Team $team, User $user, Supplier $supplier, \DateTimeInterface $purchasedAt, array $items, string $notes): Purchase
    {
        $total = collect($items)->sum(fn (array $item): float => $item['quantity'] * $item['unit_cost']);

        $purchase = $team->purchases()->create([
            'supplier_id' => $supplier->id,
            'user_id' => $user->id,
            'purchased_at' => $purchasedAt,
            'total' => $total,
            'notes' => $notes,
        ]);

        foreach ($items as $item) {
            $subtotal = $item['quantity'] * $item['unit_cost'];

            $purchase->items()->create([
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'subtotal' => $subtotal,
            ]);

            $item['product']->increment('stock', $item['quantity'], [
                'purchase_price' => $item['unit_cost'],
            ]);
        }

        return $purchase;
    }

    /**
     * @param  array<int, array{product: Product, quantity: int, unit_price: float}>  $items
     */
    private function createSale(Team $team, User $user, \DateTimeInterface $soldAt, array $items, string $notes): Sale
    {
        $total = collect($items)->sum(fn (array $item): float => $item['quantity'] * $item['unit_price']);

        $sale = $team->sales()->create([
            'user_id' => $user->id,
            'sold_at' => $soldAt,
            'total' => $total,
            'status' => Sale::STATUS_CONFIRMED,
            'notes' => $notes,
        ]);

        foreach ($items as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];

            $sale->items()->create([
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $subtotal,
            ]);

            $item['product']->decrement('stock', $item['quantity']);
        }

        return $sale;
    }
}
