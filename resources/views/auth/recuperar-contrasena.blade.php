@extends ('layouts.app')
@section ('titulo', 'Recuperar contraseña')
@section ('content')
    <section class="contenedor-acceso">
        <div class="lateral-acceso">
            <span class="sobrelinea">Recupera tu cuenta</span>
            <h1>Volvamos a conectarte con tu comunidad.</h1>
            <p>Te enviaremos un enlace temporal para crear una contraseña nueva.</p>
            <div class="simbolo-acceso">Ch</div>
        </div>
        <div class="tarjeta-acceso">
            <span class="sobrelinea">Restablecimiento seguro</span>
            <h2>¿Olvidaste tu contraseña?</h2>
            <p>Escribe el correo que usas para ingresar. Si está registrado, recibirás instrucciones.</p>
            <form method="post" action="{{ route('password.email') }}">
                @csrf
                <label>
                    Correo electrónico
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus />
                </label>
                <button class="boton boton-ancho-completo" type="submit">Enviar enlace de recuperación</button>
            </form>
            <p class="pie-formulario"><a href="{{ route('login') }}">Volver a ingresar</a></p>
        </div>
    </section>
@endsection