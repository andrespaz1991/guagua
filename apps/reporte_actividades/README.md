# Reporte de actividades

Entrada: `/guagua/apps/reporte_actividades/`. Requiere una sesión de Guagua con rol docente, directivo o administrador tanto en la página como en la API.

Lee los `.xlsx` guardados en las subcarpetas `6-8` y `9-11` de `G:/Mi unidad/PC_HANDRES/SEDUCA/La Josefina/Vallesol/2026/Valoraciones`. Apache debe tener acceso a esa unidad. Puede cambiarse mediante `GUAGUA_VALORACIONES_DIR` en el entorno o en `.env`; usar `/` en rutas de Windows. No modifica los libros ni los registros de notas en la base de datos.

## Reglas

- Busca la hoja `valoraciones`, reconoce los encabezados y enlaza números del listado lateral con los números bajo Talleres. La marca explícita `E2` enlaza con el examen 2. El nombre de una actividad no cambia su número ni su tipo.
- Respeta secciones como `Actividades 6`, `Actividades 7` y `Actividades 10-11`. Un listado sin grados se aplica al grupo del archivo.
- Solo evalúa actividades con nombre. Una columna con notas y sin nombre genera una observación, sin añadirla a los indicadores. Las actividades nombradas se consideran activas aunque todavía no tengan notas o fecha.
- Celda vacía o con espacios: falta registrar nota. Nota de 1: no alcanzó. Nota mayor que 1 y menor que la mínima seleccionada: bajo desempeño. Nota igual o superior a la mínima: alcanzó. Cero, texto y valores fuera de 1 a 5: revisar dato.
- El promedio usa únicamente notas válidas. No sustituye la valoración ponderada final. Los indicadores superiores y los gráficos resumen toda la selección de grupo, grado, período, materia, actividad, estudiante y búsqueda. El filtro de estado limita los resultados y el CSV sin cambiar el denominador de los indicadores. Las tablas por estudiante y actividad conservan los totales de contexto para los resultados coincidentes.
- Usa el nombre del archivo para identificar la materia porque algunos encabezados conservan otra asignatura. Señala las diferencias para revisión.
- Excluye archivos temporales `~$` y copias con prefijo `Copia`/`Copy`. Entre archivos de igual materia, grupo y período, prioriza `Oficial`; avisa de duplicados excluidos.
- Agrupa estudiantes entre materias por nombre normalizado, grado y grupo. No une nombres diferentes por documento. Avisa de documentos compartidos o inconsistentes; no corrige identidades automáticamente.
- No recalcula fórmulas ni sigue vínculos externos: lee el resultado guardado de Excel. Una fórmula sin resultado guardado se marca como dato por revisar. Guardar Excel y pulsar **Actualizar** vuelve a leer las fuentes, sin caché persistente.

## Vistas y salida

Resumen, matriz por materia y grado, estudiantes con detalle y actividades con distribución de estados. La búsqueda reconoce nombres, documentos, materias y actividades sin distinguir mayúsculas o acentos. Los filtros principales siempre están visibles; los secundarios se despliegan en «Más filtros». Cada filtro activo se puede quitar por separado.

Los accesos rápidos permiten revisar notas faltantes, notas de 1 y bajo desempeño. «Necesita refuerzo» agrupa las notas válidas inferiores a la mínima y conserva las dos categorías por separado en el detalle. Cada nota de la matriz abre su archivo, hoja y celda de origen. La matriz ofrece densidad cómoda o compacta y secciones desplegables.

Los filtros se aplican al CSV y a la impresión de la vista actual. El reporte individual puede abrirse desde un nombre de estudiante. La impresión usa A4 horizontal, despliega automáticamente las secciones de la matriz y permite guardar PDF desde el navegador. Si falla una actualización, conserva la última lectura y muestra el error; no borra los resultados anteriores.

Las tablas de estudiantes, actividades, archivos de origen y detalle individual tienen páginas de 10, 25 o 50 filas. La matriz pagina por secciones (5, 10 o 25 por página) y por estudiantes dentro de cada sección cuando supera 10 filas. Los filtros y la búsqueda vuelven a la primera página; cada vista conserva su cantidad de filas. La impresión incluye todos los resultados del filtro y restaura la página al finalizar; el CSV también conserva todos los registros filtrados.

Los estilos de las tablas están aislados de las reglas antiguas de Guagua: fondo explícito en los encabezados de fila, resaltado suave, tipografía consistente y tabla horizontal en móvil. La vista QA carga también las hojas compartidas reales para detectar interferencias.

El CSV incluye materia, período, actividad, nota, estado, archivo, hoja y celda, con BOM UTF-8 y separador `;`. Los textos que puedan interpretarse como fórmulas se neutralizan.

## Menú y comprobación

Registrar una vez con `php apps/reporte_actividades/setup/registrar_menu.php`. Es idempotente y solo inserta la nueva entrada; no cambia favoritos existentes. También se puede registrar desde el gestor de menú.

Pruebas: `php tests/reporte_actividades_test.php` y `node tests/reporte_actividades_core_test.cjs`. La opción `--real` del test PHP lee la carpeta original y verifica el inventario de los 16 archivos detectados durante la implementación.
