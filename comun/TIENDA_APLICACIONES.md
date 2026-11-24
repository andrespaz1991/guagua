# Tienda de aplicaciones de Guagua

Acceso: **APPS** o **Configuración → Tienda de aplicaciones**. Administradores, directivos y docentes pueden instalar, desinstalar y descargar respaldos. Estos cambios afectan a todos los usuarios de la instalación. La página y la API comparten los permisos definidos en `comun/tienda_permisos.php`.

La tienda descubre las aplicaciones con entrada `index.php`, `index.html`, `index2.php` o `static/index.html` dentro de `apps/`. Reutiliza nombres y descripciones del menú. Omite carpetas de copias y scripts sin entrada. No instala aplicaciones de terceros ni descarga código de Internet.

## Apariencia

La cabecera incluye los botones **Claro** y **Oscuro**. La elección se aplica al catálogo, la biblioteca, el gestor del menú y sus editores. Se guarda en este navegador para próximas visitas y se aplica antes de dibujar la página para evitar un destello con el tema incorrecto. El modo inicial es oscuro. Esta preferencia solo afecta al módulo Apps.

## Menú integrado

**APPS** de la cabecera abre `comun/tienda.php`. La sección **Menú** dentro de Apps permite buscar y filtrar todos los accesos, crear y editar nombres, descripciones, categorías, enlaces, iconos y destino de pestaña. También permite marcar favoritas, anclar a cabecera y añadir las apps instaladas que no tienen acceso. El editor usa la misma interfaz de Apps, sin un iframe ni una página separada.

El botón **Gestionar menú** aparece en la cabecera del catálogo. Cada tarjeta también tiene **Editar menú** y un lápiz sobre el icono: ambos abren directamente el editor de ese acceso, sin salir de la vista actual ni pasar por los detalles. Si la app aún no tiene un acceso, se prepara uno nuevo. Los detalles conservan el acceso **Configurar acceso en el menú**.

Los cambios se guardan en `menu_items2` y actualizan el catálogo y **Mis favoritas**. Anclar a cabecera marca también como favorita; quitar una favorita la desancla. Quitar un acceso conserva los archivos y datos de la app. Si tiene accesos dependientes, hay que reasignarlos antes de quitarlo.

Las rutas `comun/gestor_menu.php` y `comun/modulo.php?path=comun/gestor_menu.php` redirigen a `comun/tienda.php?vista=menu`. El archivo antiguo solo conserva esa compatibilidad: ya no muestra el gestor anterior, ejecuta migraciones ni acepta sus operaciones por GET. Los cambios del menú pasan por la API de Apps mediante POST, sesión vigente, CSRF y consultas preparadas.

## Desinstalar y recuperar

1. Pulsar **Desinstalar** directamente en la tarjeta de una app, en el catálogo o en **Instaladas**. No hace falta abrir sus detalles. Los componentes protegidos muestran **Protegida** y explican su dependencia al pulsarlo.
2. Elegir si se desea descargar una copia. Está seleccionada de forma predeterminada. Si se elige, primero se prepara y solicita la descarga del ZIP; un fallo del respaldo impide continuar con la desinstalación.
3. La tienda mueve la carpeta a una zona privada, crea un paquete y verifica el SHA-256 de cada archivo antes de retirar la copia sin comprimir. Las operaciones simultáneas están bloqueadas.
4. La app pasa a **Disponibles** y desaparece de los accesos del menú y del directorio. Su registro de menú se conserva, incluidos favoritos e identificadores.
5. **Instalar** verifica el paquete y recupera archivos, datos locales y carpetas vacías. Nunca sobrescribe una carpeta existente ni ejecuta SQL o scripts de instalación. La carpeta restaurada recupera automáticamente sus accesos.

El paquete comprimido permanece en el servidor para permitir reinstalar. El ahorro es la diferencia entre la carpeta original y el ZIP; no supone eliminar todo su tamaño del disco. Si algún archivo en uso impide limpiar el área temporal, la respuesta lo indica y se reintenta al reinstalar.

## Datos y copias

- Los archivos de la app, incluidos sus datos locales y bases SQLite dentro de su carpeta, permanecen en el paquete de reinstalación.
- Las tablas MySQL compartidas **no se eliminan** al desinstalar. Tampoco se ejecutan los antiguos `uninstall.php` de las aplicaciones.
- La copia descargable contiene la carpeta completa de la app y un SQL con las tablas de la base Guagua completa, para conservar relaciones entre módulos. Para EduTask incluye también su perfil de base propio. La interfaz explica ese alcance antes de descargar.
- El SQL se exporta sin ejecutar herramientas del sistema ni exponer credenciales. Su restauración es manual: revisar y respaldar el destino antes de importarlo; contiene `CREATE TABLE IF NOT EXISTS` e `INSERT` y no reemplaza automáticamente datos existentes.
- No incluye archivos externos a la carpeta de la app, servicios remotos, otras bases no declaradas ni información guardada solo en el navegador. El ZIP puede contener configuraciones y datos sensibles de la app; debe guardarse en un lugar privado.

## Protección y almacenamiento

`sga-data/tienda/` contiene el catálogo, los paquetes y el área de trabajo. Está excluido de Git y bloqueado por la regla raíz de Apache y su propio `.htaccess`. Las copias descargables temporales se eliminan después de servirlas. El catálogo solo contiene metadatos, sin credenciales de conexión.

La API exige una sesión vigente de administrador, directivo o docente, más CSRF para operaciones POST. Estudiantes, acudientes y sesiones anónimas no pueden gestionar la tienda. Instalar, desinstalar o respaldar requiere la extensión PHP ZIP y permisos de escritura en `apps/` y `sga-data/`. Las aplicaciones y paquetes con enlaces o rutas externas se rechazan.

Los componentes utilizados por otras partes de Guagua y las apps que requieren gestionar servicios externos se marcan como protegidos. La razón aparece al abrir sus detalles. Sus reglas están en `comun/tienda_catalogo.php`. El núcleo (usuarios, cursos, calendario, conexión y menú) está fuera del ámbito desinstalable de la tienda.

Prueba aislada del ciclo: `php tests/tienda_apps_test.php`. Solo modifica una carpeta temporal de prueba y la elimina al terminar.

Prueba de gestión del menú: `php tests/tienda_menu_crud_test.php`. Requiere MySQL y crea una tabla temporal en una conexión aislada; no cambia los accesos reales. Comprueba edición, favoritos, cabecera, relaciones, enlaces, sincronización y redirecciones.
