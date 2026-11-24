# Verificación de Horizonte

Fecha: 6 de noviembre de 2026, hora de Bogotá.

Resultado: **50 comprobaciones aprobadas**: 5 pruebas del extractor y 45 verificaciones en un navegador Chromium real, contra el servicio de localhost.

## Recorrido principal: 38 verificaciones

Se probó subir un PDF sintético, pulsar Analizar, revisar sus seis movimientos y saldos, cambiar una cifra, clasificar una transferencia y un aporte a inversión, confirmar y aplicar. Se verificó que no se modificaran las finanzas antes de la confirmación y que editar una fila invalidara la confirmación previa.

Se volvió a subir el mismo documento para comprobar que no se duplicara. Un segundo extracto añadió dos movimientos nuevos, preservando los anteriores. La importación invalidó la conciliación existente. Un saldo con diferencia fue rechazado; el saldo correcto actualizó el activo bancario.

Con esos datos se confirmaron las secciones de ejemplo y se cerró el mes. El cierre bloqueó los campos editables, generó su evaluación y permitió preparar el siguiente presupuesto. Exportar y restaurar el JSON preservó todos los datos financieros y el assessment. Un JSON inválido conservó la sesión actual. Reabrir el mes invalidó su evaluación.

También se verificaron Chart.js, los doce meses, tasas cero y negativas, deuda no amortizable, reducción de plazo e intereses con un pago extra, simulación de crédito y ausencia de desbordamiento en pantalla móvil. No hubo excepciones JavaScript durante el recorrido principal. Las respuestas HTTP 400/404 observadas corresponden a los casos de rechazo intencionales.

Resultados contables del caso de prueba:

| Concepto | Resultado COP |
| --- | ---: |
| Saldo inicial de cuenta | 1.000.000 |
| Ingresos verificados | 6.000.000 |
| Gastos verificados | 2.550.000 |
| Aporte de inversión | 400.000 |
| Transferencia saliente | 200.000 |
| Saldo final conciliado | 3.850.000 |
| Utilidad mensual | 3.450.000 |
| Efectivo libre antes de transferencias | 3.050.000 |
| Activos, incluido el saldo conciliado | 80.850.000 |
| Pasivos del ejemplo confirmado | 13.300.000 |
| Patrimonio | 67.550.000 |

## Casos adicionales de navegador: 7 verificaciones

Se subió un PDF sin texto; no produjo filas ficticias y mostró el aviso de ausencia de OCR. Se añadió un movimiento manual, se aplicó y se concilió su cuenta. Se comprobaron rutas de acceso inválidas, rechazo de código del servidor y de archivos fuera de los endpoints autorizados, un crédito liquidado en un solo mes y validación de respaldos con importes o estructuras inválidas.

## Extractor: 5 pruebas

`python tests/test_parser.py` comprobó formatos numéricos colombianos e internacionales, extracción de movimientos y saldos, clasificación, fechas fuera del mes, varias columnas numéricas que necesitan revisión, PDFs sin texto e identificadores estables al repetir el análisis.

## Evidencia y límites

Los scripts están en `tests/test_parser.py`, `tests/journey.js` y `tests/edge-journey.js`. Las capturas y un respaldo sintético se guardan en `../../output/playwright/horizonte/`, relativo a esta carpeta. Se verificó la sintaxis de Python y JavaScript, y que el HTML no cargara scripts externos.

Los PDFs utilizados son sintéticos. No se proporcionaron extractos personales para comprobar formatos bancarios específicos. El extractor no realiza OCR y no garantiza reconocer todos los diseños. En esos casos muestra advertencias y conserva el recorrido de revisión y transcripción manual. Los documentos subidos por las pruebas se retiraron del archivo de uso normal al finalizar.
