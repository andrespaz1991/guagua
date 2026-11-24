const fs = require('fs');
const draft = 'tmp/whatsapp-diario/ultimo-diario.json';
if (fs.existsSync(draft)) fs.copyFileSync(draft, `tmp/whatsapp-diario/diario-antes-reflexion-${Date.now()}.json`);
const socket = new WebSocket('ws://127.0.0.1:3000/socket.io/?EIO=4&transport=websocket');
let finished = false, requested = false;
const timer = setTimeout(() => finish(new Error('Tiempo de generación agotado.')), 15 * 60000);
function finish(error) {
    if (finished) return;
    finished = true;
    clearTimeout(timer); socket.close();
    if (error) { console.error(error.message); process.exitCode = 1; }
}
socket.onerror = () => finish(new Error('No se pudo conectar a Diario.'));
socket.onmessage = event => {
    const packet = String(event.data);
    if (packet.startsWith('0')) socket.send('40');
    if (packet === '2') socket.send('3');
    if (packet.startsWith('40') && !requested) {
        requested = true;
        socket.send('42' + JSON.stringify(['analyze_whisper_transcript', { instruction: '' }]));
    }
    if (!packet.startsWith('42')) return;
    const [name, data] = JSON.parse(packet.slice(2));
    if (name === 'diary_generation_progress') console.log(JSON.stringify(data));
    if (name === 'whisper_analysis_result') {
        try {
            if (data.error) throw new Error(data.error);
            const entries = data.result.split(/^## /m).filter(text => text.trim());
            const valid = entries.every(entry => /\*\*Reflexión del día:\*\*\s*\S/.test(entry));
            if (entries.length !== 9 || !valid) throw new Error('Faltan jornadas o reflexiones en el diario.');
            if (JSON.parse(fs.readFileSync(draft, 'utf8')).text !== data.result) throw new Error('El diario no se guardó.');
            console.log(JSON.stringify({ success: true, entries: entries.length, reflections: entries.length, characters: data.result.length, durationMs: data.durationMs, saved: true }));
            finish();
        } catch (error) { finish(error); }
    }
};
