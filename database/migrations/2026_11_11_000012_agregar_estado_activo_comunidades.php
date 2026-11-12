<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Añade el estado sin reemplazar ni modificar registros existentes. */
    public function up(): void
    {
        Schema::table('comunidades', function (Blueprint $tabla): void {
            $tabla->boolean('activo')->default(true)->index();
        });
    }

    public function down(): void
    {
        Schema::table('comunidades', function (Blueprint $tabla): void {
            $tabla->dropColumn('activo');
        });
    }
};
