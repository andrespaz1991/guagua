# Persistencia y datos

## Implementación actual de desarrollo

ISA guarda actualmente el historial y el perfil en la base local **MySQL/MariaDB de XAMPP**, esquema `guagua`, mediante las tablas `isa_profiles`, `isa_conversations` e `isa_messages`. El esquema reproducible está en `database/schema.sql` y la API local en `api/index.php`.

Esto permite revisar conversaciones sin Internet mientras XAMPP esté ejecutándose. La aplicación conserva una copia de revisión en el navegador si la base local no está disponible. SQLite sigue siendo el objetivo para el empaquetado autónomo de Tauri, cuando el toolchain nativo de Windows esté listo.

## Decisión principal

SQLite es la base de datos canónica local en el MVP. Así se conserva el funcionamiento offline, simplifica backup/restauración y evita que un fallo de red afecte una sesión.

MySQL podrá usarse cuando sea necesario, por ejemplo para una réplica explícita, despliegue personal centralizado o futura sincronización. No se usará como requisito del MVP ni se harán escrituras dobles silenciosas.

PostgreSQL queda soportado por la arquitectura para una etapa posterior.

## Abstracción

Los casos de uso consumen puertos de repository y `UnitOfWork`; no consumen consultas ni entidades SeaORM. Infrastructure implementa esos puertos. SeaORM se limita a la infraestructura.

```text
Application → Repository Ports → SeaORM Adapter → SQLite/MySQL/PostgreSQL
```

## Entidades iniciales

- `LearnerProfile`, `LearningGoal`, `LearningObjective`.
- `Session`, `Conversation`, `Message`.
- `Correction`, `ErrorPattern`, `SkillEvidence`.
- `VocabularyItem`, `ReviewItem`, `ReviewLog`.
- `TutorSettings`, `ProviderSettings`, `ModelAsset`.
- `DailyProgress`.

Todos los IDs usan UUID v7. Fechas en UTC. Los datos flexibles se serializan con versión explícita. Los secretos no pertenecen a ninguna tabla.

## Migraciones

- Migraciones incrementales y revisables.
- El esquema común debe evitar SQL específico del motor.
- Cuando sea inevitable SQL específico, usar migración separada por backend y prueba de contrato por motor.
- Todas las migraciones deben aplicar desde una base vacía y actualizar una base de la versión anterior.

## Backup, restore e intercambio

- Backup SQLite consistente antes de aplicar cambios relevantes.
- Exportación canónica JSON versionada para migrar entre motores.
- CSV solo para tablas o reportes aptos para usuario.
- Restore siempre valida versión, checksum y compatibilidad antes de sobrescribir una base local.

## MySQL: uso futuro y seguro

- La URL/credenciales van en Windows Credential Manager o keyring, nunca en código, logs ni Git.
- El usuario activa explícitamente el modo MySQL desde Settings.
- Las conexiones usan timeout, TLS cuando el servidor lo requiera y mínimo privilegio SQL.
- Ante indisponibilidad de MySQL no se cambia automáticamente a SQLite; se informa el estado y se evita divergencia.
- Si se implementa sincronización, debe tener modelo de conflictos y consentimiento explícito antes de escribir fuera del equipo.
