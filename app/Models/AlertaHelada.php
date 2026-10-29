<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaHelada extends Model
{
    protected $table = 'frost_alerts';

    protected $fillable = ['created_by', 'community_id', 'title', 'message', 'level', 'starts_at', 'ends_at', 'status'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'community_id');
    }
}
