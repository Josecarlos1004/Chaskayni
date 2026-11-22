<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Clasifica los avisos nuevos y conserva los registros anteriores. */
    public function up(): void
    {
        Schema::table('reportes_riesgo', function (Blueprint $table) {
            $table->string('tipo', 20)->default('riesgo');
        });

        Schema::table('alertas_heladas', function (Blueprint $table) {
            $table->string('tipo', 20)->default('clima');
        });
    }

    public function down(): void
    {
        Schema::table('alertas_heladas', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('reportes_riesgo', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
