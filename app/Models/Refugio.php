<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refugio extends Model
{
    protected $table = 'shelters';

    protected $fillable = ['community_id', 'name', 'address', 'description', 'capacity', 'active'];

    protected function casts(): array
    {
        return ['capacity' => 'integer', 'active' => 'boolean'];
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'community_id');
    }
}
