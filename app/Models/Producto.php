<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = ['usuario_id', 'comunidad_id', 'nombre', 'categoria', 'descripcion', 'cantidad', 'unidad', 'precio_unitario', 'imagen_url', 'cesta_salvada', 'estado'];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'cesta_salvada' => 'boolean',
        ];
    }

    /** Calcula el precio unitario con descuento cuando la publicación es una cesta salvada. */
    public function precioUnitarioAplicado(): float
    {
        $precio = (float) $this->precio_unitario;

        return round($this->cesta_salvada ? $precio * 0.70 : $precio, 2);
    }

    /** Devuelve una ruta del servidor para fotos guardadas o conserva las URL externas. */
    public function urlImagen(): ?string
    {
        if (! $this->imagen_url) {
            return null;
        }

        if ($this->rutaImagenAlmacenada()) {
            return route('productos.imagen', $this, false);
        }

        return filter_var($this->imagen_url, FILTER_VALIDATE_URL)
            ? $this->imagen_url
            : null;
    }

    /** Normaliza rutas locales antiguas y rutas guardadas en el almacenamiento persistente. */
    public function rutaImagenAlmacenada(): ?string
    {
        $imagen = $this->imagen_url;
        if (! $imagen) {
            return null;
        }

        if (filter_var($imagen, FILTER_VALIDATE_URL)) {
            $imagen = parse_url($imagen, PHP_URL_PATH) ?: '';
        }

        if (str_starts_with($imagen, '/storage/productos/')) {
            return substr($imagen, strlen('/storage/'));
        }

        return str_starts_with($imagen, 'productos/')
            ? ltrim($imagen, '/')
            : null;
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }

    public function detallesPedido(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'producto_id');
    }
}
