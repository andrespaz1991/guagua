# Auditoría de código, seguridad y rendimiento de Guagua

Fecha: 12 de octubre de 2026. Instalación local: `D:\xampp\htdocs\guagua`.

## Alcance y evidencia

Se inventariaron 545 archivos propios PHP, JavaScript, HTML y herramientas, y se contrastaron 54 entradas de menú de la base local. Las dependencias de terceros, archivos de usuarios y resultados generados se separaron del inventario. Se verificó la sintaxis de 259 archivos PHP. La prueba de arquitectura revisó además 1067 archivos PHP sin encontrar conexiones directas fuera del proveedor central.

La búsqueda de patrones es un indicador de revisión: una aparición de `SELECT *`, SHA1, `eval` en comentarios o parámetros en una línea no demuestra por sí sola una vulnerabilidad. Los cambios siguientes se basan en lectura del flujo y pruebas específicas.

Se examinó una muestra de los últimos 10 MiB del registro local de Apache: 10729 peticiones de Guagua entre el 31 de agosto y el 12 de octubre de 2026. El detalle agregado está en `auditoria-accesos-resumen.json`; no contiene direcciones IP, credenciales ni consultas completas. Incluye las comprobaciones realizadas durante esta auditoría.

## Correcciones realizadas

| Módulo | Problema comprobado | Cambio |
|---|---|---|
| Inicio: `index.php`, `index3.php` y `Persona` | PHP de la tabla `tarjeta` ejecutado mediante `eval`; atributos y títulos sin escapar | Renderizadores explícitos por ID, enlaces y texto controlados; consulta de columnas necesarias. Se conserva la tarjeta especial de pendientes. Las tarjetas personalizadas nuevas deben registrar su renderizador en código. |
| Usuarios | CRUD con SQL concatenado, eliminación por GET y listado con todas las columnas | Repositorio separado con parámetros; acceso administrativo, CSRF y eliminación por POST; edición conserva la contraseña; impide borrar la propia cuenta. |
| Contenidos, objetivos, recursos, tarjetas y eventos | Cinco CRUD repetidos con SQL concatenado, búsquedas costosas y salida sin escapar | Definiciones comunes, repositorio con parámetros y vista profesional compartida; paginación a 20 filas, texto escapado, CSRF y exportación CSV para Excel con protección contra fórmulas. Eventos respeta institución y propietario docente. Recursos se identifica por su ID. |
| Contraseñas | El login podía aceptar como contraseña el propio hash almacenado; cuentas nuevas usaban documento como clave | Verificación separada por formato, rechazo de hashes como credenciales y contraseña inicial de al menos 8 caracteres. Las cuentas nuevas usan `password_hash`; las antiguas se actualizan al autenticar correctamente. Se conserva el acceso por mascota existente. |
| Registro antiguo | Segundo formulario con inserción SQL y carga de foto inseguras | Alias hacia el CRUD administrativo actual. La ruta se conserva. |
| Recuperación | SQL concatenado, token predecible, enlace de destino inexistente y ruta antigua que enviaba la contraseña guardada | Flujo nuevo con firma HMAC, vigencia de 30 minutos, invalidación al cambiar la contraseña, CSRF y consultas preparadas. Mensajes públicos uniformes y espera mínima de 60 segundos por sesión. |
| Directorio del inicio | Consulta sin autenticar, nombre concatenado, filtro de curso ignorado y selección por año de creación del usuario | Consulta preparada basada en matrícula y año lectivo `Activo`; institución y docente delimitan los resultados; filtro de curso/grado, máximo 50 estudiantes. |
| JavaScript del directorio | HTML construido con datos sin escapar y petición por cada pulsación | Construcción con nodos DOM y `textContent`, imágenes diferidas, espera de 250 ms, cancelación y descarte de respuestas atrasadas. |
| Visibilidad de cursos | Cambios sin CSRF y sin comprobar propiedad del curso | POST con token, validación, institución y docente propietario; permisos incluidos en el UPDATE. Se adaptaron los clientes del inicio y Mis cursos. |
| Búsqueda antigua de cursos | SQL concatenado, OR sin agrupar, referencia a tabla inexistente y respuesta JavaScript ejecutable | Tablas actuales, JOIN y parámetros; salida JSON. Ambos clientes antiguos consumen JSON sin `eval`. |
| Lectores SQL de clases y Seguimiento | SQL obtenido del cuerpo HTTP, eliminación de palabras dentro de valores y preparación duplicada | SELECT explícito proveniente del código, parámetros opcionales, cierre de statements y una sola preparación. La conexión permanece viva hasta finalizar. |
| Descargas `red/readfile*.php` | Rutas arbitrarias, token predecible que permitía acceso anónimo y lectura completa en memoria en la variante antigua | Autenticación, resolución con `realpath`, límites de carpeta y extensiones; alias compartido y descarga por streaming liberando la sesión. |
| Dispatch compartido | Handlers ejecutados antes de verificar sesión y logins alternativos que comparaban hashes o aceptaban roles del cliente | Sesión antes del dispatch; acceso antiguo rechazado con 410; parámetros del login limitados a los esperados. Los handlers antiguos exigen token o procedencia del mismo origen. |
| Cargas antiguas | Nombres con rutas, extensiones ejecutables o archivos de configuración | Validación compartida de nombres. Apache impide ejecutar código dentro de carpetas de medios, incluso nombres como `archivo.php.jpg`. |
| Instalación, cuentas de BD, respaldo y Evidencias | Entradas administrativas sin controles consistentes; desinstalador de Evidencias accesible sin autenticar | Administración y CSRF antes de las acciones. El respaldo por consola conserva la entrada usada por cron. No se ejecutó instalación, desinstalación ni respaldo para probarlo. |
| Seguimiento, importación antigua y administración de tarjetas | Entradas con permisos insuficientes o controles después de emitir contenido | Verificación de rol antes de leer o actuar; importación exige administración y CSRF. |
| Apache | Metadatos internos, pruebas y herramientas servibles | Bloqueo de `.git`, directorios internos, pruebas y scripts de consola; `.env` continúa protegido. |

