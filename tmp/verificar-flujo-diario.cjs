const fs = require('fs');
const w = new WebSocket('ws://localhost:3000/socket.io/?EIO=4&transport=websocket');
const send = (event,payload={})=>w.send('42'+JSON.stringify([event,payload]));
let sawConsolidated=false;
let requested=false;
w.onmessage=event=>{
    const t=String(event.data);
    if(t.startsWith('0'))w.send('40');
    if(t==='2')w.send('3');
    if(t.startsWith('40'))send('get_config');
    if(!t.startsWith('42'))return;
    const [name,data]=JSON.parse(t.slice(2));
    if(name==='config_data'&&!requested) {
        requested=true;
        console.log(JSON.stringify({extension:data.transcriptExtension,hasBasePrompt:Boolean(data.diaryBasePrompt),model:data.modelName}));
        send('consolidate_whisper_tsv');
    }
    if(name==='whisper_transcript_ready'&&!sawConsolidated) {
        if(data.error)throw new Error(data.error);
        sawConsolidated=true;
        const snapshot=JSON.parse(fs.readFileSync('tmp/whatsapp-diario/consolidado.json','utf8'));
        console.log(JSON.stringify({files:data.fileCount,characters:data.characterCount,dates:[...new Set(snapshot.sections.map(s=>s.date))],correctedDates:snapshot.sections.filter(s=>s.date!==s.messageDate).length}));
        send('export_diary_docx',{text:'## DOMINGO, 4 DE OCTUBRE DE 2026\n\n### Comprobación del flujo\n\nEste texto es exclusivamente una prueba técnica de la descarga.\n\n**Reflexión del día:** Revisar el texto permite comprobar el documento antes de compartirlo.'});
    }
    if(name==='diary_docx_result') {
        if(data.error)throw new Error(data.error);
        const bytes=Buffer.from(data.base64,'base64');
        if(bytes.slice(0,2).toString()!=='PK')throw new Error('El archivo no es DOCX');
        fs.writeFileSync('tmp/diario-descarga-verificacion.docx',bytes);
        console.log(JSON.stringify({download:data.filename,bytes:bytes.length}));
        w.close();process.exit(0);
    }
};
setTimeout(()=>{console.error('Tiempo agotado');process.exit(1);},30000);
