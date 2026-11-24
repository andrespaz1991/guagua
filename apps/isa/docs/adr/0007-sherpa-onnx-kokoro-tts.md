# ADR 0007: sherpa-onnx y Kokoro para TTS

**Estado:** Accepted

## Contexto

ISA necesita voces locales configurables en inglés, incluidas variantes US/UK.

## Decisión

Usar sherpa-onnx/Kokoro en CPU detrás de `TextToSpeechProvider`, solo después de verificar licencia de motor, pesos y voces.

## Consecuencias

- Varias voces y generación local.
- Se debe mantener un manifest de activos con checksum/licencia.
- Piper y voz del sistema quedan como fallback potencial.
