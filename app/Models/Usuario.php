<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'rol', 'comunidad_id', 'comunidad_propuesta', 'distrito_propuesto', 'activo', 'dni', 'telefono', 'aprobado_en'];

    protected $hidden = ['password', 'remember_token', 'dni', 'telefono', 'comunidad_propuesta', 'distrito_propuesto'];

    protected function dni(): Attribute
    {
        return Attribute::make(
            get: fn (?string $valor) => $valor === null ? null : Crypt::decryptString($valor),
            set: function (?string $valor): array {
                if ($valor === null || $valor === '') {
                    return ['dni' => null, 'dni_hash' => null];
                }

                return [
                    'dni' => Crypt::encryptString($valor),
                    'dni_hash' => hash_hmac('sha256', $valor, (string) config('app.key')),
                ];
            },
        );
    }

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

    public function esProductor(): bool
    {
        return in_array($this->rol, ['productor', 'administrador'], true);
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }
}
