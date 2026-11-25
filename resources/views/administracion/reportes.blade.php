@extends ('layouts.app')
@section ('titulo', 'Revisar reportes')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Administración</span>
            <h1>Reportes comunitarios</h1>
            <p>Aprueba avisos de clima, refugios u otras emergencias antes de publicarlos para la comunidad.</p>
            <div class="resumen-cola-reportes" role="status">
                La lista empieza con {{ $reportesPendientes }} {{ $reportesPendientes === 1 ? 'reporte pendiente' : 'reportes pendientes' }} de aprobación. Dentro de cada estado, se ordena por prioridad: alta, media y baja.
            </div>
        </div>
        <a class="boton boton-contorno" href="{{ route('administracion.panel') }}">Volver al panel</a>
    </section>
    <section class="seccion seccion-superior">
        <div class="panel">
            @forelse ($reportes as $reporte)
                <article class="detalle-pedido" id="reporte-{{ $reporte->id }}">
                    <div class="encabezado-panel">
                        <div>
                            <h2>{{ $reporte->titulo }}</h2>
                            <small
                                >{{ $reporte->reportante->name }} · {{ $reporte->comunidad?->nombre ?: 'Sin comunidad' }} · {{ $reporte->created_at->format('d/m/Y H:i') }}</small
                            >
                        </div>
                        <span class="etiqueta-estado">{{ ucfirst($reporte->estado) }}</span>
                        <span
                            class="etiqueta-estado"
                            >{{ $reporte->tipo === 'otro' ? ($reporte->tipo_personalizado ?: 'Otra emergencia') : ($reporte->tipo === 'refugio' ? 'Refugios' : ($reporte->tipo === 'clima' ? 'Clima' : 'Riesgo')) }}</span
                        >
                    </div>
                    <p>{{ $reporte->descripcion }}</p>
                    <span class="etiqueta-estado prioridad-reporte prioridad-{{ $reporte->prioridad }}">Prioridad {{ ucfirst($reporte->prioridad) }}</span>
                    @if (in_array($reporte->estado, ['pendiente', 'revisado'], true))
                        <a
                            class="boton boton-contorno boton-pequeno"
                            href="{{ route('administracion.reportes.editar', ['reporteRiesgo' => $reporte->getKey()]) }}"
                        >Editar reporte</a>
                    @endif
                    @if ($reporte->foto_evidencia)
                        <div class="evidencia-reporte">
                            <span class="sobrelinea">Foto del incidente · será pública si se aprueba</span>
                            <a
                                href="{{ route('administracion.reportes.evidencia', $reporte) }}"
                                target="_blank"
                                rel="noopener"
                            >
                                <img
                                    src="{{ route('administracion.reportes.evidencia', $reporte) }}"
                                    alt="Fotografía del incidente enviada para revisión"
                                />
                            </a>
                        </div>
                    @endif
                    @if (in_array($reporte->estado, ['pendiente', 'revisado', 'publicado'], true))
                        <form
                            method="post"
                            action="{{ route('administracion.reportes.evidencia.actualizar', $reporte) }}"
                            enctype="multipart/form-data"
                            class="formulario-evidencia"
                            onsubmit="
                                return confirm(
                                    '¿Guardar la foto? Si el reporte ya está publicado, se hará visible para la comunidad.',
                                );
                            "
                        >
                            @csrf
                            @method ('patch')
                            <label
                                >{{ $reporte->estado === 'publicado' ? 'Agregar o reemplazar fotografía del reporte publicado' : 'Subir o reemplazar fotografía de evidencia' }}
                                <input
                                    type="file"
                                    name="foto_evidencia"
                                    accept="image/jpeg,image/png,image/webp"
                                    required
                                />
                            </label>
                            <small
                                >JPG, PNG o WebP; máximo 5 MB. {{ $reporte->estado === 'publicado' ? 'El reporte ya está aprobado: la foto se mostrará públicamente al guardarla.' : 'La nueva foto permanecerá privada hasta aprobar el reporte.' }}</small
                            >
                            <button class="boton boton-pequeno" type="submit">Guardar fotografía</button>
                        </form>
                    @endif
                    @if ($reporte->latitud && $reporte->longitud)
                        @if ($reporte->ubicacion_detallada)
                            <p><strong>Lugar aproximado:</strong> {{ $reporte->ubicacion_detallada }}</p>
                        @endif
                        <small>Ubicación: {{ $reporte->latitud }}, {{ $reporte->longitud }}</small>
                    @endif
                    @if ($reporte->estado === 'pendiente')
                        <div class="acciones-tarjeta">
                            <form
                                method="post"
                                action="{{ route('administracion.reportes.revisar', $reporte) }}"
                                onsubmit="
                                    return confirm(
                                        '¿Aprobar y publicar este reporte? Revisa que el texto y la foto, si existe, no revelen datos personales.',
                                    );
                                "
                            >
                                @csrf
                                @method ('patch')
                                <input type="hidden" name="estado" value="publicado" />
                                <button class="boton boton-pequeno" type="submit">Aprobar y publicar alerta</button>
                            </form>
                            <form
                                method="post"
                                action="{{ route('administracion.reportes.revisar', $reporte) }}"
                                onsubmit="return confirm('¿Descartar este reporte?');"
                            >
                                @csrf
                                @method ('patch')
                                <input type="hidden" name="estado" value="descartado" />
                                <button class="boton boton-destructivo boton-pequeno" type="submit">Descartar</button>
                            </form>
                        </div>
                    @elseif ($reporte->estado === 'revisado')
                        <div class="acciones-tarjeta">
                            <form
                                method="post"
                                action="{{ route('administracion.reportes.revisar', $reporte) }}"
                                onsubmit="
                                    return confirm(
                                        '¿Publicar este reporte? Revisa que el texto y la foto, si existe, no revelen datos personales.',
                                    );
                                "
                            >
                                @csrf
                                @method ('patch')
                                <input type="hidden" name="estado" value="publicado" />
                                <button class="boton boton-pequeno" type="submit">Publicar como alerta</button>
                            </form>
                            <form method="post" action="{{ route('administracion.reportes.revisar', $reporte) }}">
                                @csrf
                                @method ('patch')
                                <input type="hidden" name="estado" value="descartado" />
                                <button class="boton boton-destructivo boton-pequeno" type="submit">Descartar</button>
                            </form>
                        </div>
                    @endif
                    <form class="acciones-reporte-peligro"
                        method="post"
                        action="{{ route('administracion.reportes.eliminar', $reporte) }}"
                        onsubmit="return confirm('¿Eliminar definitivamente este reporte comunitario?');"
                    >
                        @csrf
                        @method ('delete')
                        <button class="boton boton-destructivo boton-pequeno" type="submit">Eliminar reporte</button>
                    </form>
                </article>
            @empty
                <div class="estado-vacio">
                    <span>◎</span>
                    <h3>No hay reportes recibidos</h3>
                    <p>Los reportes enviados por la comunidad aparecerán aquí.</p>
                </div>
            @endforelse
        </div>
        <div class="paginacion">{{ $reportes->links() }}</div>
    </section>
@endsection
