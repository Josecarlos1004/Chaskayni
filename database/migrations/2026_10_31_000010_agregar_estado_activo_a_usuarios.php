<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Agrega el estado de acceso sin modificar las cuentas existentes. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('rol')->index();
        });
    }

    /** Revierte únicamente la columna agregada por esta migración. */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['activo']);
            $table->dropColumn('activo');
        });
    }
};
