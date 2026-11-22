@extends ('layouts.app')
@section ('titulo', 'Administrar refugios')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Administración</span>
            <h1>Refugios comunitarios</h1>
            <p>Registra lugares verificados y administra su publicación en el mapa.</p>
        </div>
        <a class="boton boton-contorno" href="{{ route('mapa.indice') }}">Ver mapa</a>
    </section>
    <section class="seccion seccion-superior cuadricula-administracion">
        <div class="tarjeta-formulario">
            <h2>Registrar refugio</h2>
            <p>Agrega datos confirmados. La ubicación puede seleccionarse en el mapa.</p>
            <form method="post" action="{{ route('administracion.refugios.guardar') }}">
                @csrf
                <label>Nombre<input name="nombre" value="{{ old('nombre') }}" maxlength="160" required /></label
                ><label
                    >Dirección<input name="direccion" value="{{ old('direccion') }}" maxlength="255" required
                /></label>
                <label
                    >Descripción<textarea
                        name="descripcion"
                        rows="3"
                        maxlength="2000"
                        >{{ old('descripcion') }}</textarea
                    ></label
                ><label
                    >Capacidad<input
                        name="capacidad"
                        type="number"
                        min="0"
                        max="100000"
                        value="{{ old('capacidad', 0) }}"
                        required
                /></label>
                <label
                    >Comunidad<select name="comunidad_id">
                        <option value="">Sin comunidad asociada</option>
                        @foreach ($comunidades as $comunidad)
                            <option value="{{ $comunidad->id }}" @selected (old('comunidad_id') == $comunidad->id)>
                                {{ $comunidad->nombre }}
                            </option>
                        @endforeach</select
                ></label>
                <div
                    class="mapa-selector"
                    id="mapa-selector"
                    role="region"
                    aria-label="Selecciona la ubicación del refugio"
                ></div>
                <p id="estado-coordenadas" class="texto-atenuado" aria-live="polite">Ubicación: aún no seleccionada</p>
                <div class="fila-formulario">
                    <label
                        >Latitud<input
                            id="latitud"
                            name="latitud"
                            type="number"
                            step="any"
                            min="-90"
                            max="90"
                            value="{{ old('latitud') }}" /></label
                    ><label
                        >Longitud<input
                            id="longitud"
                            name="longitud"
                            type="number"
                            step="any"
                            min="-180"
                            max="180"
                            value="{{ old('longitud') }}"
                    /></label>
                </div>
                <button class="boton" type="submit">Guardar refugio</button>
            </form>
        </div>
        <div class="panel">
            <h2>Refugios registrados</h2>
            @forelse ($refugios as $refugio)
                <article class="fila-mapa">
                    <div class="encabezado-panel">
                        <strong>{{ $refugio->nombre }}</strong
                        ><span class="etiqueta-estado">{{ $refugio->activo ? 'Activo' : 'Inactivo' }}</span>
                    </div>
                    <small
                        >{{ $refugio->comunidad?->nombre ?? 'Sin comunidad' }} · {{ $refugio->direccion }} · Capacidad: {{ $refugio->capacidad }}</small
                    >
                    @if ($refugio->latitud && $refugio->longitud)
                        <small>Ubicación: {{ $refugio->latitud }}, {{ $refugio->longitud }}</small>
                    @else
                        <small>Sin coordenadas; aún no aparece en el mapa.</small>
                    @endif
                    <details>
                        <summary>Editar refugio</summary>
                        <form method="post" action="{{ route('administracion.refugios.actualizar', $refugio) }}">
                            @csrf
                            @method ('put')
                            <label
                                >Nombre<input
                                    name="nombre"
                                    value="{{ $refugio->nombre }}"
                                    maxlength="160"
                                    required /></label
                            ><label
                                >Dirección<input
                                    name="direccion"
                                    value="{{ $refugio->direccion }}"
                                    maxlength="255"
                                    required
                            /></label>
                            <label
                                >Descripción<textarea
                                    name="descripcion"
                                    rows="2"
                                    maxlength="2000"
                                    >{{ $refugio->descripcion }}</textarea
                                ></label
                            ><label
                                >Capacidad<input
                                    name="capacidad"
                                    type="number"
                                    min="0"
                                    max="100000"
                                    value="{{ $refugio->capacidad }}"
                                    required
                            /></label>
                            <label
                                >Comunidad<select name="comunidad_id">
                                    <option value="">Sin comunidad asociada</option>
                                    @foreach ($comunidadesTodas as $comunidad)
                                        @if ($comunidad->activo || $comunidad->id === $refugio->comunidad_id)
                                            <option
                                                value="{{ $comunidad->id }}"
                                                @selected ($refugio->comunidad_id === $comunidad->id)
                                            >
                                                {{ $comunidad->nombre }}{{ $comunidad->activo ? '' : ' (inactiva; vínculo actual)' }}
                                            </option>
                                        @endif
                                    @endforeach</select
                            ></label>
                            <div class="fila-formulario">
                                <label
                                    >Latitud<input
                                        name="latitud"
                                        type="number"
                                        step="any"
                                        min="-90"
                                        max="90"
                                        value="{{ $refugio->latitud }}" /></label
                                ><label
                                    >Longitud<input
                                        name="longitud"
                                        type="number"
                                        step="any"
                                        min="-180"
                                        max="180"
                                        value="{{ $refugio->longitud }}"
                                /></label>
                            </div>
                            <label class="fila-casillas"
                                ><input type="hidden" name="activo" value="0" /><input
                                    type="checkbox"
                                    name="activo"
                                    value="1"
                                    @checked ($refugio->activo)
                                />
                                Refugio activo y visible en el mapa</label
                            >
                            <button class="boton boton-pequeno" type="submit">Guardar cambios</button>
                        </form>
                    </details>
                    <form
                        method="post"
                        action="{{ route('administracion.refugios.eliminar', $refugio) }}"
                        onsubmit="return confirm('¿Eliminar este refugio? Dejará de aparecer en el mapa.');"
                    >
                        @csrf
                        @method ('delete')
                        <button class="boton boton-contorno boton-pequeno" type="submit">Eliminar refugio</button>
                    </form>
                </article>
            @empty
                <p class="texto-atenuado">Todavía no se registraron refugios.</p>
            @endforelse
        </div>
    </section>
    <script>
        window.datosMapa = @json ($datosMapa);
    </script>
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
    <script src="{{ asset('js/mapa-comunitario.js') }}" defer></script>
@endsection
