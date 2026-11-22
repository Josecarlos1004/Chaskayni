@extends ('layouts.app')
@section ('titulo', 'Contacto y soporte')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Estamos para ayudarte</span>
            <h1>Contacto y soporte</h1>
            <p>Envíanos tu consulta sobre el mercado, los pedidos o la participación comunitaria.</p>
        </div>
    </section>
    <section class="pagina-formulario">
        <div class="tarjeta-formulario">
            <form method="post" action="{{ route('contacto.guardar') }}">
                @csrf
                <label
                    >Nombre<input
                        name="nombre"
                        value="{{ old('nombre') }}"
                        maxlength="120"
                        autocomplete="name"
                        required
                /></label>
                <div class="fila-formulario">
                    <label
                        >Correo electrónico (si no ingresas teléfono)<input
                            type="email"
                            name="correo"
                            value="{{ old('correo') }}"
                            maxlength="190"
                            autocomplete="email"
                    /></label>
                    <label
                        >Teléfono (si no ingresas correo)<input
                            type="tel"
                            name="telefono"
                            value="{{ old('telefono') }}"
                            maxlength="30"
                            autocomplete="tel"
                    /></label>
                </div>
                <label>Asunto<input name="asunto" value="{{ old('asunto') }}" maxlength="160" required /></label>
                <label
                    >Mensaje<textarea name="mensaje" rows="6" maxlength="3000" required>{{ old('mensaje') }}</textarea>
                </label>
                <p class="texto-atenuado">Debes proporcionar un correo o un teléfono para que el equipo pueda responderte.</p>
                <div class="acciones-formulario"><button class="boton" type="submit">Enviar consulta</button></div>
            </form>
        </div>
    </section>
@endsection
