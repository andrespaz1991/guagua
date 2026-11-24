# ADR 0003: SQLite como almacenamiento canónico del MVP

**Estado:** Accepted

## Contexto

El requisito principal es offline-first. Un servidor MySQL remoto no puede ser requisito de una sesión local.

## Decisión

SQLite será la fuente de verdad local. MySQL y PostgreSQL se habilitarán por adapters posteriores, export/import o sincronización explícita.

## Consecuencias

- Backup y restore locales sencillos.
- MySQL se podrá usar si una tarea posterior lo justifica.
- No se implementa dual-write ni fallback silencioso que cause divergencias.
