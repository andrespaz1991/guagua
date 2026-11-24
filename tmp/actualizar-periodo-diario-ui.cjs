const fs = require('fs');
const file = 'D:/xampp/htdocs/wsp/index.html';
const before = fs.readFileSync(file, 'utf8');
const after = before
    .replace('La portada usa el mes y año actuales.', 'La portada y el nombre del archivo usan el mes y año de las notas del diario.')
    .replace('Word descargado con la portada del mes actual y el texto revisado desde la segunda página.', 'Word descargado con el periodo de las notas del diario en la portada y en el nombre del archivo.');
if (after === before) throw new Error('No se encontraron los textos esperados.');
const backup = 'tmp/whatsapp-diario/index-antes-periodo-notas.html';
if (!fs.existsSync(backup)) fs.writeFileSync(backup, before);
fs.writeFileSync(file, after);
console.log('Textos de la pantalla actualizados.');
