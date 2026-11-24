# Simulador de notas

Acceso: Apps → Simulador de notas. Requiere sesión de docente, directivo o administrador.

La base compartida es el PDF de SISGA del 20 de noviembre de 2026: 40 páginas, 33 estudiantes y 218 registros de estudiante y asignatura. Se conservaron las notas de los cuatro periodos (173 registros con P4) y sus celdas vacías. No se consultan los resúmenes Excel ni Google Drive. Las importaciones Excel anteriores por cuenta no sustituyen esta base.

P1, P2 y P3 son de consulta. P4 comienza con el valor del PDF y permanece como campo numérico editable de 1 a 5, con hasta dos decimales. Una edición recalcula la final y las alertas mientras se escribe, sin modificar el PDF ni los datos importados. Vaciar P4 deja la proyección pendiente. La restauración de una fila, «Restablecer vista» y «Restaurar P4 de consulta» recuperan respectivamente esa materia, la selección filtrada o todas las notas de consulta. Deshacer también revierte simulaciones en lote y restauraciones.

Las actualizaciones permanentes solicitadas se registran en la base de consulta con la nota anterior, el valor nuevo, la fecha y el motivo en `p4_correction`. Prevalecen como nota de consulta y al restaurar P4; la interfaz las identifica como «Actualizada». El PDF original se conserva sin modificaciones. Una actualización de la base cambia su huella y evita recuperar escenarios anteriores con la nota antigua.

Los escenarios, ajustes y selección se conservan en sessionStorage por cuenta y huella del reporte hasta cerrar la pestaña. Un PDF nuevo tiene su propia huella y no recupera escenarios del reporte anterior. P4 sigue visible cuando se filtra otro periodo. Los campos inválidos bloquean la exportación de la vista afectada.

Los cuatro periodos pesan inicialmente 25 % y la meta es 3,0, ambos ajustables. La clasificación compara la final sin redondear. La nota necesaria en P4 se redondea hacia arriba a dos decimales; si supera 5, se indica. Las notas ausentes permanecen vacías y no se convierten en cero. Estos cálculos son escenarios, no una certificación de reglas institucionales.

«Alertas del año» agrupa las materias por estudiante del año y grupo elegidos. Sin P4 se usa el promedio ponderado de P1–P3, normalizado por el peso de esos periodos. Con P4 válida se usa la final. El acumulado muestra el aporte de P1–P3 al 100 % del año; no se confunde con el promedio para clasificar riesgo. Los datos incompletos se presentan por separado.

«Fichas anuales» presenta una chapola por estudiante con materias, P1–P4 y final anual ponderada. Incluye promedios por periodo, promedio general (media simple de las materias con final completa), materias bajo la meta y pendientes. El promedio general se marca parcial cuando faltan materias. Respeta el año, grupo y estudiante del panel lateral y permite buscar por nombre o código. La vista se pagina cada cuatro estudiantes. Se puede imprimir una ficha o todas las filtradas, incluidas otras páginas, o guardarlas como PDF desde el diálogo de impresión. Las fichas conservan la matriz en móvil e identifican las notas actualizadas y las simulaciones de P4; las notas inválidas bloquean su impresión.

«Notas faltantes» revisa las celdas originales de P1, P2, P3 y P4 del PDF. Una simulación no completa una celda ausente de la fuente. Cada resultado incluye el documento y la página. Las exportaciones incluyen toda la selección filtrada y distinguen P4 del PDF de una P4 simulada, conservando el valor base y la página de procedencia.

## Actualizar la base desde otro PDF

```powershell
python apps/simulador_notas/importar_pdf.py 'ruta/reporte.pdf'
```

Requiere `pdfplumber` solo al importar; el módulo PHP no depende de Python para consultar notas. El importador valida todos los encabezados, la numeración de filas, las columnas de los periodos, las identidades únicas y la escala de notas antes de reemplazar atómicamente `data/inicial.php`. Conserva fecha, SHA-256 del PDF y página de cada registro. Los documentos de identidad no se importan. La base PHP no tiene salida y el directorio `data` deniega acceso web.

El importador Excel y `P4ResumenReader.php` se conservan como utilidades históricas y no forman parte de la consulta actual. `p4_actual.php` responde desde la misma base PDF para compatibilidad con pestañas antiguas; la nueva interfaz no lo solicita.

## Comprobaciones

```powershell
node tests/simulador_notas_test.cjs
php tests/simulador_notas_test.php
php tests/simulador_notas_p4_test.php
php tests/permisos_modulos_test.php
php tests/simulador_notas_preview.php
```

La prueba P4 conserva cobertura del lector histórico con datos ficticios; no necesita acceder a Excel. Su opción histórica `--real` omite la lectura Excel cuando la fuente vigente es PDF. La vista previa CLI genera el mismo componente con notas ficticias para revisión visual; `--empty` prueba una base vacía. `view.php` no es una entrada pública.
