<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Guarda la categoría escrita por la persona sin alterar reportes ni alertas previos. */
    public function up(): void
    {
        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->string('tipo_personalizado', 100)->nullable()->after('tipo');
        });

        Schema::table('alertas_heladas', function (Blueprint $table): void {
            $table->string('tipo_personalizado', 100)->nullable()->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('alertas_heladas', function (Blueprint $table): void {
            $table->dropColumn('tipo_personalizado');
        });

        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->dropColumn('tipo_personalizado');
        });
    }
};