# Configuración central de base de datos

Edite únicamente el archivo `.env` de la raíz de Guagua. Los cambios se aplican en la siguiente petición o ejecución del script. No es necesario editar los módulos.

- `GUAGUA_DB_HOST`, `GUAGUA_DB_PORT`, `GUAGUA_DB_USER`, `GUAGUA_DB_PASSWORD` y `GUAGUA_DB_NAME`: conexión principal.
- `GUAGUA_DB_CHARSET`: codificación, normalmente `utf8mb4`.
- `GUAGUA_TIMEZONE` y `GUAGUA_DB_TIME_ZONE`: zona PHP y desplazamiento MySQL, normalmente `America/Bogota` y `-05:00`.
- `GUAGUA_APP_ENV`: `local` o `production`.
- `GUAGUA_DB_EDUTASK_NAME`, `GUAGUA_DB_PROMPT_NAME` y `GUAGUA_DB_MOODLE_NAME`: aplicaciones con esquemas independientes que heredan servidor y credenciales.
- `GUAGUA_DB_HOSTING_HOST`, `PORT`, `USER`, `PASSWORD` y `NAME` (cada sufijo con el prefijo completo): perfil remoto para los scripts de importación existentes. No hereda credenciales locales.

Las variables de entorno del servidor tienen prioridad sobre `.env`. Use `.env.example` como plantilla en otra instalación. No publique `.env`; está excluido de Git y bloqueado por Apache.

En PHP, incluya `comun/database.php` con una ruta basada en `__DIR__` y use `guagua_db_mysqli()` o `guagua_db_pdo()`. El proveedor no inicia sesiones ni consulta tablas de negocio. Los conectores antiguos mantienen sus funciones y delegan en él. Cada llamada obtiene una conexión independiente.

Los scripts Python y PowerShell leen esta misma configuración mediante el puente PHP CLI privado. Las herramientas MySQL reciben la contraseña por el entorno del proceso, sin incluirla en los argumentos. El instalador actualiza `.env` y conserva los perfiles adicionales.

Validación: `php tests/database_config_test.php` y `php tests/database_connection_test.php`. La segunda prueba requiere MySQL disponible y solo realiza lecturas; no ejecuta importaciones, restauraciones ni respaldos.

Para trasladar la instalación, un administrador o docente puede usar el botón **Descargar Guagua** junto a su cuenta en la barra superior, o abrir **Configuración → Descargar Guagua**. El ZIP contiene `guagua/`, los SQL en `base_de_datos/`, `IMPORTAR_BD.bat` y `LEEME-XAMPP.txt`. La base principal es obligatoria; las bases visibles de EduTask, Prompt y Moodle son opcionales. El manifiesto y la guía registran las bases incluidas y omitidas. Un error al exportar impide entregar un paquete incompleto.

La copia sustituye las credenciales de conexión de origen por una configuración local: `localhost:3306`, `root`, contraseña vacía. Conserva los nombres de las bases y las claves de aplicación y cifrado. Para otro puerto, usuario o contraseña, edite el `.env` del destino. El asistente de importación pide la ruta de XAMPP, el puerto y la contraseña de root; use una instalación nueva porque los SQL reemplazan tablas existentes. No ejecute nuevamente el instalador de Guagua.

Requisitos de exportación: PHP con `ZipArchive`, `proc_open` y el cliente `mysqldump`/`mariadb-dump` de XAMPP o del `PATH`. Se exportan tablas, vistas, datos binarios, disparadores, rutinas y eventos, sin cargar el SQL completo en memoria. Evite cambios de estructura durante la descarga; la instantánea consistente de datos depende de tablas transaccionales. Los temporales se crean fuera de la carpeta pública y se eliminan al finalizar o ante un fallo. El cron anterior de `reportes/copia.php` se conserva.

Prueba sin datos reales: `php tests/portable_backup_test.php`. La prueba de restauración `tests/portable_backup_mysql_test.php` exige un servidor temporal y la ruta explícita de sus datos; rechaza conexiones fuera de `tmp/portable-backup-qa-*`.

Para acelerar el ZIP se usa la biblioteca ZipStream ya instalada con PhpSpreadsheet, con lectura en bloques y compresión rápida del código y SQL. Imágenes, PDF, Office y otros formatos comprimidos se almacenan directamente. Si esa biblioteca no está disponible, se conserva el escritor nativo `ZipArchive`.

Los archivos se mantienen en una caché privada fuera del directorio público. Cada descarga comprueba rutas, tamaños y fechas de modificación; solo vuelve a empaquetar archivos añadidos o cambiados y retira los eliminados. Las bases de datos y el `.env` se generan nuevamente en cada descarga y nunca entran en esa caché. La primera preparación aún depende del volumen de archivos; las siguientes reutilizan el trabajo. Se conserva una sola versión de archivos y un bloqueo evita preparaciones simultáneas que saturen el disco. Pruebas: `php tests/portable_backup_test.php` y `php tests/portable_zip_writer_test.php`.

