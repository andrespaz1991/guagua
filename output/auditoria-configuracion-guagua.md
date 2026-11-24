# Auditoría de conexión con la configuración de Guagua

Fecha: 11 de octubre de 2026 · America/Bogota.

## Resultado

La integración es **parcial**. Cambiar un periodo, año o institución no garantiza todavía que todos los módulos muestren el mismo contexto. Hay fuentes compartidas, pero también valores fijos, reglas repetidas y diferencias entre identificadores y nombres.

Se hizo un barrido estático de **624 archivos de aplicación, incluidos 322 PHP**, excluyendo dependencias, datos y artefactos temporales; se revisaron los **54 registros del menú**, el código de los módulos académicos principales y la configuración real de MySQL mediante consultas de lectura. El barrido identifica candidatos; las conclusiones siguientes se apoyan en revisión de código y consultas concretas. No equivale a probar todas las pantallas con todos los roles ni todos los procesos de producción.

Los 54 destinos registrados se resuelven a archivos o directorios existentes, incluyendo el alias de Diario que conduce a `comun/whatsapp_diario.php`. Su ruta antigua no es un enlace roto: [Alias de Diario](D:/xampp/htdocs/guagua/comun/modulo.php:113). Existencia de archivos no confirma que todas las funciones internas estén operativas.

## Fuentes de configuración encontradas

- Instituciones: `institucion_educativa`, administrada desde `Planeador/institucion_educativa.php`.
- Año lectivo: `ano_lectivo`, administrado desde `cursos/ano_lectivo.php`.
- Periodos: `periodo`, administrados desde `Planeador/periodo.php`.
- Configuración de reportes: `ia/config_reporte.php`, con una parte operativa y otra simulada.
- Rutas y mensajes del reporte: `ia/config_rutas.json`, leído por `ia/reporte.php`.
- Excel de asistencia: constante en `comun/config.php`, arreglo local en `apps/asistencia/index.php` y archivo distinto `apps/asistencia/config_sincronizacion.json`.
- Apariencia: `apps/configuracion_interfaz/` genera `comun/css/interface_custom.css`.

**No existe un servicio único de contexto institucional y académico compartido por todas estas piezas.**

## Hallazgos prioritarios

### 1. Institución fija en el conector y los encabezados — prioridad alta

El inicio de sesión guarda la institución seleccionada: [Institución seleccionada al ingresar](D:/xampp/htdocs/guagua/usuario/login.php:136). Sin embargo, [Conector común](D:/xampp/htdocs/guagua/comun/conexion.php:20) y [Plantilla común](D:/xampp/htdocs/guagua/comun/plantilla.php:3) consultan siempre la institución **1**. La base contiene siete instituciones: la 1 es **Fundación Educativa HST** y la 7 es **Institución Educativa Rural La Josefina**. Por ello, el nombre/logo/banner y las políticas de archivos pueden proceder de una institución distinta de la seleccionada. La ficha de la hoja de vida hereda ese nombre global.

[Mis cursos](D:/xampp/htdocs/guagua/cursos/mis_cursos.php:15) usa la institución de sesión con respaldo 7. Además, [Carga de Institucion](D:/xampp/htdocs/guagua/clases/Institucion.Class.php:63) itera el conjunto de filas usando el índice de la fila como nombre de propiedad, en lugar de cargar los campos de la primera fila. Esto impide que el objeto reciba correctamente nombre, logo y banner.

Pendiente: resolver institución y sede una sola vez desde un contexto válido, cargar sus campos correctamente y aplicar ese contexto a encabezados, cursos, fichas, impresiones y documentos. Las consultas de perfiles y reportes también necesitan una política común de pertenencia institucional; varias consultan globalmente sin filtro institucional.

### 2. El año calendario se confunde con el ID del año lectivo — prioridad alta

En la base, el año lectivo **2026 tiene ID 17**. `asignacion.ano_lectivo` usa ese ID; `periodo.ano_lectivo` contiene el texto/número **2026**.

[Importador de notas](D:/xampp/htdocs/guagua/apps/notas/lib/NotasAuditoriaService.php:63) entrega el año del periodo a [Consulta de asignaciones del importador](D:/xampp/htdocs/guagua/apps/notas/lib/NotasAuditoriaService.php:126), que lo compara directamente con el ID de las asignaciones. Consultas de comprobación: buscar `asignacion.ano_lectivo = 2026` devuelve **0 asignaciones**, mientras resolver primero el año 2026 y usar su ID devuelve **106**.

