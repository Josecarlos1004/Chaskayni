@extends ('layouts.app')
@section ('titulo', 'Pedidos recibidos')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Gestión de ventas</span>
            <h1>Pedidos recibidos</h1>
            <p>Revisa las solicitudes de tus productos y actualiza su estado.</p>
        </div>
        <a class="boton boton-contorno" href="{{ route('mercado.indice') }}">Volver al mercado</a>
    </section>
    <section class="seccion seccion-superior">
        <div class="panel">
            @forelse ($pedidos as $pedido)
                <article class="detalle-pedido">
                    <div class="encabezado-panel">
                        <div>
                            <h2>Pedido #{{ $pedido->id }}</h2>
                            <small
                                >{{ $pedido->comprador?->name ?: 'Comprador no disponible' }} · {{ $pedido->created_at->format('d/m/Y H:i') }}</small
                            >
                        </div>
                        <span class="etiqueta-estado {{ $pedido->claseEstado() }}">{{ $pedido->nombreEstado() }}</span>
                    </div>
                    @foreach ($pedido->detalles as $detalle)
                        <div class="fila-pedido">
                            <div>
                                <strong>{{ $detalle->producto?->nombre ?: 'Producto retirado' }}</strong
                                ><small
                                    >{{ number_format((float) $detalle->cantidad, 2) }} {{ $detalle->producto?->unidad ?: 'unidad' }} ·
                                    Precio aplicado: S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</small
                                >
                            </div>
                            <strong>S/ {{ number_format((float) $detalle->subtotal, 2) }}</strong>
                        </div>
                    @endforeach
                    @if ($pedido->notas)
                        <p class="texto-atenuado">Nota: {{ $pedido->notas }}</p>
                    @endif
                    <div class="total-pedido">
                        Total <strong>S/ {{ number_format((float) $pedido->total, 2) }}</strong>
                    </div>
                    @if ($pedido->estado === 'pendiente')
                        <div class="acciones-tarjeta">
                            <form method="post" action="{{ route('pedidos.estado', $pedido) }}">
                                @csrf
                                @method ('patch')
                                <input type="hidden" name="estado" value="aceptado" /><button
                                    class="boton boton-pequeno"
                                    type="submit"
                                >
                                    Aceptar pedido
                                </button>
                            </form>
                            <form method="post" action="{{ route('pedidos.estado', $pedido) }}">
                                @csrf
                                @method ('patch')
                                <input type="hidden" name="estado" value="rechazado" /><button
                                    class="boton boton-contorno boton-pequeno"
                                    type="submit"
                                >
                                    Rechazar y devolver existencias
                                </button>
                            </form>
                        </div>
                    @elseif ($pedido->estado === 'aceptado')
                        <form method="post" action="{{ route('pedidos.estado', $pedido) }}">
                            @csrf
                            @method ('patch')
                            <input type="hidden" name="estado" value="preparando" /><button
                                class="boton boton-pequeno"
                                type="submit"
                            >
                                Iniciar preparación
                            </button>
                        </form>
                    @elseif ($pedido->estado === 'preparando')
                        <form method="post" action="{{ route('pedidos.estado', $pedido) }}">
                            @csrf
                            @method ('patch')
                            <input type="hidden" name="estado" value="en_camino" /><button
                                class="boton boton-pequeno"
                                type="submit"
                            >
                                Marcar en camino
                            </button>
                        </form>
                    @elseif ($pedido->estado === 'en_camino')
                        <form method="post" action="{{ route('pedidos.estado', $pedido) }}">
                            @csrf
                            @method ('patch')
                            <input type="hidden" name="estado" value="completado" /><button
                                class="boton boton-pequeno"
                                type="submit"
                            >
                                Marcar como entregado
                            </button>
                        </form>
                    @endif
                    @if (auth()->user()->esAdministrador())
                        <form
                            method="post"
                            action="{{ route('administracion.pedidos.eliminar', $pedido) }}"
                            onsubmit="return confirm('¿Eliminar definitivamente el pedido #{{ $pedido->id }}? Si aún no fue entregado ni rechazado, se devolverán las existencias al mercado.')"
                        >
                            @csrf
                            @method ('delete')
                            <button class="boton boton-contorno boton-pequeno" type="submit">Eliminar pedido</button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="estado-vacio">
                    <span>🧺</span>
                    <h3>No tienes pedidos recibidos</h3>
                    <p>Cuando alguien solicite uno de tus productos, aparecerá aquí.</p>
                    <a class="boton boton-contorno" href="{{ route('productos.crear') }}">Publicar una cosecha</a>
                </div>
            @endforelse
        </div>
        <div class="paginacion">{{ $pedidos->links() }}</div>
    </section>
@endsection
