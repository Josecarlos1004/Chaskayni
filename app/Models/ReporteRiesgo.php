<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReporteRiesgo extends Model
{
    protected $table = 'reportes_riesgo';

    protected $fillable = ['reportante_id', 'comunidad_id', 'tipo', 'titulo', 'descripcion', 'foto_evidencia', 'latitud', 'longitud', 'estado'];

    public function reportante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'reportante_id');
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }

    public function alerta(): HasOne
    {
        return $this->hasOne(AlertaHelada::class, 'reporte_riesgo_id');
    }
}
