<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrostAlert extends Model
{
    protected $fillable = ['created_by', 'community_id', 'title', 'message', 'level', 'starts_at', 'ends_at', 'status'];

    protected function casts(): array { return ['starts_at' => 'datetime', 'ends_at' => 'datetime']; }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function community(): BelongsTo { return $this->belongsTo(Community::class); }
}