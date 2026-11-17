<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('titulo', 'Chaskayni') · Chaskayni</title><link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700;800&amp;display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700&amp;display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/app.css') }}"></head>
<body>
<header class="encabezado-sitio">
    <a class="marca" href="{{ route('inicio') }}"><span class="sello-marca">Ch</span>
    <span>chaskayni<small>mercado y apoyo comunitario</small>
    </span></a>
    <button class="boton-menu" type="button" aria-label="Abrir menú" aria-expanded="false" onclick="document.body.classList.toggle('navegacion-abierta'); this.setAttribute('aria-expanded', document.body.classList.contains('navegacion-abierta'))">☰</button>
    <nav class="enlaces-navegacion" aria-label="Navegación principal">
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
@if (session('success'))<div class="aviso aviso-exito" rol="estado">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="aviso aviso-error" rol="alert"><strong>Revisa estos datos:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
<footer class="pie-sitio"><a class="marca marca-pie" href="{{ route('inicio') }}"><span class="sello-marca">Ch</span><span>chaskayni<small>Conectando comunidad y cosecha</small></span></a><p>Proyecto académico · Cusco, Perú @if (! auth()->check() || ! auth()->user()->esAdministrador())· <a href="{{ route('contacto.formulario') }}">Contacto</a>@endif</p></footer>
</body></html>