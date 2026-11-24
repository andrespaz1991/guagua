# ADR 0006: whisper.cpp como STT inicial

**Estado:** Accepted

## Contexto

El producto requiere STT local bilingüe y la GPU disponible tiene 4 GB de VRAM.

## Decisión

Usar whisper.cpp con modelo multilingüe cuantizado y CPU como ruta inicial.

## Consecuencias

- Menos conflicto de VRAM con el LLM.
- Sidecar local controlable y testeable con fixtures.
- Faster-Whisper permanece como adaptador futuro, no dependencia inicial.
