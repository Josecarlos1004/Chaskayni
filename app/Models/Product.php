<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['user_id', 'community_id', 'name', 'category', 'description', 'quantity', 'unit', 'unit_price', 'image_url', 'status'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2'];
    }

    public function seller(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function community(): BelongsTo { return $this->belongsTo(Community::class); }
    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class); }
}