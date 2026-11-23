<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

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

        $estado = Password::reset($datos, function (Usuario $usuario, string $contrasena): void {
            $usuario->forceFill([
                'password' => Hash::make($contrasena),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($usuario));
        });

        if ($estado === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Tu contraseña se actualizó. Ya puedes ingresar.');
        }

        return back()->withErrors([
            'email' => 'El enlace venció o no es válido. Solicita uno nuevo para continuar.',
        ]);
    }
}