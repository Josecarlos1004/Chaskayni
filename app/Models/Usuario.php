<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'role', 'community_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'community_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'user_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'buyer_id');
    }

    public function esProductor(): bool
    {
        return in_array($this->role, ['productor', 'administrador'], true);
    }

    public function esAdministrador(): bool
    {
        return $this->role === 'administrador';
    }
}
