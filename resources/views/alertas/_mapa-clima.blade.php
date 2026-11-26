@php
    if ($alerta->latitud !== null && $alerta->longitud !== null) {
        $latitudMapa = $alerta->latitud;
        $longitudMapa = $alerta->longitud;
    } elseif ($alerta->reporteOrigen?->latitud !== null && $alerta->reporteOrigen?->longitud !== null) {
        $latitudMapa = $alerta->reporteOrigen->latitud;
        $longitudMapa = $alerta->reporteOrigen->longitud;
    } elseif ($alerta->comunidad?->latitud !== null && $alerta->comunidad?->longitud !== null) {
        $latitudMapa = $alerta->comunidad->latitud;
        $longitudMapa = $alerta->comunidad->longitud;
    } else {
        $latitudMapa = null;
        $longitudMapa = null;
    }
@endphp

@if ($latitudMapa !== null && $longitudMapa !== null)
    @php
        $margenLatitud = 0.025;
        $margenLongitud = 0.025;
        $urlMapaClima = 'https://www.openstreetmap.org/export/embed.html?bbox='
            .((float) $longitudMapa - $margenLongitud).'%2C'
            .((float) $latitudMapa - $margenLatitud).'%2C'
            .((float) $longitudMapa + $margenLongitud).'%2C'
            .((float) $latitudMapa + $margenLatitud)
            .'&layer=mapnik&marker='.$latitudMapa.'%2C'.$longitudMapa;
    @endphp

    <div class="alerta-mapa-clima">
        <iframe
            title="Mapa de ubicación de la alerta climática {{ $alerta->titulo }}"
            loading="lazy"
            referrerpolicy="strict-origin-when-cross-origin"
            src="{{ $urlMapaClima }}"
        ></iframe>
        <div class="alerta-mapa-clima-resumen">
            <span aria-hidden="true">☁</span>
            <div>
                <strong>Ubicación de la alerta climática</strong>
                <small>
                    {{ $alerta->comunidad?->nombre ?: 'Ubicación del aviso' }}
                    @if (! is_null($alerta->temperatura_c))
                        · {{ number_format((float) $alerta->temperatura_c, 1) }} °C
                    @endif
                </small>
            </div>
        </div>
        <small class="alerta-mapa-clima-credito">Mapa © OpenStreetMap</small>
    </div>
@else
    <div class="alerta-sin-imagen">
        <span aria-hidden="true">☁</span>
        <strong>Aviso climático</strong>
        <small>No hay coordenadas registradas para mostrar el mapa.</small>
    </div>
@endif