Los helpers nuevos están en `usuario/lib/usuarios_repository.php`, `passwords.php`, `password_reset.php`, `estudiantes_home.php`, `comun/home_cards.php`, `comun/files.php`, `comun/sql.php` y `comun/catalog_*`. Las cinco rutas de catálogo ahora declaran su configuración y cargan la vista común. Las vistas ya no contienen la lógica de persistencia.

## Rendimiento comprobado

- En la misma muestra de 10 usuarios, el JSON de los resultados pasó de **5642 a 1456 bytes: 74,2 % menos** al seleccionar seis columnas necesarias. El resultado ya no incluye `clave`. Esto mide volumen de resultados, no una reducción equivalente del tiempo total de la página.
- La búsqueda de usuarios sigue paginada a 10 registros y acota la página solicitada. Sus peticiones anteriores se cancelan.
- El directorio tiene un máximo de 50 filas, agrupa la actualización del DOM y evita consultas disparadas por cada tecla.
- El lector de Seguimiento prepara una vez; se comprobó el incremento de `Com_stmt_prepare` en una conexión de prueba.
- Las descargas liberan el bloqueo de sesión antes de transmitir el archivo.
- Los cinco catálogos realizan COUNT y consulta paginada, sin cargar todos los registros para contarlos ni consultar dentro de cada palabra buscada. Su esquema real se verificó y sus SELECT preparados funcionan con los 164 contenidos, 210 objetivos, 144 recursos y 8 tarjetas de esta base. No se modificaron esas filas. La institución consultada tiene 0 eventos.
- Se dejó compresión condicional para CSS, JavaScript y SVG. **El Apache local no tiene `mod_deflate` cargado**, por lo que esta mejora requiere activar ese módulo en la configuración del servidor y reiniciar Apache. Esa configuración está fuera del proyecto y no se modificó.

## Pruebas y límites

Pruebas nuevas: `tests/legacy_security_test.php` y `tests/legacy_repository_mysql_test.php`. Cubren hashes, firmas, caducidad, reutilización de enlaces, nombres peligrosos, rutas, procedencia, consultas con apóstrofes, intentos de SQL, comodines literales, paginación, fórmulas en CSV y separación por docente/institución/año. También prueban CRUD de catálogo y rechazo de eventos ajenos. El CRUD y el restablecimiento se probaron con tablas TEMPORARY que desaparecen al cerrar la conexión, sin tocar registros reales.

También pasaron las regresiones de sesión, conexión, periodos, fechas de asistencia y Excel. Los cambios JavaScript pasaron verificación de sintaxis.

Por HTTP se comprobó login y recuperación con 200; inicio anónimo redirige al login; entradas de consulta/administración rechazan acceso anónimo con 401; código antiguo de login con 410; `.env`, `.git`, scripts de consola, pruebas y cargas ejecutables con 403. Las pruebas no ejecutaron eliminaciones de usuarios reales, restauraciones, importaciones ni envíos de correo.

**Correo pendiente de configuración:** ya se generó una clave privada aleatoria `GUAGUA_RESET_SECRET` en `.env` y la URL local. Deben configurarse `GUAGUA_MAIL_FROM`, la URL pública al desplegar y el transporte de correo de PHP/SMTP. La entrega real no fue validada. El límite por sesión es básico; para exposición pública conviene limitar también por IP/cuenta en el servidor.

## Código que requiere otra migración

Quedan familias antiguas que el inventario identifica y que requieren una migración funcional con pruebas de sus consumidores:

