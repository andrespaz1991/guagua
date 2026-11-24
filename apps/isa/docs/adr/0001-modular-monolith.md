# ADR 0001: Monolito modular local

**Estado:** Accepted

## Contexto

ISA es una aplicación personal offline-first. Necesita componentes intercambiables, pero no carga operativa distribuida.

## Decisión

Usar monolito modular con Domain, Application, Infrastructure, Desktop y UI separadas por dependencias.

## Consecuencias

- Menor complejidad de instalación y depuración.
- Límites claros para sustituir proveedores.
- No se introducen microservicios ni colas distribuidas en el MVP.
