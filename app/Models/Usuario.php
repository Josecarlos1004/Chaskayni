<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'rol', 'comunidad_id', 'activo', 'dni', 'telefono', 'aprobado_en'];

    protected $hidden = ['password', 'remember_token', 'dni', 'telefono'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'activo' => 'boolean', 'aprobado_en' => 'datetime'];
    }

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'comprador_id');
    }

    /** Cuenta las solicitudes de compra que el productor aún no ha respondido. */
    public function pedidosPendientesRecibidos(): int
    {
        return Pedido::where('estado', 'pendiente')->deProductor($this->id)->count();
    }

    public function esProductor(): bool
    {
        return in_array($this->rol, ['productor', 'administrador'], true);
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }
}