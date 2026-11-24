# Recursos educativos digitales

- `index.php`: consulta y preparación del catálogo.
- `visor_red.php`: carga del recurso por ID y preparación de datos.
- `lib/visor.php`: validación de rutas y lectura de actividades SCORM.
- `views/`: vista del visor, estados de error y renderizado por formato.
- `assets/`: estilos y comportamiento del catálogo y del visor.

El visor se integra como fragmento en `comun/plantilla.php`, sin duplicar el documento HTML. Los enlaces del catálogo solo necesitan `?red=ID`; el contenido procede de la base de datos. Se conserva la integración existente de estrellas y descargas.

La vista ampliada oculta los detalles. La pantalla completa depende de la API del navegador y permite salir con Escape. La copia de enlaces ofrece un campo seleccionable cuando no hay portapapeles disponible. Los sitios que impiden la incrustación se consultan con «Abrir recurso».

## Comprobaciones

`php red/tests/visor_test.php` verifica los formatos, el escape HTML y la selección de actividades SCORM, incluyendo manifiestos con namespace y rechazo de rutas inválidas. No necesita base de datos ni sesión.

`php -l red/visor_red.php` y `node --check red/assets/visor.js` comprueban la sintaxis. Para la comprobación integrada del catálogo, estrellas y descargas se requiere una sesión de Guagua y una base de datos configurada.
