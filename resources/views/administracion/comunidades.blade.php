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
            <form method="post" action="{{ route('administracion.comunidades.guardar') }}" data-formulario-comunidad data-url-datos-ubicacion="{{ route('administracion.comunidades.ubicacion') }}">
                @csrf
                @include ('administracion.partials.campos-comunidad', [
                    'nombre' => old('nombre'),
                    'distrito' => old('distrito'),
                    'provincia' => old('provincia', 'Cusco'),
                    'altitud' => old('altitud_msnm'),
                    'latitud' => old('latitud'),
                    'longitud' => old('longitud'),
                ])
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
                        <form method="post" action="{{ route('administracion.comunidades.actualizar', $comunidad) }}" data-formulario-comunidad data-url-datos-ubicacion="{{ route('administracion.comunidades.ubicacion') }}">
                            @csrf
                            @method ('put')
                            @include ('administracion.partials.campos-comunidad', [
                                'nombre' => $comunidad->nombre,
                                'distrito' => $comunidad->distrito,
                                'provincia' => $comunidad->provincia,
                                'altitud' => $comunidad->altitud_msnm,
                                'latitud' => $comunidad->latitud,
                                'longitud' => $comunidad->longitud,
                            ])
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
