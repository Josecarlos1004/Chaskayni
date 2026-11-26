@extends ('layouts.app')
@section ('titulo', 'Gestión de alertas')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Administración</span>
            <h1>Gestión de alertas</h1>
            <p>Administra avisos publicados y cerrados. Hay {{ $reportesPendientes }} {{ $reportesPendientes === 1 ? 'reporte pendiente' : 'reportes pendientes' }} de revisión.</p>
        </div>
        <div class="acciones-tarjeta">
            <a class="boton boton-contorno" href="{{ route('administracion.reportes.indice') }}">
                Revisar y aprobar reportes ({{ $reportesPendientes }})
            </a>
            <a class="boton" href="{{ route('administracion.alertas.crear') }}">Publicar alerta</a>
        </div>
    </section>

    <section class="seccion seccion-superior">
        @if ($alertas->isNotEmpty())
            <div class="cuadricula-alertas cuadricula-alertas-admin">
                @foreach ($alertas as $alerta)
                    <article class="tarjeta-alerta">
                        <div class="tarjeta-alerta-etiquetas">
                            <span class="nivel-alerta">{{ ucfirst($alerta->nivel) }}</span>
                            <span class="etiqueta-estado">
                                {{ $alerta->tipo === 'otro' ? ($alerta->tipo_personalizado ?: 'Otra emergencia') : ($alerta->tipo === 'refugio' ? 'Refugios' : 'Clima') }}
                            </span>
                            <span class="etiqueta-estado">{{ ucfirst($alerta->estado) }}</span>
                            @if ($alerta->nivel_riesgo)
                                <span class="etiqueta-estado">Riesgo {{ $alerta->nivel_riesgo }}</span>
                            @endif
                        </div>

                        <div class="tarjeta-alerta-contenido">
                            <div class="tarjeta-alerta-resumen">
                                @if ($alerta->reporte_riesgo_id)
                                    <a
                                        class="enlace-texto alerta-origen"
                                        href="{{ route('administracion.reportes.indice') }}#reporte-{{ $alerta->reporte_riesgo_id }}"
                                    >
                                        Reporte comunitario de origen
                                    </a>
                                @endif
                                <h2>{{ $alerta->titulo }}</h2>
                                <p class="tarjeta-alerta-mensaje">{{ $alerta->mensaje }}</p>

                                @if (! is_null($alerta->temperatura_c))
                                    <p class="alerta-dato-destacado">
                                        Temperatura prevista:
                                        <strong>{{ number_format((float) $alerta->temperatura_c, 1) }} °C</strong>
                                    </p>
                                @endif

                                <dl class="tarjeta-alerta-datos">
                                    <div>
                                        <dt>Comunidad</dt>
                                        <dd>{{ $alerta->comunidad?->nombre ?: 'Alerta general' }}</dd>
                                    </div>
                                    <div>
                                        <dt>Publicada</dt>
                                        <dd>{{ $alerta->created_at->format('d/m/Y H:i') }}</dd>
                                    </div>
                                    <div>
                                        <dt>Responsable</dt>
                                        <dd>{{ $alerta->autor?->name ?: 'Autor no disponible' }}</dd>
                                    </div>
                                    @if ($alerta->fecha_fin)
                                        <div>
                                            <dt>Vigente hasta</dt>
                                            <dd>{{ $alerta->fecha_fin->format('d/m/Y H:i') }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>

                            <aside class="tarjeta-alerta-visual" aria-label="Evidencia y ubicación de la alerta">
                                @if ($alerta->foto)
                                    <img
                                        class="imagen-alerta"
                                        src="{{ route('administracion.alertas.foto', $alerta) }}"
                                        alt="Fotografía asociada a {{ $alerta->titulo }}"
                                        loading="lazy"
                                    />
                                    <small>Fotografía adjunta a la alerta</small>
                                @elseif ($alerta->reporteOrigen?->foto_evidencia)
                                    <a
                                        class="alerta-evidencia-enlace"
                                        href="{{ route('administracion.reportes.evidencia', $alerta->reporteOrigen) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <img
                                            class="imagen-alerta"
                                            src="{{ route('administracion.reportes.evidencia', $alerta->reporteOrigen) }}"
                                            alt="Evidencia del reporte de origen: {{ $alerta->titulo }}"
                                            loading="lazy"
                                        />
                                        <span>Abrir evidencia del reporte</span>
                                    </a>
                                @elseif ($alerta->tipo === 'clima')
                                    @include('alertas._mapa-clima', ['alerta' => $alerta])
                                @else
                                    <div class="alerta-sin-imagen">
                                        <span aria-hidden="true">◎</span>
                                        <strong>Información de la alerta</strong>
                                        <small>
                                            {{ $alerta->reporteOrigen?->latitud !== null && $alerta->reporteOrigen?->longitud !== null ? 'El reporte incluye una ubicación que puedes revisar abajo.' : 'No se adjuntó una fotografía a este aviso.' }}
                                        </small>
                                    </div>
                                @endif

                                @if ($alerta->reporteOrigen?->latitud !== null && $alerta->reporteOrigen?->longitud !== null)
                                    <div class="alerta-ubicacion">
                                        <span class="alerta-ubicacion-icono" aria-hidden="true">⌖</span>
                                        <div>
                                            <strong>{{ $alerta->reporteOrigen->ubicacion_detallada ?: 'Ubicación del incidente' }}</strong>
                                            <small>{{ $alerta->reporteOrigen->latitud }}, {{ $alerta->reporteOrigen->longitud }}</small>
                                        </div>
                                        <a
                                            href="https://www.openstreetmap.org/?mlat={{ $alerta->reporteOrigen->latitud }}&mlon={{ $alerta->reporteOrigen->longitud }}#map=16/{{ $alerta->reporteOrigen->latitud }}/{{ $alerta->reporteOrigen->longitud }}"
                                            target="_blank"
                                            rel="noopener"
                                        >Mapa</a>
                                    </div>
                                @endif
                            </aside>
                        </div>

                        <div class="tarjeta-alerta-pie">
                            <div class="pie-alerta">
                                <span>{{ $alerta->comunidad?->nombre ?: 'Alerta general' }}</span>
                                <span>{{ $alerta->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div class="acciones-tarjeta">
                                @if (! $alerta->reporte_riesgo_id)
                                    <a
                                        class="boton boton-contorno boton-pequeno"
                                        href="{{ route('administracion.alertas.editar', $alerta) }}"
                                    >Editar alerta</a>
                                @endif
                                @if ($alerta->estado === 'publicada')
                                    <form
                                        method="post"
                                        action="{{ route('administracion.alertas.cerrar', $alerta) }}"
                                        onsubmit="return confirm('¿Cerrar esta alerta?');"
                                    >
                                        @csrf
                                        @method ('patch')
                                        <button class="boton boton-contorno boton-pequeno" type="submit">Cerrar alerta</button>
                                    </form>
                                @endif
                                <form
                                    method="post"
                                    action="{{ route('administracion.alertas.eliminar', $alerta) }}"
                                    onsubmit="return confirm('¿Eliminar definitivamente esta alerta?');"
                                >
                                    @csrf
                                    @method ('delete')
                                    <button class="boton boton-destructivo boton-pequeno" type="submit">Eliminar alerta</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="paginacion">{{ $alertas->links() }}</div>
        @else
            <div class="estado-vacio">
                <span aria-hidden="true">⌁</span>
                <h3>No hay alertas registradas</h3>
                <p>Las alertas publicadas aparecerán aquí.</p>
                <a class="boton" href="{{ route('administracion.alertas.crear') }}">Publicar la primera alerta</a>
            </div>
        @endif
    </section>
@endsection