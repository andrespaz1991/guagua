# Horizonte · Finanzas personales en localhost

Herramienta de uso personal, en español, oscura y adaptable a computador y móvil. La interfaz, estilos, cálculos y Chart.js se incluyen en `index.html`. El servicio Python permite guardar PDFs en el disco y extraer su texto sin enviar información a internet. No necesita base de datos, claves de API ni conexión con bancos.

## Iniciar

1. Abre esta carpeta y ejecuta `iniciar.bat`.
2. Entra a **http://localhost:8765** y mantén abierta la ventana del servicio mientras lo usas.
3. Si necesitas instalar la dependencia: `python -m pip install -r requirements.txt`.

También puedes iniciarlo desde una terminal en esta carpeta:

```powershell
python server.py
```

Si el puerto está ocupado: `python server.py --port 8766` y abre `http://localhost:8766`. El servicio solo escucha en `127.0.0.1`. Para detenerlo, presiona Ctrl+C en su terminal. Evita publicar esta carpeta en internet.

## Primer uso

Hay doce meses de ejemplo, desde noviembre de 2026 hasta octubre de 2027, con importes COP. Los valores de ingresos, gastos, reservas, activos, deudas e inversiones se pueden reemplazar. Una franja identifica las secciones que aún contienen ejemplos. Guardar cada sección confirma sus valores para el mes. Cada mes tiene saldos y presupuestos independientes.

## PDFs: recorrido completo

1. Elige el mes y entra a **Documentos y análisis**.
2. Selecciona uno o varios PDFs y pulsa **Subir documentos**. Se guardan en `documentos/AAAA-MM/`. Los nombres físicos usan el hash SHA-256; la lista conserva el nombre original. Una subida repetida en el mismo mes no crea otro documento.
3. Pulsa **Analizar**. El análisis propone movimientos y saldos sin cambiar tus finanzas.
4. Abre el original y revisa las filas. Corrige fechas, importes, tipos y categorías, o añade filas manuales. Excluye lo que no corresponda. Las transferencias requieren decidir su dirección; un aporte de inversión se clasifica aparte de los gastos.
5. Elige la cuenta y verifica sus saldos inicial y final. Crea otras cuentas en **Conciliación** cuando sea necesario.
6. Elige reemplazar los totales con **todos** los movimientos verificados del mes, o sumar solo los nuevos. El reemplazo elimina los valores manuales de ingresos, gastos y aportes; la suma los conserva. Revisa la vista previa del resultado.
7. Marca la confirmación de revisión y pulsa **Aplicar datos verificados**. Cambiar los datos de una fila vuelve a exigir la confirmación. Se bloquean movimientos pendientes, fechas fuera del mes y documentos ya aplicados.
8. En **Conciliación**, comprueba la diferencia y confirma el saldo. Esto actualiza el activo bancario en el balance. Las nuevas importaciones invalidan la conciliación anterior.

Al sustituir un mes de ejemplo con su primer extracto se eliminan las entradas y salidas manuales de ejemplo de la cuenta seleccionada. Las cuentas reales pueden conservar ajustes manuales: revísalos para evitar contar un movimiento dos veces. No mezcles extractos de cuenta y comprobantes de las mismas operaciones sin excluir sus duplicados.

### Alcance del extractor

Admite PDFs con texto seleccionable y movimientos con fecha, descripción e importe en una misma línea. Acepta fechas `DD/MM/AAAA`, `DD/MM`, `AAAA-MM-DD`, números colombianos e internacionales y saldos inicial/final. La clasificación se basa en palabras habituales, no en un modelo de IA. Las líneas con varias columnas numéricas se marcan para revisión del importe. No garantiza compatibilidad automática con todos los bancos.

**No realiza OCR:** un PDF escaneado, ilegible o con formato no reconocido muestra un aviso y permite transcribir los movimientos en la revisión. No inventa datos. Los PDFs con contraseña deben reemplazarse por una copia desbloqueada. Límite: 10 MB y 100 páginas por documento.

## Conciliación, balance y cierre

