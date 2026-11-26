<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccesoController extends Controller
{
    public function formularioIngreso(): View
    {
        return view('auth.ingresar');
    }

    public function formularioRegistro(): View
    {
        return view('auth.registro', ['comunidades' => Comunidad::where('activo', true)->orderBy('nombre')->get()]);
    }

    public function ingresar(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('email', $credenciales['email'])->first();

        if (! $usuario || ! Hash::check($credenciales['password'], $usuario->password)) {
            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos.'])->onlyInput('email');
        }

        if (! $usuario->activo) {
            return back()->withErrors(['email' => 'Esta cuenta está desactivada. Contacta a administración.'])->onlyInput('email');
        }

        if (! $usuario->aprobado_en && $usuario->rol !== 'administrador') {
            return back()->withErrors(['email' => 'Tu cuenta está pendiente de aprobación. Te contactaremos al número registrado cuando sea revisada.'])->onlyInput('email');
        }

        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }

    public function registrar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'dni' => ['required', 'regex:/^[0-9]{8}$/', function ($atributo, $valor, $fallar): void {
                $huella = hash_hmac('sha256', (string) $valor, (string) config('app.key'));

                if (Usuario::where('dni_hash', $huella)->exists()) {
                    $fallar('Ya existe una cuenta registrada con ese DNI.');
                }
            }],
            'telefono' => ['required', 'regex:/^9[0-9]{8}$/'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'rol' => ['required', 'in:productor,comprador'],
            'tipo_comunidad' => ['required', 'in:existente,propuesta'],
            'comunidad_id' => ['exclude_unless:tipo_comunidad,existente', 'required', Rule::exists('comunidades', 'id')->where('activo', true)],
            'comunidad_propuesta' => ['exclude_unless:tipo_comunidad,propuesta', 'required', 'string', 'min:2', 'max:120'],
            'distrito_propuesto' => ['exclude_unless:tipo_comunidad,propuesta', 'nullable', 'string', 'max:120'],
        ], [
            'dni.regex' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Ya existe una cuenta registrada con ese DNI.',
            'telefono.regex' => 'Ingresa un celular peruano de 9 dígitos que empiece en 9.',
            'comunidad_id.required' => 'Selecciona una comunidad de la lista.',
            'comunidad_propuesta.required' => 'Escribe el nombre de tu comunidad.',
            'comunidad_id.exists' => 'La comunidad seleccionada ya no está disponible. Elige otra.',
        ]);

        Usuario::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'dni' => $datos['dni'],
            'telefono' => $datos['telefono'],
            'password' => Hash::make($datos['password']),
            'rol' => $datos['rol'],
            'comunidad_id' => $datos['comunidad_id'] ?? null,
            'comunidad_propuesta' => $datos['comunidad_propuesta'] ?? null,
            'distrito_propuesto' => $datos['distrito_propuesto'] ?? null,
            'activo' => true,
            'aprobado_en' => null,
        ]);

        return redirect()->route('login')->with('success', 'Tu solicitud de cuenta fue registrada. Podrás ingresar cuando administración revise tus datos y apruebe la cuenta.');
    }

    public function salir(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio')->with('success', 'Has cerrado sesión.');
    }
}
