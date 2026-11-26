<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Permite reportes urgentes sin obligar a crear una cuenta. */
    public function up(): void
    {
        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->dropForeign('risk_reports_reporter_id_foreign');
        });

        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->unsignedBigInteger('reportante_id')->nullable()->change();
            $table->foreign('reportante_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /** No revierte si hay reportes anónimos, para no borrar ni perderlos. */
    public function down(): void
    {
        if (DB::table('reportes_riesgo')->whereNull('reportante_id')->exists()) {
            throw new \RuntimeException('No se puede revertir: hay reportes anónimos guardados.');
        }

        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->dropForeign('reportes_riesgo_reportante_id_foreign');
        });

        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->unsignedBigInteger('reportante_id')->nullable(false)->change();
            $table->foreign('reportante_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
