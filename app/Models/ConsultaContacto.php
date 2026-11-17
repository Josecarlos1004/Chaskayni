<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaContacto extends Model
{
    protected $table = 'consultas_contacto';

    protected $fillable = ['nombre', 'correo', 'telefono', 'asunto', 'mensaje', 'leido_en'];

    protected function casts(): array
    {
        return ['leido_en' => 'datetime'];
    }
}