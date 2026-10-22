<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskReport extends Model
{
    protected $fillable = ['reporter_id', 'community_id', 'title', 'description', 'latitude', 'longitude', 'status'];

    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_id'); }
    public function community(): BelongsTo { return $this->belongsTo(Community::class); }
}