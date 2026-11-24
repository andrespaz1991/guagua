# Asistente de inicio de año escolar

Acceso: **CONFIG → Asistente de inicio de año**, o `Planeador/inicio_anual.php`.

El asistente permite empezar desde cero o copiar la estructura de otro año. Los ocho pasos cubren institución y año, períodos, grados y asignaturas, asignaciones docentes, horarios, inscripciones, archivos y opciones, y revisión final.

## Borradores y permisos

- Los borradores se guardan por usuario, institución y año en `inicio_anual_borradores`, con control de revisión para evitar sobrescrituras entre pestañas.
- Guardar un borrador no modifica registros académicos ni el año activo.
- Docentes pueden preparar e instalar sus asignaciones. Administradores y directivos pueden gestionar el conjunto y activar el año general.
- La institución siempre procede de la sesión autenticada. La activación del año y período es global porque las tablas académicas existentes son compartidas por la plataforma.

## Instalación

La confirmación exige revisar los datos y escribir el año. Puede instalarse sin activar; el año queda preparado y se puede retomar para activarlo más adelante.

La instalación reutiliza coincidencias existentes y agrega las faltantes. Conserva inscripciones históricas, observaciones del perfil del estudiante y datos académicos de otros años. No elimina automáticamente cursos previamente instalados cuando se quitan de un borrador: los retiros se gestionan en los módulos académicos correspondientes.

Antes de instalar, las tablas académicas de instalaciones antiguas que usan MyISAM se convierten a InnoDB, conservando sus registros. Esto ocurre únicamente al confirmar un borrador válido, para permitir revertir la instalación si falla. El año, períodos, catálogos, cursos, horarios e inscripciones se guardan en una transacción. Las rutas se aplican al activar y se restauran si falla la confirmación. Los cambios concurrentes en archivos de configuración exigen volver a leer las rutas.

Los números de período se mantienen separados de sus identificadores de base de datos. Se asignan IDs explícitos porque `periodo.id_periodo` no tiene autoincremento en el esquema histórico.

Las cuentas de estudiantes y docentes se administran en Usuarios. Los grupos de reportes se definen en Configuración de reportes IA; el asistente permite revisar sus rutas. La malla curricular y los planes permanecen disponibles desde el resumen final.

## Validación

`php tests/inicio_anual_test.php` prueba el ciclo de instalación y activación con tablas temporales basadas en el esquema real; no modifica registros reales. Incluye conflictos de fechas y horarios, permisos, concurrencia, reintentos, reversión, años preparados, preservación de históricos y archivos.

`node tests/inicio_anual_preview.cjs` ofrece una vista aislada con datos ficticios para comprobar la interfaz; no permite instalar años reales.
