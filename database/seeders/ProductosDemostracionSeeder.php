<?php

namespace Database\Seeders;

use App\Models\Comunidad;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ProductosDemostracionSeeder extends Seeder
{
    
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Los productos de demostración están deshabilitados en producción.');

            return;
        }

        $productor = $this->buscarProductor();

        if (! $productor) {
            $this->command?->warn('No hay ningún productor o administrador. Crea o aprueba una cuenta de productor y vuelve a ejecutar este seeder.');

            return;
        }

        $creados = 0;

        foreach ($this->productos() as $datos) {
            $comunidad = Comunidad::firstOrCreate(['nombre' => $datos['comunidad']]);
            unset($datos['comunidad']);

            $producto = Producto::firstOrCreate(
                ['usuario_id' => $productor->id, 'nombre' => $datos['nombre']],
                $datos + ['comunidad_id' => $comunidad->id, 'estado' => 'disponible'],
            );

            $creados += $producto->wasRecentlyCreated ? 1 : 0;
        }

        $this->command?->info("Productos creados: {$creados} (publicados a nombre de {$productor->name}).");
    }

    private function buscarProductor(): ?Usuario
    {
        $consulta = Usuario::whereIn('rol', ['productor', 'administrador'])->where('activo', true);

        if ($correo = env('PRODUCTOR_EMAIL')) {
            $consulta->where('email', $correo);
        }

        return $consulta->orderBy('id')->first();
    }

    /** @return array<int, array<string, mixed>> */
    private function productos(): array
    {
        return [
            // ---- 3 cestas salvadas (el sistema aplica 30 % de descuento al pedir) ----
            [
                'nombre' => 'Papa huayro',
                'categoria' => 'tuberculos',
                'descripcion' => 'Papa huayro con golpes leves de la cosecha. Muy buena para sopas y guisos.',
                'cantidad' => 150, 'unidad' => 'kg', 'precio_unitario' => 2.80,
                'cesta_salvada' => true, 'comunidad' => 'Urubamba',
            ],
            [
                'nombre' => 'Tomate de huerta',
                'categoria' => 'hortalizas',
                'descripcion' => 'Tomates maduros con detalles en la piel. Ideales para salsas y ensaladas del día.',
                'cantidad' => 80, 'unidad' => 'kg', 'precio_unitario' => 3.50,
                'cesta_salvada' => true, 'comunidad' => 'Pisac',
            ],
            [
                'nombre' => 'Choclo cusqueño',
                'categoria' => 'cereales',
                'descripcion' => 'Choclo grande de granos blancos, cosecha reciente. Se recomienda consumir pronto.',
                'cantidad' => 200, 'unidad' => 'unidad', 'precio_unitario' => 1.20,
                'cesta_salvada' => true, 'comunidad' => 'Anta',
            ],

            // ---- 6 productos normales ----
            [
                'nombre' => 'Quinua blanca',
                'categoria' => 'granos',
                'descripcion' => 'Quinua blanca seleccionada, lista para cocinar.',
                'cantidad' => 120, 'unidad' => 'kg', 'precio_unitario' => 9.00,
                'cesta_salvada' => false, 'comunidad' => 'Chinchero',
            ],
            [
                'nombre' => 'Maíz blanco gigante',
                'categoria' => 'cereales',
                'descripcion' => 'Maíz blanco gigante del Valle Sagrado, grano grande y parejo.',
                'cantidad' => 200, 'unidad' => 'kg', 'precio_unitario' => 6.50,
                'cesta_salvada' => false, 'comunidad' => 'Ollantaytambo',
            ],
            [
                'nombre' => 'Oca',
                'categoria' => 'tuberculos',
                'descripcion' => 'Oca fresca de altura, dulce y de buen tamaño.',
                'cantidad' => 90, 'unidad' => 'kg', 'precio_unitario' => 4.00,
                'cesta_salvada' => false, 'comunidad' => 'Maras',
            ],
            [
                'nombre' => 'Habas frescas',
                'categoria' => 'hortalizas',
                'descripcion' => 'Habas tiernas recién cosechadas, con vaina.',
                'cantidad' => 100, 'unidad' => 'kg', 'precio_unitario' => 4.50,
                'cesta_salvada' => false, 'comunidad' => 'Ccorca',
            ],
            [
                'nombre' => 'Zanahoria',
                'categoria' => 'hortalizas',
                'descripcion' => 'Zanahoria fresca de huerta, lavada y seleccionada.',
                'cantidad' => 150, 'unidad' => 'kg', 'precio_unitario' => 2.50,
                'cesta_salvada' => false, 'comunidad' => 'Poroy',
            ],
            [
                'nombre' => 'Kiwicha',
                'categoria' => 'granos',
                'descripcion' => 'Kiwicha (amaranto) limpia, ideal para desayunos y sopas.',
                'cantidad' => 60, 'unidad' => 'kg', 'precio_unitario' => 10.00,
                'cesta_salvada' => false, 'comunidad' => 'Pisac',
            ],
        ];
    }
}
