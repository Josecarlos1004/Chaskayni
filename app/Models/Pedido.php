<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'orders';

    protected $fillable = ['buyer_id', 'status', 'total', 'notes'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2'];
    }

    public function comprador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'buyer_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'order_id');
    }
}
