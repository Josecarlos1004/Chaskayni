<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Agrega el vínculo sin alterar ni borrar reportes o alertas existentes. */
    public function up(): void
    {
        Schema::table('alertas_heladas', function (Blueprint $table) {
            $table->foreignId('reporte_riesgo_id')
                ->nullable()
                ->unique()
                ->constrained('reportes_riesgo')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alertas_heladas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reporte_riesgo_id');
        });
    }
};
