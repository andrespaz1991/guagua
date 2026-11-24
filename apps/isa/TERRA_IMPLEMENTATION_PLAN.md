# Plan de implementación para Terra

## Protocolo de ejecución

1. Leer `README.md`, `ARCHITECTURE.md`, los ADR y el bloque de la fase asignada.
2. Leer `AGENTS.md` si existe y ejecutar `git status --short`.
3. Confirmar que las decisiones de la fase están aprobadas. Si no lo están, documentar y detenerse.
4. Trabajar en una sola fase.
5. Crear primero contratos, pruebas y migraciones; después adaptadores/UI.
6. Ejecutar validaciones reales y actualizar documentos afectados.
7. Informar archivos, comandos, resultados, riesgos y trabajo pendiente de la siguiente fase.

No instalar modelos en CI, no crear commits sin autorización y no sustituir decisiones de ADR sin aprobación.

## Mapa de dependencias

```text
F0 docs
 └─ F1 shell/tooling
     └─ F2 domain + SQLite
         ├─ F3 onboarding/settings/hardware
         └─ F4 Ollama chat
             └─ F5 Learning Engine
                 ├─ F6 historial/progreso
                 └─ F7 STT → F8 TTS
                     └─ F9 MVP/offline/package
                         ├─ F10 FSRS
                         ├─ F11 adaptación
                         ├─ F12 pronunciación
                         ├─ F13 MySQL/PostgreSQL
                         └─ F14 wake word
```

## Fase 0 — Arquitectura y aprobación

### Objetivo

Entregar la arquitectura aprobable sin iniciar la implementación.

### Límites

- Crear únicamente documentación y ADR en estado `Proposed`.
- No crear `package.json`, `Cargo.toml`, scaffolding, dependencias, código de aplicación ni `ARCHITECTURE_APPROVED.md`.
- No instalar herramientas ni modelos.
- No avanzar a Fase 1 sin aprobación explícita del usuario.

### Entregables

- `README.md`, `ARCHITECTURE.md`, `ROADMAP.md` y este plan.
- `DATABASE.md`, `AI_PROVIDERS.md`, `LEARNING_ENGINE.md`, `SECURITY.md`, `TESTING.md` y `DECISIONS.md`.
- ADR 0001–0010 en `docs/adr/`.

### Aceptación

- Enlaces relativos válidos.
- Diez ADR en estado `Proposed`.
- No existe código de aplicación ni archivo de aprobación creado por el agente.
- Las únicas decisiones solicitadas son las críticas de `DECISIONS.md`.

## Fase 1 — Foundation

### Objetivo

Crear un shell de escritorio que compile y abra una UI ISA accesible, sin proveedores reales ni persistencia.

### Archivos esperados

- Root: `Cargo.toml`, `package.json`, `pnpm-workspace.yaml`, `.gitignore`, `.env.example`.
- `apps/desktop`: configuración Vite/TypeScript y UI mínima.
- `apps/desktop/src-tauri`: Tauri, capabilities restrictivas, logging y composición vacía.
- Configuración de format, lint, typecheck y pruebas.

### Tareas pequeñas

1. Inicializar workspace pnpm y Cargo sin dependencias innecesarias.
2. Configurar TypeScript estricto, format y lint.
3. Configurar Tauri 2 con React/Vite y capabilities mínimas.
4. Crear layout accesible: navegación, contenido principal, tema claro/oscuro y estados visuales de pipeline simulados.
5. Definir contrato IPC mínimo y generar bindings TypeScript desde Rust.
6. Añadir manejo global de errores y logging redaccionado.
7. Añadir tests frontend y Rust básicos.

### Aceptación

- App abre en Windows.
- No hay acceso a red, shell arbitrario ni filesystem desde UI.
- Build, format, lint, typecheck y tests pasan.

## Fase 2 — Domain y SQLite

### Objetivo

Persistir perfiles, settings, sesiones y mensajes en SQLite mediante ports y migraciones.

### Tareas

1. Crear crates `isa-domain`, `isa-application`, `isa-contracts`, `isa-infrastructure`.
2. Definir entidades y puertos de repository sin SeaORM en dominio/aplicación.
3. Implementar SeaORM SQLite en infraestructura.
4. Crear migración inicial y pruebas desde base vacía/versión anterior.
5. Implementar UnitOfWork y transacciones.
6. Crear backup local consistente y pruebas de restore no destructivo.

### Aceptación

- Crear y recuperar perfil, sesión y mensaje en base temporal.
- Una operación multi-entidad se revierte completamente al fallar.
- Secretos no aparecen en esquema ni logs.

## Fase 3 — Onboarding, settings y hardware

### Objetivo

Crear configuración inicial, health checks y recomendación de perfil de recursos.

### Tareas

1. Implementar `HardwareProfileProvider` con timeouts y datos Windows/NVIDIA de solo lectura.
2. Implementar modelos de salud de proveedor y errores comprensibles.
3. Implementar onboarding de nivel, objetivo, tiempo disponible, variante de inglés, voz y corrección.
4. Implementar settings sin secretos y keyring como puerto para secretos futuros.
5. Proponer perfiles light/balanced/high explicables y benchmark corto opt-in.

### Aceptación

