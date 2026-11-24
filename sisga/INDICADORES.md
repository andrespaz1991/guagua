# Indicadores capturados de SISGA

La pestaña **Indicadores** del generador permite elegir materia, grado y período,
revisar los indicadores capturados y copiar o descargar un script de asignación.
Los archivos se nombran con esos tres datos. El script generado contiene la
combinación seleccionada y usa el motor de `indicadores-consola.js`.

Los perfiles se almacenan en `sisga_indicadores_perfiles`, utilizando la conexión
central de Guagua. La API exige sesión de administrador, directivo o docente;
las importaciones exigen además el token CSRF de la sesión. El banco conserva
el contexto de institución y nivel de SISGA y no almacena datos de estudiantes.

## Instalación e importación inicial

Desde la raíz del proyecto:

```powershell
php sisga/importar_indicadores.cli.php 'D:\Descargas'
```

El comando crea la tabla si no existe y busca `asignar-indicadores*.js`.
También se puede aplicar `migrations/001_indicadores.sql` para instalar una tabla
vacía. La pestaña permite importar nuevos archivos `.js` o `.json` después.

La importación extrae el bloque JSON sin ejecutar JavaScript. Los archivos
acumulados se separan en perfiles, se deduplican por contexto y se conserva la
captura más reciente. Reimportar los mismos archivos no crea filas adicionales.
La validación de todos los archivos precede a la transacción de escritura.

## Uso

1. Para guardar otra selección, descarga el capturador desde la pestaña.
2. En SISGA abre **Gestionar Indicadores**, marca las fortalezas y dificultades
   y ejecuta el capturador en la consola antes de asignar.
3. Arrastra aquí los archivos que descargue el capturador o pulsa **Importar
   scripts**. Puedes importar varios a la vez, incluidos los grados 9, 10 y 11.
4. Filtra la combinación, revisa sus indicadores y pulsa **Generar script**.
5. Copia o descarga el script y ejecútalo en el modal de la planilla correspondiente.

La asignación ocurre al ejecutar el script en SISGA. Utiliza el botón original
de la página y sus restricciones de período, estudiantes con nota y máximo
de indicadores. El generador local no hace peticiones de asignación a SISGA.

El botón **Copiar capturador** copia directamente el mismo código que
**Descargar capturador**. Si el navegador no permite copiar, se muestra el código
seleccionado para copiarlo con Ctrl+C. Al importar se guardan los perfiles en
MySQL y se actualizan los filtros con todas las materias, grados y períodos
recibidos, sin limitar los grados disponibles a las capturas iniciales.

## Verificación

Las pruebas de interfaz requieren un directorio que contenga `node_modules/jsdom`:

```powershell
node tests/sisga_capturas_ui_test.cjs <directorio>
node tests/sisga_indicadores_test.cjs <directorio>
node tests/sisga_consola_test.cjs <directorio>
php tests/sisga_indicadores_bd_test.php
```

La prueba de MySQL utiliza la tabla instalada y revierte sus datos de prueba.

## Período de apertura de todas las materias

La pestaña **Períodos** genera un script independiente llamado
`seleccionar-periodo-N-todas-las-materias.js`. Se ejecuta en la consola de la
lista de **Evaluaciones parciales**, donde está `tabla-carga-academica`.

El motor `periodos-runtime.js` accede a todas las filas de DataTables, incluidas
las separadas del DOM por la paginación y las excluidas por la búsqueda. Conserva
el filtro, el tamaño de página y la página activa. Al redibujar la tabla reaplica
la selección y dispara los eventos de cambio de las filas visibles.

Las materias sin selector, sin el período solicitado o con controles
deshabilitados se muestran como omitidas en la consola. El script selecciona
el período de apertura; no pulsa los botones de calificar, asignar indicadores
ni recalcular definitivas. Para detener el mantenimiento al paginar:
`PeriodosSISGA.detener()`. El informe está en `PeriodosSISGA.informe()`.

La estructura proporcionada carga todas las materias en el navegador. Si otra
pantalla usa paginación del servidor o filas todavía no cargadas, se detiene
para no presentar una selección parcial como completa.

Prueba con el HTML original y el plugin DataTables del proyecto:

```powershell
node tests/sisga_periodos_test.cjs <directorio con node_modules/jsdom> 'D:\Descargas\lista_periodo.html'
```
