# Evidencias de aprendizaje y palabras clave

La pestaña **Evidencias de aprendizaje** comparte los filtros de grado y materia
del panel lateral. Incluye búsqueda por texto o conceptos, filtro por período y
estado, selección múltiple, edición, revisión de IA y eliminación individual.
Las evidencias también aparecen debajo de cada DBA en el editor curricular.

## Importación

En **Importar**, descargue la plantilla CSV: contiene los ID y nombres reales de
los DBA de esta instalación. Complete `evidencia` y, opcionalmente,
`palabras_clave` (conceptos separados por comas). Puede repetir el ID para añadir
varias evidencias. `dba_referencia` es informativo; la asociación usa `id_dba`.

Se admiten CSV/TXT UTF-8 separados por punto y coma, coma o tabulación y JSON:

```json
[
  {
    "id_dba": 2,
    "evidencia": "Compare dos estrategias de solución de un conflicto y justifique una alternativa pacífica mediante un texto argumentativo.",
    "palabras_clave": "conflicto, diálogo, paz"
  }
]
```

El ID debe existir. Se permiten hasta 1.000 filas y 5 MB. Las filas sin evidencia
se omiten, así como evidencias repetidas para el mismo DBA. La vista previa
muestra errores por fila. El guardado es transaccional: un registro inválido
revierte todo el lote. Las palabras clave importadas solo completan campos
vacíos; use **Editar DBA y palabras clave** para cambiar conceptos existentes.

## LM Studio

El servidor PHP conecta con `http://169.254.173.61:1234/v1`. Puede configurarse
otro servidor con la variable de entorno `GUAGUA_LM_STUDIO_URL` en Apache.
El navegador no realiza llamadas directas al servidor de IA.

**Conectar** consulta los modelos disponibles. El modelo preferido es
`gemma-2-2b-it`. Seleccione DBA, cantidad (1–3) y genere propuestas. El proceso
es secuencial y permite detenerse tras el DBA actual. Los fallos se muestran
por registro y las propuestas obtenidas se conservan en la página.

La IA usa el texto principal del DBA, grado y materia. Algunas descripciones
históricas incluyen unidades más amplias; no se usan como fuente para evitar
que el modelo genere evidencias sobre otros temas. Las propuestas no son
transcripciones oficiales. Antes de guardar puede corregir el texto y elegir
las propuestas. Se guardan con modelo y origen `ia_pendiente`. Abrirlas con
**Revisar** y guardar el texto cambia el origen a `ia_revisada`.

## Datos e instalación

Ejecute una vez `migrations/20260911_evidencias.sql` en la base Guagua. La
migración es aditiva y reejecutable en MariaDB: añade `dba.palabras_clave` y
`origen`, `modelo`, `fecha_creacion` a `evidencia_de_aprendizaje`. Conserva la
tabla existente y la compatibilidad con Planeador. Las lecturas no ejecutan DDL.

La preparación inicial explícita por consola es reanudable:

```text
php apps/dba/preparar_evidencias.php gemma-2-2b-it
php apps/dba/preparar_evidencias.php gemma-2-2b-it 1
```

Solo completa conceptos vacíos y añade una evidencia si el DBA no tiene ninguna
con texto. No modifica evidencias ya registradas. La variante con `1` procesa
un DBA por ejecución. El script no puede ejecutarse por HTTP. Requiere cURL,
mbstring y mysqli. El modelo debe estar disponible en LM Studio.

## Verificación

Desde la raíz de Guagua, con Apache y MariaDB local activos:

```text
php tests/dba_evidencias_test.php lectura
php tests/dba_evidencias_test.php escritura
php tests/dba_evidencias_test.php importacion
php tests/dba_evidencias_test.php generacion
```

Las pruebas usan una sesión sintética de docente y registros temporales propios,
que eliminan al terminar. Comprueban HTTP, permisos y CSRF, CRUD, procedencia,
revisión, duplicados, validación de importación, transacciones y cascadas.

La prueba `tests/dba_evidencias_ui_test.cjs` verifica los flujos de interfaz en
un DOM aislado. Requiere `jsdom` como dependencia de pruebas (no se usa en
producción). Ejecútela pasando la carpeta donde se instaló la dependencia:

```text
node tests/dba_evidencias_ui_test.cjs RUTA_DE_LA_CARPETA_QA
```
