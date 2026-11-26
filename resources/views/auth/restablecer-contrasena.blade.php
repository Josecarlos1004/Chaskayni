@extends ('layouts.app')
@section ('titulo', 'Crear contraseña nueva')
@section ('content')
    <section class="contenedor-acceso">
        <div class="lateral-acceso">
            <span class="sobrelinea">Recupera tu cuenta</span>
            <h1>Elige una contraseña nueva.</h1>
            <p>Usa una contraseña larga que no compartas con otras personas.</p>
            <div class="simbolo-acceso">Ch</div>
        </div>
        <div class="tarjeta-acceso">
            <span class="sobrelinea">Restablecimiento seguro</span>
            <h2>Nueva contraseña</h2>
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />
                <label>
                    Correo electrónico
                    <input type="email" name="email" value="{{ old('email', $correo) }}" autocomplete="email" required autofocus />
                </label>
                <label>
                    Contraseña nueva
                    <input type="password" name="password" autocomplete="new-password" minlength="10" required />
                </label>
                <label>
                    Repite la contraseña nueva
                    <input type="password" name="password_confirmation" autocomplete="new-password" minlength="10" required />
                </label>
                <button class="boton boton-ancho-completo" type="submit">Guardar contraseña nueva</button>
            </form>
            <p class="pie-formulario"><a href="{{ route('login') }}">Volver a ingresar</a></p>
        </div>
    </section>
@endsection