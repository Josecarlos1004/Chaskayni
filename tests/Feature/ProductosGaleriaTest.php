<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\ProductoImagen;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductosGaleriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_productor_puede_publicar_con_foto_principal_y_fotos_adicionales(): void
    {
        Storage::fake('public');
        $productor = $this->crearUsuario('productor');

        $this->actingAs($productor)
            ->post(route('productos.guardar'), $this->datosProducto([
                'imagen' => $this->crearImagen('principal.png'),
                'imagenes' => [$this->crearImagen('a.png'), $this->crearImagen('b.png')],
            ]))
            ->assertRedirect(route('mercado.indice'));

        $producto = Producto::where('nombre', 'Maíz cusqueño')->firstOrFail();

        $this->assertCount(2, $producto->imagenes);
        Storage::disk('public')->assertExists($producto->imagen_url);
        foreach ($producto->imagenes as $imagen) {
            $this->assertStringStartsWith('productos/', $imagen->ruta);
            Storage::disk('public')->assertExists($imagen->ruta);
        }
        $this->assertCount(3, $producto->galeria());
    }

    public function test_rechaza_mas_fotos_adicionales_que_el_maximo(): void
    {
        Storage::fake('public');
        $productor = $this->crearUsuario('productor');
        $demasiadas = array_map(
            fn (int $n) => $this->crearImagen("foto{$n}.png"),
            range(1, Producto::MAX_IMAGENES_ADICIONALES + 1),
        );

        $this->actingAs($productor)
            ->post(route('productos.guardar'), $this->datosProducto(['imagenes' => $demasiadas]))
            ->assertSessionHasErrors('imagenes');

        $this->assertDatabaseMissing('productos', ['nombre' => 'Maíz cusqueño']);
        $this->assertSame([], Storage::disk('public')->allFiles('productos'));
    }

    public function test_rechaza_archivos_adicionales_que_no_son_imagenes(): void
    {
        Storage::fake('public');
        $productor = $this->crearUsuario('productor');

        $this->actingAs($productor)
            ->post(route('productos.guardar'), $this->datosProducto([
                'imagenes' => [UploadedFile::fake()->createWithContent('nota.txt', 'no es una imagen')],
            ]))
            ->assertSessionHasErrors('imagenes.0');

        $this->assertDatabaseMissing('productos', ['nombre' => 'Maíz cusqueño']);
    }

    public function test_al_editar_se_pueden_quitar_y_agregar_fotos_adicionales(): void
    {
        Storage::fake('public');
        $productor = $this->crearUsuario('productor');
        $producto = $this->crearProducto($productor);
        $vieja = $this->agregarImagen($producto, 'productos/vieja.png', 1);
        $conservada = $this->agregarImagen($producto, 'productos/conservada.png', 2);

        $this->actingAs($productor)
            ->put(route('productos.actualizar', $producto), $this->datosProducto([
                'quitar_imagenes' => [$vieja->id],
                'imagenes' => [$this->crearImagen('nueva.png')],
            ]))
            ->assertRedirect(route('mercado.indice'));

        $this->assertDatabaseMissing('producto_imagenes', ['id' => $vieja->id]);
        Storage::disk('public')->assertMissing('productos/vieja.png');
        Storage::disk('public')->assertExists('productos/conservada.png');
        $this->assertDatabaseHas('producto_imagenes', ['id' => $conservada->id]);
        $this->assertCount(2, $producto->fresh()->imagenes);
    }

    public function test_al_editar_no_se_supera_el_maximo_sumando_las_existentes(): void
    {
        Storage::fake('public');
        $productor = $this->crearUsuario('productor');
        $producto = $this->crearProducto($productor);
        for ($n = 1; $n <= Producto::MAX_IMAGENES_ADICIONALES; $n++) {
            $this->agregarImagen($producto, "productos/existente{$n}.png", $n);
        }

        $this->actingAs($productor)
            ->put(route('productos.actualizar', $producto), $this->datosProducto([
                'imagenes' => [$this->crearImagen('extra.png')],
            ]))
            ->assertSessionHasErrors('imagenes');

        $this->assertCount(Producto::MAX_IMAGENES_ADICIONALES, $producto->fresh()->imagenes);
    }

    public function test_no_se_pueden_quitar_fotos_de_otro_producto(): void
    {
        Storage::fake('public');
        $productor = $this->crearUsuario('productor');
        $otroProductor = $this->crearUsuario('productor', 'otro-productor@example.test');
        $propio = $this->crearProducto($productor);
        $ajeno = $this->crearProducto($otroProductor, 'Quinua ajena');
        $imagenAjena = $this->agregarImagen($ajeno, 'productos/ajena.png', 1);

        $this->actingAs($productor)
            ->put(route('productos.actualizar', $propio), $this->datosProducto([
                'quitar_imagenes' => [$imagenAjena->id],
            ]))
            ->assertRedirect(route('mercado.indice'));

        $this->assertDatabaseHas('producto_imagenes', ['id' => $imagenAjena->id]);
        Storage::disk('public')->assertExists('productos/ajena.png');
    }

    public function test_el_formulario_de_edicion_muestra_las_fotos_adicionales(): void
    {
        Storage::fake('public');
        $productor = $this->crearUsuario('productor');
        $producto = $this->crearProducto($productor);
        $this->agregarImagen($producto, 'productos/galeria.png', 1);

        $this->actingAs($productor)
            ->get(route('productos.editar', $producto))
            ->assertOk()
            ->assertSee('Fotos adicionales')
            ->assertSee('quitar_imagenes[]', false);
    }

    public function test_al_eliminar_un_producto_se_borran_sus_fotos_adicionales(): void
    {
        Storage::fake('public');
        $administrador = $this->crearUsuario('administrador', 'admin-galeria@example.test');
        $productor = $this->crearUsuario('productor');
        $producto = $this->crearProducto($productor);
        $this->agregarImagen($producto, 'productos/para-borrar.png', 1);

        $this->actingAs($administrador)
            ->delete(route('productos.eliminar', $producto))
            ->assertRedirect();

        $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
        $this->assertDatabaseMissing('producto_imagenes', ['producto_id' => $producto->id]);
        Storage::disk('public')->assertMissing('productos/para-borrar.png');
    }

    public function test_el_mercado_muestra_las_miniaturas_cuando_hay_varias_fotos(): void
    {
        Storage::fake('public');
        $productor = $this->crearUsuario('productor');
        $producto = $this->crearProducto($productor);
        $producto->update(['imagen_url' => 'productos/principal.png']);
        $this->agregarImagen($producto, 'productos/segunda.png', 1);

        $this->get(route('mercado.indice'))
            ->assertOk()
            ->assertSee('miniaturas-producto', false);
    }

    private function crearUsuario(string $rol, string $correo = 'productor-galeria@example.test'): Usuario
    {
        return Usuario::create([
            'name' => 'Usuario '.$rol,
            'email' => $correo,
            'password' => 'clave-de-prueba',
            'rol' => $rol,
            'activo' => true,
            'aprobado_en' => now(),
        ]);
    }

    private function crearProducto(Usuario $vendedor, string $nombre = 'Maíz cusqueño'): Producto
    {
        return Producto::create([
            'usuario_id' => $vendedor->id,
            'nombre' => $nombre,
            'cantidad' => 10,
            'unidad' => 'kg',
            'precio_unitario' => 3.5,
            'cesta_salvada' => false,
            'estado' => 'disponible',
        ]);
    }

    private function agregarImagen(Producto $producto, string $ruta, int $orden): ProductoImagen
    {
        Storage::disk('public')->put($ruta, 'contenido de imagen');

        return $producto->imagenes()->create(['ruta' => $ruta, 'orden' => $orden]);
    }

    private function datosProducto(array $adicionales = []): array
    {
        return array_merge([
            'nombre' => 'Maíz cusqueño',
            'categoria' => 'Granos',
            'cantidad' => 10,
            'unidad' => 'kg',
            'precio_unitario' => 3.5,
            'cesta_salvada' => 0,
        ], $adicionales);
    }

    private function crearImagen(string $nombre = 'foto.png'): UploadedFile
    {
        $contenido = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j2ioAAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent($nombre, $contenido);
    }
}
