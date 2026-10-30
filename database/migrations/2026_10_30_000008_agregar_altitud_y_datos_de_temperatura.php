<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Añade altitud y datos de temperatura sin cambiar ni borrar registros previos. */
    public function up(): void
    {
        Schema::table('comunidades', function (Blueprint $tabla) {
            $tabla->unsignedSmallInteger('altitud_msnm')->nullable()->after('provincia');
        });

        Schema::table('alertas_heladas', function (Blueprint $tabla) {
            $tabla->decimal('temperatura_c', 5, 2)->nullable()->after('nivel');
            $tabla->string('nivel_riesgo', 12)->nullable()->after('temperatura_c');
            $tabla->timestamp('cerrado_en')->nullable()->after('fecha_fin');
        });
    }

    /** Retira solo las columnas agregadas por esta migración. */
    public function down(): void
    {
        Schema::table('alertas_heladas', function (Blueprint $tabla) {
            $tabla->dropColumn(['temperatura_c', 'nivel_riesgo', 'cerrado_en']);
        });

        Schema::table('comunidades', function (Blueprint $tabla) {
            $tabla->dropColumn('altitud_msnm');
        });
    }
};
