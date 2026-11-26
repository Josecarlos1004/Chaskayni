# Guía de verificación y evidencias de Chaskayni

Esta guía prepara las comprobaciones y el material del informe sin inventar resultados. Las filas marcadas **Pendiente** deben completarse con ejecuciones reales y capturas del equipo.

## 1. Seguridad antes de probar

- Usa únicamente una copia local y la base MySQL `chaskayni_pruebas`.
- No apuntes las pruebas a `chaskayni`, Railway ni a una instancia usada por el equipo.
- La configuración de PHPUnit fija `DB_CONNECTION=mysql` y `DB_DATABASE=chaskayni_pruebas`.
- Los tests usan `RefreshDatabase`. Laravel puede reconstruir las tablas de esa base de pruebas al ejecutar el conjunto. No ejecutes el conjunto hasta confirmar que esa base es desechable y no contiene información que debas conservar.
- No ejecutes `migrate:fresh` manualmente. La base principal se actualiza solo con migraciones aditivas.

## 2. Verificación funcional

El proyecto ya incluye pruebas de inicio, comunidades, imágenes, pedidos, Cestas Salvadas, alertas, mapa, catálogos y eliminaciones administrativas en `tests/Feature`.

| Recorrido | Qué comprobar | Evidencia por adjuntar | Resultado |
|---|---|---|---|
| Registro y aprobación | Comprador/productor registra DNI, teléfono y comunidad; queda pendiente; administración asigna comunidad y aprueba. | Capturas sin DNI/correo/teléfono reales; usa usuarios ficticios. | Pendiente |
| Roles | Comprador no puede entrar a administración; productor solo gestiona sus publicaciones y pedidos; administrador ve sus paneles. | Captura de respuesta o registro de prueba con resultado esperado/obtenido. | Pendiente |
| Mercado e imágenes | Imagen válida se guarda y aparece; formato no permitido se rechaza; imagen ausente muestra alternativa. | Capturas y nombre del test ejecutado. | Pendiente |
| Cesta Salvadas | Verifica que el precio aplicado sea 70 % del precio base y que el pedido conserve el precio de compra. | Captura de producto y pedido con datos ficticios. | Pendiente |
| Pedidos | El comprador ve su pedido; el productor avanza estados válidos; otra cuenta no puede modificarlo. | Capturas de la secuencia y los estados. | Pendiente |
| Reportes y alertas | Reporte nuevo queda pendiente; solo al aprobar administración se publica como alerta; la foto no es visible antes de aprobar. | Capturas antes y después, con datos ficticios. | Pendiente |
| Mapa y refugios | Solo aparecen refugios activos y reportes aprobados; verifica enlaces al detalle. | Captura del mapa y registros de prueba. | Pendiente |

### Cuidado con la suite automática

No se ejecutó `php artisan test` durante esta preparación. Aunque PHPUnit apunta a la base de pruebas, `RefreshDatabase` puede reconstruir sus tablas. Antes de ejecutarlo, confirma que `chaskayni_pruebas` no guarda datos que quieras conservar. La base principal no debe usarse.

## 3. Seguridad web: plan de evidencia

Los siguientes controles se observaron en el código; esto es una **revisión estática**, no un resultado de pruebas de ataque:

- Validación del lado del servidor en controladores.
- Consultas Eloquent/query builder con valores de búsqueda como parámetros.
- Escape de salida Blade mediante `{{ ... }}`.
- Tokens CSRF en formularios POST/PATCH/DELETE.
- Comprobaciones de rol y propiedad en operaciones administrativas y de productor.
- Fotos de reportes en el disco privado y acceso administrativo; la ruta pública solo entrega fotos de reportes publicados.
- Límites de intentos en ingreso, registro y formulario de contacto.

| Prueba local segura | Resultado esperado | Resultado real / captura |
|---|---|---|
| Ingresar como comprador a una ruta administrativa | Acceso denegado (403), sin cambios de datos. | Pendiente |
| Intentar actualizar un pedido de otro productor | Acceso denegado (403), pedido sin cambios. | Pendiente |
| Buscar el texto literal `%' OR '1'='1` | Se trata como texto de búsqueda; no amplía resultados ni altera datos. | Pendiente |
| Enviar `<script>alert('prueba')</script>` en una descripción de prueba | Se muestra escapado como texto o se rechaza; no se ejecuta JavaScript. | Pendiente |
| Enviar formulario sin token CSRF | Laravel lo rechaza; no se guarda la operación. | Pendiente |
| Subir un archivo de texto renombrado como `.png` | Validación lo rechaza y no queda archivo guardado. | Pendiente |
| Pedir foto privada de un reporte pendiente como visitante | Respuesta 404, sin exponer la fotografía. | Pendiente |
| Superar el límite de intentos de ingreso | Solicitudes adicionales quedan limitadas; no se bloquean cuentas de otras personas. | Pendiente |

Haz estas comprobaciones solo con cuentas y contenido ficticios en local. Registra fecha, entorno, paso, resultado esperado, resultado real y captura. No uses datos de usuarios reales.

## 4. Rendimiento: medir antes de optimizar

El mapa carga todos los refugios activos y reportes publicados para mostrarlos. Esto puede crecer con los datos; **no se ha medido que hoy sea la causa de demora**. Antes de añadir índices, paginación o límites, registra una línea base.

