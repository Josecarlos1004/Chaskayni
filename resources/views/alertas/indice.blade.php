@extends('layouts.app')
@section('titulo', 'Alertas comunitarias')
@section('content')
<section class="encabezado-pagina">
    <div>
        <span class="sobrelinea">Información comunitaria</span>
        <h1>Alertas y avisos</h1>
        <p>Consulta avisos vigentes registrados por el equipo del proyecto.</p>
    </div>
</section>
<section class="seccion seccion-superior">
    @if ($alertas->isNotEmpty())
        <div class="cuadricula-alertas cuadricula-alertas-amplia">
            @foreach ($alertas as $alerta)
                <article class="tarjeta-alerta">
                    <span class="nivel-alerta">{{ ucfirst($alerta->nivel) }}</span>
                    @if ($alerta->nivel_riesgo)
                        <span class="etiqueta-estado">Riesgo {{ $alerta->nivel_riesgo }}</span>
                    @endif
                    <h2>{{ $alerta->titulo }}</h2>
                    <p>{{ $alerta->mensaje }}</p>
                    @if (! is_null($alerta->temperatura_c))
                        <strong>Temperatura prevista: {{ number_format((float) $alerta->temperatura_c, 1) }} °C</strong>
                    @endif
                    <div class="pie-alerta">
                        <span>{{ $alerta->comunidad?->nombre ?: 'Alerta general' }}</span>
                        <span>{{ $alerta->created_at->format('d/m/Y') }}</span>
                    </div>
                    @if ($alerta->fecha_fin)
                        <small>Vigente hasta {{ $alerta->fecha_fin->format('d/m/Y H:i') }}</small>
                    @endif
                    @auth
                        @if (auth()->user()->esAdministrador())
                            <form method="post" action="{{ route('administracion.alertas.cerrar', $alerta) }}" class="acciones-tarjeta">
                                @csrf
                                @method('patch')
                                <button class="boton boton-sutil boton-pequeno" type="submit">Cerrar alerta</button>
                            </form>
                            <form method="post" action="{{ route('administracion.alertas.eliminar', $alerta) }}" class="acciones-tarjeta" onsubmit="return confirm('¿Eliminar definitivamente esta alerta?')">
                                @csrf
                                @method('delete')
                                <button class="boton boton-contorno boton-pequeno" type="submit">Eliminar alerta</button>
                            </form>
                        @endif
                    @endauth
                </article>
            @endforeach
        </div>
        <div class="paginacion">{{ $alertas->links() }}</div>
    @else
        <div class="estado-vacio"><span>⌁</span><h3>No hay alertas vigentes</h3><p>Cuando el equipo publique un aviso, lo encontrarás aquí.</p></div>
    @endif
</section>
@endsection