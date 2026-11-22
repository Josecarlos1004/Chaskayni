@extends('layouts.app')
@section('titulo', 'Administrar cuentas')
@section('content')
<section class="encabezado-pagina">
    <div>
        <span class="sobrelinea">Administración</span>
        <h1>Cuentas del sistema</h1>
        <p>Revisa la identidad, la comunidad y los permisos antes de aprobar cada cuenta.</p>
    </div>
    <a class="boton boton-contorno" href="{{ route('administracion.panel') }}">Volver al panel</a>
</section>
<section class="seccion seccion-superior">
    <div class="panel">
        @forelse ($usuarios as $usuario)
            <article class="detalle-pedido">
                <div class="encabezado-panel">
                    <div>
                        <h2>{{ $usuario->name }}</h2>
                        <small>Correo: {{ $usuario->email }} · DNI: {{ $usuario->dni ?: 'No registrado' }} · Teléfono:
                            @if ($usuario->telefono)
                                <a href="tel:{{ $usuario->telefono }}">{{ $usuario->telefono }}</a>
                            @else
                                No registrado
                            @endif
                        </small>
                        <small>Comunidad asignada: {{ $usuario->comunidad?->nombre ?: 'Sin comunidad asociada' }}</small>
                        @if ($usuario->comunidad_propuesta)
                            <p class="propuesta-comunidad-aviso">
                                <strong>Comunidad propuesta por la persona:</strong> {{ $usuario->comunidad_propuesta }}
                                @if ($usuario->distrito_propuesto)
                                    · Distrito: {{ $usuario->distrito_propuesto }}
                                @endif
                                <br><span>Verifica si ya existe o regístrala en Comunidades antes de aprobar.</span>
                            </p>
                        @endif
                    </div>
                    <span class="etiqueta-estado">{{ ! $usuario->aprobado_en ? 'Pendiente de aprobación' : ($usuario->activo ? 'Aprobada y activa' : 'Desactivada') }}</span>
                </div>
                <form class="formulario-en-linea formulario-cuenta" method="post" action="{{ route('administracion.usuarios.actualizar', $usuario) }}">
                    @csrf
                    @method('patch')
                    <label>Rol
                        <select name="rol" required>
                            <option value="comprador" @selected($usuario->rol === 'comprador')>Comprador</option>
                            <option value="productor" @selected($usuario->rol === 'productor')>Productor</option>
                            <option value="administrador" @selected($usuario->rol === 'administrador')>Administrador</option>
                        </select>
                    </label>
                    <label>Comunidad verificada
                        <select name="comunidad_id" aria-describedby="ayuda-comunidad-{{ $usuario->id }}">
                            <option value="">Sin comunidad asignada</option>
                            @foreach ($comunidades as $comunidad)
                                <option value="{{ $comunidad->id }}" @selected((string) $usuario->comunidad_id === (string) $comunidad->id)>
                                    {{ $comunidad->nombre }}{{ $comunidad->activo ? '' : ' (inactiva)' }}
                                </option>
                            @endforeach
                        </select>
                        <small id="ayuda-comunidad-{{ $usuario->id }}">Para aprobar a una persona, asígnale una comunidad activa.</small>
                    </label>
                    <label class="fila-casillas"><input type="hidden" name="activo" value="0"><input type="checkbox" name="activo" value="1" @checked($usuario->activo)> Cuenta activa</label>
                    <label class="fila-casillas"><input type="hidden" name="aprobado" value="0"><input type="checkbox" name="aprobado" value="1" @checked($usuario->aprobado_en)> Aprobar cuenta (identidad y comunidad verificadas)</label>
                    <button class="boton boton-pequeno" type="submit">Guardar cambios</button>
                </form>
                @if (! $usuario->is(auth()->user()))
                    <form method="post" action="{{ route('administracion.usuarios.eliminar', $usuario) }}" onsubmit="return confirm('¿Eliminar esta cuenta? Si tiene registros asociados, el sistema la conservará para proteger el historial.')">
                        @csrf
                        @method('delete')
                        <button class="boton boton-contorno boton-pequeno" type="submit">Eliminar cuenta</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="estado-vacio">
                <span>◎</span>
                <h3>No hay cuentas registradas</h3>
                <p>Las cuentas creadas aparecerán en esta lista.</p>
            </div>
        @endforelse
    </div>
    <div class="paginacion">{{ $usuarios->links() }}</div>
</section>
@endsection