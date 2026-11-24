import fs from 'node:fs/promises';
import { pathToFileURL } from 'node:url';

const [inputPath, outputPath] = process.argv.slice(2);
if (!inputPath || !outputPath) throw new Error('Faltan las rutas de entrada y salida.');
const { items, config, artifact, background_image_path } = JSON.parse(await fs.readFile(inputPath, 'utf8'));
const { Presentation, PresentationFile } = await import(pathToFileURL(artifact).href);
const backgroundBytes = background_image_path ? new Uint8Array(await fs.readFile(background_image_path)) : null;

const width = config.ratio === '4:3' ? 960 : 1280;
const height = 720;
const deck = Presentation.create({ slideSize: { width, height } });
const footerY = height - 62;
const total = items.length;

items.forEach((item, index) => {
  const slide = deck.slides.add();
  slide.background.fill = config.background;
  if (backgroundBytes) {
    slide.images.add({
      blob: backgroundBytes, contentType: 'image/jpeg', alt: 'Imagen de fondo',
      fit: 'cover', position: { left: 0, top: 0, width, height },
    });
    if (config.background_overlay > 0) {
      slide.shapes.add({
        geometry: 'rect', position: { left: 0, top: 0, width, height },
        fill: `#000000/${Math.round(config.background_overlay)}`,
        line: { fill: 'none', width: 0 },
      });
    }
  }
  const text = config.uppercase ? item.text.toLocaleUpperCase('es') : item.text;
  const central = slide.shapes.add({
    geometry: 'textbox',
    position: { left: width * 0.075, top: height * 0.12, width: width * 0.85, height: height * 0.72 },
    fill: 'none', line: { fill: 'none', width: 0 },
  });
  central.text = text;
  central.text.style = {
    typeface: 'Arial', fontSize: config.font_size * 4 / 3,
    bold: config.bold, color: config.foreground,
    alignment: 'center', verticalAlignment: 'middle',
    wrap: 'square', autoFit: 'shrinkText',
    insets: { left: 0, right: 0, top: 0, bottom: 0 },
  };

  if (config.show_number) {
    const number = slide.shapes.add({
      geometry: 'textbox',
      position: { left: 48, top: footerY, width: 170, height: 34 },
      fill: 'none', line: { fill: 'none', width: 0 },
    });
    number.text = `${String(index + 1).padStart(2, '0')} / ${String(total).padStart(2, '0')}`;
    number.text.style = { typeface: 'Arial', fontSize: 18, color: config.accent, verticalAlignment: 'middle' };
  }
  if (config.show_time) {
    const time = slide.shapes.add({
      geometry: 'textbox',
      position: { left: width - 190, top: footerY, width: 142, height: 34 },
      fill: 'none', line: { fill: 'none', width: 0 },
    });
    time.text = `${item.seconds} s`;
    time.text.style = { typeface: 'Arial', fontSize: 18, color: config.accent, alignment: 'right', verticalAlignment: 'middle' };
  }
});

await (await PresentationFile.exportPptx(deck)).save(outputPath);
