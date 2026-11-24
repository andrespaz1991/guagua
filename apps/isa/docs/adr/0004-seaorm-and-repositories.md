# ADR 0004: SeaORM detrás de repositories

**Estado:** Accepted

## Contexto

Se requiere portabilidad SQLite/MySQL/PostgreSQL sin acoplar negocio al motor.

## Decisión

Usar SeaORM dentro de Infrastructure y exponer repositories/UnitOfWork definidos por Application.

## Consecuencias

- Los casos de uso no conocen SQL ni entidades ORM.
- Se deben probar migraciones y contratos por backend cuando se añadan.
- SQL específico queda aislado y documentado.
