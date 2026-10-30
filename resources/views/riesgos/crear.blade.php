@extends('layouts.app')
@section('titulo', 'Reportar una situación')
@section('content')
<section class="pagina-formulario"><a class="enlace-regreso" href="{{ route('panel') }}">← Volver a mi panel</a><div class="tarjeta-formulario"><span class="sobrelinea">Participación comunitaria</span><h1>Reportar una situación de riesgo</h1><p>El reporte quedará pendiente de revisión. No se publica como alerta automáticamente.</p>
<form method="post" action="{{ route('riesgos.guardar') }}">@csrf<label>Título breve<input name="titulo" value="{{ old('titulo') }}" maxlength="160" required></label><label>Descripción<textarea name="descripcion" rows="5" maxlength="3000" required>{{ old('descripcion') }}</textarea></label>
<div class="fila-formulario"><label>Latitud (opcional)<input type="number" name="latitud" step="any" min="-90" max="90" value="{{ old('latitud') }}"></label><label>Longitud (opcional)<input type="number" name="longitud" step="any" min="-180" max="180" value="{{ old('longitud') }}"></label></div>
<div class="nota-privacidad">No incluyas datos personales de terceros. Comparte solo la información necesaria para ubicar y comprender el riesgo.</div><div class="acciones-formulario"><a class="boton boton-claro" href="{{ route('panel') }}">Cancelar</a><button class="boton" type="submit">Enviar para revisión</button></div></form></div></section>
@endsection