Existen **2.158 notas de auditoría**, todas sin `id_asignacion` ni `id_materia`. El desajuste anterior explica por qué el importador actual no encuentra esos enlaces; no permite atribuir por sí solo el origen de cada registro histórico. La hoja de vida conserva un respaldo por nombre de materia, pero eso no reemplaza una relación correcta.

Pendiente: distinguir `id_ano_lectivo` de `nombre_ano_lectivo` en todo el código y reparar los enlaces históricos mediante una migración verificable que preserve los registros y señale los casos ambiguos.

### 3. Periodos: reglas distintas y configuración con solapamientos — prioridad alta

Hay un año activo, 2026, y un periodo activo: **4, del 14/09/2026 al 20/11/2026**. También hay un registro con ID **0**, nombre **4**, del 14/09 al 23/09. Los periodos 1 y 2 incluyen ambos el **27/03/2026**. Hay un intervalo del 28/06 al 04/07 sin periodo; puede ser vacaciones y debe documentarse, no corregirse por suposición.

[Gestión de periodos](D:/xampp/htdocs/guagua/Planeador/periodo.php:43) permite activar un periodo desactivando todos los demás, sin transacción ni comprobación de rangos o solapamientos. La tabla no tiene dimensión institucional. Algunas lecturas exigen estado activo y que hoy esté dentro del rango; otras solo exigen estado activo. El sistema puede mostrar contextos distintos cuando un periodo queda activado fuera de sus fechas.

Pendiente: una política única para periodo seleccionado, activo, vigente e histórico; vincularlo al ID del año; definir si los periodos son globales o específicos por institución; validar extremos, solapamientos y números duplicados; documentar recesos. No se eliminaron ni cambiaron estos periodos durante la auditoría.

### 4. Fechas y estados de asistencia incompatibles entre módulos — prioridad alta

Hay **7.432 controles**: **5.339** con fecha ISO `YYYY-MM-DD` y **2.093** con fecha histórica `DD/MM/YYYY`. Comparar las fechas como texto solo encuentra 5.339 registros de 2026; normalizarlas encuentra los 7.432.

Se corrigió [Hoja de vida](D:/xampp/htdocs/guagua/usuario/hoja_vida_estudiante.php:299) para interpretar ambos formatos antes de filtrar y ordenar, conservando los límites inclusivos del periodo. [Prueba SQL de fechas históricas](D:/xampp/htdocs/guagua/tests/hoja_vida_fechas_mysql_test.php:1) valida en MySQL fechas ficticias de ambos formatos, extremos, fechas fuera de rango y valores inválidos. Pasaron también las pruebas existentes de promedios y exportación. **No se reescribieron las fechas almacenadas.**

Persiste una diferencia en otros módulos: [Asistencia por curso](D:/xampp/htdocs/guagua/asistencia/index.php:53) interpreta solo `DD/MM/YYYY`; [Reporte de asistencia](D:/xampp/htdocs/guagua/reportes/informe_asistencia.php:51) acepta ambos formatos, pero su rango predeterminado es el mes actual; [Indicadores SISGA](D:/xampp/htdocs/guagua/sisga/api_inasistencias.php:9) usa el año calendario y no el periodo configurado.

Además, [Estados importados de asistencia](D:/xampp/htdocs/guagua/apps/asistencia/sincronizar.php:32) registra `P` como permiso y `R` como retardo; [Estado de asistencia en la hoja de vida](D:/xampp/htdocs/guagua/usuario/hoja_vida_estudiante.php:71) interpreta `P` como presente y excluye retardos del total. Hay 123 permisos y 6 retardos en la base. Pendiente: unificar el catálogo y la fórmula del porcentaje de asistencia; mostrar por separado presentes, ausencias, permisos y retardos.

### 5. Reportes con valores fijos y configuración que no persiste — prioridad alta

[Configuración de reportes](D:/xampp/htdocs/guagua/ia/config_reporte.php:30) tiene docente e institución fijos y usa ID de año **16**, correspondiente a **2025**, aunque el activo es 2026. [Acciones simuladas](D:/xampp/htdocs/guagua/ia/config_reporte.php:144) devuelve éxito para guardar/eliminar grupos, pero esas acciones son simulaciones.

[Reporte académico](D:/xampp/htdocs/guagua/ia/reporte.php:27) mantiene institución, sede, docente, grados y columnas Excel en un arreglo propio; solo carga rutas/mensajes desde su JSON. Por tanto, editar la pantalla de configuración de reportes no sincroniza esos datos del reporte consumidor.

