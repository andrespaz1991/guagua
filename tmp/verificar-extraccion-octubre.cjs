const fs = require('fs');
const path = require('path');
const socket = new WebSocket('ws://127.0.0.1:3000/socket.io/?EIO=4&transport=websocket');
let requested = false, finished = false;
const emit = (event, data = {}) => { if (socket.readyState === WebSocket.OPEN) socket.send('42' + JSON.stringify([event, data])); };
const poll = setInterval(() => emit('get_status'), 5000);
const timeout = setTimeout(() => finish(new Error('Tiempo máximo de verificación agotado.')), 15 * 60000);
function finish(error) {
    if (finished) return;
    finished = true;
    clearInterval(poll); clearTimeout(timeout); socket.close();
    if (error) { console.error(error.message); process.exitCode = 1; }
}
socket.onerror = () => finish(new Error('No fue posible conectar al servidor de Diario.'));
socket.onmessage = event => {
    const packet = String(event.data);
    if (packet.startsWith('0')) socket.send('40');
    if (packet === '2') socket.send('3');
    if (!packet.startsWith('42')) return;
    const [name, data] = JSON.parse(packet.slice(2));
    if (name === 'status') {
        console.log(JSON.stringify({ status: data.status, message: data.loadingMessage }));
        if (data.status === 'READY' && !requested) {
            requested = true;
            emit('extract_content', {
                startDate: '2026-10-01', endDate: '2026-10-31',
                selectedChats: ['120363395026501872@g.us'],
                includeAudio: true, includeText: false, includeMedia: false
            });
        }
    }
    if (name === 'extraction_progress') console.log(JSON.stringify(data));
    if (name === 'extraction_result') {
        try {
            if (data.error) throw new Error(data.error);
            const result = data.result;
            fs.writeFileSync(path.join(__dirname, 'extraccion-octubre-verificada.json'), JSON.stringify(data, null, 2));
            const manifest = JSON.parse(fs.readFileSync(result.manifestPath, 'utf8'));
            const files = manifest.files.filter(file => file.type === 'audio');
            const valid = files.every(file => {
                const input = path.join(result.outputFolder, file.file);
                const bytes = fs.readFileSync(input);
                return bytes.length > 0 && bytes.subarray(0, 4).toString() === 'OggS';
            });
            console.log(JSON.stringify({ result, validatedFiles: files.length, validOgg: valid }));
            if (result.partial || result.savedAudio !== 9 || files.length !== 9 || !valid) throw new Error('La extracción no descargó los nueve audios completos.');
            finish();
        } catch (error) { finish(error); }
    }
};
