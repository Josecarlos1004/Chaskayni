<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\AlertasHeladasController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\MapaComunitarioController;
use App\Http\Controllers\MercadoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ReportesRiesgoController;
use App\Http\Controllers\RestablecerContrasenaController;
use App\Http\Middleware\VerificarCuentaActiva;
use Illuminate\Support\Facades\Route;

Route::get('/', [InicioController::class, 'index'])->name('inicio');
Route::get('/mercado', [MercadoController::class, 'index'])->name('mercado.indice');
Route::get('/productos/{producto}/imagen', [ProductosController::class, 'verImagenPublica'])->name('productos.imagen');
Route::get('/productos/imagenes/{imagen}', [ProductosController::class, 'verImagenAdicional'])->name('productos.imagenes.ver');
Route::get('/alertas', [AlertasHeladasController::class, 'index'])->name('alertas.indice');
Route::get('/alertas/{alertaHelada}/foto', [AlertasHeladasController::class, 'verFotoPublica'])->name('alertas.foto');
Route::get('/mapa-comunitario', [MapaComunitarioController::class, 'index'])->name('mapa.indice');
Route::get('/reportes/nuevo', [ReportesRiesgoController::class, 'create'])->name('riesgos.crear');
Route::post('/reportes', [ReportesRiesgoController::class, 'store'])->middleware('throttle:3,1')->name('riesgos.guardar');
Route::get('/reportes/ubicacion', [ReportesRiesgoController::class, 'buscarUbicacion'])
    ->middleware('throttle:30,1')
    ->name('riesgos.ubicacion');
