<?php

namespace Database\Seeders;

use App\Models\Comunidad;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Agrega comunidades de referencia sin duplicar ni borrar datos existentes. */
    public function run(): void
    {
        $comunidadesDeDemostracion = [
            'Chinchero',
            'Maras',
            'Ollantaytambo',
            'Pisac',
            'Urubamba',
            'Ccorca',
            'Poroy',
            'Anta',
        ];

        foreach ($comunidadesDeDemostracion as $nombre) {
            Comunidad::firstOrCreate(['nombre' => $nombre]);
        }

        $this->call(ProductosDemostracionSeeder::class);
        $this->call(AlertasDemostracionSeeder::class);
    }
}