- Conciliación por cuenta: saldo inicial + entradas manuales e importadas − salidas manuales e importadas = saldo esperado. Diferencia = saldo del extracto − saldo esperado. La confirmación exige diferencia inferior a un centavo.
- Las transferencias son caja, pero no ingreso ni gasto. Registra cada lado en su cuenta si transfieres entre tus propias cuentas.
- El balance suma activos y resta saldos de pasivos. La conciliación actualiza los activos bancarios; el resto de los valores y los saldos de deuda se revisan manualmente. Importar una cuota no permite inferir cuánto fue capital y cuánto interés, por eso no reduce automáticamente el pasivo.
- El fondo de emergencia es un subconjunto de los activos líquidos y no se vuelve a sumar al patrimonio.
- El cierre exige cuentas conciliadas, documentos aplicados, ejemplos confirmados y cuatro verificaciones. Genera una evaluación con indicadores, flujo de caja, patrimonio y recomendaciones. Protege el mes y permite reabrirlo, invalidando la evaluación anterior.
- Desde el cierre puedes preparar el siguiente mes copiando el presupuesto y el balance, con confirmación antes de reemplazar sus valores. Revisa los saldos proyectados y registra los movimientos reales del nuevo mes. Los gráficos futuros son escenarios editables, no pronósticos automáticos.

## Cálculos

- P&G en base de caja: ingresos menos gastos, incluyendo cuotas completas de deuda. Es una vista personal de flujo, no un estado contable por devengo. Efectivo libre = utilidad − aportes de inversión. Ahorro acumulado = suma de utilidades del período desde cero; no equivale al saldo bancario.
- Deuda / ingreso: verde <15 %, rojo >30 %, amarillo entre ambos. Ahorro / ingreso: verde >15 %, rojo <5 %. Reserva: verde desde 3 meses, amarillo desde 1 y rojo por debajo. Ingreso pasivo / gasto: cobertura verde desde 100 %, amarilla desde 25 %, roja por debajo. Sin denominador se muestra «Sin base».
- Deudas: conversión de tasa EA a mensual `(1 + EA)^(1/12) - 1`, interés sobre saldo al inicio del mes y pago al final. El presupuesto fijo combina cuotas mínimas iniciales y extra; las cuotas liberadas se reasignan. Solo se incluyen deudas marcadas «Consumo». Se compara contra el mismo plan sin extra, sin nuevas compras ni cargos. Se detectan pagos que no amortizan y se limita la simulación a 1.200 meses; la gráfica muestra hasta 240.
- Créditos: cuota fija por anualidad, con tabla de interés, abono a capital y saldo. Tasa cero usa capital / plazo.
- Inversiones: capital inicial más aportes al final de cada mes, con capitalización mensual derivada de la tasa EA. Se muestran 5, 10 y 20 años y se admiten escenarios de tasas negativas. Capital inicial y aportes deben reflejarse en los activos según tus saldos reales; no se suman automáticamente para evitar duplicar inversiones.

## Respaldos y privacidad

Los datos financieros viven **en memoria durante la sesión**. Recargar o cerrar puede perderlos. **Exporta JSON** antes de salir e impórtalo al volver. La importación valida su estructura y pide confirmar el reemplazo; un archivo inválido conserva la sesión.

Los PDFs y sus metadatos se conservan en `documentos/`, independientemente del JSON. El JSON incluye las referencias, no el contenido de los PDFs. Para llevar todo a otro equipo, copia esa carpeta y tu respaldo JSON. Los documentos no contienen datos de ejemplo del banco: los archivos que se usan en pruebas son sintéticos.

Chart.js 4.5.1, licencia MIT, se distribuye integrado para funcionar sin CDN. PyMuPDF se instala localmente. Las recomendaciones son reglas explicables a partir de los indicadores; no consultan productos ni tasas de mercado.

## Pruebas reproducibles

```powershell
python tests/test_parser.py
```

Genera PDFs sintéticos en `tests/fixtures/` y prueba importes, extracción, saldos, fechas de otro mes, filas ambiguas, ausencia de texto e identificadores estables.

`tests/journey.js` contiene el recorrido de navegador para ejecutar mediante `playwright-cli run-code` con el servicio iniciado. Verifica subida, análisis, corrección, aplicación, duplicados, segundo extracto, conciliación, cierre, presupuesto siguiente, exportación/importación, fórmulas y pantalla móvil. Las capturas y el respaldo sintético quedan en `output/playwright/horizonte/` del proyecto superior. Ejecuta el recorrido con una carpeta `documentos` de pruebas vacía, o aparta tus documentos personales antes de usarlo.
