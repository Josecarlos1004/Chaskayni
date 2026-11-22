<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Guarda la propuesta de comunidad sin publicarla ni alterar asociaciones actuales. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('comunidad_propuesta', 120)->nullable();
            $table->string('distrito_propuesto', 120)->nullable();
        });
    }

    /** Revierte únicamente las columnas nuevas de propuesta. */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['comunidad_propuesta', 'distrito_propuesto']);
        });
    }
};
