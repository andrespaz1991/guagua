# Crear un archivo de preguntas

1. Pulsa **Descargar plantilla JSON** desde la preparación del juego.
2. Abre el archivo descargado (`plantilla-reto-por-equipos.json`) en un editor de texto y sustituye los ejemplos. Conserva los nombres de las claves, las comillas, las comas y los corchetes. Guarda como `.json` con codificación UTF-8.
3. En el juego, pulsa **Importar respaldo**, selecciona tu archivo y confirma. Revisa el banco de preguntas antes de comenzar.

La plantilla importa preguntas y grupos con el marcador en cero. Exporta un respaldo de tu partida actual si quieres conservarla antes de importar otra.

## Datos de la partida (`config`)

| Clave | Qué debes escribir |
| --- | --- |
| `title` | Nombre de la partida, hasta 100 caracteres. |
| `seconds` | Tiempo general para cada pregunta, entre 5 y 3600 segundos. |
| `points` | Puntos generales por acierto, entre 1 y 1000. |
| `penalty` | Puntos que se descuentan por error, entre 0 y 1000. Usa 0 para no descontar. |
| `teams` | Entre 2 y 12 grupos, con nombres distintos de hasta 50 caracteres. |
| `questions` | Entre 1 y 200 preguntas con sus respuestas. |

## Grupos (`teams`)

Cada grupo tiene `name` (nombre visible) e `id`. Mantén los identificadores en orden: `g1`, `g2`, `g3`… El juego identifica los grupos por su posición en esta lista.

## Preguntas (`questions`)

| Clave | Qué debes escribir |
| --- | --- |
| `question` | La pregunta, hasta 2000 caracteres. |
| `answer` | La respuesta oculta, hasta 4000 caracteres. |
| `teamId` | `auto` para repartir por turnos o el identificador de un grupo, por ejemplo `g2`. |
| `seconds` | Opcional: tiempo particular para esa pregunta. Si omites esta clave, usa el tiempo general. |
| `points` | Opcional: valor particular de esa pregunta. Si omites esta clave, usa los puntos generales. |

Para añadir preguntas, copia un bloque dentro de `questions` y sepáralo del anterior con una coma. El último bloque no lleva coma al final. Para escribir comillas dentro de una pregunta usa `\"`; para un salto de línea, `\n`.

Conserva `version` en `1` y `session` en `null`. No necesitas calcular ni escribir puntuaciones: el juego las registra al evaluar cada respuesta.
