<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>
        @yield ('titulo', 'Chaskayni')
        · Chaskayni
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700;800&amp;display=swap"
        media="print"
        onload="this.media = 'all'"
    />
    <noscript
        ><link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700&amp;display=swap"
    /></noscript>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-chaskayni-marca.png') }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" />
</head>
<body>
    <header class="encabezado-sitio">
        <a class="marca" href="{{ route('inicio') }}"
            ><span class="sello-marca"><img src="{{ asset('images/logo-chaskayni-marca.png') }}" alt="" /></span>
            <span>Chaskayni<small>mercado y apoyo comunitario</small> </span></a
        >
        <button
            class="boton-menu"
            type="button"
            aria-label="Abrir menú"
            aria-expanded="false"
            aria-controls="navegacion-principal"
        >
            ☰
        </button>
        <nav id="navegacion-principal" class="enlaces-navegacion" aria-label="Navegación principal">
            <a href="{{ route('mercado.indice') }}" @if (request()->routeIs('mercado.indice')) aria-current="page" @endif>Mercado</a>
            <a href="{{ route('alertas.indice') }}" @if (request()->routeIs('alertas.indice')) aria-current="page" @endif>Alertas</a>
            @auth
                <a href="{{ route('riesgos.crear') }}" @if (request()->routeIs('riesgos.crear')) aria-current="page" @endif>Enviar aviso</a>
            @endauth
            <a href="{{ route('mapa.indice') }}" @if (request()->routeIs('mapa.indice')) aria-current="page" @endif>Mapa comunitario</a>
            @if (! auth()->check() || ! auth()->user()->esAdministrador())
                <a href="{{ route('contacto.formulario') }}" @if (request()->routeIs('contacto.*')) aria-current="page" @endif>Contacto</a>
            @endif
            @auth
                @if (! auth()->user()->esAdministrador())
                    <a href="{{ route('panel') }}" @if (request()->routeIs('panel')) aria-current="page" @endif>Mi panel</a>
                @endif
                @if (! auth()->user()->esAdministrador())
                    <a href="{{ route('pedidos.indice') }}" @if (request()->routeIs('pedidos.indice')) aria-current="page" @endif>Mis pedidos</a>
                @endif
                @if (auth()->user()->esProductor() && ! auth()->user()->esAdministrador())
                    <a href="{{ route('pedidos.recibidos') }}" @if (request()->routeIs('pedidos.recibidos')) aria-current="page" @endif>Pedidos recibidos</a>
                @endif
                @if (auth()->user()->esAdministrador())
                    <a href="{{ route('administracion.panel') }}" @if (request()->routeIs('administracion.panel')) aria-current="page" @endif>Administración</a>
                    <a href="{{ route('administracion.usuarios.indice') }}" @if (request()->routeIs('administracion.usuarios.*')) aria-current="page" @endif>Cuentas</a>
                @endif
                <form class="formulario-en-linea" action="{{ route('logout') }}" method="post">
                    @csrf
                    <button class="boton-navegacion" type="submit">Salir</button>
                </form>
            @else
                <a href="{{ route('login') }}" @if (request()->routeIs('login')) aria-current="page" @endif>Ingresar</a>
                <a class="boton boton-pequeno" href="{{ route('register') }}" @if (request()->routeIs('register')) aria-current="page" @endif>Crear cuenta</a>
            @endauth
        </nav>
    </header>
    <main>
        @if (session('success'))
            <div class="aviso aviso-exito" role="status">
                <span>{{ session('success') }}</span
                ><button class="aviso-cerrar" type="button" aria-label="Cerrar aviso" data-cerrar-aviso>×</button>
            </div>
        @endif
        @if ($errors->any())
            <div class="aviso aviso-error" role="alert">
                <div>
                    <strong>Revisa estos datos:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button class="aviso-cerrar" type="button" aria-label="Cerrar aviso" data-cerrar-aviso>×</button>
            </div>
        @endif
        @yield ('content')
    </main>
    <footer class="pie-sitio">
        <a class="marca marca-pie" href="{{ route('inicio') }}"
            ><span class="sello-marca"><img src="{{ asset('images/logo-chaskayni-marca.png') }}" alt="" /></span
            ><span>Chaskayni<small>Conectando comunidad y cosecha</small></span></a
        >
        <nav class="enlaces-pie" aria-label="Información y políticas">
            <a href="{{ route('privacidad') }}" @if (request()->routeIs('privacidad')) aria-current="page" @endif>Privacidad</a>
            <a href="{{ route('seguridad') }}" @if (request()->routeIs('seguridad')) aria-current="page" @endif>Seguridad</a>
            <a href="{{ route('acerca') }}" @if (request()->routeIs('acerca')) aria-current="page" @endif>Acerca de Chaskayni</a>
        </nav>
        <p>Proyecto académico · Cusco, Perú · © {{ now()->year }} Chaskayni</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const botonMenu = document.querySelector('.boton-menu');
            const navegacion = document.querySelector('.enlaces-navegacion');
            const cambiarMenu = (abierto) => {
                document.body.classList.toggle('navegacion-abierta', abierto);
                botonMenu?.setAttribute('aria-expanded', String(abierto));
                botonMenu?.setAttribute('aria-label', abierto ? 'Cerrar menú' : 'Abrir menú');
                if (abierto) navegacion?.querySelector('a')?.focus();
                if (botonMenu) botonMenu.textContent = abierto ? '×' : '☰';
            };

            botonMenu?.addEventListener('click', () => {
                cambiarMenu(botonMenu.getAttribute('aria-expanded') !== 'true');
            });
            navegacion?.addEventListener('click', (evento) => {
                if (evento.target.closest('a')) cambiarMenu(false);
            });
            document.addEventListener('keydown', (evento) => {
                if (evento.key === 'Escape' && botonMenu?.getAttribute('aria-expanded') === 'true') {
                    cambiarMenu(false);
                    botonMenu.focus();
                }
            });
            document.addEventListener('click', (evento) => {
                if (!evento.target.closest('.encabezado-sitio')) cambiarMenu(false);
            });

            document.querySelectorAll('[data-cerrar-aviso]').forEach((boton) => {
                boton.addEventListener('click', () => boton.closest('.aviso')?.remove());
            });
        });
    </script>
</body>
</html>
