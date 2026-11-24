# Slider Studio

Aplicación para preparar imágenes de igual tamaño para un slider de WordPress, sin estirar sus proporciones. No necesita PHP, base de datos, librerías externas ni conexión a Internet.

El tamaño predeterminado **Vallesol** es **768 × 576 px (4:3)**, correspondiente a las fotos que utiliza el carrusel de [la sede Vallesol](https://ierlajosefinasanluis.edu.co/sedes/sedes_vallesol/), comprobado el 24 de octubre de 2026. Se verificaron ambas imágenes del slider (`Vallesol_banner-768x576.jpeg` y `Entrada_vallesol-768x576.jpeg`). Los demás tamaños siguen disponibles.

## Abrir

Con Apache de XAMPP iniciado: `http://localhost/guagua/apps/slider-studio/`.

También se puede abrir `index.html` directamente en un navegador moderno. Todos los recursos son locales. La carpeta puede copiarse completa a otro servidor estático.

## Uso

1. Arrastra imágenes o pulsa la zona de carga. Se admiten JPG, PNG, WebP, AVIF y GIF que el navegador pueda decodificar.
2. Elige un tamaño común, predefinido o personalizado. El ancho y alto admiten enteros entre 64 y 6000 px, hasta 24 megapíxeles de salida.
3. Selecciona una miniatura y ajusta su modo de fondo, zoom y posición. También puedes mover la imagen arrastrando la vista previa.
4. Usa **Aplicar este ajuste a todas** para copiar fondo, desenfoque, color, zoom y posición al lote. El tamaño y formato de descarga siempre son comunes. Las nuevas imágenes heredan el último ajuste utilizado.
5. Elige JPG, PNG o WebP y descarga una imagen o el lote completo en ZIP. Los nombres incluyen orden, nombre original y dimensiones. Usa las flechas de las miniaturas para cambiar el orden.
6. Descomprime el ZIP y sube sus imágenes a la biblioteca multimedia de WordPress. Configura el slider con la misma proporción para evitar un recorte adicional de su parte.

## Modos

- **Desenfoque:** imagen completa sobre una versión ampliada y desenfocada de sí misma.
- **Reflejo:** completa los bordes con reflejos continuos de la propia imagen.
- **Color sólido:** imagen completa con fondo de color configurable.
- **Transparente:** imagen completa sobre fondo transparente; cambia automáticamente a PNG si se estaba usando JPG.
- **Recortar para llenar:** llena el destino conservando proporciones; puede recortar bordes.

Los cuatro modos de complemento conservan la imagen completa con zoom al 100%. Un zoom mayor también puede recortar. La aplicación avisa cuando el encuadre recorta o cuando la resolución original obliga a ampliar. No realiza expansión generativa de imágenes.

## Límites y privacidad

- Hasta 50 archivos, 30 MB por archivo y 80 megapíxeles por original.
- Hasta 700 MB de imágenes resultantes por descarga para limitar el uso de memoria.
- GIF/AVIF animados se convierten en una imagen estática. El soporte AVIF y WebP depende del navegador. PNG es una alternativa universal de exportación.
- No se suben archivos, ni se usan servicios, cookies o almacenamiento persistente. Al recargar se limpia la sesión.
- Se decodifica cada original de forma secuencial al importar y exportar; la vista previa mantiene una sola copia reducida, y las descargas se generan con la resolución original.

## Comprobaciones

```powershell
node --check apps/slider-studio/app.js
node --check apps/slider-studio/engine.js
node --test apps/slider-studio/tests/engine.test.cjs apps/slider-studio/tests/zip.test.cjs
```

La aplicación es independiente del menú y de la base de datos de Guagua. El gestor de menú existente puede descubrir la carpeta `slider-studio` mediante su opción de sincronizar aplicaciones.
