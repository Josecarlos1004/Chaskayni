@extends('layouts.app')
@section('titulo', 'Administrar comunidades')
@section('content')
<section class="encabezado-pagina">
    <div>
        <span class="sobrelinea">Administración</span>
        <h1>Comunidades</h1>
        <p>Registra comunidades y su altitud cuando el dato esté disponible.</p>
    </div>
    <a class="boton" href="{{ route('administracion.alertas.crear') }}">+ Publicar alerta</a>
</section>

<section class="seccion seccion-superior cuadricula-administracion">
    <div class="tarjeta-formulario">
        <span class="sobrelinea">Nueva comunidad</span>
        <h2>Registrar comunidad</h2>
        <form method="post" action="{{ route('administracion.comunidades.guardar') }}">
            @csrf
            <label>Nombre<input name="nombre" value="{{ old('nombre') }}" maxlength="120" required></label>
            <div class="fila-formulario">
                <label>Distrito (opcional)<input name="distrito" value="{{ old('distrito') }}" maxlength="120"></label>
                <label>Provincia<input name="provincia" value="{{ old('provincia', 'Cusco') }}" maxlength="120" required></label>
            </div>
            <label>Altitud sobre el nivel del mar (m s. n. m., opcional)
                <input type="number" name="altitud_msnm" min="0" max="8000" step="1" value="{{ old('altitud_msnm') }}">
            </label>
            <small>Ingresa una altitud confirmada. Si no la conoces, déjala vacía.</small>
            <label>Descripción<textarea name="descripcion" rows="3" maxlength="2000">{{ old('descripcion') }}</textarea></label>
            <button class="boton" type="submit">Guardar comunidad</button>
        </form>
    </div>

    <div class="panel">
        <div class="encabezado-panel">
            <h2>Comunidades registradas</h2>
            <a class="enlace-texto" href="{{ route('administracion.reportes.indice') }}">Ver reportes →</a>
        </div>
        @forelse ($comunidades as $comunidad)
            <div class="fila-pedido">
                <div>
                    <strong>{{ $comunidad->nombre }}</strong>
                    <small>{{ $comunidad->distrito ?: $comunidad->provincia }} · {{ $comunidad->altitud_msnm ? $comunidad->altitud_msnm.' m s. n. m.' : 'Altitud no registrada' }} · {{ $comunidad->usuarios_count }} cuentas · {{ $comunidad->productos_count }} publicaciones</small>
                </div>
            </div>
        @empty
            <div class="vacio-en-linea"><p>Aún no hay comunidades. Registra información confirmada del equipo.</p></div>
        @endforelse
    </div>
</section>
@endsection