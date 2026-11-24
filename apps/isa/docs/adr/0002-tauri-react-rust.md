# ADR 0002: Tauri 2, React y Rust

**Estado:** Accepted

## Contexto

El equipo tiene 24 GB de RAM y una GPU de 4 GB; IA local necesita reservar recursos. Se requiere Windows, sidecars y UI moderna.

## Decisión

Usar Tauri 2 con React/TypeScript/Vite y backend Rust.

## Alternativas

- Electron: ecosistema Node amplio, pero mayor runtime base y no necesario para el MVP.
- Aplicación web: no cumple experiencia desktop/offline de forma independiente.

## Consecuencias

- Requiere toolchain Rust y WebView2.
- Permite capabilities y sidecars acotados.
- Toda integración nativa se centraliza en el host Rust.
