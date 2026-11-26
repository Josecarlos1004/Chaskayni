<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Cifra los DNI existentes y conserva su unicidad mediante una huella HMAC. */
    public function up(): void
    {
        if (! config('app.key')) {
            throw new RuntimeException('Configura APP_KEY antes de cifrar los DNI.');
        }

        if (! Schema::hasColumn('users', 'dni_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['dni']);
                $table->text('dni')->nullable()->change();
                $table->char('dni_hash', 64)->nullable()->unique();
            });
        }

        DB::table('users')
            ->whereNotNull('dni')
            ->whereNull('dni_hash')
            ->orderBy('id')
            ->chunkById(200, function ($usuarios): void {
                foreach ($usuarios as $usuario) {
                    $dni = (string) $usuario->dni;
                    DB::table('users')->where('id', $usuario->id)->update([
                        'dni' => Crypt::encryptString($dni),
                        'dni_hash' => hash_hmac('sha256', $dni, (string) config('app.key')),
                    ]);
                }
            });
    }

    /** Restaura el formato previo; al revertir, el DNI vuelve a texto legible. */
    public function down(): void
    {
        DB::table('users')
            ->whereNotNull('dni')
            ->orderBy('id')
            ->chunkById(200, function ($usuarios): void {
                foreach ($usuarios as $usuario) {
                    DB::table('users')->where('id', $usuario->id)->update([
                        'dni' => Crypt::decryptString((string) $usuario->dni),
                    ]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['dni_hash']);
            $table->dropColumn('dni_hash');
            $table->string('dni', 8)->nullable()->change();
            $table->unique('dni');
        });
    }
};