[Informe docente](D:/xampp/htdocs/guagua/reportes/informe_docente.php:21) fija 2026 y Vallesol; además cuenta estudiantes por año de creación del perfil, no por inscripción en el año lectivo. [Consulta de estudiantes](D:/xampp/htdocs/guagua/clases/Academico.Class.php:523) repite ese último criterio. Un estudiante registrado antes y matriculado este año puede quedar excluido.

Pendiente: configuración persistente por institución/año/docente; quitar las simulaciones; usar inscripciones y asignaciones del año seleccionado para los conteos. La configuración de reportes necesita validación propia de rol y CSRF antes de procesar sus POST; cargar `config.php` no proporciona por sí solo esa protección.

## Estado de conexiones por módulo

| Módulo | Conexión comprobada | Lo que falta |
|---|---|---|
| Inicio | Widget lee fechas de `periodo` | Resolver el mismo periodo/año/institución que el resto. |
| Mis cursos | Lee años/categorías y filtra cursos por institución de sesión | Carga correcta del objeto Institucion y encabezados comunes. |
| Hoja de vida / ficha / Excel | Selección de periodo por fechas, año por ID, exportación del mismo rango; asistencia histórica corregida | Nombre institucional, catálogo de asistencia, escala de notas y política de pertenencia institucional. |
| Detalle de curso | Consulta periodo vigente | [Promedios del curso](D:/xampp/htdocs/guagua/cursos/curso.php:306) filtra actividades por número, no por fechas; [Auditoría de notas del curso](D:/xampp/htdocs/guagua/cursos/curso.php:335) usa ese número como ID de periodo; asistencia depende del horario completo. Puede diferir de la hoja de vida. |
| Horarios | [Horarios](D:/xampp/htdocs/guagua/asistencia/horario.php:11) propone fechas del periodo activo | Validar contra el año e institución de la asignación y permitir excepciones explícitas. |
| Asistencia por curso | Consulta controles | Fechas históricas/ISO y selector de periodo común. |
| Reporte de asistencia | Normaliza ambos formatos | Periodo común, año lectivo e institución; validar también el armado del DSN PDO cuando el host incluye puerto. |
| SISGA | Consulta las faltas reales | Año lectivo/periodo e institución; actualmente toma el año calendario. |
| Notas importadas | Registra periodo activo y conserva historial | Resolver ID del año, enlaces a materias/asignaciones y alcance institucional. |
| Formatos | [Contexto de formatos](D:/xampp/htdocs/guagua/apps/formatos/lib/FormatosService.php:101) obtiene año activo y periodo | Institución/sede/docente en documentos: [Sede en documentos](D:/xampp/htdocs/guagua/apps/formatos/descargar.php:146) conserva respaldos fijos; alinear vigencia del periodo. |
| PlanMind | [PlanMind](D:/xampp/htdocs/guagua/apps/PlanMind/index.php:56) consulta el periodo activo | [El calendario del formulario](D:/xampp/htdocs/guagua/apps/PlanMind/index.php:1338) calcula el periodo dividiendo los 12 meses en 1–4 bloques y usa rangos mensuales, en lugar de las fechas configuradas. Falta aplicar el contexto de año/institución. |
| Planeador anual | Separa planes por usuario e institución | [Planeador anual](D:/xampp/htdocs/guagua/apps/planeador_anual/api.php:72) inicia el año usando calendario y fechas de ejemplo, sin enlazar el año lectivo activo ni sus periodos. |
| DBA / malla | [Validación curricular de periodos](D:/xampp/htdocs/guagua/apps/dba/api/crud.php:101) valida la existencia del periodo | [Importación curricular](D:/xampp/htdocs/guagua/apps/dba/api/import.php:214) limita la importación a 1–4 y confunde número con ID; falta contexto de año/institución. |
| Rúbricas | Catálogo SIE propio | Compartir escala, materias, estudiantes e institución; [Conexión fija de Rúbricas](D:/xampp/htdocs/guagua/apps/rubrica/index.php:7) tiene conexión local fija. |
| EduTask | Usa sesión compartida | [Datos propios de EduTask](D:/xampp/htdocs/guagua/apps/edutask/index.php:11) mantiene base y catálogo de materias propios; necesita mapeo explícito con cursos y año lectivo, si se pretende integración académica. |
| Apariencia | [Configuración de apariencia](D:/xampp/htdocs/guagua/apps/configuracion_interfaz/index.php:45) genera el CSS y el menú/plantilla lo cargan | Las páginas independientes e iframes necesitan cargar y consumir el tema en su propio documento; la hoja de vida mantiene variables visuales propias. |

## Qué falta centralizar en el módulo de configuración

