const fs = require('fs');
const target = 'D:/xampp/htdocs/wsp/server.js';
const source = fs.readFileSync(target, 'utf8');
const start = source.indexOf('async function readExtractionHistory(chat, startTs, endTs) {');
const end = source.indexOf('async function safeDownloadMedia(', start);
if (start < 0 || end < 0) throw new Error('No se encontró el bloque esperado; no se modificó el servidor.');
const replacement = `async function readExtractionHistory(chat, startTs, endTs) {
    const { readExtractionHistory: readHistory } = require('../guagua/apps/diario/extraction-history.cjs');
    const Message = require('whatsapp-web.js/src/structures/Message');
    return readHistory({ client, chat, startTs, endTs, cap: MAX_MESSAGES_PER_CHAT, Message });
}

`;
const backup = 'D:/xampp/htdocs/guagua/tmp/whatsapp-diario/server-antes-historial-octubre.js';
if (!fs.existsSync(backup)) fs.writeFileSync(backup, source);
fs.writeFileSync(target, source.slice(0, start) + replacement + source.slice(end));
console.log('Servidor integrado con el historial corregido; copia anterior guardada.');
