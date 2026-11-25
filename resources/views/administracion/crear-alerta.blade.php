@extends ('layouts.app')
@section ('titulo', 'Publicar alerta')
@section ('content')
    <section class="pagina-formulario">
        <a class="enlace-regreso" href="{{ route('administracion.comunidades.indice') }}">← Administración</a>

        <div class="tarjeta-formulario">
            <span class="sobrelinea">Alerta por temperatura</span>
            <h1>Evaluar riesgo de helada</h1>
            <p>Ingresa la temperatura prevista para la madrugada. El pronóstico cercano es orientativo. Puedes usar su mínima de las próximas 24 horas o ingresar la temperatura manualmente.</p>
            <details class="registro-comunidad-rapida">
                <summary>¿Falta una comunidad? Registrar una nueva</summary>
                <form method="post" action="{{ route('administracion.comunidades.rapida') }}" data-registro-comunidad data-formulario-comunidad>
                    @csrf
                    <div class="fila-formulario">
                        <label>Nombre de la comunidad
                            <input name="nombre" maxlength="120" required />
                        </label>
                        <label>Distrito (opcional)
                            <input name="distrito" maxlength="120" />
                        </label>
                    </div>
                    <div class="fila-formulario">
                        <label>Provincia
                            <input name="provincia" value="Cusco" maxlength="120" required />
                        </label>
                        <label>Altitud en metros sobre el nivel del mar (opcional)
                            <input type="number" name="altitud_msnm" min="0" max="8000" step="1" />
                        </label>
                    </div>
                    <div class="campo-ubicacion-comunidad">
                        <span class="etiqueta-campo">Ubicación para el pronóstico</span>
                        <p class="texto-atenuado">Selecciona el punto exacto. Sin coordenadas, el clima se deberá ingresar manualmente.</p>
                        <div class="mapa-selector-comunidad" data-mapa-comunidad role="region" aria-label="Selecciona la ubicación de la comunidad"></div>
                        <p class="estado-mapa-comunidad" data-estado-mapa-comunidad role="status" aria-live="polite">Haz clic en el mapa para seleccionar la ubicación.</p>
                        <div class="fila-formulario fila-coordenadas-comunidad">
                            <label>Latitud
                                <input type="number" name="latitud" step="any" min="-90" max="90" />
                            </label>
                            <label>Longitud
                                <input type="number" name="longitud" step="any" min="-180" max="180" />
                            </label>
                        </div>
                    </div>
                    <button class="boton boton-pequeno" type="submit">Registrar y seleccionar comunidad</button>
                    <p class="estado-registro-comunidad" data-registro-estado role="status" aria-live="polite" hidden></p>
                </form>
            </details>
            <form method="post" action="{{ route('administracion.alertas.temperatura') }}">
                @csrf
                <div class="fila-formulario fila-clima-comunidad">
                    <label>Comunidad
                        <select name="comunidad_id" data-seleccionar-comunidad required>
                            <option value="">Selecciona una comunidad</option>
                            @foreach ($comunidades as $comunidad)
                                <option
                                    value="{{ $comunidad->id }}"
                                    @selected ((string) old('comunidad_id', session('comunidadCreadaId')) === (string) $comunidad->id)
                                >
                                    {{ $comunidad->nombre }}{{ $comunidad->altitud_msnm ? ' · '.$comunidad->altitud_msnm.' m s. n. m.' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>
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
                            <button class="boton boton-clima-minima" type="button" data-usar-temperatura>
                                Usar mínima de 24 h para evaluar
                            </button>
                        </div>
                        <small class="atribucion-clima-comunidad">
                            Pronóstico calculado para las coordenadas registradas; es orientativo y debe verificarse antes de publicar.
                            Datos meteorológicos: <a href="https://open-meteo.com/en/license" target="_blank" rel="noopener">Open-Meteo (CC BY 4.0)</a>.
                        </small>
                    </section>
                </div>
                <label>Temperatura prevista (°C)
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

        <div class="tarjeta-formulario formulario-alerta-manual">
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
                <div data-campo-tipo-emergencia @if (old('tipo', 'clima') !== 'otro') hidden @endif>
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
                    <button class="boton" type="submit">Publicar alerta manual</button>
                </div>
            </form>
        </div>
    </section>
    <script src="{{ asset('js/tipo-emergencia.js') }}?v={{ filemtime(public_path('js/tipo-emergencia.js')) }}" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/mapa-comunidades-admin.js') }}?v={{ filemtime(public_path('js/mapa-comunidades-admin.js')) }}" defer></script>
    <script src="{{ asset('js/clima-comunidad.js') }}?v={{ filemtime(public_path('js/clima-comunidad.js')) }}" defer></script>
