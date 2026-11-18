<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Agrega una ruta privada para la fotografía opcional de evidencia. */
    public function up(): void
    {
        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->string('foto_evidencia')->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->dropColumn('foto_evidencia');
        });
    }
};
