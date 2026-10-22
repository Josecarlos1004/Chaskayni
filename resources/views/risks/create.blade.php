@extends('layouts.app')
@section('title', 'Reportar una situación')
@section('content')
<section class="form-page"><a class="back-link" href="{{ route('dashboard') }}">← Volver a mi panel</a><div class="form-card"><span class="eyebrow">Participación comunitaria</span><h1>Reportar una situación de riesgo</h1><p>El reporte quedará pendiente de revisión. No se publica como alerta automáticamente.</p>
<form method="post" action="{{ route('risks.store') }}">@csrf<label>Título breve<input name="title" value="{{ old('title') }}" maxlength="160" required></label><label>Descripción<textarea name="description" rows="5" maxlength="3000" required>{{ old('description') }}</textarea></label>
<div class="form-row"><label>Latitud (opcional)<input type="number" name="latitude" step="any" min="-90" max="90" value="{{ old('latitude') }}"></label><label>Longitud (opcional)<input type="number" name="longitude" step="any" min="-180" max="180" value="{{ old('longitude') }}"></label></div>
<div class="privacy-note">No incluyas datos personales de terceros. Comparte solo la información necesaria para ubicar y comprender el riesgo.</div><div class="form-actions"><a class="button button-light" href="{{ route('dashboard') }}">Cancelar</a><button class="button" type="submit">Enviar para revisión</button></div></form></div></section>
@endsection