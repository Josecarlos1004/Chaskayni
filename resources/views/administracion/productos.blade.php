@extends ('layouts.app')
@section ('titulo', 'Administrar publicaciones')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Administración</span>
            <h1>Publicaciones</h1>
            <p>Revisa y administra todas las publicaciones, incluidas las ocultas o agotadas.</p>
        </div>
        <a class="boton boton-contorno" href="{{ route('administracion.panel') }}">Volver al panel</a>
    </section>
    <section class="seccion seccion-superior">
        @if ($productos->isNotEmpty())
            <div class="cuadricula-productos">
                @foreach ($productos as $producto)
                    <div>
                        <p class="etiqueta-estado">Estado: {{ ucfirst($producto->estado) }}</p>
                        @php ($telefonoProductor = $producto->vendedor?->telefono)
                        @if ($telefonoProductor)
                            @php ($numeroProductor = preg_replace('/\D+/', '', (string) $telefonoProductor))
                            @php ($numeroInternacional = str_starts_with($numeroProductor, '51') ? $numeroProductor : '51' . $numeroProductor)
                            <div class="panel">
                                <strong>Contacto del productor</strong>
                                <p>
                                    {{ $producto->vendedor->name }} ·
                                    <a href="tel:+{{ $numeroInternacional }}">{{ $telefonoProductor }}</a>
                                </p>
                                <div class="acciones-tarjeta">
                                    <a class="boton boton-contorno boton-pequeno" href="tel:+{{ $numeroInternacional }}">
                                        Llamar al productor
                                    </a>
                                    <a
                                        class="boton boton-pequeno"
                                        href="https://wa.me/{{ $numeroInternacional }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        Escribir por WhatsApp
                                    </a>
                                </div>
                            </div>
                        @else
                            <p class="texto-ayuda">Este productor no tiene un teléfono registrado.</p>
                        @endif
                        @include ('components.tarjeta-producto', ['producto' => $producto])
                    </div>
                @endforeach
            </div>
            <div class="paginacion">{{ $productos->links() }}</div>
        @else
            <div class="estado-vacio">
                <span>◎</span>
                <h3>No hay publicaciones</h3>
                <p>Las publicaciones del mercado aparecerán aquí.</p>
            </div>
        @endif
    </section>
@endsection
