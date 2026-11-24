const fs = require('fs');
const path = require('path');
const socket = new WebSocket('ws://127.0.0.1:3000/socket.io/?EIO=4&transport=websocket');
const output = 'D:/xampp/htdocs/whisper/output';
let requested = false, finished = false;
const emit = (event, data = {}) => { if (socket.readyState === WebSocket.OPEN) socket.send('42' + JSON.stringify([event, data])); };
const timeout = setTimeout(() => finish(new Error('Whisper no terminó dentro de 30 minutos.')), 30 * 60000);
function finish(error) {
    if (finished) return;
    finished = true;
    clearTimeout(timeout); socket.close();
    if (error) { console.error(error.message); process.exitCode = 1; }
}
socket.onerror = () => finish(new Error('No fue posible conectar a Diario.'));
socket.onmessage = event => {
    const packet = String(event.data);
    if (packet.startsWith('0')) socket.send('40');
    if (packet === '2') socket.send('3');
    if (!packet.startsWith('42')) return;
    const [name, data] = JSON.parse(packet.slice(2));
    if (name === 'status' && !requested) { requested = true; emit('start_whisper'); }
    if (name === 'whisper_job_result') {
        console.log(JSON.stringify(data));
        if (data.status === 'error') return finish(new Error(data.message));
        if (data.status === 'completed') {
            try {
                const files = fs.readdirSync(output, { withFileTypes: true }).filter(e => e.isFile());
                const tsv = files.filter(e => e.name.endsWith('.tsv'));
                const valid = tsv.every(e => {
                    const text = fs.readFileSync(path.join(output, e.name), 'utf8');
                    return text.startsWith('StartTime\tEndTime\tText') && text.trim().split('\n').length > 1;
                });
                const result = { ...data, transcriptions: tsv.length, outputFiles: files.length, nonemptyTsv: valid };
                fs.writeFileSync(path.join(__dirname, 'whisper-octubre-verificado.json'), JSON.stringify(result, null, 2));
                console.log(JSON.stringify(result));
                if (tsv.length !== 9 || files.length !== 36 || !valid) throw new Error('Faltan transcripciones de octubre.');
                finish();
            } catch (error) { finish(error); }
        }
    }
};
