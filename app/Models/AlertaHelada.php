<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaHelada extends Model
{
    protected $table = 'alertas_heladas';

    protected $fillable = [
        'creado_por',
        'comunidad_id',
        'titulo',
        'mensaje',
        'nivel',
        'temperatura_c',
        'nivel_riesgo',
        'fecha_inicio',
        'fecha_fin',
        'cerrado_en',
        'reporte_riesgo_id',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'temperatura_c' => 'decimal:2',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'cerrado_en' => 'datetime',
        ];
    }

    /** Filtra los avisos publicados, abiertos y dentro de su periodo de vigencia. */
    public function scopeActivas(Builder $consulta): Builder
    {
        return $consulta->where('estado', 'publicada')
            ->whereNull('cerrado_en')
            ->where(fn (Builder $consulta) => $consulta->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', now()))
            ->where(fn (Builder $consulta) => $consulta->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now()));
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }

    public function reporteOrigen(): BelongsTo
    {
        return $this->belongsTo(ReporteRiesgo::class, 'reporte_riesgo_id');
    }
}
