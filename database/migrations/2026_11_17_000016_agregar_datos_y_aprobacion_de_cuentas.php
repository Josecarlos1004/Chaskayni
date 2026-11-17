<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Agrega los datos para verificar cuentas sin invalidar las cuentas actuales. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 8)->nullable()->unique();
            $table->string('telefono', 20)->nullable();
            $table->timestamp('aprobado_en')->nullable()->index();
        });

        // Las cuentas existentes conservan el acceso; los registros nuevos quedan pendientes.
        DB::table('users')->whereNull('aprobado_en')->update(['aprobado_en' => now()]);
    }

    /** Revierte solo las columnas agregadas por esta migración. */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->dropIndex(['aprobado_en']);
            $table->dropColumn(['dni', 'telefono', 'aprobado_en']);
        });
    }
};