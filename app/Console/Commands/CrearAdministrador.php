<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CrearAdministrador extends Command
{
    protected $signature = 'app:crear-administrador';

    protected $description = 'Crea de forma interactiva la primera cuenta administradora';

    public function handle(): int
    {
        $nombre = trim((string) $this->ask('Nombre de la persona administradora'));
        $correo = strtolower(trim((string) $this->ask('Correo electrónico')));

        if ($nombre === '' || ! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->error('Ingresa un nombre y un correo válidos.');

            return self::FAILURE;
        }

        if (Usuario::where('email', $correo)->exists()) {
            $this->error('Ya existe una cuenta con ese correo.');

            return self::FAILURE;
        }

        $contrasena = (string) $this->secret('Contraseña (mínimo 10 caracteres)');
        $confirmacion = (string) $this->secret('Repite la contraseña');

        if (strlen($contrasena) < 10 || ! hash_equals($contrasena, $confirmacion)) {
            $this->error('Las contraseñas deben coincidir y tener al menos 10 caracteres.');

            return self::FAILURE;
        }

        Usuario::create([
            'name' => $nombre,
            'email' => $correo,
            'password' => Hash::make($contrasena),
            'role' => 'administrador',
        ]);

        $this->info('Cuenta administradora creada. Inicia sesión desde la página de ingreso.');

        return self::SUCCESS;
    }
}
