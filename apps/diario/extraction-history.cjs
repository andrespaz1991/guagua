// Executed inside the authenticated WhatsApp page by Puppeteer.
async function loadHistoryInPage(chatId, since, until, cap, timeoutMs = 120000, retryDelayMs = 500) {
    const collections = window.require('WAWebCollections');
    const chat = collections.Chat.get(chatId) || await window.WWebJS.getChat(chatId, { getAsModel: false });
    if (!chat?.msgs) throw new Error('El historial del chat no está disponible.');
    const loader = window.require('WAWebChatLoadMessages');
    const deadline = Date.now() + timeoutMs;
    const messages = new Map();
    let warning = null;
    const collect = (loaded = []) => {
        for (const message of [...(Array.isArray(loaded) ? loaded : []), ...chat.msgs.getModelsArray()]) {
            if (!message.isNotification && Number.isFinite(message.t)) messages.set(message.id.toString(), message);
        }
    };
    const load = async method => {
        let timer;
        const remaining = deadline - Date.now();
        if (remaining <= 0) throw new Error('La carga del historial alcanzó el tiempo máximo; el resultado es parcial.');
        try {
            const loaded = await Promise.race([
                loader[method]({ chat }),
                new Promise((_, reject) => { timer = setTimeout(() => reject(new Error('WhatsApp tardó demasiado en cargar el historial; el resultado es parcial.')), Math.min(30000, remaining)); })
            ]);
            // Some WhatsApp versions update the collection but return undefined or [].
            collect(loaded);
        } finally { clearTimeout(timer); }
    };
    collect();
    try {
        // An empty/lazy chat must be hydrated from its newest messages first.
        await load('loadRecentMsgs');
        let stalled = 0;
        while (messages.size < cap && ![...messages.values()].some(m => m.t <= since)) {
            if (chat.msgs.msgLoadState?.noEarlierMsgs === true) break;
            const before = messages.size;
            await load('loadEarlierMsgs');
            if (messages.size > before) { stalled = 0; continue; }
            if (chat.msgs.msgLoadState?.noEarlierMsgs === true) break;
            if (++stalled >= 3) {
                warning = 'WhatsApp no entregó más mensajes, pero no confirmó el inicio del historial. La extracción puede estar incompleta; reintente con el teléfono conectado.';
                break;
            }
            await new Promise(resolve => setTimeout(resolve, retryDelayMs));
        }
    } catch (error) {
        collect();
        warning = error.message || String(error);
    }
    const sorted = [...messages.values()].sort((a, b) => b.t - a.t);
    if (sorted.length >= cap && sorted[cap - 1]?.t > since) {
        warning = 'El intervalo supera el límite de mensajes; extraiga por intervalos más cortos.';
    }
    return { warning, messages: sorted.slice(0, cap).filter(m => m.t >= since && m.t <= until).map(m => {
        const data = window.WWebJS.getMessageModel(m);
        data.id = { ...data.id, _serialized: m.id.toString() };
        data.directPath = data.directPath || m.directPath || m.mediaData?.directPath;
        data.mimetype = data.mimetype || m.mimetype || m.mediaData?.mimetype;
        return data;
    }) };
}

async function readExtractionHistory({ client, chat, startTs, endTs, cap, Message }) {
    const result = await client.pupPage.evaluate(loadHistoryInPage, chat.id._serialized, startTs, endTs, cap);
    return { messages: result.messages.map(data => new Message(client, data)), warning: result.warning };
}

module.exports = { loadHistoryInPage, readExtractionHistory };
