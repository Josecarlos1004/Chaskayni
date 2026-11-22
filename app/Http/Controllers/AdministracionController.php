<?php

namespace App\Http\Controllers;

use App\Models\AlertaHelada;
use App\Models\Comunidad;
use App\Models\ConsultaContacto;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Refugio;
use App\Models\ReporteRiesgo;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdministracionController extends Controller
{
    private function verificarAdministrador(): void
    {
        abort_unless(Auth::user()->esAdministrador(), 403, 'Esta sección es solo para administración.');
    }

    /** Resume los indicadores principales y enlaza las herramientas administrativas. */
    public function panel(): View
    {
        $this->verificarAdministrador();

        return view('administracion.panel', [
            'cantidadUsuarios' => Usuario::count(),
            'cantidadCuentasPendientes' => Schema::hasColumn('users', 'aprobado_en') ? Usuario::whereNull('aprobado_en')->count() : 0,
            'cantidadProductos' => Producto::where('estado', 'disponible')->count(),
            'cantidadPedidosPendientes' => Pedido::where('estado', 'pendiente')->count(),
            'cantidadReportesPendientes' => ReporteRiesgo::where('estado', 'pendiente')->count(),
            'cantidadAlertasActivas' => AlertaHelada::activas()->count(),
            'cantidadRefugiosActivos' => Refugio::where('activo', true)->count(),
        ]);
    }

    /** Lista cuentas para que administración pueda revisar roles y accesos. */
    public function listarUsuarios(): View
    {
        $this->verificarAdministrador();

        $consultaUsuarios = Usuario::with('comunidad');
        if (Schema::hasColumn('users', 'aprobado_en')) {
            $consultaUsuarios->orderBy('aprobado_en');
        }

        return view('administracion.usuarios', [
            'usuarios' => $consultaUsuarios->orderBy('name')->paginate(15),
            'comunidades' => Comunidad::orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    /** Muestra los mensajes recibidos para que administración pueda responderlos. */
    public function listarConsultas(): View
    {
        $this->verificarAdministrador();

        return view('administracion.consultas', [
            'consultas' => ConsultaContacto::latest()->paginate(15),
        ]);
    }

    public function marcarConsultaLeida(ConsultaContacto $consulta): RedirectResponse
    {
        $this->verificarAdministrador();
        $consulta->update(['leido_en' => $consulta->leido_en ?? now()]);

        return back()->with('success', 'La consulta quedó marcada como leída.');
    }

    /** Muestra todas las publicaciones, incluidas las ocultas, para administración. */
    public function listarProductos(): View
    {
        $this->verificarAdministrador();

        return view('administracion.productos', [
            'productos' => Producto::with(['vendedor', 'comunidad'])->latest()->paginate(12),
        ]);
    }

    /** Muestra todas las alertas, incluso las que ya fueron cerradas. */
    public function listarAlertas(): View
    {
        $this->verificarAdministrador();

        return view('administracion.alertas', [
            'alertas' => AlertaHelada::with(['comunidad', 'autor', 'reporteOrigen'])->latest()->paginate(15),
        ]);
    }

    /** Actualiza rol y acceso sin permitir que se desactive al último administrador. */
    public function actualizarUsuario(Request $request, Usuario $usuario): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'rol' => ['required', 'in:productor,comprador,administrador'],
            'activo' => ['required', 'boolean'],
            'aprobado' => ['required', 'boolean'],
            'comunidad_id' => ['nullable', Rule::exists('comunidades', 'id')],
        ]);

        $activo = (bool) $datos['activo'];
        $comunidadId = array_key_exists('comunidad_id', $datos) ? $datos['comunidad_id'] : $usuario->comunidad_id;
        if ((bool) $datos['aprobado'] && $datos['rol'] !== 'administrador'
            && (! $comunidadId || ! Comunidad::whereKey($comunidadId)->where('activo', true)->exists())) {
            return back()->withErrors(['comunidad_id' => 'Asigna una comunidad activa antes de aprobar esta cuenta.'])->withInput();
        }
        $datos['comunidad_id'] = $comunidadId;
        if ($usuario->is(Auth::user()) && ($datos['rol'] !== 'administrador' || ! $activo || ! (bool) $datos['aprobado'])) {
            return back()->withErrors(['cuenta' => 'No puedes quitarte el rol de administrador ni desactivar tu propia cuenta.']);
        }

        return DB::transaction(function () use ($usuario, $datos, $activo): RedirectResponse {
            // Serializa cambios de administración para proteger la última cuenta activa.
            $administradoresActivos = Usuario::where('rol', 'administrador')
                ->where('activo', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $seRetiraUltimoAdministrador = $usuario->rol === 'administrador'
                && $usuario->activo
                && ($datos['rol'] !== 'administrador' || ! $activo)
                && $administradoresActivos->count() <= 1;

            if ($seRetiraUltimoAdministrador) {
                return back()->withErrors(['cuenta' => 'Debe quedar al menos una cuenta administradora activa.']);
            }

            $usuario->update([
                'rol' => $datos['rol'],
                'comunidad_id' => $datos['comunidad_id'],
                'comunidad_propuesta' => (bool) $datos['aprobado'] ? null : $usuario->comunidad_propuesta,
                'distrito_propuesto' => (bool) $datos['aprobado'] ? null : $usuario->distrito_propuesto,
                'activo' => $activo,
                'aprobado_en' => (bool) $datos['aprobado'] ? ($usuario->aprobado_en ?? now()) : null,
            ]);

            return back()->with('success', 'Se actualizaron los permisos de la cuenta.');
        });
    }

    /** Elimina una cuenta solo si no tiene información vinculada que dependa de ella. */
    public function eliminarUsuario(Usuario $usuario): RedirectResponse
    {
        $this->verificarAdministrador();

        if ($usuario->is(Auth::user())) {
            return back()->withErrors(['cuenta' => 'No puedes eliminar la cuenta con la que tienes abierta esta sesión.']);
        }

        $administradoresActivos = Usuario::where('rol', 'administrador')->where('activo', true)->count();
        if ($usuario->rol === 'administrador' && $usuario->activo && $administradoresActivos <= 1) {
            return back()->withErrors(['cuenta' => 'No se puede eliminar la última cuenta administradora activa.']);
        }

        $tieneRegistros = $usuario->productos()->exists()
            || $usuario->pedidos()->exists()
            || ReporteRiesgo::where('reportante_id', $usuario->id)->exists()
            || AlertaHelada::where('creado_por', $usuario->id)->exists();

        if ($tieneRegistros) {
            return back()->withErrors(['cuenta' => 'Esta cuenta tiene publicaciones, pedidos o aportes vinculados. Desactívala para conservar el historial.']);
        }

        $usuario->delete();

        return back()->with('success', 'Se eliminó la cuenta.');
    }

    public function listarComunidades(): View
    {
        $this->verificarAdministrador();

        return view('administracion.comunidades', [
            'comunidades' => Comunidad::withCount(['usuarios', 'productos'])->orderBy('nombre')->get(),
        ]);
    }

    public function guardarComunidad(Request $request): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:comunidades,nombre'],
            'distrito' => ['nullable', 'string', 'max:120'],
            'provincia' => ['required', 'string', 'max:120'],
            'altitud_msnm' => ['nullable', 'integer', 'between:0,8000'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ]);

        Comunidad::create($datos);

        return back()->with('success', 'La comunidad quedó registrada.');
    }

    /** Edita datos de una comunidad y permite suspender nuevas asociaciones. */
    public function actualizarComunidad(Request $request, Comunidad $comunidad): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120', Rule::unique('comunidades', 'nombre')->ignore($comunidad->id)],
            'distrito' => ['nullable', 'string', 'max:120'],
            'provincia' => ['required', 'string', 'max:120'],
            'altitud_msnm' => ['nullable', 'integer', 'between:0,8000'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'activo' => ['required', 'boolean'],
        ]);
        $comunidad->update([...$datos, 'activo' => (bool) $datos['activo']]);

        return back()->with('success', 'Se actualizaron los datos de la comunidad.');
    }

    /** Elimina la comunidad; conserva las personas y publicaciones sin esa asociación. */
    public function eliminarComunidad(Comunidad $comunidad): RedirectResponse
    {
        $this->verificarAdministrador();
        $comunidad->delete();

        return back()->with('success', 'Se eliminó la comunidad. Los registros relacionados se conservaron sin comunidad asociada.');
    }

    public function crearAlerta(): View
    {
        $this->verificarAdministrador();

        return view('administracion.crear-alerta', [
            'comunidades' => Comunidad::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    /** Muestra una foto de alerta solo dentro del panel administrativo. */
    public function verFotoAlerta(AlertaHelada $alertaHelada): StreamedResponse
    {
        $this->verificarAdministrador();
        abort_unless($alertaHelada->foto && Storage::disk('public')->exists($alertaHelada->foto), 404);

        return Storage::disk('public')->response($alertaHelada->foto, 'foto-alerta-'.$alertaHelada->id, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    /** Solo las alertas manuales se editan aquí; los reportes se administran desde su revisión. */
    public function editarAlerta(AlertaHelada $alertaHelada): View
    {
        $this->verificarAdministrador();
        abort_if($alertaHelada->reporte_riesgo_id, 404);

        return view('administracion.editar-alerta', [
            'alerta' => $alertaHelada,
            'comunidades' => Comunidad::where('activo', true)->orWhereKey($alertaHelada->comunidad_id)->orderBy('nombre')->get(),
        ]);
    }

    public function actualizarAlerta(Request $request, AlertaHelada $alertaHelada): RedirectResponse
    {
        $this->verificarAdministrador();
        abort_if($alertaHelada->reporte_riesgo_id, 404);
        $datos = $request->validate([
            'tipo' => ['required', 'in:clima,refugio'],
            'titulo' => ['required', 'string', 'max:160'],
            'mensaje' => ['required', 'string', 'max:3000'],
            'nivel' => ['required', 'in:informacion,precaucion,urgente'],
            'comunidad_id' => ['nullable', Rule::exists('comunidades', 'id')->where(fn ($consulta) => $consulta->where('activo', true)->orWhere('id', $alertaHelada->comunidad_id))],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'quitar_foto' => ['nullable', 'boolean'],
        ]);
        $foto = $datos['foto'] ?? null;
        $quitarFoto = (bool) ($datos['quitar_foto'] ?? false);
        unset($datos['foto'], $datos['quitar_foto']);
        $rutaNueva = null;
        $rutaAnterior = $alertaHelada->foto;

        try {
            if ($foto) {
                $rutaNueva = $foto->store('alertas', 'public');
                if (! $rutaNueva) {
                    throw ValidationException::withMessages(['foto' => 'No se pudo guardar la fotografía. Inténtalo nuevamente.']);
                }
            }
            $datos['foto'] = $rutaNueva ?: ($quitarFoto ? null : $rutaAnterior);
            $alertaHelada->update($datos);
        } catch (Throwable $error) {
            if ($rutaNueva) {
                Storage::disk('public')->delete($rutaNueva);
            }
            throw $error;
        }
        if ($rutaAnterior && $rutaAnterior !== $alertaHelada->foto) {
            Storage::disk('public')->delete($rutaAnterior);
        }

        return redirect()->route('administracion.alertas.indice')->with('success', 'Se actualizaron los datos de la alerta.');
    }

    public function guardarAlerta(Request $request): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'tipo' => ['required', 'in:clima,refugio'],
            'titulo' => ['required', 'string', 'max:160'],
            'mensaje' => ['required', 'string', 'max:3000'],
            'nivel' => ['required', 'in:informacion,precaucion,urgente'],
            'comunidad_id' => ['nullable', Rule::exists('comunidades', 'id')->where('activo', true)],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $foto = $datos['foto'] ?? null;
        unset($datos['foto']);
        $rutaFoto = null;

        try {
            if ($foto) {
                $rutaFoto = $foto->store('alertas', 'public');
                if (! $rutaFoto) {
                    throw ValidationException::withMessages(['foto' => 'No se pudo guardar la fotografía. Inténtalo nuevamente.']);
                }
            }
            AlertaHelada::create([...$datos, 'foto' => $rutaFoto, 'creado_por' => Auth::id(), 'estado' => 'publicada']);
        } catch (Throwable $error) {
            if ($rutaFoto) {
                Storage::disk('public')->delete($rutaFoto);
            }
            throw $error;
        }

        return redirect()->route('alertas.indice')->with('success', 'La alerta fue publicada por administración.');
    }

    public function evaluarTemperatura(Request $request): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'comunidad_id' => ['required', Rule::exists('comunidades', 'id')->where('activo', true)],
            'temperatura_c' => ['required', 'numeric', 'between:-80,60'],
        ]);
        $temperatura = round((float) $datos['temperatura_c'], 2);

        if ($temperatura >= 0) {
            return back()->withErrors([
                'temperatura_c' => 'Con 0 °C o más no se genera una alerta de helada.',
            ])->withInput();
        }

        $riesgo = match (true) {
            $temperatura <= -5 => 'alto',
            $temperatura <= -2 => 'medio',
            default => 'bajo',
        };
        $nivelAviso = match ($riesgo) {
            'alto' => 'urgente',
            'medio' => 'precaucion',
            default => 'informacion',
        };

        AlertaHelada::create([
            'creado_por' => Auth::id(),
            'comunidad_id' => $datos['comunidad_id'],
            'tipo' => 'clima',
            'titulo' => 'Riesgo '.$riesgo.' de helada',
            'mensaje' => 'Temperatura prevista: '.$temperatura.' °C. Revisa las medidas de protección frente a heladas recomendadas para tu comunidad.',
            'nivel' => $nivelAviso,
            'temperatura_c' => $temperatura,
            'nivel_riesgo' => $riesgo,
            'fecha_inicio' => now(),
            'estado' => 'publicada',
        ]);

        return redirect()->route('alertas.indice')->with('success', 'Se evaluó la temperatura y se publicó la alerta.');
    }

    public function cerrarAlerta(AlertaHelada $alertaHelada): RedirectResponse
    {
        $this->verificarAdministrador();
        abort_unless($alertaHelada->estado === 'publicada', 404);

        $alertaHelada->update([
            'estado' => 'cerrada',
            'cerrado_en' => now(),
        ]);

        return back()->with('success', 'La alerta se cerró.');
    }

    /** Elimina una alerta individual publicada o cerrada por administración. */
    public function eliminarAlerta(AlertaHelada $alertaHelada): RedirectResponse
    {
        $this->verificarAdministrador();
        $rutaFoto = $alertaHelada->foto;
        $alertaHelada->delete();
        if ($rutaFoto) {
            Storage::disk('public')->delete($rutaFoto);
        }

        return back()->with('success', 'Se eliminó la alerta.');
    }

    public function listarReportes(): View
    {
        $this->verificarAdministrador();

        return view('administracion.reportes', [
            'reportes' => ReporteRiesgo::with(['reportante', 'comunidad'])->latest()->paginate(15),
        ]);
    }

    /** Muestra evidencia privada solo al administrador que revisa el reporte. */
    public function verEvidenciaReporte(ReporteRiesgo $reporteRiesgo): StreamedResponse
    {
        $this->verificarAdministrador();
        $rutaEvidencia = $reporteRiesgo->foto_evidencia;
        abort_unless($rutaEvidencia && Storage::disk('privado')->exists($rutaEvidencia), 404);

        return Storage::disk('privado')->response(
            $rutaEvidencia,
            'evidencia-reporte-'.$reporteRiesgo->id,
            [
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    /** Reemplaza evidencia privada solo mientras el reporte siga en revisión. */
    public function actualizarEvidenciaReporte(Request $request, ReporteRiesgo $reporteRiesgo): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate(['foto_evidencia' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        $rutaNueva = $datos['foto_evidencia']->store('evidencias-reportes', 'privado');
        if (! $rutaNueva) {
            throw ValidationException::withMessages(['foto_evidencia' => 'No se pudo guardar la fotografía. Inténtalo nuevamente.']);
        }

        try {
            $rutaAnterior = DB::transaction(function () use ($reporteRiesgo, $rutaNueva): ?string {
                $reporte = ReporteRiesgo::query()->lockForUpdate()->findOrFail($reporteRiesgo->id);
                if (! in_array($reporte->estado, ['pendiente', 'revisado', 'publicado'], true)) {
                    throw ValidationException::withMessages(['reporte' => 'Solo se puede cambiar la evidencia de reportes pendientes, en revisión o publicados.']);
                }
                $anterior = $reporte->foto_evidencia;
                $reporte->update(['foto_evidencia' => $rutaNueva]);

                return $anterior;
            });
        } catch (Throwable $error) {
            Storage::disk('privado')->delete($rutaNueva);
            throw $error;
        }
        if ($rutaAnterior && $rutaAnterior !== $rutaNueva) {
            Storage::disk('privado')->delete($rutaAnterior);
        }

        return back()->with('success', 'Se reemplazó la evidencia privada del reporte.');
    }

    public function revisarReporte(Request $request, ReporteRiesgo $reporteRiesgo): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate(['estado' => ['required', 'in:publicado,descartado']]);

        return DB::transaction(function () use ($reporteRiesgo, $datos): RedirectResponse {
            // Bloquea el reporte para evitar que dos aprobaciones creen alertas duplicadas.
            $reporte = ReporteRiesgo::query()->lockForUpdate()->findOrFail($reporteRiesgo->id);

            if (! in_array($reporte->estado, ['pendiente', 'revisado', 'publicado'], true)) {
                return back()->withErrors(['reporte' => 'Este reporte ya fue revisado.']);
            }

            if ($datos['estado'] === 'descartado') {
                $reporte->update(['estado' => 'descartado']);

                return back()->with('success', 'El reporte fue descartado.');
            }

            // La aprobación publica una alerta relacionada que conserva el origen del aviso.
            $reporte->alerta()->firstOrCreate([], [
                'creado_por' => Auth::id(),
                'comunidad_id' => $reporte->comunidad_id,
                'tipo' => in_array($reporte->tipo, ['clima', 'refugio'], true) ? $reporte->tipo : 'clima',
                'titulo' => $reporte->titulo,
                'mensaje' => $reporte->descripcion,
                'nivel' => 'precaucion',
                'fecha_inicio' => now(),
                'estado' => 'publicada',
            ]);
            $reporte->update(['estado' => 'publicado']);

            return back()->with('success', 'El reporte fue aprobado y publicado como alerta comunitaria.');
        });
    }

    /** Elimina un reporte individual después de que administración confirma la acción. */
    public function eliminarReporte(ReporteRiesgo $reporteRiesgo): RedirectResponse
    {
        $this->verificarAdministrador();
        $rutaEvidencia = $reporteRiesgo->foto_evidencia;
        $reporteRiesgo->delete();
        if ($rutaEvidencia) {
            Storage::disk('privado')->delete($rutaEvidencia);
        }

        return back()->with('success', 'Se eliminó el reporte comunitario.');
    }

    /** Cancela y elimina un pedido; devuelve existencias si seguía pendiente o aceptado. */
    public function eliminarPedido(Pedido $pedido): RedirectResponse
    {
        $this->verificarAdministrador();

        DB::transaction(function () use ($pedido): void {
            $pedidoBloqueado = Pedido::query()->lockForUpdate()->findOrFail($pedido->id);
            $detalles = $pedidoBloqueado->detalles()->orderBy('id')->lockForUpdate()->get();

            if (in_array($pedidoBloqueado->estado, ['pendiente', 'aceptado', 'preparando', 'en_camino'], true)) {
                foreach ($detalles as $detalle) {
                    $producto = Producto::query()->lockForUpdate()->find($detalle->producto_id);
                    if ($producto) {
                        $producto->cantidad = round((float) $producto->cantidad + (float) $detalle->cantidad, 2);
                        if ($producto->estado === 'agotado' && (float) $producto->cantidad > 0) {
                            $producto->estado = 'disponible';
                        }
                        $producto->save();
                    }
                }
            }

            $pedidoBloqueado->delete();
        });

        return back()->with('success', 'Se eliminó el pedido y se ajustaron las existencias cuando correspondía.');
    }

    public function listarRefugios(): View
    {
        $this->verificarAdministrador();

        return view('administracion.refugios', [
            'comunidades' => Comunidad::where('activo', true)->orderBy('nombre')->get(),
            'comunidadesTodas' => Comunidad::orderBy('nombre')->get(),
            'refugios' => Refugio::with('comunidad')->latest()->get(),
            'datosMapa' => ['refugios' => Refugio::where('activo', true)->whereNotNull('latitud')->whereNotNull('longitud')->get(['nombre', 'direccion', 'latitud', 'longitud'])],
        ]);
    }

    /** Actualiza un refugio y controla si sigue publicado en el mapa. */
    public function actualizarRefugio(Request $request, Refugio $refugio): RedirectResponse
    {
        $this->verificarAdministrador();
        $reglaComunidad = Rule::exists('comunidades', 'id');
        if ((string) $request->input('comunidad_id') !== (string) $refugio->comunidad_id) {
            $reglaComunidad->where('activo', true);
        }
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:160'],
            'direccion' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'capacidad' => ['required', 'integer', 'between:0,100000'],
            'comunidad_id' => ['nullable', $reglaComunidad],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'activo' => ['required', 'boolean'],
        ]);
        $refugio->update([...$datos, 'activo' => (bool) $datos['activo']]);

        return back()->with('success', 'Se actualizaron los datos del refugio.');
    }

    /** Elimina el refugio del mapa y del registro administrativo. */
    public function eliminarRefugio(Refugio $refugio): RedirectResponse
    {
        $this->verificarAdministrador();
        $refugio->delete();

        return back()->with('success', 'Se eliminó el refugio.');
    }

    public function guardarRefugio(Request $request): RedirectResponse
    {
        $this->verificarAdministrador();
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:160'],
            'direccion' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'capacidad' => ['required', 'integer', 'between:0,100000'],
            'comunidad_id' => ['nullable', Rule::exists('comunidades', 'id')->where('activo', true)],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        Refugio::create([...$datos, 'activo' => true]);

        return back()->with('success', 'El refugio quedó registrado.');
    }
}
