<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Guarda el nombre aproximado que devuelve el mapa junto a las coordenadas del reporte. */
    public function up(): void
    {
        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->string('ubicacion_detallada', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->dropColumn('ubicacion_detallada');
        });
    }
};
