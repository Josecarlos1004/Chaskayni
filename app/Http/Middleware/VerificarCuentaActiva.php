<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarCuentaActiva
{
    /** Cierra las sesiones de cuentas que hayan sido desactivadas. */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();
        if ($usuario && (! $usuario->activo || ($usuario->rol !== 'administrador' && ! $usuario->aprobado_en))) {
            $mensaje = ! $usuario->activo
                ? 'Esta cuenta está desactivada. Contacta a administración.'
                : 'Esta cuenta aún está pendiente de aprobación.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => $mensaje]);
        }

        return $next($request);
    }
}
