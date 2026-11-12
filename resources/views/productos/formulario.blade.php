@extends('layouts.app')
@section('titulo', $producto->exists ? 'Editar publicación' : 'Publicar cosecha')
@section('content')
<section class="pagina-formulario"><a class="enlace-regreso" href="{{ route('mercado.indice') }}">← Volver al mercado</a><div class="tarjeta-formulario"><span class="sobrelinea">Mercado comunitario</span><h1>{{ $producto->exists ? 'Editar publicación' : 'Publicar una cosecha' }}</h1><p>Completa la información para que otras personas conozcan tu producto.</p>
@php($urlImagenInicial = $producto->urlImagen())
<form method="post" enctype="multipart/form-data" action="{{ $producto->exists ? route('productos.actualizar', $producto) : route('productos.guardar') }}">@csrf @if ($producto->exists) @method('put') @endif
<label>Nombre del producto<input name="nombre" value="{{ old('nombre', $producto->nombre) }}" maxlength="140" required></label><div class="fila-formulario"><label>Categoría<input name="categoria" value="{{ old('categoria', $producto->categoria) }}" maxlength="80" placeholder="Ej. tubérculos"></label><label>Comunidad<select name="comunidad_id"><option value="">Mi comunidad</option>@foreach ($comunidades as $comunidad)<option value="{{ $comunidad->id }}" @selected((string) old('comunidad_id', $producto->comunidad_id ?? auth()->user()->comunidad_id) === (string) $comunidad->id)>{{ $comunidad->nombre }}{{ $comunidad->activo ? '' : ' (inactiva; asociación actual)' }}</option>@endforeach</select></label></div>
<label>Descripción<textarea name="descripcion" rows="4" maxlength="3000">{{ old('descripcion', $producto->descripcion) }}</textarea></label><div class="fila-formulario"><label>Cantidad disponible<input type="number" name="cantidad" min="0.01" step="0.01" value="{{ old('cantidad', $producto->cantidad) }}" required></label><label>Unidad<select name="unidad" required>@foreach (['kg' => 'Kilogramos (kg)', 'arroba' => 'Arroba', 'saco' => 'Saco', 'unidad' => 'Unidad', 'litro' => 'Litro'] as $value => $label)<option value="{{ $value }}" @selected(old('unidad', $producto->unidad ?: 'kg') === $value)>{{ $label }}</option>@endforeach</select></label></div>
<div class="fila-formulario"><label>Precio por unidad (S/)<input type="number" name="precio_unitario" min="0.01" step="0.01" value="{{ old('precio_unitario', $producto->precio_unitario) }}" required></label><label>URL externa de imagen (opcional)<input id="url-imagen" type="url" name="imagen_url" value="{{ old('imagen_url', filter_var($producto->imagen_url, FILTER_VALIDATE_URL) ? $producto->imagen_url : '') }}" maxlength="2048" placeholder="https://..."></label></div>
<input type="hidden" name="cesta_salvada" value="0">
<label class="fila-casillas opcion-cesta-salvada"><input type="checkbox" name="cesta_salvada" value="1" @checked((bool) old('cesta_salvada', $producto->cesta_salvada))><span><strong>Cesta Salvadas</strong><small>Ofrece esta cosecha con 30% de descuento al realizar el pedido.</small></span></label>
<div class="campo-imagen"><label for="imagen">Subir fotografía del producto<input id="imagen" type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-describedby="ayuda-imagen"><small id="ayuda-imagen">JPG, PNG o WebP. Tamaño máximo: 5 MB. La imagen cargada reemplaza la URL externa.</small></label><div class="previsualizacion-imagen" aria-live="polite">@if ($urlImagenInicial)<img id="vista-previa-imagen" src="{{ $urlImagenInicial }}" alt="Vista previa de {{ $producto->nombre }}" onerror="this.hidden=true; this.nextElementSibling.hidden=false"><div id="imagen-sin-vista" class="imagen-sin-vista" hidden><span>Vista previa no disponible</span></div>@else<img id="vista-previa-imagen" alt="Vista previa de la imagen seleccionada" hidden><div id="imagen-sin-vista" class="imagen-sin-vista"><span>Vista previa de la imagen</span></div>@endif</div></div>
@if ($producto->exists && $urlImagenInicial)<label class="fila-casillas"><input type="checkbox" name="quitar_imagen" value="1" @checked(old('quitar_imagen'))> Quitar la imagen actual</label>@endif<div class="acciones-formulario"><a class="boton boton-claro" href="{{ route('mercado.indice') }}">Cancelar</a><button class="boton" type="submit">{{ $producto->exists ? 'Guardar cambios' : 'Publicar producto' }}</button></div></form></div></section>
<script>
(() => {
    const entradaArchivo = document.getElementById('imagen');
    const entradaUrl = document.getElementById('url-imagen');
    const vistaPrevia = document.getElementById('vista-previa-imagen');
    const reemplazo = document.getElementById('imagen-sin-vista');
    if (!entradaArchivo || !entradaUrl || !vistaPrevia || !reemplazo) return;

    let urlTemporal = null;
    const mostrarVistaPrevia = (origen) => {
        vistaPrevia.src = origen;
        vistaPrevia.hidden = false;
        reemplazo.hidden = true;
        vistaPrevia.onerror = () => {
            vistaPrevia.hidden = true;
            reemplazo.hidden = false;
        };
    };

    entradaArchivo.addEventListener('change', () => {
        if (urlTemporal) URL.revokeObjectURL(urlTemporal);
        const archivo = entradaArchivo.files?.[0];
        if (archivo) {
            urlTemporal = URL.createObjectURL(archivo);
            mostrarVistaPrevia(urlTemporal);
        } else if (entradaUrl.value) {
            mostrarVistaPrevia(entradaUrl.value);
        }
    });

    entradaUrl.addEventListener('input', () => {
        if (!entradaArchivo.files?.length && entradaUrl.value) mostrarVistaPrevia(entradaUrl.value);
    });
})();
</script>
@endsection
