@extends ('layouts.app')
@section ('titulo', 'Reportar una situación')
@section ('content')
    <section class="pagina-formulario">
        @php($esAdministrador = auth()->user()?->esAdministrador() ?? false)
        <a class="enlace-regreso" href="{{ auth()->check() ? route('panel') : route('alertas.indice') }}">← {{ auth()->check() ? 'Volver a mi panel' : 'Volver a alertas' }}</a>
        <div class="tarjeta-formulario">
            <span class="sobrelinea">Participación comunitaria</span>
            <h1>{{ $esAdministrador ? 'Publicar situación comunitaria' : (auth()->check() ? 'Enviar aviso comunitario' : 'Reportar una emergencia') }}</h1>
            @if ($esAdministrador)
                <p>Como administrador, puedes publicar directamente una situación comunitaria. Revisa que el texto y la evidencia no incluyan datos personales.</p>
            @elseif (! auth()->check())
                <p>Puedes reportar un accidente, una emergencia climática o una necesidad de refugio sin crear una cuenta. Administración revisará el aviso antes de publicarlo.</p>
            @else
                <p>Productores y consumidores pueden reportar clima, refugios u otra emergencia. El aviso quedará pendiente hasta la aprobación de administración.</p>
            @endif
            <form method="post" enctype="multipart/form-data" action="{{ route('riesgos.guardar') }}">
                @csrf
                <label
                    >Tipo de emergencia
                    <select name="tipo" data-tipo-emergencia required>
                        <option value="clima" @selected (old('tipo', 'clima') === 'clima')>Clima</option>
                        <option value="refugio" @selected (old('tipo') === 'refugio')>Refugios</option>
                        <option value="otro" @selected (old('tipo') === 'otro')>Otra emergencia</option>
                    </select>
                </label>
                <div data-campo-tipo-emergencia @if (old('tipo', 'clima') !== 'otro') hidden @endif>
                    <label
                        >¿Qué tipo de emergencia?
                        <input name="tipo_personalizado" value="{{ old('tipo_personalizado') }}" maxlength="100" />
                    </label>
                </div>
                <label
                    >Título breve
                    <input name="titulo" value="{{ old('titulo') }}" maxlength="160" required />
                </label>
                <label
                    >Descripción
                    <textarea name="descripcion" rows="5" maxlength="3000" required>{{ old('descripcion') }}</textarea>
                </label>
                <label class="evidencia-campo"
                    >{{ auth()->check() ? 'Fotografía de evidencia (opcional)' : 'Fotografía de evidencia (obligatoria)' }}
                    <input
                        type="file"
                        name="foto_evidencia"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        @required (! auth()->check())
                        aria-describedby="ayuda-evidencia"
                    />
                    <small id="ayuda-evidencia">
                        JPG, PNG o WebP; máximo 5 MB. @if (! auth()->check()) La fotografía es obligatoria para reportar sin cuenta. @endif Adjunta solo una foto del incidente, sin rostros ni datos personales.
                        @if ($esAdministrador)
                            La fotografía se publicará junto con la alerta.
                        @else
                            Administración la revisará y, si aprueba el reporte, se mostrará en su detalle público.
                        @endif
                    </small>
                </label>
                @if ($comunidades->isNotEmpty())
                    <label
                        >Comunidad
                        <select name="comunidad_id">
                            <option value="">Usar la comunidad de mi cuenta</option>
                            @foreach ($comunidades as $comunidad)
                                <option value="{{ $comunidad->id }}" @selected (old('comunidad_id') == $comunidad->id)>
                                    {{ $comunidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <p class="texto-atenuado">{{ auth()->check() ? 'Si conoces el lugar, selecciónalo en el mapa. La ubicación es opcional.' : 'Para un reporte anónimo debes compartir la ubicación actual desde el botón GPS.' }}</p>
                <div
                    class="mapa-selector"
                    id="mapa-selector"
                    data-requiere-gps="{{ auth()->check() ? 'false' : 'true' }}"
                    data-ubicacion-url="{{ route('riesgos.ubicacion') }}"
                    role="region"
                    aria-label="Selecciona la ubicación del reporte"
                ></div>
                <small class="texto-atenuado">El lugar se consulta mediante Photon y datos de OpenStreetMap; el nombre encontrado es aproximado.</small>
                <div class="ubicacion-seleccionada" aria-live="polite">
                    <p id="estado-coordenadas" class="texto-atenuado">{{ auth()->check() ? 'Haz clic en el mapa para señalar el lugar del incidente.' : 'La ubicación GPS actual es obligatoria para reportar sin cuenta.' }}</p>
                    <p id="nombre-ubicacion" class="texto-atenuado" hidden></p>
                </div>
                @guest
                    <div class="acciones-tarjeta">
                        <button class="boton boton-contorno" id="usar-ubicacion-gps" type="button">Usar mi ubicación actual (GPS)</button>
                        <small id="estado-gps" role="status">Tu navegador solicitará permiso para compartir la ubicación de este dispositivo.</small>
                    </div>
                    <input id="gps-confirmado" type="hidden" name="gps_confirmado" value="{{ old('gps_confirmado') }}" />
                @endguest
                <div class="fila-formulario">
                    <label
                        >{{ auth()->check() ? 'Latitud (opcional)' : 'Latitud GPS actual (obligatoria)' }}
                        <input
                            id="latitud"
                            type="number"
                            name="latitud"
                            step="any"
                            min="-90"
                            max="90"
                            @required (! auth()->check())
                            value="{{ old('latitud') }}"
                        />
                    </label>
                    <label
                        >{{ auth()->check() ? 'Longitud (opcional)' : 'Longitud GPS actual (obligatoria)' }}
                        <input
                            id="longitud"
                            type="number"
                            name="longitud"
                            step="any"
                            min="-180"
                            max="180"
                            @required (! auth()->check())
                            value="{{ old('longitud') }}"
                        />
                    </label>
                </div>
                <input id="ubicacion-detallada" type="hidden" name="ubicacion_detallada" value="{{ old('ubicacion_detallada') }}" />
                <small class="texto-atenuado">El nombre del lugar es aproximado. Si no se encuentra, se guardarán las coordenadas para que administración pueda ubicarlo.</small>
                <div class="nota-privacidad">
                    No incluyas nombres, teléfonos, DNI ni otros datos personales en la descripción o la foto. Comparte
                    solo la información necesaria para comprender el incidente. Para reportes anónimos, la foto y la ubicación GPS se compartirán con administración y podrán mostrarse a la comunidad si el reporte se aprueba.
                </div>
                <div class="acciones-formulario">
                    <a class="boton boton-claro" href="{{ auth()->check() ? route('panel') : route('alertas.indice') }}">Cancelar</a>
                    <button class="boton" type="submit">{{ $esAdministrador ? 'Publicar situación' : (auth()->check() ? 'Enviar para revisión' : 'Enviar reporte') }}</button>
                </div>
            </form>
        </div>
    </section>
    <script>
        window.datosMapa = @json ($datosMapa);
    </script>
    <script src="{{ asset('js/tipo-emergencia.js') }}?v={{ filemtime(public_path('js/tipo-emergencia.js')) }}" defer></script>
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    <script src="{{ asset('js/mapa-comunitario.js') }}?v={{ filemtime(public_path('js/mapa-comunitario.js')) }}" defer></script>
@endsection
