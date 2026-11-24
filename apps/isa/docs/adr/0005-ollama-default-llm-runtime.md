# ADR 0005: Ollama como runtime LLM del MVP

**Estado:** Accepted

## Contexto

Se necesita inferencia local configurable en Windows y cambio de modelos sin recompilar.

## Decisión

Usar Ollama mediante API loopback detrás de `LlmProvider`.

## Consecuencias

- Desarrollo e instalación inicial más simples.
- El MVP requiere informar claramente si Ollama/modelo no están disponibles.
- Un runtime empaquetado como llama.cpp se evaluará en packaging, no antes.
