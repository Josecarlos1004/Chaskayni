@extends ('layouts.app')
@section ('titulo', 'Publicar alerta')
@section ('content')
    <section class="pagina-formulario">
        <a class="enlace-regreso" href="{{ route('administracion.comunidades.indice') }}">← Administración</a>

        <div id="seccion-clima" class="tarjeta-formulario">
            <span class="sobrelinea">Alerta por temperatura</span>
            <h1>Evaluar riesgo de helada</h1>
            <p>Ingresa la temperatura prevista para la madrugada. El pronóstico cercano es orientativo. Puedes usar su mínima de las próximas 24 horas o ingresar la temperatura manualmente.</p>
            <nav class="acciones-formulario" aria-label="Secciones de publicación">
                <a class="boton boton-claro" href="#seccion-clima">Clima y heladas</a>
                <a class="boton boton-claro" href="#seccion-otras-emergencias">Otras emergencias y refugios</a>
            </nav>
            <details class="registro-comunidad-rapida">
                <summary>¿Falta una comunidad? Registrar una nueva</summary>
                <form method="post" action="{{ route('administracion.comunidades.rapida') }}" data-registro-comunidad data-formulario-comunidad data-url-datos-ubicacion="{{ route('administracion.comunidades.ubicacion') }}">
                    @csrf
                    @include ('administracion.partials.campos-comunidad', [
                        'nombre' => old('nombre'),
                        'distrito' => old('distrito'),
                        'provincia' => old('provincia', 'Cusco'),
                        'altitud' => old('altitud_msnm'),
                        'latitud' => old('latitud'),
                        'longitud' => old('longitud'),
                    ])                    <button class="boton boton-pequeno" type="submit">Registrar y seleccionar comunidad</button>
                    <p class="estado-registro-comunidad" data-registro-estado role="status" aria-live="polite" hidden></p>
                </form>
            </details>
            <form method="post" action="{{ route('administracion.alertas.temperatura') }}">
                @csrf
                <div class="flujo-clima-comunidad">
                    <label>Comunidad
                        <select name="comunidad_id" data-seleccionar-comunidad required>
                            <option value="">Selecciona una comunidad</option>
                            @foreach ($comunidades as $comunidad)
                                <option
                                    value="{{ $comunidad->id }}"
                                    data-latitud="{{ $comunidad->latitud }}"
                                    data-longitud="{{ $comunidad->longitud }}"
                                    @selected ((string) old('comunidad_id', session('comunidadCreadaId')) === (string) $comunidad->id)
                                >
                                    {{ $comunidad->nombre }}{{ $comunidad->altitud_msnm ? ' · '.$comunidad->altitud_msnm.' m s. n. m.' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <section
                        class="ubicacion-clima-alerta"
                        aria-labelledby="titulo-ubicacion-clima"
                        data-ubicacion-clima
                        data-url-guardar="{{ route('administracion.comunidades.coordenadas', ['comunidad' => '__ID__']) }}"
                    >
                        <div class="encabezado-ubicacion-clima">
                            <div>
                                <span class="sobrelinea">Ubicación para el pronóstico</span>
                                <h2 id="titulo-ubicacion-clima">Punto de la comunidad</h2>
                                <p data-coordenadas-seleccionadas>Selecciona una comunidad para ver sus coordenadas.</p>
                            </div>
                            <button class="boton boton-pequeno" type="button" data-usar-gps disabled>Usar ubicación GPS</button>
                        </div>
                        <p class="estado-ubicacion-clima" data-estado-ubicacion role="status" aria-live="polite">El pronóstico utilizará la ubicación guardada de la comunidad.</p>
                        <div class="mapa-selector-comunidad mapa-pronostico-comunidad" data-mapa-pronostico role="region" aria-label="Mapa de ubicación de la comunidad"></div>
                        <div class="acciones-ubicacion-clima">
                            <small>Si estás en la comunidad, usa GPS; si no, marca su punto en el mapa. La ubicación requiere permiso del navegador.</small>
                            <button class="boton boton-claro boton-pequeno" type="button" data-guardar-ubicacion hidden>Guardar punto seleccionado</button>
                        </div>
                    </section>
                    <section
                        class="panel-clima-comunidad"
                        data-panel-clima
                        data-url-clima="{{ route('administracion.comunidades.clima', ['comunidad' => '__ID__']) }}"
                        aria-labelledby="titulo-clima-comunidad"
                    >
                        <div class="encabezado-clima-comunidad">
                            <div>
                                <span class="sobrelinea">Pronóstico de referencia</span>
                                <h2 id="titulo-clima-comunidad" data-titulo-clima>Clima cercano</h2>
                                <p class="ubicacion-clima-comunidad" data-clima-ubicacion></p>
                            </div>
                            <button class="boton boton-claro boton-pequeno" type="button" data-consultar-clima disabled>
                                Consultar clima
                            </button>
                        </div>
                        <p class="estado-clima-comunidad" data-clima-estado role="status" aria-live="polite">
                            Selecciona una comunidad para consultar las condiciones cercanas.
                        </p>
                        <div class="datos-clima-comunidad" data-datos-clima hidden>
                            <div class="clima-actual">
                                <div>
                                    <strong class="temperatura-clima-actual" data-temperatura-actual></strong>
                                    <p class="descripcion-clima-actual" data-descripcion-clima></p>
                                    <p class="maximos-clima-actual">
                                        ↑ Máx. <strong data-maxima-hoy></strong>
                                        <span aria-hidden="true"> · </span>
                                        ↓ Mín. <strong data-minima-hoy></strong>
                                    </p>
                                </div>
                                <span class="icono-clima-actual" data-icono-clima aria-hidden="true">☁</span>
                            </div>
                            <section class="franja-pronostico-horas" aria-labelledby="titulo-pronostico-horas">
                                <div class="encabezado-pronostico-horas">
                                    <h3 id="titulo-pronostico-horas">Próximas horas</h3>
                                    <span>Mínima en 24 h: <strong data-temperatura-minima></strong> <small data-hora-minima></small></span>
                                </div>
                                <div class="pronostico-horas" data-pronostico-horas></div>
                            </section>
                            <div class="detalles-clima-actual">
                                <p>Sensación <strong data-sensacion-termica></strong></p>
                                <p>Humedad <strong data-humedad></strong></p>
                                <p>Viento <strong data-viento></strong></p>
                            </div>
                            <p class="temperatura-autocompletada" data-temperatura-autocompletada role="status" aria-live="polite">La temperatura prevista se completará automáticamente con la mínima del pronóstico.</p>
                        </div>
                        <small class="atribucion-clima-comunidad">
                            Pronóstico basado en las coordenadas guardadas; se actualizará automáticamente y es una estimación meteorológica.
                            Datos meteorológicos: <a href="https://open-meteo.com/en/license" target="_blank" rel="noopener">Open-Meteo (CC BY 4.0)</a>.
                        </small>
                    </section>
                </div>
                <label>Temperatura prevista (°C, completada automáticamente)
                    <input
                        type="number"
                        name="temperatura_c"
                        min="-80"
                        max="60"
                        step="0.5"
                        value="{{ old('temperatura_c') }}"
                        required
                    />
                </label>
                <label>Indicaciones adicionales (opcional)
                    <textarea
                        name="indicaciones"
                        rows="4"
                        maxlength="1000"
                        placeholder="Añade recomendaciones o detalles útiles para esta comunidad."
                    >{{ old('indicaciones') }}</textarea>
                </label>
                <small>El riesgo se calcula automáticamente; las indicaciones son opcionales (máximo 1000 caracteres).</small>
                <small>Menos de 0 °C: riesgo bajo. Desde −2 °C: medio. Desde −5 °C: alto.</small>
                <div class="acciones-formulario">
                    <a class="boton boton-claro" href="{{ route('alertas.indice') }}">Cancelar</a>
                    <button class="boton" type="submit">Evaluar y publicar alerta</button>
                </div>
            </form>
        </div>

        <div id="seccion-otras-emergencias" class="tarjeta-formulario formulario-alerta-manual">
            <span class="sobrelinea">Aviso verificado</span>
            <h2>Publicar alerta manual</h2>
            <p>Usa este formulario para avisos revisados que no dependen de una temperatura.</p>
            <form method="post" action="{{ route('administracion.alertas.guardar') }}" enctype="multipart/form-data">
                @csrf
                <label
                    >Tipo de aviso
                    <select name="tipo" data-tipo-emergencia required>
                        <option value="clima" @selected (old('tipo', 'clima') === 'clima')>Clima</option>
                        <option value="refugio" @selected (old('tipo') === 'refugio')>Refugios</option>
                        <option value="otro" @selected (old('tipo') === 'otro')>Otra emergencia</option>
                    </select>
                </label>
                <div data-campo-tipo-emergencia @if (old('tipo', 'refugio') !== 'otro') hidden @endif>
                    <label
                        >¿Qué tipo de emergencia?
                        <input name="tipo_personalizado" value="{{ old('tipo_personalizado') }}" maxlength="100" />
                    </label>
                </div>
                <label>Título<input name="titulo" value="{{ old('titulo') }}" maxlength="160" required /></label>
                <label
                    >Mensaje<textarea name="mensaje" rows="4" maxlength="3000" required>{{ old('mensaje') }}</textarea>
                </label>
                <div class="fila-formulario">
                    <label
                        >Nivel<select name="nivel" required>
                            <option value="informacion">Información</option>
                            <option value="precaucion">Precaución</option>
                            <option value="urgente">Urgente</option>
                        </select></label
                    >
                    <label
                        >Comunidad<select name="comunidad_id">
                            <option value="">Todas las comunidades</option>
                            @foreach ($comunidades as $comunidad)
                                <option value="{{ $comunidad->id }}">{{ $comunidad->nombre }}</option>
                            @endforeach</select
                    ></label>
                </div>
                <div class="fila-formulario">
                    <label
                        >Inicio de vigencia (opcional)<input
                            type="datetime-local"
                            name="fecha_inicio"
                            value="{{ old('fecha_inicio') }}"
                    /></label>
                    <label
                        >Fin de vigencia (opcional)<input
                            type="datetime-local"
                            name="fecha_fin"
                            value="{{ old('fecha_fin') }}"
                    /></label>
                </div>
                <label
                    >Fotografía del aviso (opcional)
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" />
                </label>
                <small
                    >JPG, PNG o WebP; máximo 5 MB. La fotografía será visible junto a la alerta mientras esté
                    publicada.</small
                >
                <div class="acciones-formulario">
                    <a class="boton boton-claro" href="{{ route('administracion.comunidades.indice') }}">Volver</a>
                    <button class="boton" type="submit">Publicar aviso</button>
                </div>
            </form>
        </div>
    </section>
    <script src="{{ asset('js/tipo-emergencia.js') }}?v={{ filemtime(public_path('js/tipo-emergencia.js')) }}" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/mapa-comunidades-admin.js') }}?v={{ filemtime(public_path('js/mapa-comunidades-admin.js')) }}" defer></script>
    <script src="{{ asset('js/clima-comunidad.js') }}?v={{ filemtime(public_path('js/clima-comunidad.js')) }}" defer></script>
