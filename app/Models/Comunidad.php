<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comunidad extends Model
{
    protected $table = 'communities';

    protected $fillable = ['name', 'district', 'province', 'description'];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'community_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'community_id');
    }
}
