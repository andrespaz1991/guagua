const fs = require('fs');
let html = fs.readFileSync('D:/xampp/htdocs/wsp/index.html', 'utf8');
html = html.replaceAll('2. Iniciar Whisper', '2. Ejecutar run_script.bat');
html = html.replace('<div class="pt-2 grid grid-cols-2 gap-2">', '<p id="whisperAfterExtraction" role="status" class="hidden p-3 rounded-xl bg-sky-50 border border-sky-200 text-[10px] text-sky-900"></p>\n                            <div class="pt-2 grid grid-cols-1 gap-2">');
html = html.replace('            const r = d.result;', `            const r = d.result;
            const nextStep = document.getElementById('whisperAfterExtraction');
            if (r.savedAudio > 0) {
                nextStep.textContent = r.savedAudio + ' audio(s) extraídos. Pulsa “2. Ejecutar run_script.bat” para transcribirlos con Whisper. Confirma el aviso de administrador de Windows si aparece.';
                nextStep.classList.remove('hidden');
            }`);
html = html.replace("            document.getElementById('extractionResult').classList.add('hidden');", "            document.getElementById('extractionResult').classList.add('hidden');\n            document.getElementById('whisperAfterExtraction').classList.add('hidden');");
html = html.replace("        function startWhisper() {", "        function startWhisper() {\n            if (!socket.connected) return showWhisperStatus('El servidor está desconectado. Espera a que se reconecte antes de ejecutar Whisper.', 'error');");
fs.writeFileSync('tmp/diario-opcion-whisper.html', html);
const scripts = [...html.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)].map(m=>m[1]).filter(Boolean);
for (const script of scripts) new (require('vm').Script)(script);
console.log('JavaScript de Diario verificado.');
