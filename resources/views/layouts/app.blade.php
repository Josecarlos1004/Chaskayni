<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Chaskayni') · Chaskayni</title><link rel="stylesheet" href="{{ asset('css/app.css') }}"></head>
<body>
<header class="site-header">
    <a class="brand" href="{{ route('home') }}"><span class="brand-mark">Ch</span><span>chaskayni<small>mercado y apoyo comunitario</small></span></a>
    <button class="menu-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" onclick="document.body.classList.toggle('nav-open'); this.setAttribute('aria-expanded', document.body.classList.contains('nav-open'))">☰</button>
    <nav class="nav-links" aria-label="Navegación principal">
        <a href="{{ route('market.index') }}">Mercado</a><a href="{{ route('alerts.index') }}">Alertas</a>
        @auth<a href="{{ route('dashboard') }}">Mi panel</a><a href="{{ route('orders.index') }}">Mis pedidos</a>@if (auth()->user()->isAdministrator())<a href="{{ route('admin.communities.index') }}">Administración</a>@endif<form class="inline-form" action="{{ route('logout') }}" method="post">@csrf<button class="nav-button" type="submit">Salir</button></form>
        @else<a href="{{ route('login') }}">Ingresar</a><a class="button button-small" href="{{ route('register') }}">Crear cuenta</a>@endauth
    </nav>
</header>
<main>
@if (session('success'))<div class="flash flash-success" role="status">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="flash flash-error" role="alert"><strong>Revisa estos datos:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
<footer class="site-footer"><a class="brand brand-footer" href="{{ route('home') }}"><span class="brand-mark">Ch</span><span>chaskayni<small>Conectando comunidad y cosecha</small></span></a><p>Proyecto académico · Cusco, Perú</p></footer>
</body></html>