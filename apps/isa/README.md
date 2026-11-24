# ISA — English Tutor

ISA es una aplicación de escritorio personal para practicar inglés mediante texto y voz. Su principio rector es **local-first / offline-first**: una vez instalados los runtimes y modelos, una sesión normal no depende de Internet.

## Estado

El proyecto está en **Fase 1: Foundation**. La arquitectura y los ADR iniciales fueron aprobados y el shell local de escritorio ya está implementado. La validación completa del host Tauri queda condicionada a terminar de instalar el toolchain de Rust/Windows.

## Objetivo del MVP

- Tutor ISA por texto, con correcciones estructuradas.
- Persistencia local en SQLite.
- Sesiones, historial, errores frecuentes y progreso básico.
- Reconocimiento y síntesis de voz locales.
- Recuperación y diagnóstico de proveedores.
- Prueba completa sin Internet después de instalar activos locales.

## Decisiones adoptadas

- Escritorio: Tauri 2, React, TypeScript, Vite y Rust.
- LLM: Ollama en loopback; Qwen 3.5 4B/9B según benchmark local.
- STT: whisper.cpp, inicialmente en CPU.
- TTS: sherpa-onnx con Kokoro, inicialmente en CPU y sujeto a verificación de licencias de modelos.
- Datos: SQLite como fuente de verdad local; SeaORM y repositories para habilitar MySQL/PostgreSQL después del MVP.

## Documentación

- [Arquitectura](ARCHITECTURE.md)
- [Plan de implementación para Terra](TERRA_IMPLEMENTATION_PLAN.md)
- [Base de datos](DATABASE.md)
- [Proveedores de IA](AI_PROVIDERS.md)
- [Learning Engine](LEARNING_ENGINE.md)
- [Seguridad](SECURITY.md)
- [Pruebas](TESTING.md)
- [Decisiones y ADR](DECISIONS.md)

## Desarrollo local

Requisitos: Node.js 22+, pnpm 11+ y, para ejecutar Tauri, Rust estable con las herramientas de compilación C++ de Microsoft.

```powershell
pnpm install
pnpm dev
```

Para abrir ISA con doble clic en este equipo, usa [INICIAR_ISA.cmd](INICIAR_ISA.cmd). El iniciador comprueba XAMPP, inicia Apache y MySQL si hiciera falta, inicia el servidor local y abre `http://127.0.0.1:1420/`. La primera vez hay que ejecutar `pnpm install` desde esta carpeta.

Validación del frontend:

```powershell
pnpm lint
pnpm test
pnpm build
```

Cuando Rust esté listo, el host de escritorio se inicia con:

```powershell
pnpm --dir apps/desktop tauri dev
```

Foundation incluye un shell de conversación local, selector claro/oscuro, estados de tutor y pruebas unitarias del flujo de mensajería. La siguiente fase incorpora SQLite, perfiles y repositorios.

## Modelo conversacional local

La implementación actual usa LM Studio en `http://169.254.173.61:1234` con el modelo `gemma-2-2b-it`. ISA envía el historial completo de la conversación activa junto con el perfil del estudiante, para que el modelo mantenga el contexto y evite repetir actividades o explicaciones. Para conversaciones muy largas, el límite efectivo es la ventana de contexto configurada en el modelo local.

En LM Studio, carga `gemma-2-2b-it` y activa el servidor desde **Developer → Start server**. Después, abre ISA y pulsa **Check LM Studio**. El panel Local AI permite cambiar la dirección del servidor y el identificador del modelo sin modificar código.
