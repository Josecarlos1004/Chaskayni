@extends('layouts.app')
@section('titulo', 'Administrar alertas')
@section('content')
<section class="encabezado-pagina">
    <div><span class="sobrelinea">Administración</span><h1>Alertas registradas</h1><p>Consulta avisos vigentes y cerrados, y administra cada registro.</p></div>
    <a class="boton" href="{{ route('administracion.alertas.crear') }}">Publicar alerta</a>
</section>
<section class="seccion seccion-superior">
    @forelse ($alertas as $alerta)
        <article class="tarjeta-alerta">
            <span class="nivel-alerta">{{ ucfirst($alerta->nivel) }}</span><span class="etiqueta-estado">{{ $alerta->tipo === "refugio" ? "Refugios" : "Clima" }}</span>
            <span class="etiqueta-estado">{{ ucfirst($alerta->estado) }}</span>
            <h2>{{ $alerta->titulo }}</h2>
            <p>{{ $alerta->mensaje }}</p>
            <div class="pie-alerta">
                <span>{{ $alerta->comunidad?->nombre ?: 'Alerta general' }} · {{ $alerta->autor?->name ?: 'Autor no disponible' }}</span>
                <span>{{ $alerta->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="acciones-tarjeta">
                @if ($alerta->estado === 'publicada')
                    <form method="post" action="{{ route('administracion.alertas.cerrar', $alerta) }}" onsubmit="return confirm('¿Cerrar esta alerta?')">
                        @csrf @method('patch')
                        <button class="boton boton-sutil boton-pequeno" type="submit">Cerrar alerta</button>
                    </form>
                @endif
                <form method="post" action="{{ route('administracion.alertas.eliminar', $alerta) }}" onsubmit="return confirm('¿Eliminar definitivamente esta alerta?')">
                    @csrf @method('delete')
                    <button class="boton boton-contorno boton-pequeno" type="submit">Eliminar alerta</button>
                </form>
            </div>
        </article>
    @empty
        <div class="estado-vacio"><span>⌁</span><h3>No hay alertas registradas</h3><p>Las alertas publicadas aparecerán aquí.</p></div>
    @endforelse
    <div class="paginacion">{{ $alertas->links() }}</div>
</section>
@endsection