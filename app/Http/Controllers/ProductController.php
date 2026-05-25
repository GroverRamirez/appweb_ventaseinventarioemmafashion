<?php

namespace App\Http\Controllers;

use App\Http\Requests\Products\SaveProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display product inventory.
     */
    public function index(Request $request, Team $currentTeam): Response
    {
        $search = $request->string('search')->toString();

        return Inertia::render('commerce/Products', [
            'filters' => [
                'search' => $search,
            ],
            'categories' => $currentTeam->categories()
                ->orderBy('name')
                ->get(['id', 'name']),
            'products' => $currentTeam->products()
                ->with('category')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('size', 'like', "%{$search}%")
                            ->orWhere('color', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Product $product) => $this->serializeProduct($product)),
        ]);
    }

    /**
     * Store a product.
     */
    public function store(SaveProductRequest $request, Team $currentTeam): RedirectResponse
    {
        $data = $this->productData($request, $currentTeam);

        $currentTeam->products()->create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto registrado.']);

        return back();
    }

    /**
     * Update a product.
     */
    public function update(SaveProductRequest $request, Team $currentTeam, Product $product): RedirectResponse
    {
        abort_unless($product->team_id === $currentTeam->id, 404);

        $product->update($this->productData($request, $currentTeam));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto actualizado.']);

        return back();
    }

    /**
     * Delete a product.
     */
    public function destroy(Team $currentTeam, Product $product): RedirectResponse
    {
        abort_unless($product->team_id === $currentTeam->id, 404);

        if ($product->purchaseItems()->exists() || $product->saleItems()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar un producto con movimientos.']);

            return back();
        }

        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Producto eliminado.']);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function productData(SaveProductRequest $request, Team $team): array
    {
        $validated = $request->validated();
        $categoryName = trim((string) ($validated['category_name'] ?? ''));

        if ($categoryName !== '') {
            $validated['category_id'] = Category::firstOrCreate([
                'team_id' => $team->id,
                'name' => $categoryName,
            ])->id;
        }

        unset($validated['category_name']);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'category' => $product->category?->name,
            'sku' => $product->sku,
            'name' => $product->name,
            'model' => $product->model,
            'size' => $product->size,
            'color' => $product->color,
            'location' => $product->location,
            'purchase_price' => $product->purchase_price,
            'sale_price' => $product->sale_price,
            'stock' => $product->stock,
            'minimum_stock' => $product->minimum_stock,
            'is_active' => $product->is_active,
        ];
    }
}
