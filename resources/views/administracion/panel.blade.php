@extends ('layouts.app')
@section ('titulo', 'Panel de administración')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Administración</span>
            <h1>Resumen de Chaskayni</h1>
            <p>Consulta el estado general y accede a las herramientas de gestión.</p>
        </div>
        <a class="boton" href="{{ route('administracion.reportes.indice') }}">Revisar reportes ({{ $indicadores['reportes_pendientes'] }})</a>
    </section>
    <section class="seccion seccion-superior" id="panel-estadisticas" data-url="{{ route('administracion.estadisticas') }}">
        <div class="distribucion-estadisticas">
            <div class="cuadricula-estadisticas">
                <article class="tarjeta-estadistica">
                    <span>Cuentas registradas</span><strong><span data-indicador="usuarios">{{ $indicadores['usuarios'] }}</span></strong
                    ><small>Usuarios del sistema</small>
                </article>
                <article class="tarjeta-estadistica">
                    <span>Cuentas por aprobar</span><strong><span data-indicador="cuentas_pendientes">{{ $indicadores['cuentas_pendientes'] }}</span></strong
                    ><small>Revisa identidad y teléfono en Cuentas</small>
                </article>
                <article class="tarjeta-estadistica">
                    <span>Productos disponibles</span><strong><span data-indicador="productos">{{ $indicadores['productos'] }}</span></strong
                    ><small>Publicaciones visibles en el mercado</small>
                </article>
                <article class="tarjeta-estadistica">
                    <span>Pedidos pendientes</span><strong><span data-indicador="pedidos_pendientes">{{ $indicadores['pedidos_pendientes'] }}</span></strong
                    ><small>Solicitudes por coordinar</small>
                </article>
                <article class="tarjeta-estadistica">
                    <span>Reportes pendientes</span><strong><span data-indicador="reportes_pendientes">{{ $indicadores['reportes_pendientes'] }}</span></strong
                    ><small>Aportes comunitarios por revisar</small>
                </article>
                <article class="tarjeta-estadistica">
                    <span>Alertas activas</span><strong><span data-indicador="alertas_activas">{{ $indicadores['alertas_activas'] }}</span></strong><small>Avisos vigentes</small>
                </article>
                <article class="tarjeta-estadistica">
                    <span>Refugios activos</span><strong><span data-indicador="refugios_activos">{{ $indicadores['refugios_activos'] }}</span></strong
                    ><small>Espacios registrados como activos</small>
                </article>
            </div>
            <article class="panel panel-grafico-estadistico" aria-labelledby="titulo-grafico-estadistico">
                <div class="encabezado-panel">
                    <div>
                        <span class="sobrelinea">Actividad reciente</span>
                        <h2 id="titulo-grafico-estadistico">Movimiento de Chaskayni</h2>
                        <p>Distribución actual de cuentas, productos disponibles, pedidos y reportes pendientes.</p>
                    </div>
                    <small id="estado-actualizacion-estadisticas" role="status" aria-live="polite" aria-busy="true">Actualizando datos…</small>
                </div>
                <div class="grafico-estadistico-contenedor">
                    <svg id="grafico-estadistico" viewBox="0 0 400 300" role="img" aria-labelledby="titulo-grafico-estadistico descripcion-grafico-estadistico">
                        <desc id="descripcion-grafico-estadistico">Gráfico circular con la distribución actual de los indicadores del sistema.</desc>
                    </svg>
                </div>
                <ul id="leyenda-grafico-estadistico" class="leyenda-grafico-estadistico" aria-label="Distribución de registros por categoría"></ul>
            </article>
        </div>
        <div class="cuadricula-panel">
            <article class="panel">
                <span class="sobrelinea">Gestión comunitaria</span>
                <h2>Herramientas de administración</h2>
                <div class="acciones-tarjeta">
                    <a class="boton boton-contorno" href="{{ route('administracion.usuarios.indice') }}">Cuentas</a>
                    <a class="boton boton-contorno" href="{{ route('administracion.productos.indice') }}"
                        >Publicaciones</a
                    >
                    <a class="boton boton-contorno" href="{{ route('pedidos.recibidos') }}">Pedidos</a>
                    <a class="boton boton-contorno" href="{{ route('administracion.alertas.indice') }}">Administrar alertas</a>
                    <a class="boton boton-contorno" href="{{ route('administracion.comunidades.indice') }}"
                        >Comunidades</a
                    >
                    <a class="boton boton-contorno" href="{{ route('administracion.alertas.crear') }}"
                        >Publicar alerta</a
                    >
                    <a class="boton boton-contorno" href="{{ route('administracion.reportes.indice') }}"
                        >Revisar reportes ({{ $indicadores['reportes_pendientes'] }})</a
                    >
                    <a class="boton boton-contorno" href="{{ route('administracion.refugios.indice') }}"
                        >Gestionar refugios</a
                    >
                    <a class="boton boton-contorno" href="{{ route('administracion.consultas.indice') }}"
                        >Mensajes recibidos</a
                    >
                </div>
            </article>
            <aside class="panel panel-resaltado">
                <span class="sobrelinea">Cuentas y permisos</span>
                <h2>Acceso responsable</h2>
                <p>Revisa el rol y el estado de cada cuenta. La aplicación conserva siempre al menos una cuenta administradora activa.</p>
                <a class="boton boton-contorno" href="{{ route('administracion.usuarios.indice') }}">Ver cuentas</a>
            </aside>
        </div>
    </section>
    <script src="{{ asset('js/estadisticas-administracion.js') }}?v={{ filemtime(public_path('js/estadisticas-administracion.js')) }}" defer></script>
@endsection
