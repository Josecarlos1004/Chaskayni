<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Traduce las columnas propias del sistema conservando sus registros. */
    public function up(): void
    {
        $renombres = [
            'users' => ['role' => 'rol', 'community_id' => 'comunidad_id'],
            'comunidades' => [
                'name' => 'nombre',
                'district' => 'distrito',
                'province' => 'provincia',
                'description' => 'descripcion',
            ],
            'productos' => [
                'user_id' => 'usuario_id',
                'community_id' => 'comunidad_id',
                'name' => 'nombre',
                'category' => 'categoria',
                'description' => 'descripcion',
                'quantity' => 'cantidad',
                'unit' => 'unidad',
                'unit_price' => 'precio_unitario',
                'image_url' => 'imagen_url',
                'status' => 'estado',
            ],
            'pedidos' => [
                'buyer_id' => 'comprador_id',
                'status' => 'estado',
                'notes' => 'notas',
            ],
            'detalles_pedido' => [
                'order_id' => 'pedido_id',
                'product_id' => 'producto_id',
                'quantity' => 'cantidad',
                'unit_price' => 'precio_unitario',
            ],
            'alertas_heladas' => [
                'created_by' => 'creado_por',
                'community_id' => 'comunidad_id',
                'title' => 'titulo',
                'message' => 'mensaje',
                'level' => 'nivel',
                'starts_at' => 'fecha_inicio',
                'ends_at' => 'fecha_fin',
                'status' => 'estado',
            ],
            'reportes_riesgo' => [
                'reporter_id' => 'reportante_id',
                'community_id' => 'comunidad_id',
                'title' => 'titulo',
                'description' => 'descripcion',
                'latitude' => 'latitud',
                'longitude' => 'longitud',
                'status' => 'estado',
            ],
            'refugios' => [
                'community_id' => 'comunidad_id',
                'name' => 'nombre',
                'address' => 'direccion',
                'description' => 'descripcion',
                'capacity' => 'capacidad',
                'active' => 'activo',
            ],
        ];

        $this->renombrarColumnas($renombres);
    }

    /** Revierte los nombres de las columnas sin recrear las tablas. */
    public function down(): void
    {
        $renombres = [
            'users' => ['rol' => 'role', 'comunidad_id' => 'community_id'],
            'comunidades' => [
                'nombre' => 'name',
                'distrito' => 'district',
                'provincia' => 'province',
                'descripcion' => 'description',
            ],
            'productos' => [
                'usuario_id' => 'user_id',
                'comunidad_id' => 'community_id',
                'nombre' => 'name',
                'categoria' => 'category',
                'descripcion' => 'description',
                'cantidad' => 'quantity',
                'unidad' => 'unit',
                'precio_unitario' => 'unit_price',
                'imagen_url' => 'image_url',
                'estado' => 'status',
            ],
            'pedidos' => [
                'comprador_id' => 'buyer_id',
                'estado' => 'status',
                'notas' => 'notes',
            ],
            'detalles_pedido' => [
                'pedido_id' => 'order_id',
                'producto_id' => 'product_id',
                'cantidad' => 'quantity',
                'precio_unitario' => 'unit_price',
            ],
            'alertas_heladas' => [
                'creado_por' => 'created_by',
                'comunidad_id' => 'community_id',
                'titulo' => 'title',
                'mensaje' => 'message',
                'nivel' => 'level',
                'fecha_inicio' => 'starts_at',
                'fecha_fin' => 'ends_at',
                'estado' => 'status',
            ],
            'reportes_riesgo' => [
                'reportante_id' => 'reporter_id',
                'comunidad_id' => 'community_id',
                'titulo' => 'title',
                'descripcion' => 'description',
                'latitud' => 'latitude',
                'longitud' => 'longitude',
                'estado' => 'status',
            ],
            'refugios' => [
                'comunidad_id' => 'community_id',
                'nombre' => 'name',
                'direccion' => 'address',
                'descripcion' => 'description',
                'capacidad' => 'capacity',
                'activo' => 'active',
            ],
        ];

        $this->renombrarColumnas($renombres);
    }

    /** Aplica cada cambio por separado para que MySQL actualice sus índices y relaciones. */
    private function renombrarColumnas(array $tablas): void
    {
        foreach ($tablas as $tabla => $columnas) {
            foreach ($columnas as $anterior => $nuevo) {
                Schema::table($tabla, function (Blueprint $esquema) use ($anterior, $nuevo) {
                    $esquema->renameColumn($anterior, $nuevo);
                });
            }
        }
    }
};
