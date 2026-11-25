@extends ('layouts.app')
@section ('titulo', 'Administrar comunidades')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Administración</span>
            <h1>Comunidades</h1>
            <p>Administra los datos y controla qué comunidades pueden asociarse a nuevos registros.</p>
        </div>
        <a class="boton" href="{{ route('administracion.alertas.crear') }}">+ Publicar alerta</a>
    </section>
    <section class="seccion seccion-superior cuadricula-administracion">
        <div class="tarjeta-formulario">
            <span class="sobrelinea">Nueva comunidad</span>
            <h2>Registrar comunidad</h2>
            <form method="post" action="{{ route('administracion.comunidades.guardar') }}" data-formulario-comunidad>
                @csrf
                <label>Nombre<input name="nombre" value="{{ old('nombre') }}" maxlength="120" required /></label>
                <div class="fila-formulario">
                    <label
                        >Distrito (opcional)<input
                            name="distrito"
                            value="{{ old('distrito') }}"
                            maxlength="120" /></label
                    ><label
                        >Provincia<input
                            name="provincia"
                            value="{{ old('provincia', 'Cusco') }}"
                            maxlength="120"
                            required
                    /></label>
                </div>
                                <div class="campo-ubicacion-comunidad">
                    <span class="etiqueta-campo">Ubicación para el pronóstico</span>
                    <p class="texto-atenuado">Marca en el mapa el punto de la comunidad. El pronóstico usará estas coordenadas.</p>
                    <div class="mapa-selector-comunidad" data-mapa-comunidad role="region" aria-label="Selecciona la ubicación de la comunidad"></div>
                    <p class="estado-mapa-comunidad" data-estado-mapa-comunidad role="status" aria-live="polite">Haz clic en el mapa para seleccionar la ubicación.</p>
                    <div class="fila-formulario fila-coordenadas-comunidad">
                        <label>Latitud
                            <input type="number" name="latitud" step="any" min="-90" max="90" value="{{ old('latitud') }}" />
                        </label>
                        <label>Longitud
                            <input type="number" name="longitud" step="any" min="-180" max="180" value="{{ old('longitud') }}" />
                        </label>
                    </div>
                </div>
                <label
                    >Altitud (m s. n. m., opcional)<input
                        type="number"
                        name="altitud_msnm"
                        min="0"
                        max="8000"
                        step="1"
                        value="{{ old('altitud_msnm') }}"
                /></label>
                <small>Ingresa una altitud confirmada. Si no la conoces, déjala vacía.</small>
                <label
                    >Descripción<textarea
                        name="descripcion"
                        rows="3"
                        maxlength="2000"
                        >{{ old('descripcion') }}</textarea
                    >
                </label>
                <button class="boton" type="submit">Guardar comunidad</button>
            </form>
        </div>
        <div class="panel">
            <div class="encabezado-panel">
                <h2>Comunidades registradas</h2>
                <a class="enlace-texto" href="{{ route('administracion.reportes.indice') }}">Ver reportes →</a>
            </div>
            @forelse ($comunidades as $comunidad)
                <article class="detalle-pedido">
                    <div class="encabezado-panel">
                        <div>
                            <strong>{{ $comunidad->nombre }}</strong
                            ><small
                                >{{ $comunidad->distrito ?: $comunidad->provincia }} · {{ $comunidad->altitud_msnm ? $comunidad->altitud_msnm.' m s. n. m.' : 'Altitud no registrada' }} · {{ $comunidad->usuarios_count }} cuentas
                                · {{ $comunidad->productos_count }} publicaciones</small
                            >
                        </div>
                        <span class="etiqueta-estado">{{ $comunidad->activo ? 'Activa' : 'Inactiva' }}</span>
                    </div>
                    <details>
                        <summary>Editar o activar comunidad</summary>
                        <form method="post" action="{{ route('administracion.comunidades.actualizar', $comunidad) }}" data-formulario-comunidad>
                            @csrf
                            @method ('put')
                            <label
                                >Nombre<input name="nombre" value="{{ $comunidad->nombre }}" maxlength="120" required
                            /></label>
                            <div class="fila-formulario">
                                <label
                                    >Distrito<input
                                        name="distrito"
                                        value="{{ $comunidad->distrito }}"
                                        maxlength="120" /></label
                                ><label
                                    >Provincia<input
                                        name="provincia"
                                        value="{{ $comunidad->provincia }}"
                                        maxlength="120"
                                        required
                                /></label>
                            </div>
                                                        <div class="campo-ubicacion-comunidad">
                                <span class="etiqueta-campo">Ubicación para el pronóstico</span>
                                <p class="texto-atenuado">Marca en el mapa el punto de la comunidad. El pronóstico usará estas coordenadas.</p>
                                <div class="mapa-selector-comunidad" data-mapa-comunidad role="region" aria-label="Selecciona la ubicación de la comunidad"></div>
                                <p class="estado-mapa-comunidad" data-estado-mapa-comunidad role="status" aria-live="polite">Haz clic en el mapa para seleccionar la ubicación.</p>
                                <div class="fila-formulario fila-coordenadas-comunidad">
                                    <label>Latitud
                                        <input type="number" name="latitud" step="any" min="-90" max="90" value="{{ $comunidad->latitud }}" />
                                    </label>
                                    <label>Longitud
                                        <input type="number" name="longitud" step="any" min="-180" max="180" value="{{ $comunidad->longitud }}" />
                                    </label>
                                </div>
                            </div>
                            <label
                                >Altitud (m s. n. m.)<input
                                    type="number"
                                    name="altitud_msnm"
                                    min="0"
                                    max="8000"
                                    value="{{ $comunidad->altitud_msnm }}"
                            /></label>
                            <label
                                >Descripción<textarea
                                    name="descripcion"
                                    rows="2"
                                    maxlength="2000"
                                    >{{ $comunidad->descripcion }}</textarea
                                >
                            </label>
                            <label class="fila-casillas"
                                ><input type="hidden" name="activo" value="0" /><input
                                    type="checkbox"
                                    name="activo"
                                    value="1"
                                    @checked ($comunidad->activo)
                                />
                                Comunidad activa</label
                            >
                            <button class="boton boton-pequeno" type="submit">Guardar cambios</button>
                        </form>
                    </details>
                    <form
                        method="post"
                        action="{{ route('administracion.comunidades.eliminar', $comunidad) }}"
                        onsubmit="
                            return confirm(
                                '¿Eliminar esta comunidad? Las cuentas y publicaciones se conservarán, pero quedarán sin comunidad asociada.',
                            );
                        "
                    >
                        @csrf
                        @method ('delete')
                        <button class="boton boton-destructivo boton-pequeno" type="submit">Eliminar comunidad</button>
                    </form>
                </article>
            @empty
                <div class="vacio-en-linea">
                    <p>Aún no hay comunidades. Registra información confirmada del equipo.</p>
                </div>
            @endforelse
        </div>
    </section>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/mapa-comunidades-admin.js') }}?v={{ filemtime(public_path('js/mapa-comunidades-admin.js')) }}" defer></script>
@endsection