1. Usa la misma copia de base de pruebas y un conjunto de datos ficticio documentado.
2. En las herramientas del navegador (Network), mide la carga de `/mapa-comunitario` y guarda duración, tamaño de respuesta y cantidad de registros.
3. Repite la medición al menos cinco veces en las mismas condiciones; anota cada valor y reporta la mediana. No presentes como medición una estimación.
4. En MySQL ejecuta `EXPLAIN` para las consultas equivalentes y guarda el plan:

```sql
EXPLAIN
SELECT id, titulo, descripcion, latitud, longitud, comunidad_id, created_at
FROM reportes_riesgo
WHERE estado = 'publicado'
ORDER BY created_at DESC;

EXPLAIN
SELECT id, nombre, direccion, latitud, longitud, comunidad_id
FROM refugios
WHERE activo = 1
ORDER BY nombre;
```

5. Si el plan y la medición muestran un cuello de botella, cambia una cosa a la vez (por ejemplo, índice compuesto, columnas necesarias o carga paginada) y repite con el mismo conjunto de datos.
6. Conserva capturas del antes/después, el plan `EXPLAIN`, volumen de datos, equipo/entorno, versión y commit. Si no se midió mejora, informa el resultado sin afirmar que fue más rápido.

| Medida | Antes | Después | Dataset y condiciones |
|---|---:|---:|---|
| Tiempo de `/mapa-comunitario` (mediana de 5) | Pendiente | Pendiente | Pendiente |
| Tamaño de respuesta | Pendiente | Pendiente | Pendiente |
| Reportes/refugios cargados | Pendiente | Pendiente | Pendiente |
| Filas examinadas según `EXPLAIN` | Pendiente | Pendiente | Pendiente |

## 5. Persistencia de imágenes en Railway/Docker

El código ya usa discos separados: `public` para imágenes de productos/alertas y `privado` para evidencias de reportes. `config/filesystems.php` permite usar almacenamiento local o S3 mediante variables de entorno.

Para conservar archivos locales en Railway:

1. En el servicio de Chaskayni, crea un volumen persistente y configúralo con la ruta de montaje **`/var/www/html/storage/app`**. Es la ruta de almacenamiento del contenedor definida por el `Dockerfile`.
2. Confirma que el inicio del contenedor crea los directorios, concede escritura a Apache y crea el enlace `public/storage`.
3. En variables del servicio deja `FILESYSTEM_PUBLIC_DRIVER=local` y `FILESYSTEM_PRIVATE_DRIVER=local` para usar ese volumen. No guardes credenciales en GitHub.
4. Publica una imagen de prueba y una evidencia privada de prueba. Comprueba que la imagen de producto se ve desde otra sesión, y que la evidencia privada no se ve como visitante.
5. Reinicia/re-despliega el servicio y repite ambas comprobaciones. Adjunta capturas antes/después.

En Railway los archivos fuera de un volumen son efímeros; el volumen debe montarse en la ruta que realmente usa Laravel. Si se prefiere S3 compatible, configura las variables AWS en Railway y usa `FILESYSTEM_PUBLIC_DRIVER=s3` y `FILESYSTEM_PRIVATE_DRIVER=s3`; la evidencia privada debe conservar visibilidad privada.

| Comprobación de persistencia | Resultado |
|---|---|
| Volumen adjunto a `/var/www/html/storage/app` | Pendiente |
| Foto de producto visible para otra sesión | Pendiente |
| Evidencia privada bloqueada para visitante | Pendiente |
| Ambas sobreviven a redeploy/reinicio | Pendiente |

## 6. Texto base para completar el informe

### 7.7 Optimización de consultas

> Se evaluó la página **[ruta]** en una copia local con MySQL y datos ficticios (**[cantidad de reportes/refugios/productos]**). Se registró una mediana de **[medición real]** antes del cambio. El plan `EXPLAIN` mostró **[hallazgo real]**. Se aplicó **[cambio]** en **[archivo/commit]** y, con el mismo entorno y datos, la mediana fue **[medición real]**. La diferencia observada fue **[cálculo]**. Estas mediciones corresponden al entorno de prueba, no a una medición de producción.

No rellenes los corchetes sin haber obtenido esos valores. Si no aplicas una optimización porque no se demuestra un problema, explica que mediste y no encontraste evidencia suficiente para cambiar la consulta.

### 7.8 Seguridad web

> Se revisaron las entradas del sistema y los controles observados en Laravel: validación del lado servidor, consultas parametrizadas mediante Eloquent/query builder, escape Blade, protección CSRF y autorización por rol/propiedad. Se ejecutaron los casos de la matriz en una instancia local con la base de pruebas **[nombre]**, usando cuentas y contenido ficticios. De **[n]** casos, **[resultado real]**. Los casos pendientes y sus acciones correctivas son **[lista real]**.

Distingue controles observados en código de pruebas ejecutadas. No escribas que un control pasó una prueba si no se realizó.

## 7. Datos del informe que debe completar el equipo

- **Entrevistas:** al menos tres entrevistas reales a beneficiarios potenciales; incluir preguntas, síntesis fiel, fecha y consentimiento. No publicar datos personales sin autorización.
- **Actas:** fechas, participantes y acuerdos de reuniones que sí ocurrieron.
- **Contribuciones:** porcentajes y tareas basados en evidencia real del equipo.
- **Referencias:** verificar cada fuente y usar el formato pedido por la universidad.
- **Anexos:** capturas actuales, diagramas actualizados, manual de usuario, URL exacta del repositorio, evidencia de pruebas y declaración firmada.
- **Resultados:** separar resultados medidos de metas o impacto esperado; no inventar estadísticas ni testimonios.