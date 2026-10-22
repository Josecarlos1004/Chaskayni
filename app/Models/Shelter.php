<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shelter extends Model
{
    protected $fillable = ['community_id', 'name', 'address', 'description', 'capacity', 'active'];

    protected function casts(): array { return ['capacity' => 'integer', 'active' => 'boolean']; }
    public function community(): BelongsTo { return $this->belongsTo(Community::class); }
}