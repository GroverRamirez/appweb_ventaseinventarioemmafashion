<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea el catálogo de roles y permisos en BD. Antes vivía únicamente en
     * los enums (App\Enums\TeamRole y App\Enums\TeamPermission). Ahora queda
     * persistido y consultable directamente desde la base de datos.
     *
     * El campo `team_members.role` sigue siendo un string slug que actúa como
     * FK lógica hacia `roles.slug`. No se modifica para evitar romper código.
     */
    public function up(): void
    {
        // --- Catálogo de roles ---
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();              // 'owner', 'member'
            $table->string('name');                        // 'Propietaria', 'Vendedora'
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);  // No editable desde UI
            $table->unsignedSmallInteger('level')->default(1); // Jerarquía
            $table->timestamps();
        });

        // --- Catálogo de permisos ---
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();              // 'sale:create', 'product:manage'
            $table->string('name');                        // 'Registrar ventas'
            $table->string('description')->nullable();
            $table->string('group')->index();              // 'sale', 'product', 'team', ...
            $table->timestamps();
        });

        // --- Pivote: qué permisos tiene cada rol ---
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
