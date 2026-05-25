<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
// UserController fue eliminado — la gestión de miembros vive en Teams\TeamMemberController y Teams\TeamInvitationController.
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)
            ->middleware('team.can:dashboard:view')
            ->name('dashboard');

        Route::get('products', [ProductController::class, 'index'])
            ->middleware('team.can:product:view')
            ->name('products.index');
        Route::post('products', [ProductController::class, 'store'])
            ->middleware('team.can:product:manage')
            ->name('products.store');
        Route::patch('products/{product}', [ProductController::class, 'update'])
            ->middleware('team.can:product:manage')
            ->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])
            ->middleware('team.can:product:manage')
            ->name('products.destroy');

        Route::get('categories', [CategoryController::class, 'index'])
            ->middleware('team.can:category:view')
            ->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])
            ->middleware('team.can:category:manage')
            ->name('categories.store');
        Route::patch('categories/{category}', [CategoryController::class, 'update'])
            ->middleware('team.can:category:manage')
            ->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])
            ->middleware('team.can:category:manage')
            ->name('categories.destroy');

        Route::get('suppliers', [SupplierController::class, 'index'])
            ->middleware('team.can:supplier:view')
            ->name('suppliers.index');
        Route::post('suppliers', [SupplierController::class, 'store'])
            ->middleware('team.can:supplier:manage')
            ->name('suppliers.store');
        Route::patch('suppliers/{supplier}', [SupplierController::class, 'update'])
            ->middleware('team.can:supplier:manage')
            ->name('suppliers.update');
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])
            ->middleware('team.can:supplier:manage')
            ->name('suppliers.destroy');

        Route::get('purchases', [PurchaseController::class, 'index'])
            ->middleware('team.can:purchase:view')
            ->name('purchases.index');
        Route::post('purchases', [PurchaseController::class, 'store'])
            ->middleware('team.can:purchase:create')
            ->name('purchases.store');

        Route::get('sales', [SaleController::class, 'index'])
            ->middleware('team.can:sale:view')
            ->name('sales.index');
        Route::post('sales', [SaleController::class, 'store'])
            ->middleware('team.can:sale:create')
            ->name('sales.store');

        Route::get('reports', ReportController::class)
            ->middleware('team.can:report:view')
            ->name('reports.index');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::middleware('team.can:team:update')->group(function () {
            Route::get('admin/roles', [RoleController::class, 'index'])->name('admin.roles.index');
            Route::post('admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
            Route::patch('admin/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
            Route::delete('admin/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
        });

    });

require __DIR__.'/settings.php';
