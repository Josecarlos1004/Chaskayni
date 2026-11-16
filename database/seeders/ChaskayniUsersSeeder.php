<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChaskayniUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = [
            [
                'name' => 'Jose Molina ',
                'email' => 'productoranta@chaskayni.com',
                'password' => Hash::make('71029279'),
                'role' => 'productor',
            ],
            [
                'name' => 'Alejandro Tacusi',
                'email' => 'productorparuro@chaskayni.com',
                'password' => Hash::make('61057711'),
                'role' => 'productor',
            ],
            [
                'name' => 'Carlos Quejia (Restaurante)',
                'email' => 'compradorcusco1@chaskayni.com',
                'password' => Hash::make('71145624'),
                'role' => 'comprador',
            ],
            [
                'name' => 'Alejandro Tacusi (Mercado)',
                'email' => 'compradorcusco2@chaskayni.com',
                'password' => Hash::make('password123'),
                'role' => 'comprador',
            ],
            [
                'name' => 'Jose Molina (Ladera)',
                'email' => 'vecinonorte@chaskayni.com',
                'password' => Hash::make('71029279'),
                'role' => 'vecino',
            ],
            [
                'name' => 'Carlos Admin',
                'email' => 'Cadmin@chaskayni.com',
                'password' => Hash::make('71145624'),
                'user' => 'administrador',
            ],
            [
                'name' => 'Alejandro Admin',
                'email' => 'Aadmin@chaskayni.com',
                'password' => Hash::make('61057711'),
                'user' => 'administrador',
            ],
            [
                'name' => 'Jose Admin',
                'email' => 'Jadmin@chaskayni.com',
                'password' => Hash::make('71029279'),
                'user' => 'administrador',
            ],
        ];

        foreach ($usuarios as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }
    }
}
