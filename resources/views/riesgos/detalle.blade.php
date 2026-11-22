@extends ('layouts.app')
@section ('titulo', $reporte->titulo)
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Reporte aprobado por administración</span>
            <h1>{{ $reporte->titulo }}</h1>
            <p>Información del incidente compartida con la comunidad.</p>
        </div>
        <a class="boton boton-contorno" href="{{ route('mapa.indice') }}">Volver al mapa</a>
    </section>
    <section class="seccion seccion-superior">
        <article class="detalle-reporte">
            <div class="detalle-reporte-datos">
                <span class="etiqueta-estado">{{ $reporte->tipo === 'refugio' ? 'Refugios' : 'Clima' }}</span>
                <span>{{ $reporte->comunidad?->nombre ?? 'Comunidad no indicada' }}</span>
                <span>Reportado el {{ $reporte->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <h2>Descripción del incidente</h2>
            <p class="detalle-reporte-descripcion">{{ $reporte->descripcion }}</p>
            @if ($reporte->latitud !== null && $reporte->longitud !== null)
                <p><strong>Ubicación del incidente:</strong> {{ $reporte->latitud }}, {{ $reporte->longitud }}</p>
            @endif
            @if ($reporte->foto_evidencia)
                <figure class="foto-reporte-publico">
                    <img
                        src="{{ route('riesgos.foto', $reporte) }}"
                        alt="Fotografía del incidente reportado"
                        loading="lazy"
                    />
                    <figcaption>Fotografía del incidente aprobada para compartir con la comunidad.</figcaption>
                </figure>
            @else
                <p class="nota-privacidad">Este reporte fue aprobado sin una fotografía de evidencia.</p>
            @endif
            @if ($reporte->alerta?->estado === 'publicada')
                <p class="nota-comunidad">Este reporte también dio origen a una alerta comunitaria.</p>
            @endif
            <p class="nota-privacidad">No se muestran el nombre, los datos de contacto ni la identidad de quien envió el reporte.</p>
        </article>
    </section>
@endsection
