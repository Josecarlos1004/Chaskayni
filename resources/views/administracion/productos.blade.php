@extends('layouts.app')
@section('titulo', 'Administrar publicaciones')
@section('content')
<section class="encabezado-pagina">
    <div><span class="sobrelinea">Administración</span><h1>Publicaciones</h1><p>Revisa y administra todas las publicaciones, incluidas las ocultas o agotadas.</p></div>
    <a class="boton boton-contorno" href="{{ route('administracion.panel') }}">Volver al panel</a>
</section>
<section class="seccion seccion-superior">
    @if ($productos->isNotEmpty())
        <div class="cuadricula-productos">
            @foreach ($productos as $producto)
                <div>
                    <p class="etiqueta-estado">Estado: {{ ucfirst($producto->estado) }}</p>
                    @include('components.tarjeta-producto', ['producto' => $producto])
                </div>
            @endforeach
        </div>
        <div class="paginacion">{{ $productos->links() }}</div>
    @else
        <div class="estado-vacio"><span>◎</span><h3>No hay publicaciones</h3><p>Las publicaciones del mercado aparecerán aquí.</p></div>
    @endif
</section>
@endsection