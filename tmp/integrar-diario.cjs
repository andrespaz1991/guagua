const fs = require('fs');
let source = fs.readFileSync('tmp/diario-server-antes-flujo.js','utf8').replace(/\r\n/g,'\n');
const insert = `const { createDiaryService } = require('../guagua/apps/diario/diario-service.cjs');
const diaryService = createDiaryService({config:currentConfig, outputPath:WHISPER_OUTPUT_PATH, OpenAI, normalizeUrl:getNormalizedLmStudioUrl});
diaryService.attach(io);
`;
source = source.replace('// --- Manejo Global de Errores',insert+'\n// --- Manejo Global de Errores');
const begin = source.indexOf('async function consolidateWhisperTranscripts(');
const end = source.indexOf('\nfunction getConnectionElapsedSeconds',begin);
if(begin<0 || end<0) throw new Error('No se encontró consolidación');
source = source.slice(0,begin)+`async function consolidateWhisperTranscripts(onProgress = () => {}, options = {}) {
    const result = await diaryService.consolidate(onProgress, options);
    activeWhisperTranscriptPath = result.filePath;
    return result;
}
`+source.slice(end);
source = source.replace("socket.on('update_config', async (newConfig) => {\n        try {", "socket.on('update_config', async (newConfig) => {\n        try {\n            diaryService.updateSettings(newConfig);");
source = source.replace("io.emit('log', { msg: `Error al guardar configuración: ${parseErrorMessage(error)}`, type: 'error' });", "io.emit('log', { msg: `Error al guardar configuración: ${parseErrorMessage(error)}`, type: 'error' });\n            socket.emit('diary_settings_result', {error:parseErrorMessage(error)});");
source = source.replace("socket.on('consolidate_whisper_tsv', async () =>", "socket.on('consolidate_whisper_tsv', async (options = {}) =>");
source = source.replace("socket.emit('consolidation_progress', progress);\n            });", "socket.emit('consolidation_progress', progress);\n            }, options);");
const analysisStart = source.indexOf("    socket.on('analyze_whisper_transcript'");
const analysisEnd = source.indexOf("    socket.on('test_lmstudio'",analysisStart);
source = source.slice(0,analysisStart)+`    socket.on('analyze_whisper_transcript', async (data = {}) => {
        const startedAt = Date.now();
        try {
            const result = await diaryService.generate(data.instruction || '', progress => socket.emit('diary_generation_progress',progress));
            socket.emit('whisper_analysis_result',{result,error:null,durationMs:Date.now()-startedAt});
        } catch(error) {
            socket.emit('whisper_analysis_result',{error:'No se pudo generar el diario: '+parseErrorMessage(error),durationMs:Date.now()-startedAt});
        }
    });

`+source.slice(analysisEnd);
fs.writeFileSync('tmp/diario-completo-server.js',source);
let html = fs.readFileSync('tmp/diario-index-antes-flujo.html','utf8');
html = html.replace('class="p-8 space-y-6"','class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar"');
html = html.replace('<div id="testResultBox"', `<div class="space-y-2"><label for="transcriptExtension" class="text-xs font-bold">Extensión de las transcripciones de Whisper</label><input id="transcriptExtension" value="tsv" placeholder="tsv, txt, srt, vtt, csv, json" class="w-full p-3 rounded-xl border border-slate-200"><p class="text-[10px] text-slate-500">Se une una extensión a la vez desde D:\\xampp\\htdocs\\whisper\\output para evitar duplicados.</p></div>
<div class="space-y-2"><label for="diaryBasePrompt" class="text-xs font-bold">Prompt base del diario de campo</label><textarea id="diaryBasePrompt" rows="10" class="w-full p-3 rounded-xl border border-slate-200 text-xs"></textarea><p class="text-[10px] text-slate-500">Se guarda y aplica al generar o regenerar cada jornada. Las instrucciones de la pantalla principal son opcionales y se añaden a este prompt.</p></div>
<div id="testResultBox"`);
html = html.replace('audioOutputPath: outputPath','audioOutputPath: outputPath,\n                transcriptExtension: document.getElementById(\'transcriptExtension\').value,\n                diaryBasePrompt: document.getElementById(\'diaryBasePrompt\').value');
html = html.replaceAll('3. Consolidar TSV','3. Unir transcripciones').replaceAll('TSV listo(s)','archivo(s) listo(s)').replaceAll('Consolidar TSV','Unir transcripciones');
html = html.replaceAll('Enviar transcripción a LM Studio','4. Generar diario con LM Studio').replaceAll('Resumen IA completado.','Diario generado.').replaceAll('Resumen IA','Diario IA');
html = html.replace('Instrucción para el resumen IA','Instrucción adicional para el diario');
html = html.replace('placeholder="Ej.: Elabora un resumen pedagógico, identifica compromisos, responsables y fechas relevantes..."','placeholder="Opcional: indica ajustes para este diario. El formato principal se toma del prompt base guardado en Configuración."');
html = html.replace('<div id="consolidationProgress"', `<label class="flex gap-2 text-[10px] text-slate-600"><input id="filterTranscriptDates" type="checkbox"> Unir solo archivos del rango de fechas seleccionado</label><button onclick="socket.emit('download_consolidated')" class="w-full py-2 text-xs font-bold text-indigo-700 border border-indigo-200 rounded-xl">Descargar transcripciones unidas en TXT</button>
<div id="consolidationProgress"`);
html = html.replace("socket.emit('consolidate_whisper_tsv');", "socket.emit('consolidate_whisper_tsv', {filterDates:document.getElementById('filterTranscriptDates').checked, startDate:document.getElementById('dateStart').value,endDate:document.getElementById('dateEnd').value});");
html = html.replace("            if (!instruction) return alert('Escribe la instrucción que deseas aplicar a la transcripción.');", "            if (diaryBusy) return;\n            if (!socket.connected) return showWhisperStatus('El servidor está desconectado.','error');\n            diaryBusy = true;");
html = html.replace('<div id="summaryText"', `<div id="diaryEditorPanel" class="hidden space-y-3 mb-6">
<h3 class="font-bold text-lg">Revisar y descargar el diario</h3><p class="text-xs text-slate-500">Edita el texto antes de descargar. ## identifica la fecha de cada jornada, ### su título y **Reflexión del día:** la reflexión. La portada usa el mes y año actuales.</p>
<textarea id="diaryEditor" rows="18" spellcheck="true" class="w-full p-4 rounded-xl border border-slate-300 text-sm font-sans leading-relaxed" aria-label="Texto editable del diario"></textarea>
<div class="flex flex-wrap gap-2"><button id="btnRegenerateDiary" onclick="regenerateDiary()" class="px-4 py-3 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-xs disabled:opacity-50">Volver a generar</button><button onclick="saveDiaryDraft()" class="px-4 py-3 rounded-xl border border-slate-300 font-bold text-xs">Guardar borrador</button><button id="btnExportDiary" onclick="exportDiaryWord()" class="px-4 py-3 rounded-xl bg-emerald-600 text-white font-bold text-xs disabled:opacity-50">Descargar diario en Word</button></div>
<p id="diaryNotice" role="status" class="p-3 text-xs"></p><h4 class="font-bold">Vista previa del texto revisado</h4></div>
<div id="summaryText"`);
html = html.replace('TSV consolidados','transcripciones unidas').replaceAll('archivo(s) TSV','archivo(s) de transcripción');
html = html.replace('Leyendo los archivos TSV de','Leyendo las transcripciones de');
html = html.replace('</body>', '<script>\n'+fs.readFileSync('apps/diario/diario-ui.js','utf8')+'\n</script>\n</body>');
fs.writeFileSync('tmp/diario-completo-index.html',html);
for(const match of html.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)) if(match[1].trim()) new (require('vm').Script)(match[1]);
console.log('Integración y JavaScript preparados.');
