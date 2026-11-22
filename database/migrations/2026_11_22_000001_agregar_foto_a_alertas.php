<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Agrega una fotografía opcional para las alertas sin modificar los registros actuales. */
    public function up(): void
    {
        Schema::table('alertas_heladas', function (Blueprint $table): void {
            $table->string('foto')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('alertas_heladas', function (Blueprint $table): void {
            $table->dropColumn('foto');
        });
    }
};
