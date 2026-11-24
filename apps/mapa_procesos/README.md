# Mapa de procesos de Guagua

Explorador de diagnóstico con siete ramas, 38 procesos, relaciones funcionales y el registro de 61 entradas del menú del respaldo del 19 de noviembre de 2026.

## Acceso

En Guagua: **CONFIG → Mapa de procesos de Guagua**, o `apps/mapa_procesos/index.php`. Requiere una sesión de administrador, directivo o docente. Cada pantalla enlazada mantiene sus propios permisos.

La copia autónoma `output/diagnostico-guagua/Mapa_interactivo_Guagua.html` puede abrirse en un navegador sin base de datos ni conexión a Internet. Sus enlaces a módulos apuntan a `http://localhost/guagua/`.

## Interacciones

- Árbol expandible Guagua → rama → proceso → módulos, conservando los nodos anteriores y las otras ramas abiertas.
- Migas de navegación para volver a cada antecesor sin cerrar el árbol.
- Controles + y − para desplegar o contraer solo el nodo elegido.
- Doble clic en un proceso o módulo para abrir su pantalla principal en una ventana nueva. Los nodos agrupadores y procesos sin pantalla abren su propia vista del mapa. El clic sencillo conserva la expansión; Mayús + Entrar ofrece la misma apertura con teclado.
- Situación actual, recomendación, responsable, referencias y pantallas existentes.
- Navegación entre dependencias de un proceso.
- Flujo escolar recomendado con secuencia y dependencias propuestas.
- Búsqueda, filtros de diagnóstico, zoom, desplazamiento y ajuste del mapa.
- Directorio con rutas duplicadas y entradas sin pantalla confirmada señaladas.
- Selección y ramas desplegadas conservadas en el fragmento de la URL, sin escribir datos académicos.
- Presentación adaptada al móvil y controles accesibles con teclado.

## Alcance del contenido

Es un modelo editorial del diagnóstico, no un monitor de actividad ni una lectura en vivo de la base. Una brecha es una corrección o conexión pendiente; una herramienta complementaria no se considera sin uso. Ningún enlace ejecuta automáticamente instalación, inscripción, eliminación, envío de mensajes o cierre. La acción de cerrar sesión del registro original no tiene enlace ejecutable.

## Archivos

- `datos.js`: procesos, ramas, recomendaciones y relaciones funcionales.
- `registro.js`: entradas del menú contrastadas con archivos locales.
- `mapa.js`, `mapa.css` y `vista.html`: interacción y presentación compartidas por el módulo y la copia autónoma.
- `index.php`: sesión, permisos e integración con el menú común.

Para reflejar cambios posteriores en Guagua deben revisarse el modelo y sus rutas; no se deduce la ausencia de uso a partir del menú. Las referencias F01 a F10 remiten al informe del diagnóstico.

## Verificación

Las pruebas de permisos incluyen el nuevo módulo. La prueba de interacción del explorador está en `tests/mapa_procesos_ui.cjs` para Playwright CLI; utiliza una vista aislada sin acceso a registros reales.