1. `comun/funciones.php`, `comun/funciones.rutas.php` y partes de `clases/Academico.Class.php`: métodos mezclados con handlers, SQL concatenado y comprobaciones de propiedad dispersas. Se cerraron los accesos anónimos y la autenticación alternativa, pero las consultas restantes necesitan convertirse por caso de uso; autenticar y comprobar origen no sustituye los parámetros SQL ni la autorización por registro.
2. Otras pantallas y métodos que el inventario marca por SQL concatenado o salida antigua: requieren revisar el flujo real y sus permisos antes de sustituirlos. Los cinco catálogos de contenido, objetivos, recursos, tarjetas y eventos ya fueron migrados a un repositorio y vista común; sus tablas existentes se conservan.
3. Partes de `seguimiento/funciones.php` y `citas.php`: requieren completar autorización por matrícula/institución y separar listado, persistencia y exportadores antiguos. Ahora las entradas exigen personal autorizado; eso no demuestra aislamiento de cada registro.
4. Cargas y extracción SCORM/ZIP en código antiguo: además del bloqueo de código en medios, hace falta validar entradas y cuotas del ZIP, probar casos de traversal y separar o aislar el contenido HTML/JavaScript educativo.
5. Dependencias antiguas usadas: PHPExcel en `comun/lib`, PHPWord en `Planeador/PHPWord-master`, TinyButStrong/OpenTBS de `miplaneador` y copias antiguas de bibliotecas JavaScript. No son archivos sobrantes por su edad. PHPExcel y PHPWord tienen consumidores encontrados y requieren reemplazo o actualización con pruebas de importación/documentos.
6. Duplicación de `comun/js/funciones.js` y la copia de `plantilla1`, así como callbacks JavaScript por texto en los footers. Se adaptaron ambas copias para los cambios actuales; falta consolidar su contrato para retirar la duplicación.

No se añadieron índices ni se cambiaron tablas reales: un ajuste de índices necesita EXPLAIN de los listados más usados y medición con volumen representativo. Los tiempos locales de rechazos de acceso no miden el rendimiento completo con usuarios concurrentes.

## Archivos posiblemente sin uso

Se identificaron **33 candidatos PHP sin referencias estáticas ni entrada directa de menú**. El detalle está en `archivos-sin-referencias.md` y el inventario completo en `inventario-codigo-guagua.csv`.

Los candidatos más claros para revisar primero son:

| Archivo | Evidencia | Tratamiento |
|---|---|---|
| `index3.php` | Versión paralela del inicio; sin referencias, menú ni accesos en la muestra | Candidato a retirar después de comprobar favoritos externos. También recibió el control de tarjetas. |
| `cursos/funcionesbd.php` | Constructor SQL antiguo; sin consumidores detectados | Candidato a retirar tras comprobar scripts externos. |
| `seguimiento/actualizar_proyecto.php` | Entrada de cinco líneas con llamada de actualización comentada | Candidato a retirar. |
| `seguimiento/actualizar_datos.php` | Utilidad CSV antigua, operación de actualización comentada; sin accesos | Mantener solo si sigue existiendo ese procedimiento administrativo. Ahora está protegida. |
| `red/iconos_red.php` | Pantalla antigua sin referencias, menú ni accesos | Candidato a retirar tras contraste con navegación dinámica. |
| `mensajes/bandeja_salida.php` | Entrada antigua sin referencias ni accesos | Revisar contrato del módulo de mensajes antes de retirar. |

Los alias `usuario/registrarse.php`, `usuario/Recuperar_contra.php` y `red/readfile2.php` se conservan por compatibilidad. Los scripts de migración de rúbricas y los instaladores de Evidencias son herramientas de mantenimiento; ausencia de tráfico habitual no justifica borrarlos. Las clases por autoload, plugins, tareas de cron y las dependencias usadas se excluyen de una decisión automática de eliminación.

**No se borró ningún módulo ni archivo de negocio.** El registro local es una muestra, no contiene todos los accesos de otros servidores y los recuentos de URL no detectan cada include. La eliminación debe basarse también en revisión de cron, accesos externos y pruebas de navegación.

## Repetir el inventario

`python tests/audit_legacy_inventory.py output/auditoria-menu-snapshot.json`

`python tests/audit_access_summary.py D:\xampp\apache\logs\access.log`

La instantánea de menú corresponde a esta revisión; actualícela si cambia la configuración. El inventario usa nombres de archivos, por lo que admite falsos positivos y no resuelve por completo rutas construidas dinámicamente. La revisión visual en el navegador integrado quedó limitada porque bloqueó la navegación a localhost; se validaron sintaxis, consultas, respuestas HTTP y modelo de datos.

Criterios de referencia: [verificación de contraseñas PHP](https://www.php.net/manual/en/function.password-verify.php), [hash de contraseñas PHP](https://www.php.net/manual/en/function.password-hash.php), [prevención CSRF de OWASP](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html) y [compresión estática de Apache](https://httpd.apache.org/docs/2.4/mod/mod_deflate.html).
