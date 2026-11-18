<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('titulo', 'Chaskayni') · Chaskayni</title><link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700;800&amp;display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700&amp;display=swap"></noscript>
<link rel="icon" type="image/png" href="{{ asset('images/logo-chaskayni-marca.png') }}"><link rel="stylesheet" href="{{ asset('css/app.css') }}"></head>
<body>
<header class="encabezado-sitio">
    <a class="marca" href="{{ route('inicio') }}"><span class="sello-marca"><img src="{{ asset('images/logo-chaskayni-marca.png') }}" alt=""></span>
    <span>chaskayni<small>mercado y apoyo comunitario</small>
    </span></a>
    <button class="boton-menu" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="navegacion-principal">☰</button>
    <nav id="navegacion-principal" class="enlaces-navegacion" aria-label="Navegación principal">
        <a href="{{ route('mercado.indice') }}">Mercado</a>
        <a href="{{ route('alertas.indice') }}">Alertas</a>
        @auth<a href="{{ route('riesgos.crear') }}">Enviar aviso</a>@endauth
        <a href="{{ route('mapa.indice') }}">Mapa comunitario</a>
        @if (! auth()->check() || ! auth()->user()->esAdministrador())<a href="{{ route('contacto.formulario') }}">Contacto</a>@endif
        @auth
            @if (! auth()->user()->esAdministrador())<a href="{{ route('panel') }}">Mi panel</a>@endif
            @if (! auth()->user()->esAdministrador())<a href="{{ route('pedidos.indice') }}">Mis pedidos</a>@endif
            @if (auth()->user()->esProductor())<a href="{{ route('pedidos.recibidos') }}">Pedidos recibidos</a>@endif
            @if (auth()->user()->esAdministrador())
                <a href="{{ route('administracion.panel') }}">Administración</a>
                <a href="{{ route('administracion.usuarios.indice') }}">Cuentas</a>
            @endif
            <form class="formulario-en-linea" action="{{ route('logout') }}" method="post">@csrf<button class="boton-navegacion" type="submit">Salir</button></form>
        @else
            <a href="{{ route('login') }}">Ingresar</a>
            <a class="boton boton-pequeno" href="{{ route('register') }}">Crear cuenta</a>
        @endauth
    </nav>
</header>
<main>
@if (session('success'))<div class="aviso aviso-exito" role="status"><span>{{ session('success') }}</span><button class="aviso-cerrar" type="button" aria-label="Cerrar aviso" data-cerrar-aviso>×</button></div>@endif
@if ($errors->any())<div class="aviso aviso-error" role="alert"><div><strong>Revisa estos datos:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div><button class="aviso-cerrar" type="button" aria-label="Cerrar aviso" data-cerrar-aviso>×</button></div>@endif
@yield('content')
</main>
<footer class="pie-sitio"><a class="marca marca-pie" href="{{ route('inicio') }}"><span class="sello-marca"><img src="{{ asset('images/logo-chaskayni-marca.png') }}" alt=""></span><span>chaskayni<small>Conectando comunidad y cosecha</small></span></a><p>Proyecto académico · Cusco, Perú @if (! auth()->check() || ! auth()->user()->esAdministrador())· <a href="{{ route('contacto.formulario') }}">Contacto</a>@endif</p></footer>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const botonMenu = document.querySelector('.boton-menu');
    const navegacion = document.querySelector('.enlaces-navegacion');
    const cambiarMenu = (abierto) => {
        document.body.classList.toggle('navegacion-abierta', abierto);
        botonMenu?.setAttribute('aria-expanded', String(abierto));
        botonMenu?.setAttribute('aria-label', abierto ? 'Cerrar menú' : 'Abrir menú');
        if (abierto) navegacion?.querySelector('a')?.focus();
    };

    botonMenu?.addEventListener('click', () => {
        cambiarMenu(botonMenu.getAttribute('aria-expanded') !== 'true');
    });
    navegacion?.addEventListener('click', (evento) => {
        if (evento.target.closest('a')) cambiarMenu(false);
    });
    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') cambiarMenu(false);
    });
    document.addEventListener('click', (evento) => {
        if (!evento.target.closest('.encabezado-sitio')) cambiarMenu(false);
    });

    document.querySelectorAll('[data-cerrar-aviso]').forEach((boton) => {
        boton.addEventListener('click', () => boton.closest('.aviso')?.remove());
    });
});
</script>
</body></html>