@extends ('layouts.app')
@section ('titulo', 'Panel de administración')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Administración</span>
            <h1>Resumen de Chaskayni</h1>
            <p>Consulta el estado general y accede a las herramientas de gestión.</p>
        </div>
        <a class="boton" href="{{ route('administracion.usuarios.indice') }}">Gestionar cuentas</a>
    </section>
    <section class="seccion seccion-superior">
        <div class="cuadricula-estadisticas">
            <article class="tarjeta-estadistica">
                <span>Cuentas registradas</span><strong>{{ $cantidadUsuarios }}</strong
                ><small>Usuarios del sistema</small>
            </article>
            <article class="tarjeta-estadistica">
                <span>Cuentas por aprobar</span><strong>{{ $cantidadCuentasPendientes }}</strong
                ><small>Revisa identidad y teléfono en Cuentas</small>
            </article>
            <article class="tarjeta-estadistica">
                <span>Productos disponibles</span><strong>{{ $cantidadProductos }}</strong
                ><small>Publicaciones visibles en el mercado</small>
            </article>
            <article class="tarjeta-estadistica">
                <span>Pedidos pendientes</span><strong>{{ $cantidadPedidosPendientes }}</strong
                ><small>Solicitudes por coordinar</small>
            </article>
            <article class="tarjeta-estadistica">
                <span>Reportes pendientes</span><strong>{{ $cantidadReportesPendientes }}</strong
                ><small>Aportes comunitarios por revisar</small>
            </article>
            <article class="tarjeta-estadistica">
                <span>Alertas activas</span><strong>{{ $cantidadAlertasActivas }}</strong><small>Avisos vigentes</small>
            </article>
            <article class="tarjeta-estadistica">
                <span>Refugios activos</span><strong>{{ $cantidadRefugiosActivos }}</strong
                ><small>Espacios registrados como activos</small>
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
                    <a class="boton boton-contorno" href="{{ route('administracion.alertas.indice') }}">Alertas</a>
                    <a class="boton boton-contorno" href="{{ route('administracion.comunidades.indice') }}"
                        >Comunidades</a
                    >
                    <a class="boton boton-contorno" href="{{ route('administracion.alertas.crear') }}"
                        >Publicar alerta</a
                    >
                    <a class="boton boton-contorno" href="{{ route('administracion.reportes.indice') }}"
                        >Revisar reportes</a
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
                <a class="boton boton-oscuro" href="{{ route('administracion.usuarios.indice') }}">Ver cuentas</a>
            </aside>
        </div>
    </section>
@endsection
