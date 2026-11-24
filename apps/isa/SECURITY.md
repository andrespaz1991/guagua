# Seguridad y privacidad

## Política por defecto

ISA no envía audio, conversaciones, progreso, telemetría ni diagnósticos fuera del equipo. Las funciones online futuras serán opt-in, visibles y separadas de la experiencia local.

## Controles

- CSP restrictiva y sin contenido remoto en WebView.
- Capacidades Tauri con mínimo privilegio.
- IPC tipado, validado y limitado a comandos conocidos.
- Ollama permitido solo en loopback por defecto.
- URLs remotas bloqueadas salvo consentimiento explícito.
- Queries parametrizadas a través de la infraestructura.
- Rutas canonicalizadas; no aceptar rutas arbitrarias desde la UI.
- Timeouts y cancelación en sidecars/proveedores.
- Keyring/Windows Credential Manager para secretos remotos.
- `.env.example` únicamente para desarrollo y sin secretos.
- Checksum, versión y licencia de binarios/modelos.

## Datos sensibles

No registrar en logs credenciales, URLs completas con secreto, prompts con datos académicos, conversaciones completas, audio, tokens o claves. Los archivos temporales de audio se eliminan al terminar un turno salvo opt-in explícito.

## Logs

Logs estructurados con nivel, componente, código de error, correlación y duración. Los mensajes de UI son comprensibles; los detalles técnicos se reservan a logs redaccionados. Debe existir rotación y mecanismo de exportación local.

## Dependencias

Cada fase de código incorpora auditoría de dependencias compatible con Rust y pnpm. No se agregan librerías por conveniencia sin justificar su necesidad.
