@extends ('layouts.app')
@section ('titulo', 'Editar reporte comunitario')
@section ('content')
    <section class="pagina-formulario">
        <a class="enlace-regreso" href="{{ route('administracion.reportes.indice') }}">← Volver a revisar reportes</a>
        <div class="tarjeta-formulario">
            <span class="sobrelinea">Administración · Reporte pendiente</span>
            <h1>Editar reporte</h1>
            <p>Corrige los datos necesarios antes de aprobarlo y publicarlo como alerta.</p>

            <form method="post" action="{{ route('administracion.reportes.actualizar', $reporte) }}">
                @csrf
                @method ('put')
                <label>
                    Tipo de emergencia
                    <select name="tipo" data-tipo-emergencia required>
                        <option value="clima" @selected ($reporte->tipo === 'clima')>Clima</option>
                        <option value="refugio" @selected ($reporte->tipo === 'refugio')>Refugios</option>
                        <option value="otro" @selected ($reporte->tipo === 'otro')>Otra emergencia</option>
                    </select>
                </label>

                <div data-campo-tipo-emergencia @if ($reporte->tipo !== 'otro') hidden @endif>
                    <label>
                        ¿Qué tipo de emergencia?
                        <input
                            name="tipo_personalizado"
                            value="{{ $reporte->tipo_personalizado }}"
                            maxlength="100"
                        />
                    </label>
                </div>

                <label>
                    Título
                    <input name="titulo" value="{{ $reporte->titulo }}" maxlength="160" required />
                </label>

                <label>
                    Descripción
                    <textarea name="descripcion" rows="6" maxlength="3000" required>{{ $reporte->descripcion }}</textarea>
                </label>

                <label>
                    Prioridad de atención
                    <select name="prioridad" required>
                        <option value="alta" @selected ($reporte->prioridad === 'alta')>Alta · revisar primero</option>
                        <option value="media" @selected ($reporte->prioridad === 'media')>Media</option>
                        <option value="baja" @selected ($reporte->prioridad === 'baja')>Baja</option>
                    </select>
                    <small>Asigna la prioridad según la urgencia observada; por defecto los reportes nuevos son prioridad media.</small>
                </label>

                <label>
                    Comunidad
                    <select name="comunidad_id">
                        <option value="">Alerta general, sin comunidad asignada</option>
                        @foreach ($comunidades as $comunidad)
                            <option value="{{ $comunidad->id }}" @selected ($reporte->comunidad_id === $comunidad->id)>
                                {{ $comunidad->nombre }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="fila-formulario">
                    <label>
                        Latitud
                        <input
                            type="number"
                            name="latitud"
                            step="any"
                            min="-90"
                            max="90"
                            value="{{ $reporte->latitud }}"
                        />
                    </label>
                    <label>
                        Longitud
                        <input
                            type="number"
                            name="longitud"
                            step="any"
                            min="-180"
                            max="180"
                            value="{{ $reporte->longitud }}"
                        />
                    </label>
                </div>

                <label>
                    Lugar aproximado
                    <input
                        name="ubicacion_detallada"
                        value="{{ $reporte->ubicacion_detallada }}"
                        maxlength="500"
                        placeholder="Sector, localidad, distrito o provincia"
                    />
                </label>

                <div class="nota-privacidad">
                    Antes de aprobar, revisa que la descripción y la evidencia no expongan nombres, teléfonos, DNI u otros datos personales.
                    La fotografía se administra en la lista de reportes.
                </div>

                <div class="acciones-formulario">
                    <a class="boton boton-claro" href="{{ route('administracion.reportes.indice') }}">Cancelar</a>
                    <button class="boton" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>
    </section>
    <script
        src="{{ asset('js/tipo-emergencia.js') }}?v={{ filemtime(public_path('js/tipo-emergencia.js')) }}"
        defer
    ></script>
@endsection
