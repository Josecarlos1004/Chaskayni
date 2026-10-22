# Chaskayni

Aplicación web de impacto social para conectar productores y compradores, consultar alertas comunitarias y enviar reportes de riesgo. El proyecto usa Laravel 12, PHP 8.2 y MySQL/MariaDB, de acuerdo con la consigna del curso que requiere framework MVC.

## Requisitos locales

- PHP 8.2 o superior con openssl, mbstring, pdo_mysql, fileinfo y curl.
- Composer 2.
- MySQL o MariaDB (XAMPP incluye los servicios necesarios para desarrollo local).

## Preparar el proyecto en XAMPP

1. Inicia MySQL desde el panel de XAMPP.
2. Abre phpMyAdmin y crea una base llamada chaskayni con cotejamiento utf8mb4_unicode_ci.
3. Abre una terminal en esta carpeta. El archivo .env local ya se generó durante la instalación; revisa que DB_DATABASE, DB_USERNAME y DB_PASSWORD coincidan con tu XAMPP. El .env no se sube a GitHub.
4. Instala las dependencias si acabas de clonar el repositorio con: composer install
5. Ejecuta las migraciones para crear las tablas con: php artisan migrate
6. Crea la cuenta administradora de forma interactiva con: php artisan app:crear-administrador
7. Levanta el servidor de desarrollo con: php artisan serve
8. Abre http://127.0.0.1:8000

Para servir con Apache de XAMPP, el directorio público del sitio debe apuntar a public/. No expongas la raíz del repositorio: contiene configuración y código que no deben servirse directamente.

## Funciones que ya están construidas

- Registro de compradores y productores e inicio/cierre de sesión.
- Roles básicos y protección de rutas privadas.
- Catálogo con búsqueda, filtro por categoría y publicaciones de productores.
- Creación, edición y ocultamiento de publicaciones.
- Solicitud de pedidos con verificación de stock dentro de una transacción.
- Base de datos para comunidades, pedidos, alertas de heladas, reportes de riesgo y refugios.
- Formulario de reporte de riesgo con revisión pendiente; un reporte no se publica automáticamente como alerta.
- Interfaz adaptable a celular y computadora.

Las tablas se definen en database/migrations/. Las tablas de alertas y refugios ya están modeladas, pero el panel para administrarlas aún queda pendiente.

## Seguridad

- Las contraseñas se guardan con hash.
- Formularios Laravel usan protección CSRF.
- Las rutas privadas exigen sesión.
- Las entradas se validan en el servidor y las vistas Blade escapan la salida.
- No agregues credenciales ni claves reales a archivos versionados. Comparte cambios con commits pequeños y descriptivos.

## Etapa del trabajo

La prioridad inmediata es completar el prototipo funcional de semana 12, documentar las decisiones y registrar las contribuciones de cada integrante con commits. No se incluyen datos de demostración para evitar presentar información inventada.