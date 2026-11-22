@extends('layouts.app')
@section('titulo', 'Mapa comunitario')
@section('content')
<section class="encabezado-pagina">
    <div>
        <span class="sobrelinea">Prevención comunitaria</span>
        <h1>Mapa de refugios y riesgos</h1>
        <p>Consulta refugios activos y reportes aprobados por administración.</p>
    </div>
    @auth
        <a class="boton boton-contorno" href="{{ route('riesgos.crear') }}">Reportar una situación</a>
    @endauth
</section>
<section class="seccion seccion-superior">
    <div id="mapa-comunitario" class="mapa-comunitario" role="region" aria-label="Mapa de refugios y reportes aprobados"></div>
    <p id="estado-mapa" class="texto-atenuado" aria-live="polite"></p>
    <div class="leyenda-mapa">
        <span><i class="punto-refugio"></i> Refugio activo</span>
        <span><i class="punto-reporte"></i> Reporte aprobado</span>
    </div>
    <div class="cuadricula-mapa">
        <section class="panel">
            <h2>Refugios activos</h2>
            @forelse ($refugios as $refugio)
                <article class="fila-mapa">
                    <strong>{{ $refugio->nombre }}</strong>
                    <span>{{ $refugio->comunidad?->nombre ?? 'Comunidad no indicada' }}</span>
                    <small>{{ $refugio->direccion }} · Capacidad: {{ $refugio->capacidad }}</small>
                </article>
            @empty
                <p class="texto-atenuado">No hay refugios activos con ubicación registrada.</p>
            @endforelse
        </section>
        <section class="panel">
            <h2>Reportes aprobados</h2>
            @forelse ($reportes as $reporte)
                <article class="fila-mapa">
                    <strong>{{ $reporte->titulo }}</strong>
                    <span>{{ $reporte->comunidad?->nombre ?? 'Comunidad no indicada' }}</span>
                    <small>{{ $reporte->descripcion }}</small>
                    <a class="enlace-texto" href="{{ route('riesgos.detalle', $reporte) }}">Ver información y foto del incidente</a>
                </article>
            @empty
                <p class="texto-atenuado">Aún no hay reportes aprobados para mostrar.</p>
            @endforelse
        </section>
    </div>
</section>
<script>window.datosMapa = @json($datosMapa);</script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="{{ asset('js/mapa-comunitario.js') }}" defer></script>
@endsection
