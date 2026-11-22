@extends ('layouts.app')
@section ('titulo', 'Consultas de contacto')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Administración</span>
            <h1>Consultas de contacto</h1>
            <p>Mensajes privados enviados por las personas usuarias.</p>
        </div>
        <a class="boton boton-contorno" href="{{ route('administracion.panel') }}">Volver al panel</a>
    </section>
    <section class="seccion seccion-superior">
        <div class="panel">
            @forelse ($consultas as $consulta)
                <article class="detalle-pedido">
                    <div class="encabezado-panel">
                        <div>
                            <h2>{{ $consulta->asunto }}</h2>
                            <small>{{ $consulta->nombre }} · {{ $consulta->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <span class="etiqueta-estado">{{ $consulta->leido_en ? 'Leída' : 'Nueva' }}</span>
                    </div>
                    <p>{{ $consulta->mensaje }}</p>
                    <div class="acciones-tarjeta">
                        @if ($consulta->correo)
                            <a
                                class="boton boton-contorno boton-pequeno"
                                href="mailto:{{ $consulta->correo }}"
                                >{{ $consulta->correo }}</a
                            >
                        @endif
                        @if ($consulta->telefono)
                            <a
                                class="boton boton-contorno boton-pequeno"
                                href="tel:{{ $consulta->telefono }}"
                                >{{ $consulta->telefono }}</a
                            >
                        @endif
                        @unless ($consulta->leido_en)
                            <form method="post" action="{{ route('administracion.consultas.leida', $consulta) }}">
                                @csrf
                                @method ('patch')
                                <button class="boton boton-pequeno" type="submit">Marcar como leída</button>
                            </form>
                        @endunless
                    </div>
                </article>
            @empty
                <div class="estado-vacio">
                    <span>✉</span>
                    <h3>No hay consultas todavía</h3>
                    <p>Los mensajes enviados desde Contacto aparecerán aquí.</p>
                </div>
            @endforelse
        </div>
        <div class="paginacion">{{ $consultas->links() }}</div>
    </section>
@endsection
