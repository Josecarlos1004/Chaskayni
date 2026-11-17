<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\AlertasHeladasController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\MapaComunitarioController;
use App\Http\Controllers\MercadoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ReportesRiesgoController;
use App\Http\Middleware\VerificarCuentaActiva;
use Illuminate\Support\Facades\Route;

Route::get('/', [InicioController::class, 'index'])->name('inicio');
Route::get('/mercado', [MercadoController::class, 'index'])->name('mercado.indice');
Route::get('/alertas', [AlertasHeladasController::class, 'index'])->name('alertas.indice');
Route::get('/mapa-comunitario', [MapaComunitarioController::class, 'index'])->name('mapa.indice');
Route::get('/contacto', [ContactoController::class, 'formulario'])->name('contacto.formulario');
Route::post('/contacto', [ContactoController::class, 'guardar'])->middleware('throttle:3,1')->name('contacto.guardar');

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

Route::middleware(['auth', VerificarCuentaActiva::class])->group(function () {
    Route::post('/salir', [AccesoController::class, 'salir'])->name('logout');
    Route::get('/panel', [PanelController::class, 'index'])->name('panel');
    Route::get('/pedidos', [PedidosController::class, 'index'])->name('pedidos.indice');
    Route::get('/pedidos-recibidos', [PedidosController::class, 'recibidos'])->name('pedidos.recibidos');
    Route::patch('/pedidos/{pedido}/estado', [PedidosController::class, 'actualizarEstado'])->name('pedidos.estado');
    Route::post('/productos/{producto}/pedir', [PedidosController::class, 'store'])->name('pedidos.guardar');
    Route::get('/productos/crear', [ProductosController::class, 'create'])->name('productos.crear');
    Route::post('/productos', [ProductosController::class, 'store'])->name('productos.guardar');
    Route::get('/productos/{producto}/editar', [ProductosController::class, 'edit'])->name('productos.editar');
    Route::put('/productos/{producto}', [ProductosController::class, 'update'])->name('productos.actualizar');
    Route::delete('/productos/{producto}', [ProductosController::class, 'destroy'])->name('productos.eliminar');
    Route::get('/reportes/nuevo', [ReportesRiesgoController::class, 'create'])->name('riesgos.crear');
    Route::post('/reportes', [ReportesRiesgoController::class, 'store'])->name('riesgos.guardar');
    /*Route::post('/reportes', [ReportesRiesgoController::class, 'store'])->name('guardar');*/

    Route::prefix('administracion')->name('administracion.')->group(function () {
        Route::get('/', [AdministracionController::class, 'panel'])->name('panel');
        Route::get('/productos', [AdministracionController::class, 'listarProductos'])->name('productos.indice');
        Route::get('/alertas', [AdministracionController::class, 'listarAlertas'])->name('alertas.indice');
        Route::get('/usuarios', [AdministracionController::class, 'listarUsuarios'])->name('usuarios.indice');
        Route::patch('/usuarios/{usuario}', [AdministracionController::class, 'actualizarUsuario'])->name('usuarios.actualizar');
        Route::delete('/usuarios/{usuario}', [AdministracionController::class, 'eliminarUsuario'])->name('usuarios.eliminar');
        Route::get('/comunidades', [AdministracionController::class, 'listarComunidades'])->name('comunidades.indice');
        Route::post('/comunidades', [AdministracionController::class, 'guardarComunidad'])->name('comunidades.guardar');
        Route::put('/comunidades/{comunidad}', [AdministracionController::class, 'actualizarComunidad'])->name('comunidades.actualizar');
        Route::delete('/comunidades/{comunidad}', [AdministracionController::class, 'eliminarComunidad'])->name('comunidades.eliminar');
        Route::get('/alertas/crear', [AdministracionController::class, 'crearAlerta'])->name('alertas.crear');
        Route::post('/alertas', [AdministracionController::class, 'guardarAlerta'])->name('alertas.guardar');
        Route::post('/alertas/temperatura', [AdministracionController::class, 'evaluarTemperatura'])->name('alertas.temperatura');
        Route::patch('/alertas/{alertaHelada}/cerrar', [AdministracionController::class, 'cerrarAlerta'])->name('alertas.cerrar');
        Route::delete('/alertas/{alertaHelada}', [AdministracionController::class, 'eliminarAlerta'])->name('alertas.eliminar');
        Route::get('/consultas', [AdministracionController::class, 'listarConsultas'])->name('consultas.indice');
        Route::patch('/consultas/{consulta}/leida', [AdministracionController::class, 'marcarConsultaLeida'])->name('consultas.leida');
        Route::get('/reportes', [AdministracionController::class, 'listarReportes'])->name('reportes.indice');
        Route::patch('/reportes/{reporteRiesgo}', [AdministracionController::class, 'revisarReporte'])->name('reportes.revisar');
        Route::delete('/reportes/{reporteRiesgo}', [AdministracionController::class, 'eliminarReporte'])->name('reportes.eliminar');
        Route::delete('/pedidos/{pedido}', [AdministracionController::class, 'eliminarPedido'])->name('pedidos.eliminar');
        Route::get('/refugios', [AdministracionController::class, 'listarRefugios'])->name('refugios.indice');
        Route::post('/refugios', [AdministracionController::class, 'guardarRefugio'])->name('refugios.guardar');
        Route::put('/refugios/{refugio}', [AdministracionController::class, 'actualizarRefugio'])->name('refugios.actualizar');
        Route::delete('/refugios/{refugio}', [AdministracionController::class, 'eliminarRefugio'])->name('refugios.eliminar');
    });
});
