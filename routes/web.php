<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\AlertasHeladasController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\MercadoController;
use App\Http\Controllers\MapaComunitarioController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ReportesRiesgoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InicioController::class, 'index'])->name('inicio');
Route::get('/mercado', [MercadoController::class, 'index'])->name('mercado.indice');
Route::get('/alertas', [AlertasHeladasController::class, 'index'])->name('alertas.indice');
Route::get('/mapa-comunitario', [MapaComunitarioController::class, 'index'])->name('mapa.indice');

Route::middleware('guest')->group(function () {
    // Laravel espera la ruta llamada "login" para redirigir a quienes no han iniciado sesión.
    Route::get('/ingresar', [AccesoController::class, 'formularioIngreso'])->name('login');
    Route::post('/ingresar', [AccesoController::class, 'ingresar'])
        ->middleware('throttle:6,1')
        ->name('acceso.guardar');
    Route::get('/crear-cuenta', [AccesoController::class, 'formularioRegistro'])->name('register');
    Route::post('/crear-cuenta', [AccesoController::class, 'registrar'])
        ->middleware('throttle:6,1')
        ->name('registro.guardar');
});

Route::middleware('auth')->group(function () {
    Route::post('/salir', [AccesoController::class, 'salir'])->name('logout');
    Route::get('/panel', [PanelController::class, 'index'])->name('panel');
    Route::get('/pedidos', [PedidosController::class, 'index'])->name('pedidos.indice');
    Route::post('/productos/{producto}/pedir', [PedidosController::class, 'store'])->name('pedidos.guardar');
    Route::get('/productos/crear', [ProductosController::class, 'create'])->name('productos.crear');
    Route::post('/productos', [ProductosController::class, 'store'])->name('productos.guardar');
    Route::get('/productos/{producto}/editar', [ProductosController::class, 'edit'])->name('productos.editar');
    Route::put('/productos/{producto}', [ProductosController::class, 'update'])->name('productos.actualizar');
    Route::delete('/productos/{producto}', [ProductosController::class, 'destroy'])->name('productos.eliminar');
    Route::get('/reportes/nuevo', [ReportesRiesgoController::class, 'create'])->name('riesgos.crear');
    Route::post('/reportes', [ReportesRiesgoController::class, 'store'])->name('riesgos.guardar');

    Route::prefix('administracion')->name('administracion.')->group(function () {
        Route::get('/comunidades', [AdministracionController::class, 'listarComunidades'])->name('comunidades.indice');
        Route::post('/comunidades', [AdministracionController::class, 'guardarComunidad'])->name('comunidades.guardar');
        Route::get('/alertas/crear', [AdministracionController::class, 'crearAlerta'])->name('alertas.crear');
        Route::post('/alertas', [AdministracionController::class, 'guardarAlerta'])->name('alertas.guardar');
        Route::post('/alertas/temperatura', [AdministracionController::class, 'evaluarTemperatura'])->name('alertas.temperatura');
        Route::patch('/alertas/{alertaHelada}/cerrar', [AdministracionController::class, 'cerrarAlerta'])->name('alertas.cerrar');
        Route::get('/reportes', [AdministracionController::class, 'listarReportes'])->name('reportes.indice');
        Route::patch('/reportes/{reporteRiesgo}', [AdministracionController::class, 'revisarReporte'])->name('reportes.revisar');
        Route::get('/refugios', [AdministracionController::class, 'listarRefugios'])->name('refugios.indice');
        Route::post('/refugios', [AdministracionController::class, 'guardarRefugio'])->name('refugios.guardar');
    });
});
