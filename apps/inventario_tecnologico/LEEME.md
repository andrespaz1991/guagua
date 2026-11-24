# Galería del inventario tecnológico de Vallesol

Abre `http://localhost/guagua/apps/inventario_tecnologico/index.html` con Apache de XAMPP activo. No necesita PHP ni MySQL. Si abres el HTML con doble clic, pulsa **Abrir inventario** y selecciona el archivo `inventario.json`.

## Archivos

- `index.html`: interfaz, búsqueda, filtros y vista previa.
- `inventario.json`: datos del inventario. Es la única fuente de datos de la galería.
- `imagenes/ID/`: fotografías de cada registro, usando su **ID**, no su placa.

El inventario inicial incluye 11 registros de tecnología y accesorios tomados de `vallesoloct2026 (2).xls`. No se copiaron documentos de identidad, responsables, valores contables ni datos del resto de bienes. Los campos `tipo` y `detalle` conservan el texto del origen; nombre, categoría, marca y referencia facilitan su presentación. La agrupación tecnológica es una selección para esta galería.

## Agregar fotografías

1. Abre la carpeta con el ID del registro, por ejemplo `imagenes/10873/`.
2. Copia fotografías reales del equipo. Puedes usar `frente.jpg`, `placa.jpg` y `detalle.webp`. Se aceptan JPG, JPEG, PNG y WebP. Usa nombres sin tildes ni caracteres especiales.
3. En `inventario.json`, encuentra el equipo por su `id` y completa su lista `imagenes`:

```json
"imagenes": [
  "imagenes/10873/frente.jpg",
  "imagenes/10873/placa.jpg",
  "imagenes/10873/detalle.webp"
]
```

La primera imagen será la portada. Las demás se mostrarán en la vista previa con miniaturas y flechas. Si una imagen no existe, la galería indicará que no está disponible. Sin fotografías, muestra un símbolo del tipo de equipo, no una fotografía de ejemplo.

Un navegador no puede enumerar automáticamente los archivos de estas carpetas. Por eso debes incluir el nombre de cada fotografía en el JSON. El contador de fotografías representa las rutas asociadas en ese archivo.

## Actualizar o añadir equipos

Edita `inventario.json` con un editor de texto. Conserva las comas y comillas del formato JSON. Cada equipo debe tener un ID único y una carpeta del mismo nombre. Para un equipo nuevo, duplica un objeto dentro de `equipos`, cambia sus datos y crea `imagenes/NUEVO_ID/`.

Ejemplo de una ficha:

```json
{
  "id": "10873",
  "nombre": "Computador personal",
  "tipo": "Computadores personales",
  "categoria": "Computación",
  "marca": "SAMGUNG",
  "referencia": "NP 270E4V",
  "placa": "10873 - SL",
  "cantidad": 1,
  "estado": "BUENO",
  "ubicacion": "CENTRO EDUCATIVO VALLESOL",
  "detalle": "MARCA: SAMGUNG - REFERENCIA: NP 270E4V",
  "fecha_adquisicion": "2013-01-01",
  "imagenes": []
}
```

La fecha del ejemplo es ilustrativa; usa la fecha real del equipo o `null` si no se conoce. Los estados pueden ser `BUENO`, `REGULAR`, `MALO` u otro valor propio. Las categorías y estados de los filtros se construyen a partir de los registros del JSON.

**Importante:** el equipo con ID `20282` tiene placa `20272 - SL`; el equipo con ID `20290` tiene placa `20288 - SL`. Ambos datos se conservaron como figuran en el Excel. Sus fotografías deben ir en las carpetas `20282` y `20290`, respectivamente.

## Uso

- Busca por nombre, ID, placa, marca, referencia o detalle. La búsqueda ignora tildes y mayúsculas.
- Selecciona una categoría y filtra por estado.
- Cambia entre galería y lista, y ordena por nombre, ID o fotografías.
- Pulsa **Ver ficha** para explorar el equipo y sus fotografías.
- Usa Escape para cerrar la ficha y las flechas izquierda/derecha para cambiar de fotografía. `/` lleva el foco al buscador.
- **Descargar datos** guarda una copia del inventario que tienes abierto.
- **Abrir inventario** permite visualizar otro JSON compatible; no modifica el archivo original ni guarda cambios en el servidor.