- Sin Ollama instalado, la UI informa el estado sin romperse.
- Cambios de settings persisten tras reiniciar.
- Ningún comando viene libremente de la UI.

## Fase 4 — Chat local vertical

### Objetivo

Entregar conversación escrita ISA con Ollama y resultado estructurado validado.

### Tareas

1. Definir `LlmProvider`, request/response y FakeLlmProvider.
2. Implementar Prompt Manager versionado con secciones tipadas.
3. Implementar OllamaAdapter loopback con health, descubrimiento de modelos, timeout y cancelación.
4. Definir JSON Schema de `TutorTurnResult` y validador.
5. Implementar caso de uso de turno con persistencia atómica.
6. Construir conversación escrita con estados Thinking/Error/Cancel.
7. Añadir contract tests fake y smoke test opt-in para Ollama.

### Aceptación

- Un proveedor fake permite E2E sin modelo.
- Una respuesta JSON inválida no crea evidencia ni correcciones.
- Ollama ausente deja historial/settings disponibles.

## Fase 5 — Learning Engine

### Objetivo

Transformar turnos validados en correcciones, evidencia, patrones y resumen de sesión.

### Tareas

1. Implementar taxonomía de corrección y política por perfil/modo.
2. Implementar `SkillEvidence`, `ErrorPattern` y reglas de agregación.
3. Implementar recuperación de memoria académica relevante.
4. Implementar feedback de usuario sobre correcciones.
5. Implementar resumen de sesión y recomendación inmediata.
6. Probar reglas sin LLM real.

### Aceptación

- Un solo turno no modifica CEFR definitivo.
- Error repetido consolida patrón de forma determinista.
- Conversation y Teacher muestran distinta intensidad de corrección.

## Fase 6 — Historial y progreso

### Objetivo

Hacer visibles sesiones, errores y progreso útil.

### Tareas

1. Consultas paginadas de sesiones/conversaciones.
2. Filtros y búsqueda.
3. Métricas diarias, tiempo, días activos, racha y objetivos.
4. Vista de resumen de sesión y errores frecuentes.
5. Exportación JSON/CSV inicial.

### Aceptación

- Datos persisten y sobreviven reinicio.
- UI no carga historial completo sin paginación.
- Exportación no incluye secretos.

## Fase 7 — Captura y STT

### Objetivo

Convertir voz local en un turno de texto seguro.

### Tareas

1. Definir `SpeechToTextProvider` y fake.
2. Capturar audio mono 16 kHz, con selector de dispositivo y nivel visual.
3. Gestionar VAD y archivos temporales.
4. Implementar sidecar/adaptador whisper.cpp CPU.
5. Implementar auto-detección y language hint inglés/español.
6. Entregar WAV fixtures y pruebas de errores.

### Aceptación

- Sin micrófono o STT, el chat escrito sigue disponible.
- Temporales se eliminan tras procesar salvo opt-in.
- El pipeline se puede probar con WAV fixture sin hardware.

## Fase 8 — TTS

### Objetivo

Hablar respuestas ISA localmente con voces configurables.

### Tareas

1. Definir `TextToSpeechProvider` y fake.
2. Verificar licencias, checksum y manifest de activos Kokoro.
3. Implementar sherpa-onnx/Kokoro y catálogo de voces.
4. Integrar selección US/UK, velocidad y playback.
5. Implementar cancelación, caché limitada y degradación textual.

### Aceptación

- El usuario puede repetir una respuesta hablada.
- TTS fallido no invalida el turno escrito.
- No se descarga ningún activo sin consentimiento.

## Fase 9 — Cierre de MVP

### Objetivo

Probar instalación, recuperación, diagnóstico y operación offline de extremo a extremo.

### Tareas

1. E2E con fakes: audio→STT→LLM→persistencia→TTS.
2. Smoke tests opt-in con activos reales.
3. Prueba offline documentada y repetible.
4. Onboarding de assets/model packs con licencia/checksum.
5. Instalador Windows con soporte offline para WebView2 cuando se distribuya así.
6. Manual de operaciones: backup, restore, logs y health checks.

### Aceptación

- La prueba offline completa pasa.
- La app reinicia y conserva progreso.
- Fallos comunes dan acciones comprensibles.

## Fases posteriores

- F10: vocabulario, criterios de tarjetas y FSRS.
- F11: CEFR acumulativo, plan de 12 semanas y roleplays extensibles.
- F12: PronunciationAssessmentProvider respaldado por medición fonética real.
- F13: adapters MySQL/PostgreSQL, contract tests y export/import versionado.
- F14: wake word opt-in, presupuesto de CPU y licencias verificadas.

## Definition of Done común

- Alcance limitado a la fase.
- Build, format, lint, typecheck y tests pasando.
- Migraciones probadas cuando existan.
- Errores, cancelación y redacción de logs cubiertos.
- Documentación/ADR actualizados.
- Sin secretos ni telemetría externa.
- `git diff --check` limpio.

## Recuperación

- No reescribir ni borrar datos para resolver un fallo.
- Hacer backup antes de migraciones materiales.
- Mantener cambios pequeños y reversibles.
- Si una dependencia o licencia bloquea la fase, reportar el bloqueo y la alternativa mínima sin avanzar a una fase distinta.
