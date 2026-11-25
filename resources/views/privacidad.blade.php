@extends ('layouts.app')
@section ('titulo', 'Aviso de privacidad')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Información del proyecto</span>
            <h1>Aviso de privacidad</h1>
            <p>Qué datos solicita Chaskayni y cómo se muestran dentro de la plataforma.</p>
        </div>
    </section>
    <section class="seccion seccion-superior">
        <article class="panel contenido-privacidad">
            <h2>Datos de la cuenta</h2>
            <p>Al registrarte, solicitamos tu nombre, correo, DNI, teléfono y comunidad para crear la cuenta, evitar registros duplicados y revisar la identidad y la comunidad antes de aprobar el acceso.</p>
            <p>El DNI se guarda cifrado y también se conserva una huella técnica para detectar si ya fue usado. Administración puede consultar los datos de la cuenta para realizar esa revisión. El teléfono y la comunidad también se muestran en la gestión administrativa.</p>

            <h2>Pedidos y contacto</h2>
            <p>Los datos necesarios para coordinar un pedido pueden mostrarse a las personas que participan en esa operación. No incluyas datos personales de terceros en mensajes, descripciones o fotografías.</p>
            <p>Los mensajes enviados desde Contacto se reciben en la bandeja de administración para que el equipo pueda responderlos.</p>

            <h2>Reportes y fotografías</h2>
            <p>Los reportes quedan pendientes hasta que administración los revise. Si se aprueba un reporte, su información y la fotografía de evidencia pueden compartirse públicamente con la comunidad. Evita incluir rostros identificables, nombres, teléfonos, DNI u otros datos personales en la evidencia.</p>

            <h2>Consultas</h2>
            <p>Chaskayni es un proyecto académico. Para consultar sobre tus datos o el funcionamiento de la plataforma, comunícate mediante el <a href="{{ route('contacto.formulario') }}">formulario de contacto</a>.</p>
            <p class="texto-atenuado">Este aviso describe el funcionamiento actual de la aplicación y no sustituye una política legal revisada para un servicio en producción.</p>
        </article>
    </section>
@endsection
