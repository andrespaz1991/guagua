# Arquitectura de ISA

## Principios

1. La práctica fundamental funciona sin red después de instalar activos locales.
2. La lógica pedagógica es determinista y no depende exclusivamente de prompts.
3. El LLM propone contenido; el sistema valida y decide qué persistir.
4. Los proveedores son sustituibles detrás de contratos.
5. Es un monolito modular local, no un sistema distribuido.
6. Los recursos de la RTX 3050 de 4 GB se administran explícitamente.

## Diagrama de componentes

```text
Usuario
  ↓
React Desktop UI
  ↓  IPC tipado / eventos de estado
Tauri Host y Composition Root
  ↓
Application Use Cases
  ↓
Domain + Learning Engine
  ↓
Ports
  ├── LlmProvider ───────────── OllamaAdapter
  ├── SpeechToTextProvider ─── WhisperCppAdapter
  ├── TextToSpeechProvider ─── SherpaKokoroAdapter
  ├── HardwareProfileProvider─ Windows/NVIDIA Adapter
  └── Repository Ports ─────── SeaORM Adapters
                                  ├── SQLite (MVP)
                                  ├── MySQL (posterior, si es necesario)
                                  └── PostgreSQL (posterior)
```

## Límites de las capas

| Capa | Responsabilidad | No puede depender de |
|---|---|---|
| Domain | entidades, invariantes, reglas de dominio | Tauri, React, HTTP, SeaORM, Ollama |
| Application | casos de uso, transacciones, orquestación | detalles de proveedores |
| Infrastructure | adaptadores concretos de puertos | UI React |
| Desktop | composición, comandos IPC, eventos | reglas pedagógicas dentro de handlers |
| UI | experiencia, estado visual y accesibilidad | acceso directo a DB/LLM/audio |

## Flujo de un turno

```text
Entrada de texto o transcripción
→ validar y normalizar
→ cargar sesión, objetivo y memoria relevante
→ Prompt Manager compone solicitud tipada
→ LLM produce TutorTurnResult JSON
→ validar esquema y aplicar políticas pedagógicas
→ guardar mensaje, evidencia y correcciones en una transacción
→ actualizar patrones, progreso y revisiones candidatas
→ presentar respuesta
→ sintetizar voz opcionalmente
```

## Concurrencia y recursos

- Una generación LLM a la vez en el MVP.
- Un trabajo STT a la vez.
- STT y TTS se ejecutan por CPU inicialmente; el LLM puede usar GPU parcialmente.
- Los procesos externos deben tener timeout, cancelación, health check y métricas.
- La UI muestra: `Idle`, `Recording`, `Transcribing`, `Thinking`, `Speaking`, `Cancelling` o `Error`.
- Ninguna operación de IA bloquea el hilo de UI.

## Memoria pedagógica

La memoria no es el historial completo. Se almacenan hechos académicos estructurados: objetivos, evidencia de habilidad, correcciones, patrones de error, vocabulario y revisiones. El Prompt Manager recupera solo el contexto relevante y una ventana pequeña de conversación.

## Perfiles de tutor

ISA mantiene identidad estable, pero la política cambia por perfil:

- `Teacher`: explicación y correcciones pedagógicas.
- `Conversation`: conversación natural con corrección selectiva.
- `Coach`: metas y seguimiento.
- `Immersion`: inglés casi exclusivo.
- `Exam`: ejercicios y evaluación guiada.

## Decisiones pendientes

Todos los ADR de esta fase están en estado `Proposed`. La implementación no comienza hasta que el usuario apruebe las decisiones críticas listadas en [DECISIONS.md](DECISIONS.md).
