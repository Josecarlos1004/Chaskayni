<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear comunidades iniciales
        $comunidades = [
            ['name' => 'Chinchero'],
            ['name' => 'Maras'],
            ['name' => 'Ollantaytambo'],
            ['name' => 'Pisac'],
            ['name' => 'Urubamba'],
            ['name' => 'Ccorca'],
            ['name' => 'Poroy'],
            ['name' => 'Anta'],
        ];

        foreach ($comunidades as $comunidad) {
            Community::firstOrCreate($comunidad);
        }

        // 2. Llamar al seeder de usuarios de Chaskayni (si lo estás usando)
        $this->call([
            ChaskayniUsersSeeder::class,
        ]);

        // 3. Usuario de prueba por defecto (opcional, usa firstOrCreate para evitar duplicados al re-ejecutar)
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );
    }
}
