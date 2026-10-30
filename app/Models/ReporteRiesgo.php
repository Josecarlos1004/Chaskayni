<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteRiesgo extends Model
{
    protected $table = 'reportes_riesgo';

    protected $fillable = ['reportante_id', 'comunidad_id', 'titulo', 'descripcion', 'latitud', 'longitud', 'estado'];

    public function reportante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'reportante_id');
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }
}