Route::view('/privacidad', 'privacidad')->name('privacidad');
Route::view('/acerca-de', 'acerca')->name('acerca');
Route::view('/seguridad', 'seguridad')->name('seguridad');
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
    Route::get('/recuperar-contrasena', [RestablecerContrasenaController::class, 'formularioSolicitud'])
        ->name('password.request');
    Route::post('/recuperar-contrasena', [RestablecerContrasenaController::class, 'enviarEnlace'])
        ->middleware('throttle:3,1')
        ->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [RestablecerContrasenaController::class, 'formularioRestablecimiento'])
        ->name('password.reset');
    Route::post('/restablecer-contrasena', [RestablecerContrasenaController::class, 'restablecer'])
        ->middleware('throttle:3,1')
        ->name('password.update');
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
    Route::patch('/productos/{producto}/visibilidad', [ProductosController::class, 'actualizarVisibilidad'])->name('productos.visibilidad');
    Route::delete('/productos/{producto}', [ProductosController::class, 'destroy'])->name('productos.eliminar');
    Route::prefix('administracion')->name('administracion.')->group(function () {
        Route::get('/', [AdministracionController::class, 'panel'])->name('panel');
        Route::get('/estadisticas', [AdministracionController::class, 'estadisticas'])->name('estadisticas');
        Route::get('/productos', [AdministracionController::class, 'listarProductos'])->name('productos.indice');
        Route::get('/alertas', [AdministracionController::class, 'listarAlertas'])->name('alertas.indice');
        Route::get('/usuarios', [AdministracionController::class, 'listarUsuarios'])->name('usuarios.indice');
        Route::patch('/usuarios/{usuario}', [AdministracionController::class, 'actualizarUsuario'])->name('usuarios.actualizar');
        Route::delete('/usuarios/{usuario}', [AdministracionController::class, 'eliminarUsuario'])->name('usuarios.eliminar');
        Route::get('/comunidades', [AdministracionController::class, 'listarComunidades'])->name('comunidades.indice');
        Route::get('/comunidades/datos-ubicacion', [AdministracionController::class, 'datosUbicacionComunidad'])->middleware('throttle:20,1')->name('comunidades.ubicacion');
        Route::post('/comunidades/rapida', [AdministracionController::class, 'guardarComunidadRapida'])->middleware('throttle:10,1')->name('comunidades.rapida');
        Route::get('/comunidades/{comunidad}/clima', [AdministracionController::class, 'climaComunidad'])->middleware('throttle:20,1')->name('comunidades.clima');
        Route::get('/alertas/clima/ubicacion', [AdministracionController::class, 'climaUbicacion'])->middleware('throttle:20,1')->name('alertas.clima.ubicacion');
        Route::put('/comunidades/{comunidad}/coordenadas', [AdministracionController::class, 'actualizarCoordenadasComunidad'])->middleware('throttle:20,1')->name('comunidades.coordenadas');
        Route::post('/comunidades', [AdministracionController::class, 'guardarComunidad'])->name('comunidades.guardar');
        Route::put('/comunidades/{comunidad}', [AdministracionController::class, 'actualizarComunidad'])->name('comunidades.actualizar');
        Route::delete('/comunidades/{comunidad}', [AdministracionController::class, 'eliminarComunidad'])->name('comunidades.eliminar');
        Route::get('/alertas/crear', [AdministracionController::class, 'crearAlerta'])->name('alertas.crear');
        Route::post('/alertas', [AdministracionController::class, 'guardarAlerta'])->name('alertas.guardar');
        Route::post('/alertas/temperatura', [AdministracionController::class, 'evaluarTemperatura'])->name('alertas.temperatura');
        Route::get('/alertas/{alertaHelada}/foto-administracion', [AdministracionController::class, 'verFotoAlerta'])->name('alertas.foto');
        Route::get('/alertas/{alertaHelada}/editar', [AdministracionController::class, 'editarAlerta'])->name('alertas.editar');
        Route::put('/alertas/{alertaHelada}', [AdministracionController::class, 'actualizarAlerta'])->name('alertas.actualizar');
        Route::patch('/alertas/{alertaHelada}/cerrar', [AdministracionController::class, 'cerrarAlerta'])->name('alertas.cerrar');
        Route::delete('/alertas/{alertaHelada}', [AdministracionController::class, 'eliminarAlerta'])->name('alertas.eliminar');
        Route::get('/consultas', [AdministracionController::class, 'listarConsultas'])->name('consultas.indice');
        Route::patch('/consultas/{consulta}/leida', [AdministracionController::class, 'marcarConsultaLeida'])->name('consultas.leida');
        Route::get('/reportes', [AdministracionController::class, 'listarReportes'])->name('reportes.indice');
        Route::get('/reportes/{reporteRiesgo}/editar', [AdministracionController::class, 'editarReporte'])->name('reportes.editar');
        Route::put('/reportes/{reporteRiesgo}', [AdministracionController::class, 'actualizarReporte'])->name('reportes.actualizar');
        Route::get('/reportes/{reporteRiesgo}/evidencia', [AdministracionController::class, 'verEvidenciaReporte'])->name('reportes.evidencia');
        Route::patch('/reportes/{reporteRiesgo}/evidencia', [AdministracionController::class, 'actualizarEvidenciaReporte'])->name('reportes.evidencia.actualizar');
        Route::patch('/reportes/{reporteRiesgo}', [AdministracionController::class, 'revisarReporte'])->name('reportes.revisar');
        Route::delete('/reportes/{reporteRiesgo}', [AdministracionController::class, 'eliminarReporte'])->name('reportes.eliminar');
        Route::delete('/pedidos/{pedido}', [AdministracionController::class, 'eliminarPedido'])->name('pedidos.eliminar');
        Route::get('/refugios', [AdministracionController::class, 'listarRefugios'])->name('refugios.indice');
        Route::post('/refugios', [AdministracionController::class, 'guardarRefugio'])->name('refugios.guardar');
        Route::put('/refugios/{refugio}', [AdministracionController::class, 'actualizarRefugio'])->name('refugios.actualizar');
        Route::delete('/refugios/{refugio}', [AdministracionController::class, 'eliminarRefugio'])->name('refugios.eliminar');
    });
});

// Los reportes solo se consultan públicamente después de su aprobación.
Route::get('/reportes/{reporteRiesgo}/foto', [ReportesRiesgoController::class, 'verFotoPublica'])->name('riesgos.foto');
Route::get('/reportes/{reporteRiesgo}', [ReportesRiesgoController::class, 'detalle'])->name('riesgos.detalle');
