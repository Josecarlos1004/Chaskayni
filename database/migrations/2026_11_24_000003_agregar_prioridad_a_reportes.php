<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Añade una prioridad revisable sin alterar los datos existentes. */
    public function up(): void
    {
        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->string('prioridad', 12)->default('media')->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('reportes_riesgo', function (Blueprint $table): void {
            $table->dropColumn('prioridad');
        });
    }
};
