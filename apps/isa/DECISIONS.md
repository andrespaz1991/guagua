# Decisiones y aprobaciones

El usuario aprobó el inicio de implementación el 24 de agosto de 2026. Los ADR de Fase 0 pasan a estado `Accepted` para las fases autorizadas.

## Decisiones aprobadas

1. **Desktop:** Tauri 2 + React + Rust frente a Electron.
2. **Persistencia MVP:** SQLite canónico local; MySQL/PostgreSQL solo como adaptadores posteriores o cuando exista una necesidad explícita.
3. **LLM:** Ollama externo/gestionado durante desarrollo y MVP, antes de evaluar un runtime empaquetado.
4. **Modelos:** Qwen 3.5 4B Q4 como ligero y 9B Q4 como equilibrado, sujetos a benchmark local.
5. **STT:** whisper.cpp en CPU como primera implementación.
6. **TTS:** sherpa-onnx/Kokoro, sujeto a verificación de licencia de motor, pesos y voces.
7. **Alcance:** wake word, pronunciación fonética, FSRS avanzado, MySQL/PostgreSQL activos y plan adaptativo completo después del MVP.

## Uso de MySQL

El usuario autorizó usar MySQL si resulta necesario. La arquitectura lo permite sin convertirlo en requisito. Cuando se active, Terra debe solicitar las credenciales por mecanismo seguro y no debe crear/escribir una base remota sin una tarea explícita que lo requiera.
