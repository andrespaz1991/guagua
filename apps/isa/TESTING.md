# Estrategia de pruebas

## Pirámide

- Unit: dominio, políticas pedagógicas, taxonomías, recuperación de memoria, FSRS y planificación.
- Integration: repositories, migraciones, transacciones, IPC, Prompt Manager y validación de schema.
- Contract: cada proveedor LLM/STT/TTS y cada backend de base de datos.
- Frontend: componentes, accesibilidad, navegación y estados de pipeline mediante IPC simulado.
- E2E: flujo de una sesión con fixtures y proveedores fake; smoke opt-in con proveedores reales.

## Flujo E2E obligatorio

```text
WAV fixture → STT fake/local → transcript → LLM fake → resultado validado
→ persistencia → TTS fake → audio → resumen y progreso
```

El CI normal no descarga modelos ni requiere GPU. Las pruebas reales se ejecutan solo con una marca explícita de entorno.

## Prueba offline del MVP

Con activos instalados previamente: desconectar red, abrir ISA, practicar por texto y voz, transcribir, responder, sintetizar, guardar, reiniciar y consultar historial/progreso. Debe probarse que no existe tráfico externo.

## Calidad de fase

Toda fase debe ejecutar los comandos apropiados de format, lint, typecheck, test y build. La fase no se completa si `git diff --check` falla o si un test queda deliberadamente pendiente.
