@extends ('layouts.app')
@section ('titulo', 'Editar alerta')
@section ('content')
    <section class="pagina-formulario">
        <a class="enlace-regreso" href="{{ route('administracion.alertas.indice') }}">← Volver a alertas</a>

        <div class="tarjeta-formulario">
            <span class="sobrelinea">Administración</span>
            <h1>Editar alerta</h1>
            <p>Actualiza el aviso y su fotografía. Solo se permiten imágenes JPG, PNG o WebP de hasta 5 MB.</p>
            <form
                method="post"
                action="{{ route('administracion.alertas.actualizar', $alerta) }}"
                enctype="multipart/form-data"
            >
                @csrf
                @method ('put')
                <label
                    >Tipo de aviso
                    <select name="tipo" data-tipo-emergencia required>
                        <option value="clima" @selected (old('tipo', $alerta->tipo) === 'clima')>Clima</option>
                        <option value="refugio" @selected (old('tipo', $alerta->tipo) === 'refugio')>Refugios</option>
                        <option value="otro" @selected (old('tipo', $alerta->tipo) === 'otro')>Otra emergencia</option>
                    </select>
                </label>
                <div data-campo-tipo-emergencia @if (old('tipo', $alerta->tipo) !== 'otro') hidden @endif>
                    <label
                        >¿Qué tipo de emergencia?
                        <input name="tipo_personalizado" value="{{ old('tipo_personalizado', $alerta->tipo_personalizado) }}" maxlength="100" />
                    </label>
                </div>
                <label
                    >Título
                    <input name="titulo" value="{{ old('titulo', $alerta->titulo) }}" maxlength="160" required />
                </label>
                <label
                    >Mensaje
                    <textarea
                        name="mensaje"
                        rows="5"
                        maxlength="3000"
                        required
                        >{{ old('mensaje', $alerta->mensaje) }}</textarea
                    >
                </label>
                <div class="fila-formulario">
                    <label
                        >Nivel
                        <select name="nivel" required>
                            <option value="informacion" @selected (old('nivel', $alerta->nivel) === 'informacion')>
                                Información
                            </option>
                            <option value="precaucion" @selected (old('nivel', $alerta->nivel) === 'precaucion')>
                                Precaución
                            </option>
                            <option value="urgente" @selected (old('nivel', $alerta->nivel) === 'urgente')>
                                Urgente
                            </option>
                        </select>
                    </label>
                    <label
                        >Comunidad
                        <select name="comunidad_id">
                            <option value="">Todas las comunidades</option>
                            @foreach ($comunidades as $comunidad)
                                <option
                                    value="{{ $comunidad->id }}"
                                    @selected ((string) old('comunidad_id', $alerta->comunidad_id) === (string) $comunidad->id)
                                >
                                    {{ $comunidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="fila-formulario">
                    <label
                        >Inicio de vigencia (opcional)
                        <input
                            type="datetime-local"
                            name="fecha_inicio"
                            value="{{ old('fecha_inicio', $alerta->fecha_inicio?->format('Y-m-d\\TH:i')) }}"
                        />
                    </label>
                    <label
                        >Fin de vigencia (opcional)
                        <input
                            type="datetime-local"
                            name="fecha_fin"
                            value="{{ old('fecha_fin', $alerta->fecha_fin?->format('Y-m-d\\TH:i')) }}"
                        />
                    </label>
                </div>
                @if ($alerta->foto)
                    <div class="evidencia-reporte">
                        <span class="sobrelinea">Fotografía actual</span>
                        <img
                            src="{{ route('administracion.alertas.foto', $alerta) }}"
                            alt="Fotografía actual de la alerta"
                        />
                    </div>
                    <label class="opcion-formulario"
                        ><input type="checkbox" name="quitar_foto" value="1" @checked (old('quitar_foto')) /> Quitar la
                        fotografía actual</label
                    >
                @endif
                <label
                    >Subir o reemplazar fotografía (opcional)
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" />
                </label>
                <div class="acciones-formulario">
                    <a class="boton boton-claro" href="{{ route('administracion.alertas.indice') }}">Cancelar</a>
                    <button class="boton" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>
    </section>
    <script src="{{ asset('js/tipo-emergencia.js') }}?v={{ filemtime(public_path('js/tipo-emergencia.js')) }}" defer></script>
