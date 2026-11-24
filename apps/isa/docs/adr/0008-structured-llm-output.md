# ADR 0008: Salida LLM estructurada y validada

**Estado:** Accepted

## Contexto

Las correcciones, vocabulario y estimaciones no pueden persistirse como texto libre no validado.

## Decisión

Solicitar `TutorTurnResult` contra JSON Schema y validarlo antes de cualquier escritura pedagógica.

## Consecuencias

- El LLM no es fuente de verdad directa.
- Se permite una reparación controlada; después se degrada a respuesta textual.
- Se versionan prompts y schemas para trazabilidad.