Para trasladar la galería, copia la carpeta completa con el HTML, JSON y subcarpetas de imágenes. No usa recursos externos ni requiere conexión a internet.


## Inventario completo y agrupación

## Gestionar productos

Abre la galería desde XAMPP, entra en una ficha y pulsa **Editar producto**. Puedes modificar nombre, categoría, tipo, marca, referencia, color, ubicación, descripción y rutas de fotografías. Puedes establecer un estado para toda la ficha o conservar y modificar los estados individuales. Cada integrante permite modificar su placa y cantidad; el total se calcula al guardar. Cuando los integrantes tienen estados distintos, la ficha muestra **Estados diferentes**.

**Guardar cambios** actualiza `inventario.json` mediante `guardar.php`, sin base de datos. Cada guardado crea una copia anterior en `respaldos`, carpeta protegida contra acceso web. Si otra ventana guardó cambios, se solicita recargar para evitar sobrescribirlos.

**Eliminar** solicita confirmar la ficha y las unidades que se retirarán. Elimina del JSON la ficha completa y sus integrantes. Las fotografías se conservan y existe el respaldo anterior.

Si abres el HTML directamente o importas un JSON con **Abrir inventario**, los cambios se aplican a la sesión: debes pulsar **Descargar datos** y reemplazar el archivo para conservarlos.

Incluye los 130 registros del Excel: 138 unidades distribuidas en 56 fichas. Las cantidades originales se suman, incluidas cinco ollas y cinco colchonetas.

Se agrupan únicamente elementos con el mismo tipo, descripción (marca, referencia y color), estado y ubicación. No se asume una marca cuando el Excel no la indica. Diferentes colores o referencias conservan fichas distintas.

Cada ficha conserva todos los IDs, placas, cantidades, fechas y filas del Excel en `registros`. La búsqueda encuentra también los IDs y placas de los integrantes de un grupo. La vista previa permite consultarlos. El ID principal es el del primer integrante y determina la carpeta de fotografías del grupo.

Los indicadores muestran unidades; las categorías y los resultados muestran fichas. Por ejemplo, diez sillas RIMAX blancas forman una ficha con cantidad 10 y sus diez registros originales.


## Subir fotografías desde la galería

Abre un producto y pulsa **Subir fotografías**, selecciona una o varias imágenes y pulsa **Subir y asociar**. Se guardan con nombres únicos en `imagenes/ID/` y sus rutas se añaden automáticamente al JSON. La primera fotografía es la portada. Puedes subir más fotos cuando quieras.

En **Editar producto** también hay un botón para subir. Las miniaturas permiten **Usar como portada** o **Quitar asociación**; guarda los cambios para aplicar estas dos acciones. Quitar la asociación conserva el archivo de imagen. Subir fotografías las guarda inmediatamente, aunque luego canceles la edición de otros datos.

Se admiten JPG, PNG y WebP, hasta diez imágenes por envío. PHP puede imponer un límite menor de tamaño por archivo o envío. La subida requiere abrir la galería desde XAMPP.


## Filtrar fotografías y exportar PDF

En el selector **Fotografías**, elige **Sin fotografía** o **Con fotografía**. Se combina con la búsqueda, la categoría y el estado. Sin fotografía significa que la ficha no tiene imágenes asociadas en el JSON.

Pulsa **Exportar PDF** para abrir el reporte completo, independientemente de los filtros activos. Incluye resumen por categoría, todas las fichas, todas sus fotografías y registros individuales. Espera a que carguen las imágenes, pulsa **Guardar como PDF / Imprimir** y elige **Guardar como PDF** en el diálogo del navegador. El diseño usa papel A4. Si una foto no existe, se indica en el reporte.
