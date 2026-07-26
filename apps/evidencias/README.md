# Evidencias Docente

Módulo de evaluación anual de desempeño laboral para docentes regidos por el Decreto Ley 1278 de 2002. Está pensado para ejecutarse dentro de Guagua/XAMPP y combina una interfaz React con Tailwind (cargados en el navegador) y una API PHP/MySQL.

## Instalación

1. Abra `http://localhost/guagua/apps/evidencias/setup/install.php`.
2. Escriba `INSTALAR EVIDENCIAS` y confirme.
3. Abra `http://localhost/guagua/apps/evidencias/`.

El instalador crea exclusivamente tablas cuyo nombre inicia con `evidencias_` y precarga la evaluación 2026 de **Hugo Andrés Paz Burbano**, C.C. 1085290375. Puede ejecutarse nuevamente sin duplicar la plantilla.

## Desinstalación

1. Haga copia de los soportes que desea conservar desde `uploads/`.
2. Abra `http://localhost/guagua/apps/evidencias/setup/uninstall.php`.
3. Escriba `ELIMINAR EVIDENCIAS 2026` y confirme.

El desinstalador elimina solo las tablas `evidencias_*`; conserva los archivos físicos de `uploads/` para evitar que una desinstalación borre evidencias accidentalmente.

## Estructura

- `database/schema.sql`: tablas, relaciones y restricciones del módulo.
- `database/uninstall.sql`: eliminación ordenada de las tablas.
- `lib/EvaluationSeeder.php`: plantilla inicial de 2026 extraída del Anexo 5.
- `lib/EvaluationService.php`: cálculo de ponderaciones, promedio por días y bloqueo del historial notificado.
- `api.php`: operaciones de tablero, valoraciones, años lectivos y adjuntos.
- `report.php`: protocolo imprimible; el navegador permite guardarlo como PDF.

Las ponderaciones funcionales se validan en 70% (30% académica, 20% administrativa y 20% comunitaria en la plantilla). Las tres competencias comportamentales elegidas representan el 30% restante. Al pasar una evaluación a `Notificado`, el año se cierra y no acepta más cambios.
