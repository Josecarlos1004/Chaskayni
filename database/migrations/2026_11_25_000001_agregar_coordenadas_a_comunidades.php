<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Añade coordenadas opcionales sin alterar los registros existentes. */
    public function up(): void
    {
        Schema::table('comunidades', function (Blueprint $tabla): void {
            $tabla->decimal('latitud', 10, 7)->nullable()->after('altitud_msnm');
            $tabla->decimal('longitud', 10, 7)->nullable()->after('latitud');
        });
    }

    /** Retira solo las columnas agregadas por esta migración. */
    public function down(): void
    {
        Schema::table('comunidades', function (Blueprint $tabla): void {
            $tabla->dropColumn(['latitud', 'longitud']);
        });
    }
};

