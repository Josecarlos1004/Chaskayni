<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Agrega la marca sin modificar las publicaciones existentes. */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_saved_basket')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_saved_basket');
        });
    }
};
