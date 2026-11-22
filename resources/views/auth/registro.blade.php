@extends ('layouts.app')
@section ('titulo', 'Crear cuenta')
@section ('content')
    <section class="contenedor-acceso">
        <div class="lateral-acceso">
            <span class="sobrelinea">Súmate a la comunidad</span>
            <h1>El primer paso para encontrarnos.</h1>
            <p>Crea una cuenta para comprar o publicar cosechas en el mercado comunitario.</p>
            <div class="simbolo-acceso">Ch</div>
        </div>

        <div class="tarjeta-acceso">
            <span class="sobrelinea">Registro</span>
            <h2>Crear cuenta</h2>
            <form method="post" action="{{ route('registro.guardar') }}">
                @csrf
                <label
                    >Nombre completo
                    <input name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required />
                </label>
                <label
                    >DNI (8 dígitos)
                    <input
                        name="dni"
                        value="{{ old('dni') }}"
                        inputmode="numeric"
                        autocomplete="off"
                        pattern="[0-9]{8}"
                        minlength="8"
                        maxlength="8"
                        required
                    />
                </label>
                <label
                    >Correo electrónico
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required />
                </label>
                <label
                    >Teléfono celular (Perú)
                    <input
                        type="tel"
                        name="telefono"
                        value="{{ old('telefono') }}"
                        inputmode="numeric"
                        autocomplete="tel"
                        pattern="9[0-9]{8}"
                        minlength="9"
                        maxlength="9"
                        placeholder="987654321"
                        required
                    />
                    <small>Te contactaremos a este número cuando revisemos tu solicitud.</small>
                </label>
                <label
                    >Quiero participar como
                    <select name="rol" required>
                        <option value="comprador" @selected (old('rol', 'comprador') === 'comprador')>
                            Comprador/a
                        </option>
                        <option value="productor" @selected (old('rol') === 'productor')>Productor/a</option>
                    </select>
                </label>
                <label for="tipo-comunidad"
                    >¿Encuentras tu comunidad en la lista?
                    <select id="tipo-comunidad" name="tipo_comunidad" required>
                        <option value="existente" @selected (old('tipo_comunidad', 'existente') === 'existente')>
                            Sí, ya está registrada
                        </option>
                        <option value="propuesta" @selected (old('tipo_comunidad') === 'propuesta')>
                            No, quiero proponerla
                        </option>
                    </select>
                </label>
                <div id="comunidad-existente" class="bloque-comunidad">
                    <label for="comunidad-id"
                        >Selecciona tu comunidad
                        <select id="comunidad-id" name="comunidad_id">
                            <option value="">Elige una comunidad</option>
                            @foreach ($comunidades as $comunidad)
                                <option
                                    value="{{ $comunidad->id }}"
                                    @selected ((string) old('comunidad_id') === (string) $comunidad->id)
                                >
                                    {{ $comunidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div id="comunidad-propuesta" class="bloque-comunidad">
                    <label for="nombre-comunidad-propuesta"
                        >Nombre de tu comunidad
                        <input
                            id="nombre-comunidad-propuesta"
                            name="comunidad_propuesta"
                            value="{{ old('comunidad_propuesta') }}"
                            maxlength="120"
                            autocomplete="off"
                        />
                    </label>
                    <label for="distrito-comunidad-propuesta"
                        >Distrito (opcional)
                        <input
                            id="distrito-comunidad-propuesta"
                            name="distrito_propuesto"
                            value="{{ old('distrito_propuesto') }}"
                            maxlength="120"
                            autocomplete="address-level2"
                        />
                    </label>
                    <p class="nota-comunidad">La propuesta será revisada por administración. No se publicará hasta que sea verificada.</p>
                </div>
                <label
                    >Contraseña <small>(mínimo 10 caracteres)</small>
                    <input type="password" name="password" autocomplete="new-password" required />
                </label>
                <label
                    >Confirma la contraseña
                    <input type="password" name="password_confirmation" autocomplete="new-password" required />
                </label>
                <button class="boton boton-ancho-completo" type="submit">Enviar solicitud de cuenta</button>
            </form>
            <p class="pie-formulario">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
            <p class="nota-privacidad">El DNI se usa para verificar tu identidad. Tu teléfono y comunidad serán visibles para administración. La cuenta quedará pendiente hasta que se revisen y aprueben tus datos.</p>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectorTipo = document.getElementById('tipo-comunidad');
            const grupoExistente = document.getElementById('comunidad-existente');
            const grupoPropuesta = document.getElementById('comunidad-propuesta');
            const selectorComunidad = document.getElementById('comunidad-id');
            const nombrePropuesta = document.getElementById('nombre-comunidad-propuesta');
            const distritoPropuesto = document.getElementById('distrito-comunidad-propuesta');

            const actualizarCamposComunidad = () => {
                const proponeNueva = selectorTipo.value === 'propuesta';
                grupoExistente.hidden = proponeNueva;
                grupoPropuesta.hidden = !proponeNueva;
                selectorComunidad.disabled = proponeNueva;
                selectorComunidad.required = !proponeNueva;
                nombrePropuesta.disabled = !proponeNueva;
                nombrePropuesta.required = proponeNueva;
                distritoPropuesto.disabled = !proponeNueva;
            };

            selectorTipo.addEventListener('change', actualizarCamposComunidad);
            actualizarCamposComunidad();
        });
    </script>
@endsection
