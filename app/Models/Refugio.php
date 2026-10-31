<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refugio extends Model
{
    protected $table = 'refugios';

    protected $fillable = ['comunidad_id', 'nombre', 'direccion', 'descripcion', 'capacidad', 'activo', 'latitud', 'longitud'];

    protected function casts(): array
    {
        return ['capacidad' => 'integer', 'activo' => 'boolean', 'latitud' => 'float', 'longitud' => 'float'];
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }
}
