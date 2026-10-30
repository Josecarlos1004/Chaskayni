<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Renombra las tablas propias del sistema sin recrearlas ni borrar sus registros. */
    public function up(): void
    {
        $renombres = [
            'communities' => 'comunidades',
            'products' => 'productos',
            'orders' => 'pedidos',
            'order_items' => 'detalles_pedido',
            'frost_alerts' => 'alertas_heladas',
            'risk_reports' => 'reportes_riesgo',
            'shelters' => 'refugios',
        ];

        foreach ($renombres as $tablaAnterior => $tablaNueva) {
            Schema::rename($tablaAnterior, $tablaNueva);
        }
    }

    /** Restaura los nombres anteriores si se revierte esta migración. */
    public function down(): void
    {
        $renombres = [
            'refugios' => 'shelters',
            'reportes_riesgo' => 'risk_reports',
            'alertas_heladas' => 'frost_alerts',
            'detalles_pedido' => 'order_items',
            'pedidos' => 'orders',
            'productos' => 'products',
            'comunidades' => 'communities',
        ];

        foreach ($renombres as $tablaAnterior => $tablaNueva) {
            Schema::rename($tablaAnterior, $tablaNueva);
        }
    }
};
