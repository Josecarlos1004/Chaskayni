@extends('layouts.app')
@section('titulo', 'Publicar alerta')
@section('content')
<section class="pagina-formulario">
    <a class="enlace-regreso" href="{{ route('administracion.comunidades.indice') }}">← Administración</a>

    <div class="tarjeta-formulario">
        <span class="sobrelinea">Alerta por temperatura</span>
        <h1>Evaluar riesgo de helada</h1>
        <p>Ingresa la temperatura prevista para la madrugada. El sistema calcula el nivel de riesgo con ese dato; no consulta un servicio meteorológico.</p>
        <form method="post" action="{{ route('administracion.alertas.temperatura') }}">
            @csrf
            <label>Comunidad
                <select name="comunidad_id" required>
                    <option value="">Selecciona una comunidad</option>
                    @foreach ($comunidades as $comunidad)
                        <option value="{{ $comunidad->id }}" @selected((string) old('comunidad_id') === (string) $comunidad->id)>
                            {{ $comunidad->nombre }}{{ $comunidad->altitud_msnm ? ' · '.$comunidad->altitud_msnm.' m s. n. m.' : '' }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>Temperatura prevista (°C)
                <input type="number" name="temperatura_c" min="-80" max="60" step="0.5" value="{{ old('temperatura_c') }}" required>
            </label>
            <small>Menos de 0 °C: riesgo bajo. Desde −2 °C: medio. Desde −5 °C: alto.</small>
            <div class="acciones-formulario">
                <a class="boton boton-claro" href="{{ route('alertas.indice') }}">Cancelar</a>
                <button class="boton" type="submit">Evaluar y publicar alerta</button>
            </div>
        </form>
    </div>

    <div class="tarjeta-formulario formulario-alerta-manual">
        <span class="sobrelinea">Aviso verificado</span>
        <h2>Publicar alerta manual</h2>
        <p>Usa este formulario para avisos revisados que no dependen de una temperatura.</p>
        <form method="post" action="{{ route('administracion.alertas.guardar') }}">
            @csrf
            <label>Tipo de aviso<select name="tipo" required><option value="clima" @selected(old('tipo', 'clima') === 'clima')>Clima</option><option value="refugio" @selected(old('tipo') === 'refugio')>Refugios</option></select></label>
            <label>Título<input name="titulo" value="{{ old('titulo') }}" maxlength="160" required></label>
            <label>Mensaje<textarea name="mensaje" rows="4" maxlength="3000" required>{{ old('mensaje') }}</textarea></label>
            <div class="fila-formulario">
                <label>Nivel<select name="nivel" required><option value="informacion">Información</option><option value="precaucion">Precaución</option><option value="urgente">Urgente</option></select></label>
                <label>Comunidad<select name="comunidad_id"><option value="">Todas las comunidades</option>@foreach ($comunidades as $comunidad)<option value="{{ $comunidad->id }}">{{ $comunidad->nombre }}</option>@endforeach</select></label>
            </div>
            <div class="fila-formulario">
                <label>Inicio de vigencia (opcional)<input type="datetime-local" name="fecha_inicio" value="{{ old('fecha_inicio') }}"></label>
                <label>Fin de vigencia (opcional)<input type="datetime-local" name="fecha_fin" value="{{ old('fecha_fin') }}"></label>
            </div>
            <div class="acciones-formulario">
                <a class="boton boton-claro" href="{{ route('administracion.comunidades.indice') }}">Volver</a>
                <button class="boton" type="submit">Publicar alerta manual</button>
            </div>
        </form>
    </div>
</section>
@endsection