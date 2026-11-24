/* Slider Studio: local, aspect-preserving image processing. */
(function (global) {
  'use strict';

  const MODES = new Set(['blur', 'mirror', 'color', 'transparent', 'cover']);
  const MAX_DIMENSION = 6000;
  const MAX_PIXELS = 24000000;

  function bounded(value, fallback, minimum, maximum) {
    const number = Number(value);
    return Number.isFinite(number) ? Math.min(maximum, Math.max(minimum, number)) : fallback;
  }

  function normalizeOptions(options = {}) {
    const minimum = options.preview === true ? 1 : 64;
    const width = Math.round(Number(options.width == null ? 768 : options.width));
    const height = Math.round(Number(options.height == null ? 576 : options.height));
    if (!Number.isFinite(width) || !Number.isFinite(height) ||
        width < minimum || height < minimum || width > MAX_DIMENSION || height > MAX_DIMENSION) {
      throw new Error('El ancho y el alto deben estar entre ' + minimum + ' y 6000 píxeles.');
    }
    if (width * height > MAX_PIXELS) {
      throw new Error('El tamaño final no puede superar los 24 megapíxeles. Reduce el ancho o el alto.');
    }
    return {
      width,
      height,
      mode: MODES.has(options.mode) ? options.mode : 'blur',
      color: /^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(options.color || '') ? options.color : '#f0efed',
      blur: bounded(options.blur, 24, 0, 200),
      zoom: bounded(options.zoom, 1, 1, 3),
      positionX: bounded(options.positionX, 0.5, 0, 1),
      positionY: bounded(options.positionY, 0.5, 0, 1),
      preview: options.preview === true
    };
  }

  function getPlacement(sourceWidth, sourceHeight, options = {}) {
    const settings = normalizeOptions(options);
    if (!Number.isFinite(sourceWidth) || !Number.isFinite(sourceHeight) || sourceWidth <= 0 || sourceHeight <= 0) {
      throw new Error('La imagen no tiene un tamaño válido.');
    }
    const fit = settings.mode === 'cover' ? Math.max : Math.min;
    const scale = fit(settings.width / sourceWidth, settings.height / sourceHeight) * settings.zoom;
    const width = sourceWidth * scale;
    const height = sourceHeight * scale;
    return {
      x: (settings.width - width) * settings.positionX,
      y: (settings.height - height) * settings.positionY,
      width,
      height,
      scale,
      upscaled: scale > 1.001,
      cropped: width > settings.width + 0.01 || height > settings.height + 0.01
    };
  }

  function canvasOf(width, height) {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    return canvas;
  }

  function contextOf(canvas) {
    const context = canvas.getContext('2d');
    if (!context) throw new Error('Tu navegador no pudo crear el lienzo. Prueba con una imagen o un tamaño menor.');
    context.imageSmoothingEnabled = true;
    context.imageSmoothingQuality = 'high';
    return context;
  }

  function dimensionsOf(source) {
    return {
      width: source.naturalWidth || source.videoWidth || source.width,
      height: source.naturalHeight || source.videoHeight || source.height
    };
  }

  function drawMirror(context, source, placement, outputWidth, outputHeight) {
    // A reflected 2×2 tile repeats seamlessly in both directions. The tile
    // raster is bounded independently of the original image and output size.
    const factor = Math.min(1, 1024 / Math.max(placement.width, placement.height));
    const tileWidth = Math.max(1, Math.ceil(placement.width * factor));
    const tileHeight = Math.max(1, Math.ceil(placement.height * factor));
    const tile = canvasOf(tileWidth * 2, tileHeight * 2);
    const tileContext = contextOf(tile);
    for (let row = 0; row < 2; row += 1) {
      for (let column = 0; column < 2; column += 1) {
        tileContext.save();
        tileContext.translate(column ? tileWidth * 2 : 0, row ? tileHeight * 2 : 0);
        tileContext.scale(column ? -1 : 1, row ? -1 : 1);
        tileContext.drawImage(source, 0, 0, tileWidth, tileHeight);
        tileContext.restore();
      }
    }
    const pattern = context.createPattern(tile, 'repeat');
    if (!pattern) throw new Error('No se pudo crear el fondo de reflejo.');
    const scaleX = placement.width / tileWidth;
    const scaleY = placement.height / tileHeight;
    context.save();
    context.translate(placement.x, placement.y);
    context.scale(scaleX, scaleY);
    context.fillStyle = pattern;
    context.fillRect(-placement.x / scaleX, -placement.y / scaleY, outputWidth / scaleX, outputHeight / scaleY);
    context.restore();
    // The foreground is always sampled directly from the original source.
    tile.width = 1;
    tile.height = 1;
  }

  function render(input, options = {}) {
    const source = input && input.source ? input.source : input;
    if (!source) throw new Error('Primero selecciona una imagen.');
    const settings = normalizeOptions(options);
    // A downsampled preview may be rounded to a different pixel aspect ratio.
    // Its wrapper retains the original logical dimensions, so placement is the
    // same as export even for unusually thin source images.
    const dimensions = input.source && (input.width !== undefined || input.height !== undefined)
      ? { width: input.width, height: input.height }
      : dimensionsOf(source);
    const placement = getPlacement(dimensions.width, dimensions.height, settings);
    const canvas = canvasOf(settings.width, settings.height);
    const context = contextOf(canvas);

    if (settings.mode !== 'transparent') {
      context.fillStyle = settings.color;
      context.fillRect(0, 0, settings.width, settings.height);
    }
    if (settings.mode === 'blur') {
      // Extra bleed keeps the blur kernel from fading at the canvas edges.
      const bleed = Math.ceil(settings.blur * 3);
      const scale = Math.max((settings.width + bleed * 2) / dimensions.width,
        (settings.height + bleed * 2) / dimensions.height);
      const width = dimensions.width * scale;
      const height = dimensions.height * scale;
      context.save();
      context.filter = 'blur(' + settings.blur + 'px)';
      context.drawImage(source,
        (settings.width + bleed * 2 - width) * settings.positionX - bleed,
        (settings.height + bleed * 2 - height) * settings.positionY - bleed,
        width, height);
      context.restore();
      context.fillStyle = 'rgba(16, 20, 28, 0.10)';
      context.fillRect(0, 0, settings.width, settings.height);
    } else if (settings.mode === 'mirror') {
      drawMirror(context, source, placement, settings.width, settings.height);
    }
    context.drawImage(source, placement.x, placement.y, placement.width, placement.height);
    return canvas;
  }

  async function decodeFile(file) {
    if (!(file instanceof Blob)) throw new Error('El archivo seleccionado no es una imagen válida.');
    if (typeof createImageBitmap === 'function') {
      try {
        const source = await createImageBitmap(file, { imageOrientation: 'from-image' });
        if (!source.width || !source.height) {
          source.close();
          throw new Error('La imagen está vacía.');
        }
        let closed = false;
        return {
          source,
          width: source.width,
          height: source.height,
          close() { if (!closed) { source.close(); closed = true; } }
        };
      } catch (_) {
        // HTMLImageElement supports formats some bitmap implementations omit,
        // and also applies embedded EXIF orientation in modern browsers.
      }
    }
    const url = URL.createObjectURL(file);
    try {
      const source = await new Promise((resolve, reject) => {
        const image = new Image();
        image.decoding = 'async';
        image.onload = () => image.naturalWidth && image.naturalHeight ? resolve(image) : reject(new Error('Imagen vacía.'));
        image.onerror = () => reject(new Error('No se pudo abrir «' + (file.name || 'imagen') + '». Usa JPG, PNG, WebP, AVIF o GIF.'));
        image.src = url;
      });
      return {
        source,
        width: source.naturalWidth,
        height: source.naturalHeight,
        close() { source.src = ''; }
      };
    } finally {
      URL.revokeObjectURL(url);
    }
  }

  function encode(canvas, format = 'webp', quality = 0.9) {
    const formats = {
      jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png', webp: 'image/webp',
      'image/jpeg': 'image/jpeg', 'image/png': 'image/png', 'image/webp': 'image/webp'
    };
    const mime = formats[String(format).toLowerCase()];
    if (!mime) return Promise.reject(new Error('Elige un formato de salida: JPG, PNG o WebP.'));
    return new Promise((resolve, reject) => {
      try {
        canvas.toBlob(blob => {
          if (!blob) return reject(new Error('No se pudo exportar la imagen. Reduce su tamaño e inténtalo de nuevo.'));
          if (blob.type !== mime) return reject(new Error('Tu navegador no permite exportar en este formato. Prueba con PNG o JPG.'));
          resolve(blob);
        }, mime, bounded(quality, 0.9, 0.1, 1));
      } catch (_) {
        reject(new Error('No se pudo exportar la imagen. Vuelve a cargarla e inténtalo de nuevo.'));
      }
    });
  }

  global.SliderEngine = Object.freeze({ decodeFile, render, encode, getPlacement, normalizeOptions });
})(window);
