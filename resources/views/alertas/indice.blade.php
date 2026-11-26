@extends ('layouts.app')
@section ('titulo', 'Alertas comunitarias')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Información comunitaria</span>
            <h1>Alertas y avisos</h1>
            <p>Consulta avisos comunitarios aprobados por administración.</p>
        </div>
        <div class="acciones-tarjeta acciones-encabezado-alertas">
            <a class="boton boton-primario" href="{{ route('riesgos.crear') }}">
                @auth
                    {{ auth()->user()->esAdministrador() ? 'Publicar situación' : 'Enviar aviso para revisión' }}
                @else
                    Enviar un aviso
                @endauth
            </a>
            @guest
                <small>Inicia sesión o crea una cuenta para reportar una situación.</small>
            @endguest
        </div>
    </section>

    <section class="seccion seccion-superior">
        @if ($alertas->isNotEmpty())
            <div class="cuadricula-alertas cuadricula-alertas-amplia">
                @foreach ($alertas as $alerta)
                    <article class="tarjeta-alerta tarjeta-alerta-publica">
                        <div class="tarjeta-alerta-etiquetas">
                            <span class="nivel-alerta">{{ ucfirst($alerta->nivel) }}</span>
                            <span class="etiqueta-estado">
                                {{ $alerta->tipo === 'otro' ? ($alerta->tipo_personalizado ?: 'Otra emergencia') : ($alerta->tipo === 'refugio' ? 'Refugios' : 'Clima') }}
                            </span>
                            @if ($alerta->nivel_riesgo)
                                <span class="etiqueta-estado">Riesgo {{ $alerta->nivel_riesgo }}</span>
                            @endif
                        </div>

                        <div class="tarjeta-alerta-contenido">
                            <div class="tarjeta-alerta-resumen">
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
                                        <dd>{{ $alerta->created_at->format('d/m/Y') }}</dd>
                                    </div>
                                    @if ($alerta->fecha_fin)
                                        <div>
                                            <dt>Vigente hasta</dt>
                                            <dd>{{ $alerta->fecha_fin->format('d/m/Y H:i') }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                @if ($alerta->reporteOrigen?->estado === 'publicado')
                                    <a class="enlace-texto alerta-origen" href="{{ route('riesgos.detalle', $alerta->reporteOrigen) }}">
                                        Ver detalle del reporte que originó esta alerta
                                    </a>
                                @endif
                            </div>

                            <aside class="tarjeta-alerta-visual" aria-label="Evidencia y ubicación de la alerta">
                                @if ($alerta->foto)
                                    <img
                                        class="imagen-alerta"
                                        src="{{ route('alertas.foto', $alerta) }}"
                                        alt="Fotografía relacionada con {{ $alerta->titulo }}"
                                        loading="lazy"
                                    />
                                    <small>Fotografía relacionada con el aviso</small>
                                @elseif ($alerta->reporteOrigen?->estado === 'publicado' && $alerta->reporteOrigen->foto_evidencia)
                                    <a
                                        class="alerta-evidencia-enlace"
                                        href="{{ route('riesgos.foto', $alerta->reporteOrigen) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <img
                                            class="imagen-alerta"
                                            src="{{ route('riesgos.foto', $alerta->reporteOrigen) }}"
                                            alt="Fotografía aprobada del incidente: {{ $alerta->titulo }}"
                                            loading="lazy"
                                        />
                                        <span>Ver fotografía del incidente</span>
                                    </a>
                                @elseif ($alerta->tipo === 'clima')
                                    @include('alertas._mapa-clima', ['alerta' => $alerta])
                                @elseif ($alerta->reporteOrigen?->latitud !== null && $alerta->reporteOrigen?->longitud !== null)
                                    <div class="alerta-sin-imagen">
                                        <span aria-hidden="true">⌖</span>
                                        <strong>Ubicación del incidente</strong>
                                        <small>Consulta el punto señalado por quien envió el reporte.</small>
                                    </div>
                                @else
                                    <div class="alerta-sin-imagen">
                                        <span aria-hidden="true">◎</span>
                                        <strong>Aviso para la comunidad</strong>
                                        <small>No se adjuntó una fotografía; revisa el mensaje y la vigencia del aviso.</small>
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
                                        >Ver mapa</a>
                                    </div>
                                @endif
                            </aside>
                        </div>

                        @auth
                            @if (auth()->user()->esAdministrador())
                                <div class="tarjeta-alerta-pie">
                                    <div class="acciones-tarjeta">
                                        <form
                                            method="post"
                                            action="{{ route('administracion.alertas.cerrar', $alerta) }}"
                                            onsubmit="return confirm('¿Cerrar esta alerta?');"
                                        >
                                            @csrf
                                            @method ('patch')
                                            <button class="boton boton-contorno boton-pequeno" type="submit">Cerrar alerta</button>
                                        </form>
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
                            @endif
                        @endauth
                    </article>
                @endforeach
            </div>
            <div class="paginacion">{{ $alertas->links() }}</div>
        @else
            <div class="estado-vacio">
                <span aria-hidden="true">⌁</span>
                <h3>No hay alertas vigentes</h3>
                <p>Cuando administración publique un aviso, lo encontrarás aquí.</p>
                @auth
                    <a class="boton" href="{{ route('riesgos.crear') }}">Enviar un aviso</a>
                @endauth
            </div>
        @endif
    </section>
@endsection