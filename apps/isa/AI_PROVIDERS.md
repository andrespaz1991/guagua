# Proveedores de IA y voz

## Contratos

La aplicación define puertos semánticos: `LlmProvider`, `SpeechToTextProvider`, `TextToSpeechProvider`, `KeywordSpottingProvider`, `PronunciationAssessmentProvider` y `HardwareProfileProvider`.

Cada adaptador implementa health check, timeout, cancelación, métricas y errores tipados. Domain y Application no conocen SDKs concretos.

## LLM

### Runtime del MVP

Ollama local en loopback. El adaptador consulta salud y modelos, envía solicitudes estructuradas y nunca filtra sus tipos al dominio.

### Perfiles propuestos

| Perfil | Modelo candidato | Contexto inicial | Uso |
|---|---|---:|---|
| Ligero | Qwen 3.5 4B Q4_K_M | 4k–8k | menor latencia |
| Equilibrado | Qwen 3.5 9B Q4_K_M | 8k | recomendado, offload parcial |
| Alta calidad | 9B con cuantización superior o 12B Q4 probado | 8k | solo tras benchmark |

La RTX 3050 tiene 4 GB de VRAM: no se presupone carga completa de un 9B en GPU ni se recomiendan modelos de 17 GB o más para uso normal.

## STT

`whisper.cpp` es el primer adaptador. Debe aceptar WAV PCM mono a 16 kHz, VAD, detección inglés/español, hint de idioma, limpieza de temporales y modo CPU. Faster-Whisper queda como alternativa posterior.

## TTS

`sherpa-onnx` + Kokoro es el adaptador inicial, en CPU. Debe exponer voces, variantes US/UK verificadas, velocidad, generación local y caché acotada. Piper y voz del sistema se evalúan como fallbacks.

Antes de distribuir un motor o modelo se registra origen, versión, licencia, checksum y tamaño. La licencia de pesos y voces se comprueba por separado de la del motor.

## Respuestas estructuradas

El LLM produce `TutorTurnResult` con respuesta, correcciones, vocabulario, evidencia de habilidad, evidencia CEFR y siguiente acción. JSON Schema se valida antes de persistir. Una reparación controlada es el máximo permitido; después se degrada a respuesta textual sin escrituras académicas no verificadas.

## Proveedores posteriores

- Wake word: sherpa-onnx KWS, solo opt-in y tras licencia verificable.
- Pronunciación: evaluación fonética/forced alignment; no inferir precisión fonética desde STT.
- Embeddings: solo si una evaluación demuestra que la recuperación estructurada actual es insuficiente.
