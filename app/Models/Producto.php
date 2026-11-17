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

    /** Devuelve la URL pública de la foto local o conserva una URL externa anterior. */
    public function urlImagen(): ?string
    {
        if (! $this->imagen_url) {
            return null;
        }

        if (str_starts_with($this->imagen_url, '/storage/productos/')) {
            return $this->imagen_url;
        }

        if (filter_var($this->imagen_url, FILTER_VALIDATE_URL)) {
            $ruta = parse_url($this->imagen_url, PHP_URL_PATH);

            // Convierte las URL absolutas guardadas por instalaciones locales en rutas compartibles.
            if (is_string($ruta) && str_starts_with($ruta, '/storage/productos/')) {
                return $ruta;
            }

            return $this->imagen_url;
        }

        return str_starts_with($this->imagen_url, 'productos/')
            ? '/storage/'.ltrim($this->imagen_url, '/')
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
