# Mi plan diario

Planificador personal de Guagua para organizar las tareas de cada día en el colegio.

La vista principal es una hoja: cada categoría aparece como un título, con viñetas y casillas para sus tareas. **Toda la hoja** reúne los títulos y las pestañas muestran una categoría a la vez.

**Fecha de la planeación** carga automáticamente las materias del horario de ese día, con su grado y horas. Consulta únicamente las asignaciones visibles del usuario autenticado en la institución seleccionada, para el año de la fecha y su día de la semana. Al igual que Mis cursos, el año de la asignación determina el período: las fechas antiguas de los bloques no ocultan el horario semanal del año seleccionado. Los bloques repetidos de una materia se reúnen en un título. **Personal** está disponible todos los días, junto a las materias cuando hay clases. Se reutiliza la misma categoría sin duplicarla, y sus tareas se guardan por fecha.

Acceso: `apps/plan_diario/`. Requiere iniciar sesión en Guagua. Apps descubre el módulo automáticamente. Para preparar las tablas y añadir su acceso al menú, ejecutar desde la raíz:

```powershell
php apps/plan_diario/setup/registrar_menu.php
```

- **Seguimiento:** marcar y desmarcar tareas. Las completadas muestran el color elegido y registran la fecha y hora de cumplimiento.
- **Tarjeta en Inicio:** abre con las tareas de hoy y permite elegir otra **Fecha del plan**, con un botón **Hoy** para regresar al día actual según la zona horaria de Guagua. Muestra las tareas de la fecha elegida agrupadas por materia o categoría, con casillas, contador y barra de avance. Marcar o desmarcar guarda el mismo cumplimiento del módulo para ese día. El título abre el planificador en la fecha consultada. Actualizar y regresar a Inicio conservan la fecha seleccionada mientras la página siga abierta. Su consulta no carga el horario ni crea categorías. Si otra pestaña cambió el plan, actualiza la lista y pide repetir la acción. Si falla una consulta, conserva la fecha y las tareas de la última carga correcta.
- **Editar tarea:** cada fila tiene un botón visible **Editar**, tanto en Editar como en Seguimiento. Abre el formulario para cambiar texto, categoría, fecha, hora, prioridad, notas y color; **Guardar cambios** conserva su estado de cumplimiento. Las pendientes de Cumplimiento también tienen **Editar**: abre su fecha y actualiza el reporte al guardar, manteniendo el rango consultado. Cancelar cierra el formulario sin aplicar sus cambios.
- **Cumplimiento:** un espacio para revisar una semana, un mes o un rango de hasta 366 días. Muestra porcentaje, completadas, pendientes y pendientes de fechas anteriores a hoy, junto con el avance por día y por materia/categoría. El porcentaje es completadas / tareas guardadas para las fechas elegidas; los días sin tareas aparecen sin porcentaje. Es el estado actual por fecha de planeación, no un registro histórico de cada cambio de casilla. La lista de pendientes muestra hasta 50, empezando por las fechas antiguas, con acceso al día para marcarlas en Seguimiento. Se consulta al abrir la vista o pulsar Actualizar, directamente desde SQLite y sin modificar el horario.
- **Editar:** escribir los títulos y los textos de las tareas directamente en la hoja. **Guardar cambios** o **Ctrl+S** guarda toda la edición en una transacción. Al pasar a Seguimiento o cambiar de fecha, se guarda primero la edición pendiente. Enter en el campo debajo de un título añade una tarea y deja el cursor listo para la siguiente. Los controles de cada tarea permiten ordenar y abrir sus detalles (notas, categoría, fecha, hora, prioridad y color).
- **Categorías:** los títulos de las materias vienen del horario y se mantienen vinculados a su asignación. Personal también se prepara automáticamente. Se pueden añadir títulos manuales adicionales para el día seleccionado; al eliminar uno, elegir otra sección para conservar sus tareas, incluidas las de otras fechas.
- **Historial:** la semana y el selector de fecha abren cada plan diario con su avance original.
- **Traer de otro día:** copiar pendientes o todas las tareas, sin alterar el original. Las copias empiezan pendientes y repetir la misma importación no las duplica.
- **Nota del día:** una nota personal por fecha, en el apartado desplegable al final de la hoja. Guardar con su botón o Ctrl+S; al cambiar de día se guarda antes de navegar.
- **Búsqueda y filtros:** abrir **Buscar y filtrar** para buscar título/notas y filtrar por estado o prioridad alta, dentro de una categoría o de toda la hoja.

Las tareas y el avance se guardan en una base SQLite propia en `data/planner.sqlite`, con PDO SQLite y transacciones. Las materias se leen de MySQL usando la conexión central de Guagua, sin modificar horarios ni asignaciones. La carpeta data está protegida por Apache y los archivos de la base están excluidos de Git. No hay dependencia de servicios de terceros, npm ni recursos externos. Las tablas SQLite se preparan con `CREATE TABLE IF NOT EXISTS`: `pd_accounts`, `pd_categories`, `pd_tasks`, `pd_days` y `pd_category_sources`. La última vincula cada sección con su asignación o fecha sin borrar datos previos. Si un horario cambia, las secciones que ya tienen tareas para una fecha siguen visibles como tareas guardadas. Un error técnico al consultar MySQL se informa y no se interpreta como un día sin clases.

Cada consulta usa el usuario de la sesión; no se acepta identidad enviada por el navegador. Las escrituras requieren POST y CSRF. Una transacción de escritura y una revisión por usuario detectan cambios desde otras pestañas; ante conflicto se actualiza la lista y se conserva el formulario para revisar y guardar de nuevo. La zona horaria procede de la configuración de Guagua. Los textos se representan como texto, sin ejecutar HTML.

Límites: 20 categorías, 300 tareas por fecha, 240 caracteres por título, 4.000 por notas y 1.000 por enfoque. Eliminar tareas es definitivo. Eliminar o mover una tarea copiada fuera de su fecha permite traerla otra vez. Un fallo de conexión se muestra y conserva el texto de los formularios; no hay modo de escritura sin conexión.

Prueba de persistencia y aislamiento sobre bases SQLite de prueba, sin cambiar datos reales:

```powershell
php tests/plan_diario_test.php
php tests/plan_diario_document_test.php
php tests/plan_diario_concurrency_test.php
php tests/plan_diario_schedule_test.php
php tests/plan_diario_compliance_test.php
php tests/plan_diario_today_test.php
node tests/plan_diario_card_test.cjs
```

Vista de navegador con una base separada y datos ficticios de la referencia:

```powershell
php -S 127.0.0.1:8169 -t . tests/plan_diario_document_preview.php
```

Esta vista acepta únicamente conexiones locales del servidor de desarrollo. Sus datos se guardan en `tmp/plan-diario-document-qa/`, separados de las cuentas y de la base del módulo.

Para probar lunes, martes y fines de semana con un horario ficticio y almacenamiento separado:

```powershell
php -S 127.0.0.1:8170 -t . tests/plan_diario_schedule_preview.php
```
