<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Convierte cualquier registro previo con rol "admin" a "member" para
     * que el esquema de roles quede en Propietaria (owner) + Empleado (member).
     */
    public function up(): void
    {
        DB::table('team_members')
            ->where('role', 'admin')
            ->update(['role' => 'member']);

        DB::table('team_invitations')
            ->where('role', 'admin')
            ->update(['role' => 'member']);
    }

    /**
     * Reverse the migrations.
     *
     * No-op: la conversión es destructiva pero segura (admin se trataba como
     * un rol intermedio que ahora consolidamos en empleado).
     */
    public function down(): void
    {
        //
    }
};
