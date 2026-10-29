<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'products';

    protected $fillable = ['user_id', 'community_id', 'name', 'category', 'description', 'quantity', 'unit', 'unit_price', 'image_url', 'cesta_salvada', 'status'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'cesta_salvada' => 'boolean',
        ];
    }

    /** Calcula el precio unitario con descuento cuando la publicación es una cesta salvada. */
    public function precioUnitarioAplicado(): float
    {
        $precio = (float) $this->unit_price;

        return round($this->cesta_salvada ? $precio * 0.70 : $precio, 2);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'community_id');
    }

    public function detallesPedido(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'product_id');
    }
}
