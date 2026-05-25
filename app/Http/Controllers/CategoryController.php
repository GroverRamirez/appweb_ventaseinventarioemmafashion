<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\SaveCategoryRequest;
use App\Models\Category;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display product categories.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('commerce/Categories', [
            'categories' => $currentTeam->categories()
                ->withCount('products')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'products_count' => $category->products_count,
                ]),
        ]);
    }

    /**
     * Store a category.
     */
    public function store(SaveCategoryRequest $request, Team $currentTeam): RedirectResponse
    {
        $currentTeam->categories()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Categoría registrada.']);

        return back();
    }

    /**
     * Update a category.
     */
    public function update(SaveCategoryRequest $request, Team $currentTeam, Category $category): RedirectResponse
    {
        abort_unless($category->team_id === $currentTeam->id, 404);

        $category->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Categoría actualizada.']);

        return back();
    }

    /**
     * Delete a category.
     */
    public function destroy(Team $currentTeam, Category $category): RedirectResponse
    {
        abort_unless($category->team_id === $currentTeam->id, 404);

        if ($category->products()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'No se puede eliminar una categoría con productos asociados.',
            ]);

            return back();
        }

        $category->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Categoría eliminada.']);

        return back();
    }
}
