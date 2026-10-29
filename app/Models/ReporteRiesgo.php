<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteRiesgo extends Model
{
    protected $table = 'risk_reports';

    protected $fillable = ['reporter_id', 'community_id', 'title', 'description', 'latitude', 'longitude', 'status'];

    public function reportante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'reporter_id');
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class, 'community_id');
    }
}
