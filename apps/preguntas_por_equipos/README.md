# Reto por equipos

Juego de preguntas por turnos para proyectar en clase. Entrada: `/guagua/apps/preguntas_por_equipos/`.

En **Bancos listos para usar → Cargar preguntas de seguridad informática** están disponibles 36 preguntas sobre navegación segura, protección de cuentas y de jóvenes y análisis preventivo del caso Scully. Incluyen respuestas concretas y tiempos de 45 o 60 segundos. El cambio conserva equipos y reglas, y reinicia el progreso tras confirmación. `seguridad-informatica.json` permite importar el banco completo; `GUIA_SEGURIDAD.md` explica la cobertura de la presentación y de los subtítulos automáticos del video, las fuentes oficiales y los criterios de evaluación.

El ejemplo predeterminado contiene 30 casos de derechos humanos, uno por artículo de la Declaración Universal. Cada respuesta identifica el derecho, la garantía o el principio específico y su artículo; no se presenta como una cita literal. Por ejemplo: «Derecho a la presunción de inocencia — artículo 11 de la DUDH». Los artículos 29 y 30 se identifican como deberes y prohibición de suprimir derechos. Referencia: [texto de la ONU](https://www.un.org/es/about-us/universal-declaration-of-human-rights).

Si ya tienes preguntas guardadas, pulsa **Cargar preguntas de derechos humanos** para sustituir el banco y poner el progreso en cero, conservando equipos y reglas. El cambio requiere confirmación; exporta antes tu respaldo si deseas conservar la partida anterior. **Descargar derechos humanos JSON** entrega `derechos-humanos.json`, listo para **Importar respaldo**, con tres equipos, 30 segundos por pregunta y 10 puntos por acierto.

Cada partida nueva mezcla automáticamente el orden de las preguntas, sin repetirlas. Las respuestas, los tiempos, los puntos y las asignaciones explícitas de grupo siguen ligados a su pregunta. Los grupos automáticos mantienen sus turnos habituales. Reiniciar puntos o volver a jugar realiza otro sorteo; continuar o importar una partida guardada conserva el orden sorteado y su progreso. El banco de preguntas mantiene el orden de edición.

1. Escribe de 2 a 12 grupos, un nombre por línea.
2. Edita las preguntas y sus respuestas. Puedes asignar un grupo concreto o dejar el reparto automático por turnos.
3. Configura los segundos, los puntos por acierto y el descuento por error. Cada pregunta puede tener su propio tiempo y valor.
4. Inicia la partida. El temporizador empieza cuando pulsas **Iniciar tiempo**. Puedes pausarlo y usar **Reiniciar tiempo** para comenzar otro intento desde el tiempo completo. En la diapositiva, **Tiempo de esta pregunta → Aplicar y reiniciar** permite cambiar su duración durante la partida (entre 5 y 3600 segundos). Reiniciar oculta de nuevo la respuesta y mantiene el marcador. Si el turno ya fue evaluado, primero pulsa **Deshacer evaluación**.
5. Pulsa **Revelar respuesta**, evalúa como correcta o incorrecta y avanza. Puedes omitir sin puntos o deshacer la evaluación del turno actual.
6. Al terminar, consulta la clasificación y descarga los resultados en CSV.

El tiempo agotado no revela la respuesta ni evalúa automáticamente. Al revelar se detiene el reloj. El marcador se calcula desde los turnos registrados para evitar puntuar dos veces la misma pregunta. Los puntos pueden quedar en negativo si hay descuento por error.

**Exportar resumen de respuestas** descarga `resumen-respuestas.txt`, con el título, las preguntas y sus respuestas esperadas. Desde la preparación usa el banco tal como está editado; durante la partida y en los resultados respeta el orden sorteado y añade el grupo, la evaluación, los puntos de cada turno y el marcador. Incluye también las preguntas pendientes, identificadas como tales. El juego no registra el texto de las respuestas dadas por los estudiantes.

Las preguntas, los grupos y la partida se guardan en el navegador, separados por usuario e institución. Al recargar, se ofrece continuar con el reloj pausado. Usa **Exportar respaldo** para conservar un archivo JSON con las preguntas y el progreso, o para llevarlo a otro navegador. No se sincroniza entre dispositivos. Evita editar la misma partida en varias pestañas.

El acceso requiere sesión de administrador, directivo o docente. Para añadir un acceso al menú, usa **Apps → Sincronizar carpetas** y selecciona `preguntas_por_equipos`.

## Plantilla de importación

En **Prepara tu partida → Descargar plantilla JSON** está `plantilla-preguntas.json`, compatible con **Importar respaldo**. Incluye tres grupos y ejemplos de reparto automático, asignación explícita y tiempo/puntos por pregunta. `GUIA_IMPORTACION.md` explica las claves y los límites. La plantilla inicia una partida sin progreso anterior (`session: null`).

## Estructura y comprobaciones

- `index.php`: sesión y ámbito del guardado.
- `view.php` y `styles.css`: interfaz y diseño adaptable.
- `engine.mjs`: validación, turnos, reloj y puntuaciones, independiente de la interfaz.
- `app.mjs`: edición, presentación, guardado, importación y exportación.

Ejecuta `node --test apps/preguntas_por_equipos/engine.test.mjs apps/preguntas_por_equipos/summary.test.mjs` y `php -l apps/preguntas_por_equipos/index.php`.
