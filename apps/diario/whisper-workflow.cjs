const fs = require('fs');
const path = require('path');
const { spawn, spawnSync } = require('child_process');

const DEFAULT_FASTER_LAUNCHER = 'C:/Users/Andres/Documents/Codex/2026-11-01/ex/outputs/Probar-faster-whisper.cmd';

function createWhisperWorkflow({ projectRoot, logPath, launcherPath = DEFAULT_FASTER_LAUNCHER }) {
    const python = path.join(projectRoot, 'venv', 'Scripts', 'python.exe');
    const runner = path.join(__dirname, 'whisper_workflow.py');
    function args(action, input, output) {
        const command = ['-u', runner, action, '--project', projectRoot, '--input', input, '--output', output];
        if (action === 'transcribe-faster') command.push('--launcher', launcherPath);
        return command;
    }
    const environment = () => ({ ...process.env, PYTHONUTF8: '1', PYTHONIOENCODING: 'utf-8' });
    function prepareExtraction(input, output) {
        const result = spawnSync(python, args('prepare-extraction', input, output), {
            windowsHide: true, encoding: 'utf8', timeout: 30000, env: environment()
        });
        if (result.error) throw result.error;
        if (result.status !== 0) throw new Error((result.stderr || result.stdout || 'No se pudieron archivar los archivos anteriores.').trim());
        return JSON.parse(result.stdout);
    }
    function start(input, output, onResult) {
        if (!fs.existsSync(python)) throw new Error(`No se encontró el Python de Whisper: ${python}.`);
        if (!fs.existsSync(launcherPath)) throw new Error(`No se encontró el lanzador de faster-whisper: ${launcherPath}.`);
        fs.mkdirSync(path.dirname(logPath), { recursive: true });
        const fd = fs.openSync(logPath, 'w');
        let job;
        try {
            job = spawn(python, args('transcribe-faster', input, output), {
                cwd: projectRoot, windowsHide: true, env: environment(), stdio: ['ignore', fd, fd]
            });
        } finally { fs.closeSync(fd); }
        let settled = false;
        const finish = (code, error) => {
            if (settled) return;
            settled = true;
            const tail = fs.readFileSync(logPath, 'utf8').trim().slice(-2500);
            onResult({ code, error, tail, logPath });
        };
        job.once('error', error => finish(null, error));
        job.once('close', code => finish(code, null));
        return job;
    }
    return { prepareExtraction, start, logPath, launcherPath };
}
module.exports = { createWhisperWorkflow, DEFAULT_FASTER_LAUNCHER };
