<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdministrator extends Command
{
    protected $signature = 'app:crear-administrador';
    protected $description = 'Crea de forma interactiva la primera cuenta administradora';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Nombre de la persona administradora'));
        $email = strtolower(trim((string) $this->ask('Correo electrónico')));

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Ingresa un nombre y un correo válidos.');
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('Ya existe una cuenta con ese correo.');
            return self::FAILURE;
        }

        $password = (string) $this->secret('Contraseña (mínimo 10 caracteres)');
        $confirmation = (string) $this->secret('Repite la contraseña');

        if (strlen($password) < 10 || ! hash_equals($password, $confirmation)) {
            $this->error('Las contraseñas deben coincidir y tener al menos 10 caracteres.');
            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'administrador',
        ]);

        $this->info('Cuenta administradora creada. Inicia sesión desde la página de ingreso.');
        return self::SUCCESS;
    }
}