<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductosImagenTest extends TestCase
{
    use RefreshDatabase;

    public function test_productor_puede_subir_imagen_y_ver_la_vista_previa(): void
    {
        Storage::fake('public');
        $productor = $this->crearProductor();

        $this->actingAs($productor)
            ->post(route('productos.guardar'), $this->datosProducto([
                'imagen' => $this->crearImagen(),
            ]))
            ->assertRedirect(route('mercado.indice'));

        $producto = Producto::where('nombre', 'Papa nativa fotografiada')->firstOrFail();

        $this->assertStringStartsWith('productos/', $producto->imagen_url);
        Storage::disk('public')->assertExists($producto->imagen_url);

        $this->actingAs($productor)
            ->get(route('productos.editar', $producto))
            ->assertOk()
            ->assertSee('Subir fotografía del producto')
            ->assertSee($producto->urlImagen(), false);
    }

    public function test_al_reemplazar_o_quitar_imagen_se_limpia_el_archivo_anterior(): void
    {
        Storage::fake('public');
        $productor = $this->crearProductor();
        Storage::disk('public')->put('productos/anterior.png', 'imagen anterior');

        $producto = Producto::create($this->datosProducto([
            'usuario_id' => $productor->id,
            'imagen_url' => 'productos/anterior.png',
        ]));

        $this->actingAs($productor)
            ->put(route('productos.actualizar', $producto), $this->datosProducto([
                'imagen' => $this->crearImagen('nueva.png'),
            ]))
            ->assertRedirect(route('mercado.indice'));

        $producto->refresh();
        $this->assertNotSame('productos/anterior.png', $producto->imagen_url);
        Storage::disk('public')->assertMissing('productos/anterior.png');
        Storage::disk('public')->assertExists($producto->imagen_url);

        $rutaNueva = $producto->imagen_url;
        $this->actingAs($productor)
            ->put(route('productos.actualizar', $producto), $this->datosProducto([
                'quitar_imagen' => '1',
            ]))
            ->assertRedirect(route('mercado.indice'));

        $this->assertNull($producto->fresh()->imagen_url);
        Storage::disk('public')->assertMissing($rutaNueva);
    }

    public function test_rechaza_archivos_que_no_son_imagenes(): void
    {
        Storage::fake('public');
        $productor = $this->crearProductor();

        $this->actingAs($productor)
            ->post(route('productos.guardar'), $this->datosProducto([
                'imagen' => UploadedFile::fake()->createWithContent('nota.txt', 'esto no es una imagen'),
            ]))
            ->assertSessionHasErrors('imagen');

        $this->assertDatabaseMissing('productos', ['nombre' => 'Papa nativa fotografiada']);
        $this->assertSame([], Storage::disk('public')->allFiles('productos'));
    }

    private function crearProductor(): Usuario
    {
        return Usuario::create([
            'name' => 'Productor de prueba',
            'email' => 'productor-imagen@example.test',
            'password' => 'clave-de-prueba',
            'rol' => 'productor',
            'activo' => true,
            'aprobado_en' => now(),
        ]);
    }

    private function datosProducto(array $adicionales = []): array
    {
        return array_merge([
            'nombre' => 'Papa nativa fotografiada',
            'categoria' => 'Tubérculos',
            'cantidad' => 12,
            'unidad' => 'kg',
            'precio_unitario' => 4.5,
            'cesta_salvada' => 0,
        ], $adicionales);
    }

    private function crearImagen(string $nombre = 'papa.png'): UploadedFile
    {
        $contenido = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j2ioAAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent($nombre, $contenido);
    }
}
