<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccesoController extends Controller
{
    public function formularioIngreso(): View
    {
        return view('auth.ingresar');
    }

    public function formularioRegistro(): View
    {
        return view('auth.registro', ['comunidades' => Comunidad::orderBy('nombre')->get()]);
    }

    public function ingresar(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credenciales, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }

    public function registrar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'rol' => ['required', 'in:productor,comprador'],
            'comunidad_id' => ['nullable', 'exists:comunidades,id'],
        ]);

        $usuario = Usuario::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
            'rol' => $datos['rol'],
            'comunidad_id' => $datos['comunidad_id'] ?? null,
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('panel')->with('success', 'Tu cuenta ya está lista.');
    }

    public function salir(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio')->with('success', 'Has cerrado sesión.');
    }
}
