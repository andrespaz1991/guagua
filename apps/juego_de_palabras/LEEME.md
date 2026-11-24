# Juego de palabras

Abre `iniciar.cmd` para iniciar la aplicación. Normalmente se abrirá `http://127.0.0.1:8766` en tu navegador. Deja la ventana de comandos abierta mientras preparas los archivos; ciérrala para detener la aplicación. Si el juego ya está abierto, el iniciador abrirá esa misma página. Si otra aplicación usa el puerto 8766, buscará uno libre y mostrará la dirección en la ventana de comandos.

## Cómo usarlo

1. Escribe palabras separadas por líneas, comas o punto y coma, o carga un CSV. Puedes combinar ambos.
2. Para asignar un tiempo distinto en la lista manual, escribe `Viaje a la luna | 12`.
3. En CSV, usa la columna `palabra` (o `frase`) y, si quieres, `segundos`.
4. Elige un color sólido o usa el botón visible **Subir imagen de plantilla** para seleccionar un JPG, PNG o WebP. Al subirla se activa automáticamente como fondo. Puedes cubrir la pantalla o mostrarla completa y ajustar cuánto se oscurece para leer mejor la palabra.
5. Ajusta colores, tamaño máximo de letra y opciones de presentación.
6. Descarga el PowerPoint o PDF.

El PowerPoint contiene una diapositiva por elemento y avance automático según el tiempo configurado. Algunas aplicaciones de presentación solo respetan el tiempo al iniciar el modo de presentación. El PDF tiene una página por elemento y no guarda tiempos automáticos.

El generador está preparado para el entorno local de Codex en Windows de este equipo. Usa el runtime de Python, ReportLab y el motor de presentaciones incluido con Codex. No requiere conexión a Internet para generar los archivos.
