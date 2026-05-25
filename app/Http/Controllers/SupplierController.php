<?php

namespace App\Http\Controllers;

use App\Http\Requests\Suppliers\SaveSupplierRequest;
use App\Models\Supplier;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    /**
     * Display suppliers.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('commerce/Suppliers', [
            'suppliers' => $currentTeam->suppliers()
                ->withCount('purchases')
                ->orderBy('name')
                ->get()
                ->map(fn (Supplier $supplier) => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'phone' => $supplier->phone,
                    'address' => $supplier->address,
                    'notes' => $supplier->notes,
                    'purchases_count' => $supplier->purchases_count,
                ]),
        ]);
    }

    /**
     * Store a supplier.
     */
    public function store(SaveSupplierRequest $request, Team $currentTeam): RedirectResponse
    {
        $currentTeam->suppliers()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proveedor registrado.']);

        return back();
    }

    /**
     * Update a supplier.
     */
    public function update(SaveSupplierRequest $request, Team $currentTeam, Supplier $supplier): RedirectResponse
    {
        abort_unless($supplier->team_id === $currentTeam->id, 404);

        $supplier->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proveedor actualizado.']);

        return back();
    }

    /**
     * Delete a supplier.
     */
    public function destroy(Team $currentTeam, Supplier $supplier): RedirectResponse
    {
        abort_unless($supplier->team_id === $currentTeam->id, 404);

        $supplier->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proveedor eliminado.']);

        return back();
    }
}
