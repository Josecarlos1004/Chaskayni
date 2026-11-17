<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = ['comprador_id', 'estado', 'total', 'notas'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2'];
    }

    public function comprador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'comprador_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    /** Devuelve la clase visual correspondiente al estado actual del pedido. */
    public function claseEstado(): string
    {
        return match ($this->estado) {
            'aceptado', 'preparando' => 'estado-pedido-aceptado',
            'en_camino' => 'estado-pedido-en-camino',
            'rechazado' => 'estado-pedido-rechazado',
            'completado' => 'estado-pedido-completado',
            default => 'estado-pedido-pendiente',
        };
    }

    public function nombreEstado(): string
    {
        return match ($this->estado) {
            'pendiente' => 'Pendiente de confirmación',
            'aceptado' => 'Confirmado',
            'preparando' => 'En preparación',
            'en_camino' => 'En camino',
            'completado' => 'Entregado',
            'rechazado' => 'Rechazado',
            default => ucfirst($this->estado),
        };
    }

    /** Prepara las etapas para que el consumidor siga el avance de su pedido. */
    public function pasosSeguimiento(): array
    {
        $nombres = [
            'aceptado' => 'Confirmado',
            'preparando' => 'En preparación',
            'en_camino' => 'En camino',
            'completado' => 'Entregado',
        ];
        $indiceActual = array_search($this->estado, array_keys($nombres), true);
        $pasos = [];

        foreach ($nombres as $estado => $nombre) {
            $indice = count($pasos);
            $pasos[] = [
                'nombre' => $nombre,
                'actual' => $indiceActual === $indice,
                'completado' => $indiceActual !== false && $indice <= $indiceActual,
            ];
        }

        return $pasos;
    }
}
