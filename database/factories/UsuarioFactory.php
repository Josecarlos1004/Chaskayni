<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * Contraseña predeterminada que usará la fábrica.
     */
    protected static ?string $contrasena;

    /**
     * Define los datos iniciales de una cuenta de prueba.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$contrasena ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Crea una cuenta de prueba sin verificar el correo.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
