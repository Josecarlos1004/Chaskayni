<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comunidad extends Model
{
    protected $table = 'comunidades';

    protected $fillable = ['nombre', 'distrito', 'provincia', 'descripcion'];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'comunidad_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'comunidad_id');
    }
}