1. **Identidad institucional:** institución y sede seleccionadas, nombre oficial, logos/banner, dirección/contactos y responsables de firmas/encabezados.
2. **Contexto académico:** ID y nombre del año por separado; periodos, fechas, vigencia, vacaciones/recesos y reglas para consultar históricos.
3. **Evaluación / SIE:** escala, mínimo aprobatorio, niveles, decimales, redondeo, ponderaciones y diferencia entre nota consolidada y promedio de actividades. Hoy están repetidos: [Escala de rúbricas](D:/xampp/htdocs/guagua/apps/rubrica/rubricas_sie.php:9) y [Escala de la hoja de vida](D:/xampp/htdocs/guagua/usuario/hoja_vida_estudiante.php:80).
4. **Asistencia:** catálogo de estados, justificación, conteo de retardos/permisos y fórmula común de porcentajes.
5. **Importaciones/exportaciones:** rutas locales/web por institución y año, hojas/columnas, grados, materias y reglas de correspondencia. Hay tres fuentes de rutas de asistencia: [Ruta común del Excel](D:/xampp/htdocs/guagua/comun/config.php:8), [Aplicación de asistencia](D:/xampp/htdocs/guagua/apps/asistencia/index.php:21) y [Sincronizador de asistencia](D:/xampp/htdocs/guagua/apps/asistencia/sincronizar.php:10).
6. **Datos administrativos:** matrículas, grados, grupos, jornadas, categorías y materias vinculados al año/contexto, evitando catálogos aislados en apps.
7. **Archivos y registro:** tamaño máximo, extensiones, autorregistro y plantilla. Esos campos existen en la tabla de institución, pero el CRUD actual no los administra; la plantilla selecciona un archivo fijo y [Funciones de adjuntos](D:/xampp/htdocs/guagua/comun/funciones.php:1489) tiene lógica de lectura comentada.
8. **Apariencia compartida:** tema consumido por todos los documentos internos, también las aplicaciones independientes.
9. **Administración de cambios:** roles autorizados, validación CSRF, historial de quién cambió qué, persistencia real y prueba de impacto sobre módulos consumidores. Credenciales y secretos deben seguir en el entorno del servidor, separados de los ajustes públicos.

## Orden recomendado de implementación

1. Crear un servicio común que devuelva institución, sede, ID/nombre del año y periodo/rango seleccionado con reglas verificadas.
2. Corregir la resolución del año en notas y la identidad institucional en conector, plantilla y clase Institucion.
3. Validar el calendario configurado y unificar interpretación de fechas/estados de asistencia.
4. Adaptar curso, reportes, SISGA, formatos y planeadores para consumir ese servicio; mantener selectores históricos.
5. Sustituir acciones simuladas por almacenamiento real y unir rutas/catálogos del módulo de reportes con sus consumidores.
6. Revisar y enlazar el historial existente sin borrarlo; ejecutar pruebas con otra institución, otro año lectivo y los extremos de cada periodo.

No todas las fechas deben reemplazarse por las del periodo: nacimiento, creación del usuario, matrícula, retiro, mensajes y eventos conservan sus fechas reales. Los periodos definen **el contexto y los filtros académicos**, no sustituyen esas fechas.

## Cambios realizados en esta revisión

- Hoja de vida: normalización de fechas de asistencia antes de filtrar, ordenar y mostrar.
- Nueva prueba SQL de lectura para verificar fechas ISO e históricas.
- Este informe de auditoría.

No se renombraron instituciones, no se reactivaron años o periodos y no se alteraron notas, asistencias ni registros del menú. Las correcciones estructurales descritas siguen pendientes.

## Actualización: conexiones centralizadas

La conexión de base de datos quedó centralizada en `comun/database.php`, con configuración privada única en `.env`. Los módulos PHP, los adaptadores antiguos y los scripts Python, PowerShell y batch delegan en ella. EduTask, Prompt, Moodle y hosting conservan perfiles separados en el mismo archivo.

Verificación: 1054 archivos PHP revisados sin conexiones directas fuera del proveedor; sintaxis de 45 archivos modificados; pruebas de MySQLi/PDO y adaptadores antiguos con consultas de lectura; pruebas de periodos, fechas de asistencia y Excel aprobadas. `.env` devuelve HTTP 403 y el puente CLI HTTP 404. No se ejecutaron restauraciones ni importaciones remotas.

Esta centralización resuelve la duplicación de conexiones. Los pendientes de identidad institucional, calendario, año lectivo y almacenamiento de configuración enumerados en este informe requieren cambios adicionales; no quedan resueltos por cambiar el conector. Consulte `comun/BASE_DE_DATOS.md` para configurar las conexiones.
