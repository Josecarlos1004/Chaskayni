# Guía de despliegue de Chaskayni en Railway

## Preparar la aplicación

1. En Railway, abre el servicio web de Chaskayni y confirma que el directorio raíz sea la raíz del repositorio, donde están `Dockerfile` y `composer.json`.
2. En **Build**, selecciona Dockerfile o define `RAILWAY_DOCKERFILE_PATH=Dockerfile`. En los registros del build debe aparecer `Using detected Dockerfile!`.
3. En **Deploy**, borra cualquier Start Command personalizado para que se use el `CMD` del Dockerfile. El contenedor ejecuta `docker/iniciar.sh`, que configura Apache con `$PORT`, prepara el enlace de almacenamiento y ejecuta las migraciones normales (`php artisan migrate --force`).
4. Conserva la base MySQL de Railway y sus variables. En la aplicación, usa las referencias de Railway para `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`; no copies credenciales a este repositorio.
5. Define `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` y `APP_URL` con los valores de Railway. Mantén la misma `APP_KEY` entre despliegues: se usa para cifrar datos personales.
6. Para correo real, configura un proveedor SMTP y sus variables `MAIL_*`. La configuración de ejemplo usa el registro local y no envía correo.

## Imágenes y archivos

El volumen `mysql-volume` conserva la base de datos, no las imágenes subidas por Laravel. El contenedor tiene discos públicos y privados locales, pero esos archivos pueden perderse al reemplazar el contenedor. Para producción, configura almacenamiento S3 compatible y define `FILESYSTEM_PUBLIC_DRIVER=s3`, `FILESYSTEM_PRIVATE_DRIVER=s3`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET` y, si corresponde, `AWS_ENDPOINT` y `AWS_URL`. La evidencia de reportes debe permanecer privada.

## Migraciones y datos personales

Antes de desplegar migraciones que transformen datos, crea una copia de seguridad de MySQL desde Railway. La nueva migración cifra los DNI ya guardados y agrega una huella HMAC para conservar la validación de unicidad. Debe ejecutarse una sola vez mediante `php artisan migrate`; nunca uses `migrate:fresh`. No cambies la `APP_KEY` después de cifrar los DNI: sin la clave con la que fueron cifrados no se podrán leer. `migrate:rollback` descifra el DNI para volver al esquema anterior, así que úsalo solo como parte de un rollback planificado. No subas `.env`, copias de seguridad ni credenciales a GitHub.

No ejecutes `db:seed` en producción: los seeders de demostración se bloquearon para ese entorno. Crea y aprueba las cuentas reales desde los flujos administrativos.

## Cómo reconocer el fallo anterior

Si los logs mencionan `ServeCommand.php` o `artisan serve`, Railway está iniciando el servidor de desarrollo de Laravel. El servicio debe construir la imagen del `Dockerfile` y arrancar Apache mediante `docker/iniciar.sh`; la advertencia de `PHP_CLI_SERVER_WORKERS` no es una corrección para el despliegue.