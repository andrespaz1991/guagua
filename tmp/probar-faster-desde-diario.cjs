const fs = require('fs');
const path = require('path');
const assert = require('node:assert/strict');
const { createWhisperWorkflow, DEFAULT_FASTER_LAUNCHER } = require('../apps/diario/whisper-workflow.cjs');
const root = path.resolve('tmp/faster-diario-qa');
const input = path.join(root, 'input');
const output = path.join(root, 'output');
fs.mkdirSync(input, { recursive: true });
fs.mkdirSync(output, { recursive: true });
const original = 'D:/xampp/htdocs/whisper/input/archivo_2026-11-01_21-31-19_76e83133/audio_2026-10-15T21-36-04-000Z_true_120363395026501872@g.us_3A59B6F4C2BE9F383DDE_56749553926282@lid.ogg';
const audio = path.join(input, path.basename(original));
fs.copyFileSync(original, audio);
fs.writeFileSync(path.join(output, 'transcripcion-anterior.tsv'), 'Contenido anterior que se debe conservar.');
const workflow = createWhisperWorkflow({ projectRoot: 'D:/xampp/htdocs/whisper', logPath: path.join(root, 'faster-whisper.log') });
assert.equal(workflow.launcherPath, DEFAULT_FASTER_LAUNCHER);
console.log(JSON.stringify({ launcher: workflow.launcherPath, input, output }));
workflow.start(input, output, result => {
    try {
        if (result.error) throw result.error;
        if (result.code !== 0) throw new Error(result.tail);
        const formats = ['tsv', 'txt', 'srt', 'vtt'];
        for (const ext of formats) {
            const file = path.join(output, path.parse(audio).name + '.' + ext);
            assert.ok(fs.statSync(file).size > 0);
        }
        const tsv = fs.readFileSync(path.join(output, path.parse(audio).name + '.tsv'), 'utf8');
        assert.ok(tsv.startsWith('start\tend\ttext'));
        const folders = fs.readdirSync(output, { withFileTypes: true }).filter(e => e.isDirectory() && e.name.startsWith('archivo_'));
        assert.ok(folders.some(e => fs.existsSync(path.join(output, e.name, 'transcripcion-anterior.tsv'))));
        assert.deepEqual(fs.readFileSync(audio), fs.readFileSync(original));
        const log = fs.readFileSync(result.logPath, 'utf8');
        assert.ok(log.includes('Ejecutando faster-whisper:'));
        assert.ok(log.includes('Transcripción completada con faster-whisper: 1 audios, 4 archivos generados.'));
        console.log(JSON.stringify({ success: true, code: result.code, formats, archivedPreviousOutput: true, preservedAudio: true, launcher: workflow.launcherPath }));
    } catch (error) { console.error(error.message); process.exitCode = 1; }
});
