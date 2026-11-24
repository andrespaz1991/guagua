// Shared with the existing Diario page. All generated text is user-reviewable.
let diaryBusy = false;
let diaryExportBusy = false;
function diaryMessage(text, error = false) {
    const notice = document.getElementById('diaryNotice');
    notice.textContent = text;
    notice.className = 'p-3 rounded-xl text-xs ' + (error ? 'bg-red-50 text-red-800' : 'bg-sky-50 text-sky-900');
}
function showDiaryEditor(text) {
    document.getElementById('diaryEditorPanel').classList.remove('hidden');
    document.getElementById('diaryEditor').value = text;
    document.getElementById('summaryText').innerHTML = formatMarkdown(text);
    document.getElementById('typeBadge').innerText = 'Diario de campo';
}
function regenerateDiary() {
    if (diaryBusy) return;
    if (!socket.connected) return diaryMessage('Espera a que el servidor se reconecte.',true);
    diaryBusy = true;
    document.getElementById('btnRegenerateDiary').disabled = true;
    document.getElementById('btnAnalyzeWhisper').disabled = true;
    diaryMessage('Generando el diario con el prompt base guardado…');
    showWhisperStatus('Generando diario con LM Studio…');
    socket.emit('analyze_whisper_transcript',{instruction:document.getElementById('whisperInstructionInput').value.trim()});
}
function saveDiaryDraft() {
    socket.emit('save_diary_draft',{text:document.getElementById('diaryEditor').value});
}
function exportDiaryWord() {
    if (diaryExportBusy) return;
    const text = document.getElementById('diaryEditor').value.trim();
    if (!text) return diaryMessage('Primero genera o escribe el diario.',true);
    if (!socket.connected) return diaryMessage('El servidor está desconectado.',true);
    diaryExportBusy = true;
    document.getElementById('btnExportDiary').disabled = true;
    diaryMessage('Creando una copia de la plantilla con el texto revisado…');
    socket.emit('export_diary_docx',{text});
}
function downloadDiaryBlob(blob, filename) {
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url; anchor.download = filename; anchor.click();
    setTimeout(()=>URL.revokeObjectURL(url),30000);
}
socket.on('config_data', settings => {
    document.getElementById('transcriptExtension').value = settings.transcriptExtension || 'tsv';
    document.getElementById('diaryBasePrompt').value = settings.diaryBasePrompt || '';
});
socket.on('diary_settings_result',result=>{
    if (result.error) {diaryMessage(result.error,true);showWhisperStatus(result.error,'error');}
});
socket.on('diary_generation_progress', progress => {
    diaryMessage(progress.message);
    showWhisperStatus(progress.message);
});
socket.on('whisper_analysis_result', result => {
    diaryBusy = false;
    document.getElementById('btnRegenerateDiary').disabled = false;
    if (result.error) return diaryMessage(result.error,true);
    showDiaryEditor(result.result);
    diaryMessage('Diario generado. Revisa el texto; puedes editarlo, regenerarlo o descargarlo en Word.');
});
socket.on('consolidated_download', result => {
    if (result.error) return showWhisperStatus(result.error,'error');
    downloadDiaryBlob(new Blob([result.text],{type:'text/plain;charset=utf-8'}),result.filename);
});
socket.on('diary_draft_saved', result => diaryMessage(result.error || 'Borrador guardado en este equipo.',Boolean(result.error)));
socket.on('diary_draft', draft => {
    if (!draft.text || document.getElementById('diaryEditor').value) return;
    showDiaryEditor(draft.text);
    document.getElementById('welcomeView').classList.add('hidden');
    document.getElementById('resultView').classList.remove('hidden');
    diaryMessage('Se recuperó el último diario guardado. Puedes revisarlo antes de descargar.');
});
socket.on('diary_docx_result', result => {
    diaryExportBusy = false;
    document.getElementById('btnExportDiary').disabled = false;
    if (result.error) return diaryMessage(result.error,true);
    const raw = atob(result.base64);
    const bytes = Uint8Array.from(raw,c=>c.charCodeAt(0));
    downloadDiaryBlob(new Blob([bytes],{type:'application/vnd.openxmlformats-officedocument.wordprocessingml.document'}),result.filename);
    diaryMessage('Word descargado con el periodo de las notas del diario en la portada y en el nombre del archivo.');
});
socket.on('disconnect',()=>{
    diaryBusy=false; diaryExportBusy=false;
    document.getElementById('btnRegenerateDiary').disabled=false;
    document.getElementById('btnExportDiary').disabled=false;
    document.getElementById('btnAnalyzeWhisper').disabled=false;
    diaryMessage('Se perdió la conexión. El texto revisado sigue en la pantalla; guarda el borrador al reconectar.',true);
});
document.getElementById('diaryEditor').addEventListener('input', event=>{
    document.getElementById('summaryText').innerHTML = formatMarkdown(event.target.value);
});
socket.on('connect',()=>socket.emit('get_diary_draft'));
socket.emit('get_diary_draft');
