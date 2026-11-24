# ADR 0009: FSRS para repetición espaciada

**Estado:** Accepted

## Contexto

ISA necesita programar revisiones basándose en historial y no en prompts.

## Decisión

Usar FSRS como algoritmo de scheduling, con logs de revisión y parámetros por defecto al inicio.

## Consecuencias

- Sustituye implementar SM-2 manualmente.
- La optimización individual se pospone hasta tener suficiente historial.
- La lógica es determinista y testeable.
