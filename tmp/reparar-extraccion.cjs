const fs = require('fs');
let source = fs.readFileSync('D:/xampp/htdocs/wsp/server.js', 'utf8');
const helper = `
function hasExtractableMedia(message) {
    return Boolean(message && (message.hasMedia || message._data?.mediaData ||
        message._data?.mediaKey || ['ptt', 'audio', 'image', 'video', 'document', 'sticker'].includes(message.type)));
}

async function readExtractionHistory(chat, startTs, endTs) {
    const Message = require('whatsapp-web.js/src/structures/Message');
    const result = await client.pupPage.evaluate(async (chatId, since, until, cap) => {
        const collections = window.require('WAWebCollections');
        const model = collections.Chat.get(chatId) || await window.WWebJS.getChat(chatId, { getAsModel: false });
        if (!model?.msgs) throw new Error('El historial del chat no está disponible.');
        const deadline = Date.now() + 120000;
        let messages = model.msgs.getModelsArray().filter(m => !m.isNotification);
        let warning = null;
        while (messages.length < cap && (!messages.length || Math.min(...messages.map(m => m.t)) > since)) {
            if (Date.now() >= deadline) { warning = 'La carga del historial alcanzó el tiempo máximo; el resultado es parcial.'; break; }
            let timer;
            try {
                const older = await Promise.race([
                    window.require('WAWebChatLoadMessages').loadEarlierMsgs({ chat: model }),
                    new Promise((_, reject) => { timer = setTimeout(() => reject(new Error('WhatsApp tardó demasiado en cargar mensajes anteriores.')), Math.min(30000, deadline - Date.now())); })
                ]);
                if (!older?.length) break;
                const before = messages.length;
                const unique = new Map();
                for (const m of [...older, ...model.msgs.getModelsArray(), ...messages]) {
                    if (!m.isNotification) unique.set(m.id.toString(), m);
                }
                messages = [...unique.values()];
                if (messages.length === before) break;
            } catch (error) { warning = error.message || String(error); break; }
            finally { clearTimeout(timer); }
        }
        messages.sort((a,b) => b.t - a.t);
        const truncated = messages.length >= cap && messages[cap - 1]?.t > since;
        if (truncated) warning = 'El intervalo supera el límite de mensajes; extraiga por intervalos más cortos.';
        return { warning, messages: messages.slice(0, cap).filter(m => m.t >= since && m.t <= until).map(m => {
            const data = window.WWebJS.getMessageModel(m);
            data.id = { ...data.id, _serialized: m.id.toString() };
            data.directPath = data.directPath || m.directPath || m.mediaData?.directPath;
            data.mimetype = data.mimetype || m.mimetype || m.mediaData?.mimetype;
            return data;
        }) };
    }, chat.id._serialized, startTs, endTs, MAX_MESSAGES_PER_CHAT);
    return { messages: result.messages.map(data => new Message(client, data)), warning: result.warning };
}

`;
source = source.replace('async function safeDownloadMedia(', helper + 'async function safeDownloadMedia(');
source = source.replace('if (!message || !message.hasMedia)', 'if (!hasExtractableMedia(message))');
source = source.replace('                        type: msg.type || msg.mediaData?.type,', `                        type: msg.type || msg.mediaData?.type,
                        mimetype: msg.mimetype || msg.mediaData?.mimetype || (msg.type === 'ptt' ? 'audio/ogg' : 'application/octet-stream'),`);
source = source.replace("if (message.hasMedia) {", "if (hasExtractableMedia(message)) {");
const begin = source.indexOf('                    const messages = await Promise.race([', source.indexOf("socket.on('extract_content'"));
const end = source.indexOf('                    plan.push({ chat, messages: filtered });', begin);
if (begin < 0 || end < 0) throw new Error('No se encontró el bloque de lectura');
source = source.slice(0, begin) + `                    const history = await readExtractionHistory(chat, startTs, endTs);
                    const filtered = history.messages;
                    if (history.warning) readErrors.push((chat.name || chat.id._serialized) + ': ' + history.warning);
` + source.slice(end);
source = source.replace('            const mediaCandidates = [];', `            if (!plan.length) throw new Error('No se pudo leer el historial de los chats seleccionados. ' + readErrors.join(' | '));
            const mediaCandidates = [];`);
source = source.replace('                failedFiles,\n', '                failedFiles,\n                partial: readErrors.length > 0 || failedFiles > 0,\n');
// Windows stores CRLF, normalize first for insertions that match whole lines.
if (!source.includes('partial: readErrors')) source = source.replace('                failedFiles,\r\n', '                failedFiles,\r\n                partial: readErrors.length > 0 || failedFiles > 0,\r\n');
fs.writeFileSync('D:/xampp/htdocs/guagua/tmp/diario-server-reparado.js', source);
let html = fs.readFileSync('D:/xampp/htdocs/wsp/index.html', 'utf8');
html = html.replace('const failureDetail = r.failedFiles', 'const failureDetail = (r.failedFiles || r.errors?.length)');
html = html.replace('box.innerText = `Listo en', "if (r.partial) box.className = 'p-3 rounded-xl text-[10px] leading-relaxed bg-amber-50 border border-amber-200 text-amber-900';\n            box.innerText = `${r.partial ? 'Extracción parcial' : 'Listo'} en");
fs.writeFileSync('D:/xampp/htdocs/guagua/tmp/diario-index-reparado.html', html);
