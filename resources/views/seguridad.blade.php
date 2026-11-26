@extends ('layouts.app')
@section ('titulo', 'Seguridad')
@section ('content')
    <section class="encabezado-pagina">
        <div>
            <span class="sobrelinea">Uso responsable</span>
            <h1>Seguridad en Chaskayni</h1>
            <p>Medidas que ya utiliza la aplicación y recomendaciones para cuidar tu cuenta.</p>
        </div>
    </section>
    <section class="seccion seccion-superior">
        <article class="panel contenido-privacidad">
            <h2>Protecciones de la aplicación</h2>
            <ul>
                <li>Las contraseñas se guardan mediante el mecanismo de hash de Laravel.</li>
                <li>El DNI se almacena cifrado; se usa una huella para detectar registros duplicados.</li>
                <li>Las acciones de administración requieren una cuenta con ese rol.</li>
                <li>Los formularios aplican validaciones y algunos envíos tienen límite de intentos.</li>
                <li>Las fotografías aceptan formatos definidos y un tamaño máximo de 5 MB.</li>
            </ul>
            <h2>Cuida tu cuenta y tus publicaciones</h2>
            <ul>
                <li>Usa una contraseña larga y no la compartas.</li>
                <li>No publiques teléfonos, DNI, direcciones particulares ni datos de otras personas en reportes o fotos.</li>
                <li>Los reportes quedan en revisión y, al aprobarse, su información y evidencia pueden ser visibles para la comunidad.</li>
                <li>No publiques el archivo <code>.env</code> ni claves de acceso en el repositorio.</li>
            </ul>
            <p class="texto-atenuado">Estas medidas reducen riesgos, pero no garantizan seguridad total. Chaskayni es un proyecto académico y requiere revisión antes de usarse como servicio público.</p>
        </article>
    </section>
@endsection