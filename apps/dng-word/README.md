# DNG a Word

Módulo de Guagua para revelar fotografías DNG/Apple ProRAW individualmente o por lotes y exportarlas a formatos que admite Microsoft Word.

## Puesta en marcha

1. Instala [RawTherapee](https://www.rawtherapee.com/downloads/) en el equipo donde corre Apache/PHP. Se usa el ejecutable `rawtherapee-cli.exe` (Linux: `rawtherapee-cli`). El módulo busca el ejecutable en `D:\xampp\rawtherapee`, PATH y en las carpetas habituales de `Program Files/RawTherapee*`. Para Apache se configuran `RT_SETTINGS` y `RT_CACHE` en el directorio temporal de cada lote. Para una instalación personalizada, configura la variable de entorno `GUAGUA_RAWTHERAPEE_CLI` con la ruta absoluta del ejecutable y reinicia Apache.
2. Abre `/guagua/apps/dng-word/` con una sesión iniciada en Guagua. En el gestor del menú común, la opción «Sincronizar apps» permite añadir esta carpeta al menú.
3. Selecciona uno o varios .dng, el formato y la dimensión. Si eres personal autorizado, escribe una ruta absoluta local del equipo donde corre XAMPP en «Carpeta de salida» y pulsa «Comprobar ruta». Debe ser una carpeta existente y escribible, fuera de la raíz pública de Apache. El navegador recuerda la ruta validada para la próxima visita.
4. Al convertir, cada imagen se copia a esa carpeta con un nombre único, sin sobrescribir archivos existentes. También se puede descargar por separado; si hay varias conversiones correctas, aparece el ZIP del lote. Si dejas la ruta vacía, solo se ofrecen las descargas.

La interfaz muestra un aviso y bloquea el botón de conversión cuando falta RawTherapee. Si RawTherapee no puede abrir un DNG y este contiene un JPEG integrado de al menos 2000 px por lado, el módulo utiliza esa imagen de alta resolución y lo indica en el resultado. Esta salida procede del JPEG integrado, no del revelado de los datos RAW. Si no hay un JPEG adecuado, se muestra el error sin generar una imagen de baja calidad.

## Opciones

- **JPEG 90 o 95, 4:4:4:** fotografía con buena nitidez y tamaño contenido. Recomendado para Word.
- **PNG de 8 bits:** compresión sin pérdida; puede ocupar bastante más.
- **2800, 3600 px o resolución original:** ajuste del lado mayor con Lanczos, sin recorte ni ampliación.
- **sRGB:** perfil de salida para colores coherentes en documentos y pantallas.

RawTherapee aplica su perfil RAW predeterminado y luego el perfil parcial de tamaño/color del módulo. Si se requiere un revelado con estilo institucional, se puede configurar el perfil predeterminado de RawTherapee para la cuenta que ejecuta Apache.

## Límites y privacidad

Hasta 20 archivos por lote, 200 MiB por archivo y 1 GiB por lote. La carga usa fragmentos de 6 MiB para funcionar con `upload_max_filesize=40M`. Aun así, el servidor necesita espacio temporal suficiente, memoria para revelar ProRAW y tiempo de ejecución. Las cargas y salidas se guardan en el directorio temporal del sistema, fuera de la raíz web, ligadas a la sesión de Guagua. Dejan de estar disponibles a las 2 horas y se eliminan cuando se vuelve a acceder al módulo con esa sesión. No se altera el DNG original del usuario.

## Referencias

- [Opciones de línea de comandos de RawTherapee](https://rawpedia.rawtherapee.com/Command-Line_Options)
- [Formatos gráficos admitidos por Word](https://support.microsoft.com/en-us/word/graphic-file-types-you-can-insert-and-save)

