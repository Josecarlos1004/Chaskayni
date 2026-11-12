<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Corrige el estado predeterminado y normaliza solo reportes pendientes mal guardados. */
    public function up(): void
    {
        DB::statement("ALTER TABLE reportes_riesgo ALTER COLUMN estado SET DEFAULT 'pendiente'");

        DB::table('reportes_riesgo')
            ->where('estado', "'pendiente'")
            ->update(['estado' => 'pendiente']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reportes_riesgo ALTER COLUMN estado SET DEFAULT '''pendiente'''");

        DB::table('reportes_riesgo')
            ->where('estado', 'pendiente')
            ->update(['estado' => "'recepcionado'"]);
    }
};
