@extends('layouts.app')
@section('titulo', 'Mis publicaciones')
@section('content')
<section class="encabezado-pagina"><div><span class="sobrelinea">Gestión de ventas</span><h1>Mis publicaciones</h1><p>Revisa tu stock, repón existencias y decide qué productos se muestran en el mercado.</p></div><a class="boton" href="{{ route('productos.crear') }}">+ Publicar cosecha</a></section>
<section class="seccion seccion-superior"><div class="panel">
@forelse ($productos as $producto)
<article class="fila-pedido fila-publicacion"><div class="datos-publicacion">@if ($producto->urlImagen())<img class="miniatura-producto" src="{{ $producto->urlImagen() }}" alt="{{ $producto->nombre }}" loading="lazy" onerror="this.hidden=true">@else<span class="miniatura-producto miniatura-vacia" aria-hidden="true">🌱</span>@endif
<div><strong>{{ $producto->nombre }}</strong><small>{{ $producto->categoria ?: 'Cosecha local' }} · {{ $producto->comunidad?->nombre ?: 'Comunidad de Cusco' }}</small><small>S/ {{ number_format((float) $producto->precio_unitario, 2) }} / {{ $producto->unidad }} · {{ number_format((float) $producto->cantidad, 2) }} {{ $producto->unidad }} en existencia</small>@if ($producto->estado === 'disponible' && (float) $producto->cantidad <= 5)<small class="aviso-poco-stock">Poco stock: considera reponer</small>@endif</div></div>
<span class="etiqueta-estado estado-producto-{{ $producto->estado }}">{{ $producto->nombreEstado() }}</span>
<div class="acciones-tarjeta"><a class="boton boton-contorno boton-pequeno" href="{{ route('productos.editar', $producto) }}">Editar o reponer</a><form method="post" action="{{ route('productos.visibilidad', $producto) }}">@csrf @method('patch')<button class="boton boton-sutil boton-pequeno" type="submit">{{ $producto->estado === 'oculto' ? 'Mostrar' : 'Ocultar' }}</button></form></div></article>
@empty<div class="estado-vacio"><span>🌾</span><h3>Aún no tienes publicaciones</h3><p>Publica tu primera cosecha para que aparezca en el mercado.</p><a class="boton boton-contorno" href="{{ route('productos.crear') }}">Publicar una cosecha</a></div>@endforelse
</div><div class="paginacion">{{ $productos->links() }}</div></section>
@endsection
