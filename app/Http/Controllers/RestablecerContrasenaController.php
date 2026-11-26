<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Notifications\ContrasenaRestablecida;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class RestablecerContrasenaController extends Controller
{
    public function formularioSolicitud(): View
    {
        return view('auth.recuperar-contrasena');
    }

    public function enviarEnlace(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ]);

        // Se usa una respuesta genérica para no revelar qué correos tienen cuenta.
        Password::sendResetLink($datos);

        return back()->with('success', 'Si existe una cuenta con ese correo, recibirás instrucciones para cambiar la contraseña.');
    }

    public function formularioRestablecimiento(Request $request, string $token): View
    {
        return view('auth.restablecer-contrasena', [
            'token' => $token,
            'correo' => $request->query('email', ''),
        ]);
    }

    public function restablecer(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $usuarioRestablecido = null;
        $sesionesCerradas = false;

        $estado = Password::reset($datos, function (Usuario $usuario, string $contrasena) use (&$usuarioRestablecido, &$sesionesCerradas): void {
            DB::transaction(function () use ($usuario, $contrasena, &$sesionesCerradas): void {
                $usuario->forceFill([
                    'password' => Hash::make($contrasena),
                    'remember_token' => Str::random(60),
                ])->save();

                $sesionesCerradas = $this->invalidarSesiones($usuario);
            });

            event(new PasswordReset($usuario));
            $usuarioRestablecido = $usuario;
        });

        if ($estado === Password::PASSWORD_RESET) {
            if ($usuarioRestablecido) {
                try {
                    $usuarioRestablecido->notify(new ContrasenaRestablecida($sesionesCerradas));
                } catch (Throwable $error) {
                    Log::warning('No se pudo enviar el aviso de cambio de contraseña.', [
                        'usuario_id' => $usuarioRestablecido->getAuthIdentifier(),
                        'tipo_error' => $error::class,
                    ]);
                }
            }

            return redirect()->route('login')->with('success', 'Tu contraseña se actualizó. Ya puedes ingresar.');
        }

        return back()->withErrors([
            'email' => 'El enlace venció o no es válido. Solicita uno nuevo para continuar.',
        ]);
    }

    /** Cierra sesiones asociadas al usuario cuando se usa el almacenamiento MySQL de Laravel. */
    private function invalidarSesiones(Usuario $usuario): bool
    {
        if (config('session.driver') !== 'database') {
            return false;
        }

        $tabla = (string) config('session.table', 'sessions');
        $conexion = config('session.connection') ?: config('database.default');

        if (! Schema::connection($conexion)->hasColumn($tabla, 'user_id')) {
            return false;
        }

        DB::connection($conexion)
            ->table($tabla)
            ->where('user_id', $usuario->getAuthIdentifier())
            ->delete();

        return true;
    }
}