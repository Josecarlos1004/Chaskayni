# Chaskayni

Chaskayni es una aplicación web comunitaria para conectar productores y compradores de Cusco, publicar cosechas, coordinar pedidos y compartir alertas y reportes de riesgo.

## Tecnologías

- PHP 8.2 o superior
- Laravel 12
- MySQL o MariaDB
- Blade, CSS y JavaScript
- Leaflet para el mapa comunitario

## Funciones

- Registro e inicio de sesión para compradores y productores.
- Administración de cuentas y roles.
- Publicación de productos con imágenes.
- Cestas Salvadas con 30 % de descuento aplicado al precio del pedido.
- Solicitudes de compra, control de existencias y seguimiento de pedidos.
- Comunidades y alertas por temperatura ingresada por administración.
- Reportes comunitarios moderados por administración.
- Mapa de refugios activos y reportes revisados.
- Eliminación individual administrativa, con confirmación y protección de registros relacionados.

La alerta por temperatura se calcula a partir del valor ingresado por administración. No representa una consulta automática a un servicio meteorológico.

## Requisitos locales

1. Windows con XAMPP y PHP 8.2 o superior.
2. MySQL iniciado desde el panel de XAMPP.
3. Composer.
4. Node.js y npm para compilar los recursos del frontend.

Comprueba las versiones:

    php -v
    composer --version
    node --version
    npm --version

## Instalación en Windows

Abre PowerShell en la carpeta del proyecto, por ejemplo C:\xampp\htdocs\Chaskayni, y ejecuta:

    composer install
    Copy-Item .env.example .env
    php artisan key:generate

Edita el archivo .env localmente y configura la conexión MySQL. No compartas ese archivo ni subas sus credenciales a GitHub:

    APP_NAME=Chaskayni
    APP_ENV=local
    APP_DEBUG=true
    APP_URL=http://127.0.0.1:8000
    APP_LOCALE=es
    APP_FALLBACK_LOCALE=es
    APP_FAKER_LOCALE=es_PE

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=chaskayni
    DB_USERNAME=root
    DB_PASSWORD=

    FILESYSTEM_DISK=public

Crea la base de datos desde phpMyAdmin o la consola de MySQL:

    CREATE DATABASE chaskayni
      CHARACTER SET utf8mb4
      COLLATE utf8mb4_unicode_ci;

Si ya tienes datos de trabajo, conserva la base y haz una copia de seguridad antes de aplicar migraciones. Para crear o actualizar tablas sin borrar registros existentes, ejecuta:

    php artisan migrate

Prepara el almacenamiento público de imágenes:

    php artisan storage:link

Instala y compila los recursos del frontend cuando corresponda:

    npm install
    npm run build

## Crear la primera cuenta administradora

Con MySQL iniciado y las migraciones aplicadas, ejecuta:

    php artisan app:crear-administrador

Ingresa el nombre, correo y contraseña cuando el comando lo solicite. La contraseña se escribe sin mostrarse en pantalla y se guarda mediante el hash de Laravel. Luego inicia sesión en /ingresar.

El registro público solo permite crear cuentas de comprador o productor. El rol administrador se asigna mediante el comando.

## Ejecutar en desarrollo

En una terminal de PowerShell, desde la carpeta del proyecto:

    php artisan serve

Abre http://127.0.0.1:8000. Para editar CSS o JavaScript durante el desarrollo, deja otra terminal ejecutando npm run dev.

## Pruebas

Las pruebas están configuradas para conectarse a una base MySQL separada llamada chaskayni_pruebas. Créala antes de correrlas:

    CREATE DATABASE chaskayni_pruebas
      CHARACTER SET utf8mb4
      COLLATE utf8mb4_unicode_ci;

Luego ejecuta:

    php artisan test

No cambies DB_DATABASE de phpunit.xml a la base principal: las pruebas pueden recrear las tablas de la base de pruebas.

## Gestión administrativa y eliminación

Desde el panel /administracion se accede a cuentas, publicaciones, pedidos, alertas, comunidades, reportes y refugios. Las acciones de eliminación requieren rol administrador y confirmación.

- No se elimina una cuenta con información vinculada; se puede desactivar para conservar su historial.
- No se elimina una publicación asociada a pedidos.
- Eliminar un pedido pendiente o aceptado devuelve su cantidad al inventario; eliminar uno completado no lo devuelve.
- Las comunidades y refugios se eliminan individualmente. Al quitar una comunidad, las referencias relacionadas quedan sin comunidad cuando la relación de base de datos lo permite.

## Protección de configuración y datos

- El archivo .env contiene configuración local y no debe publicarse.
- No guardes contraseñas, claves ni credenciales reales en el repositorio.
- No uses migrate:fresh ni borres tablas en la base principal.
- Para compartir el proyecto, comparte el código y las instrucciones; cada integrante configura su propio .env.

## Verificación, seguridad y entrega

La guía `docs/guia-validacion-y-evidencias.md` contiene una matriz para revisar los recorridos, registrar pruebas de seguridad, medir consultas y completar las evidencias del informe. Los resultados deben completarse con mediciones reales; la suite usa una base MySQL separada y puede reconstruir sus tablas.

En despliegues con Docker, el inicio crea el enlace de almacenamiento público. Para conservar imágenes entre despliegues, monta un volumen persistente en `/var/www/html/storage/app` o configura un almacenamiento S3 compatible para los discos público y privado.
