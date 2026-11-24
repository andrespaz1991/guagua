# Guagua Espacio

Espacio personal inspirado en la organización y el editor por bloques de Notion. Entrada: `/guagua/apps/espacio/`, también disponible en el menú principal como ESPACIO. Usa la sesión existente de Guagua.

Incluye páginas anidadas, favoritos, búsqueda en títulos y contenido (Ctrl+K), ocho tipos de bloque, ordenación de bloques, tareas, plantillas, iconos, portadas, duplicación, movimiento de páginas, papelera recuperable y exportación JSON. Enter crea un bloque; Shift+Enter añade una línea. El botón del margen permite cambiar el tipo y mover o eliminar bloques.

Arrastra el asa ⠿ para ordenar bloques, páginas de la barra lateral y tarjetas de subpáginas. La línea azul marca la inserción antes o después; el centro azul de una página la convierte en contenedora de la página arrastrada. Las páginas conservan sus descendientes y se impiden ciclos. En favoritos, el arrastre cambia el orden general sin cambiar la página contenedora. El orden se guarda automáticamente. Escape cancela el arrastre; cerca de los bordes, la lista o el documento se desplazan automáticamente. Las asas admiten ratón, lápiz y pantalla táctil. Los menús existentes siguen permitiendo mover bloques y páginas sin arrastrar.

## Almacenamiento

### Importar y exportar proyectos

«Importar proyectos» en la barra lateral acepta archivos `.json` de Guagua Espacio, tanto la exportación anterior `{pages:[...]}` como las nuevas exportaciones con metadatos. Muestra los proyectos raíz y permite seleccionar cuáles añadir antes de confirmar. Se crean identificadores nuevos y se conservan contenido, jerarquía, enlaces y estados; no se reemplazan los proyectos existentes. La papelera y sus descendientes se excluyen. Las páginas cuya contenedora no venga en el archivo pasan al nivel principal. Se rechazan ciclos, identificadores repetidos, bloques no compatibles, enlaces inseguros y archivos inválidos.

El archivo de entrada admite hasta 10 MB para contemplar JSON con sangrías; el espacio combinado sigue limitado a 500 páginas y 2 MB de datos serializados. La importación se valida antes de modificar el espacio y se guarda automáticamente. Los datos permanecen en el navegador hasta confirmar. Para transportar un solo proyecto, usar ••• → «Exportar este proyecto», que incluye sus subpáginas activas. No es un importador de archivos nativos de Notion, Word, PDF ni CSV.

### Duplicar proyectos

En una página, abrir el menú ••• y elegir «Duplicar proyecto con subpáginas». Se crea una copia independiente de la página seleccionada y de todos sus descendientes activos, respetando el orden, los títulos de las secciones, los bloques, las subtareas, los enlaces, los estados de cumplimiento, las fechas de cumplimiento existentes, los iconos y las portadas. La página raíz lleva «(copia)», queda junto al proyecto original y se abre para poder renombrarla. La papelera se excluye y los favoritos no se duplican. «Duplicar solo esta página» conserva la opción anterior sin copiar sus subpáginas. La copia se valida contra los límites de almacenamiento antes de modificar el espacio y utiliza el guardado automático o el botón Guardar.

### Impresión profesional

El botón «Imprimir» de la barra superior abre una vista previa. Permite imprimir la página actual, el proyecto seleccionado y todas sus subpáginas activas en orden, o una sección definida por un título/subtítulo o una tarea con subtareas. Cada subpágina de un proyecto empieza en una hoja nueva. Se preservan niveles, estados y direcciones de los enlaces.

Para reportes, seleccionar «Reporte con los filtros actuales». La impresión contiene la totalidad de los resultados filtrados, incluidos los que no se han mostrado por paginación. Sus totales y avances se calculan sobre esos resultados. La plantilla usa A4, márgenes de impresión, encabezados, fecha de emisión en Colombia, títulos, tablas y pies con numeración en navegadores compatibles con cajas de margen CSS. Los controles del editor se excluyen del papel. «Imprimir / Guardar PDF» abre el diálogo del navegador, donde se elige la impresora o el destino PDF. Imprimir no exige guardar y usa los cambios actuales; si hay cambios pendientes, la vista lo indica.

### Reportes de tareas

La opción lateral «Reportes de tareas» reúne los documentos personales activos. Muestra el total de tareas y subtareas, completadas, pendientes, porcentaje general y avance de las tareas propias de cada página. Permite filtrar por página, estado y búsqueda, y ordenar por cumplimiento reciente, pendientes primero o página y nivel. Las listas muestran 50 resultados por vez con «Mostrar más tareas». Se puede abrir la tarea original o su documento vinculado y cambiar su estado directamente desde el reporte.

Cada casilla se cuenta independientemente, incluidas las subtareas vacías. La papelera se excluye. Al completar se registra `completedAt` en UTC y la interfaz lo muestra en la zona America/Bogota. Reabrir elimina la fecha de cumplimiento actual. Las tareas antiguas completadas sin fecha permanecen «Sin fecha registrada». El reporte representa el estado actual, no un historial de todos los cambios de estado. La ruta `#reportes` abre la vista al recargar.

Las tareas admiten hasta cinco niveles de subtareas. Con el cursor en el texto, Tab aumenta el nivel y Shift+Tab lo reduce. Enter crea una tarea del mismo nivel después del grupo actual; Enter sobre una subtarea vacía reduce su nivel. «＋ Subtarea» crea una hija directamente y el menú ⠿ también incluye las acciones de nivel. Al arrastrar, indentar o duplicar una tarea, sus subtareas la acompañan; al eliminar solo el bloque padre, las hijas suben un nivel. Cada subtarea conserva su propia casilla y su enlace; completar una tarea no completa automáticamente sus hijas.

Cada tarea admite un enlace opcional mediante «＋ Enlace». Se pueden guardar direcciones HTTP/HTTPS o rutas de Guagua como `/guagua/reportes/informe_docente.php`. El botón «Abrir» consulta el recurso en otra pestaña; el lápiz permite editar o quitar el enlace. Los enlaces se guardan con el documento y se conservan al duplicar y ordenar tareas. No se cargan archivos al servidor mediante esta función.

PHP PDO SQLite debe estar habilitado y Apache debe poder escribir en `data/`. La base se crea automáticamente al primer acceso. `data/.htaccess` bloquea su acceso HTTP. Cada usuario tiene un documento separado por su identificador de sesión; el contenido de cada página se representa como texto, no HTML. Las escrituras exigen CSRF y una versión coincidente; una pestaña desactualizada no sobrescribe cambios de otra. En ese caso, exportar los cambios locales antes de recargar.

Respaldar `data/espacio.sqlite` con la aplicación sin escrituras activas. No borrar ese archivo al actualizar el módulo. Límites: 500 páginas, 1000 bloques por página y 2 MB por espacio.

## Alcance

Esta versión ofrece documentos personales. No incluye colaboración en tiempo real, permisos compartidos, bases de datos con vistas de tabla o kanban, adjuntos, historial de versiones ni importación de archivos de Notion. La exportación JSON permite respaldar e importar proyectos de Guagua Espacio.

## Verificación

`D:/xampp/php/php.exe -l apps/espacio/index.php`

`D:/xampp/php/php.exe -l apps/espacio/api.php`

`node --check apps/espacio/app.js`

Comprobar con sesión iniciada: crear y editar una página, recargar, buscar contenido, mover una subpágina, eliminar/restaurar y exportar. En dos pestañas, guardar desde una y luego desde la otra: la segunda debe informar del conflicto sin sobrescribir la primera.
