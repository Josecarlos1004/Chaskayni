<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaHelada extends Model
{
    protected $table = 'alertas_heladas';

    protected $fillable = ['creado_por', 'comunidad_id', 'titulo', 'mensaje', 'nivel', 'fecha_inicio', 'fecha_fin', 'estado'];

    protected function casts(): array
    {
        return ['fecha_inicio' => 'datetime', 'fecha_fin' => 'datetime'];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }
}
