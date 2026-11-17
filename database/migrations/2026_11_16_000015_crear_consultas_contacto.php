<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Guarda las consultas para que administración pueda atenderlas en la aplicación. */
    public function up(): void
    {
        Schema::create('consultas_contacto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('correo', 190)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('asunto', 160);
            $table->text('mensaje');
            $table->timestamp('leido_en')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas_contacto');
    }
};