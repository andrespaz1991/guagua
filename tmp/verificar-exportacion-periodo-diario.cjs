const fs = require('fs');
const path = require('path');
const text = JSON.parse(fs.readFileSync('tmp/whatsapp-diario/ultimo-diario.json', 'utf8')).text;
const socket = new WebSocket('ws://127.0.0.1:3000/socket.io/?EIO=4&transport=websocket');
let finished = false;
const timer = setTimeout(() => finish(new Error('Tiempo de exportación agotado.')), 60000);
function finish(error) {
    if (finished) return;
    finished = true; clearTimeout(timer); socket.close();
    if (error) { console.error(error.message); process.exitCode = 1; }
}
socket.onerror = () => finish(new Error('No se pudo conectar al módulo.'));
socket.onmessage = event => {
    const packet = String(event.data);
    if (packet.startsWith('0')) socket.send('40');
    if (packet === '2') socket.send('3');
    if (packet.startsWith('40')) socket.send('42' + JSON.stringify(['export_diary_docx', { text }]));
    if (!packet.startsWith('42')) return;
    const [name, result] = JSON.parse(packet.slice(2));
    if (name === 'diary_docx_result') {
        try {
            if (result.error) throw new Error(result.error);
            if (result.filename !== 'Diario_octubre_2026.docx') throw new Error('El archivo no corresponde al periodo de las notas.');
            const dir = 'tmp/diario-periodo-notas';
            fs.mkdirSync(dir, { recursive: true });
            const file = path.join(dir, result.filename);
            fs.writeFileSync(file, Buffer.from(result.base64, 'base64'));
            console.log(JSON.stringify({ filename: result.filename, bytes: fs.statSync(file).size, file }));
            finish();
        } catch (error) { finish(error); }
    }
};
