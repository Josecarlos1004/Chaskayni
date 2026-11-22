<?php

namespace App\Http\Controllers;

use App\Models\ConsultaContacto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactoController extends Controller
{
    public function formulario(): View
    {
        abort_if(auth()->check() && auth()->user()->esAdministrador(), 404);

        return view('contacto.formulario');
    }

    public function guardar(Request $request): RedirectResponse
    {
        abort_if(auth()->check() && auth()->user()->esAdministrador(), 404);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['nullable', 'required_without:telefono', 'email', 'max:190'],
            'telefono' => ['nullable', 'required_without:correo', 'string', 'max:30'],
            'asunto' => ['required', 'string', 'max:160'],
            'mensaje' => ['required', 'string', 'max:3000'],
        ]);

        ConsultaContacto::create($datos);

        return redirect()->route('contacto.formulario')->with('success', 'Tu consulta se envió al equipo de Chaskayni.');
    }
}
