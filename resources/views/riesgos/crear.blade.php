@extends('layouts.app')
@section('titulo', 'Reportar una situación')
@section('content')
<section class="pagina-formulario">
    <a class="enlace-regreso" href="{{ route('panel') }}">← Volver a mi panel</a>
    <div class="tarjeta-formulario">
        <span class="sobrelinea">Participación comunitaria</span>
        <h1>Reportar una situación de riesgo</h1>
        <p>El reporte quedará pendiente de revisión y no aparecerá en el mapa público hasta que administración lo revise.</p>
<form method="post" action="{{ route('riesgos.guardar') }}">
    @csrf
    <label>Título breve
        <input name="titulo" value="{{ old('titulo') }}" maxlength="160" required>
    </label>
    <label>Descripción
        <textarea name="descripcion" rows="5" maxlength="3000" required>{{ old('descripcion') }}</textarea>
    </label>
@if($comunidades->isNotEmpty())<label>Comunidad
        <select name="comunidad_id">
            <option value="">Usar la comunidad de mi cuenta</option>
            @foreach($comunidades as $comunidad)
                <option value="{{ $comunidad->id }}" @selected(old('comunidad_id') == $comunidad->id)>{{ $comunidad->nombre }}</option>
            @endforeach
        </select>
    </label>@endif
    <p class="texto-atenuado">Si conoces el lugar, selecciónalo en el mapa. La ubicación es opcional.</p>
    <div class="mapa-selector" id="mapa-selector" role="region" aria-label="Selecciona la ubicación del reporte"></div>
    <p id="estado-coordenadas" class="texto-atenuado" aria-live="polite">Ubicación: aún no seleccionada</p>
    <div class="fila-formulario">
        <label>Latitud (opcional)
            <input id="latitud" type="number" name="latitud" step="any" min="-90" max="90" value="{{ old('latitud') }}">
        </label>
        <label>Longitud (opcional)
            <input id="longitud" type="number" name="longitud" step="any" min="-180" max="180" value="{{ old('longitud') }}">
        </label>
    </div>
    <div class="nota-privacidad">No incluyas datos personales de terceros. Comparte solo la información necesaria para ubicar y comprender el riesgo.</div>
    <div class="acciones-formulario">
        <a class="boton boton-claro" href="{{ route('panel') }}">Cancelar</a>
        <button class="boton" type="submit">Enviar para revisión</button>
    </div>
</form>
</div>
</section>
<script>window.datosMapa = @json($datosMapa);</script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="{{ asset('js/mapa-comunitario.js') }}" defer></script>
@endsection
