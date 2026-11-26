<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertas_heladas', function (Blueprint $tabla): void {
            $tabla->decimal('latitud', 10, 7)->nullable();
            $tabla->decimal('longitud', 10, 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('alertas_heladas', function (Blueprint $tabla): void {
            $tabla->dropColumn(['latitud', 'longitud']);
        });
    }
};