<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagenes';

    protected $fillable = ['producto_id', 'ruta', 'orden'];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /** Devuelve la URL pública de la foto adicional, igual que la foto principal del producto. */
    public function url(): string
    {
        return config('filesystems.disks.public.driver') === 'local'
            ? asset('storage/'.ltrim($this->ruta, '/'))
            : route('productos.imagenes.ver', $this, false);
    }
}
