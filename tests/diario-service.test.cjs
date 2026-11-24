const assert = require('node:assert/strict');
const fs = require('fs');
const path = require('path');
const os = require('os');
const {createDiaryService,parseTranscript,sourceDate,diaryDate,formatDailyEntry,extension} = require('../apps/diario/diario-service.cjs');
(async()=>{
    assert.equal(parseTranscript('start\tend\ttext\n0\t1000\tHola\n1000\t2000\tSegunda línea','tsv'),'Hola\nSegunda línea');
    assert.equal(parseTranscript('StartTime\tEndTime\tText\n0\t1\tHola','tsv'),'Hola');
    assert.equal(parseTranscript('1\n00:00:00,000 --> 00:00:01,000\nHola\n','srt'),'Hola');
    assert.equal(parseTranscript('WEBVTT\n\n00:00.000 --> 00:01.000\nHola','vtt'),'Hola');
    assert.equal(parseTranscript('{"segments":[{"text":"Hola"}]}','json'),'Hola');
    assert.equal(sourceDate('audio_2026-10-04T02-00-00-000Z_id.tsv'),'2026-10-03');
    assert.equal(sourceDate('sin_fecha.tsv'),null);
    assert.equal(diaryDate('El viernes 25 de septiembre se inicia la jornada','2026-09-29'),'2026-09-25');
    assert.equal(diaryDate('El día lunes, 28 de septiembre trabajamos','2026-09-29'),'2026-09-28');
    assert.equal(diaryDate('Hoy 22 de septiembre, martes','2026-09-22'),'2026-09-22');
    assert.equal(diaryDate('Trabajamos historia y hablamos del 5 de mayo.','2026-09-22'),'2026-09-22');
    const formatted = formatDailyEntry('## 2026-09-14\n\n### Actividades\n\nTrabajé en clase.\n\n## Reflexión del día:\n\nAprender requiere acompañamiento.','2026-09-14');
    assert.ok(formatted.startsWith('## LUNES, 14 DE SEPTIEMBRE DE 2026'));
    assert.equal((formatted.match(/^## /gm)||[]).length,1);
    assert.ok(formatted.includes('**Reflexión del día:**'));
    assert.throws(()=>extension('../tsv'));
    const root = fs.mkdtempSync(path.join(os.tmpdir(),'diario-test-'));
    const output=path.join(root,'output');fs.mkdirSync(output);
    fs.writeFileSync(path.join(output,'audio_2026-10-04T12-00-00-000Z_a.tsv'),'start\tend\ttext\n0\t1\tClase de geometría.');
    fs.writeFileSync(path.join(output,'audio_2026-10-04T13-00-00-000Z_b.tsv'),'StartTime\tEndTime\tText\n0\t1\tRevisé trabajos.');
    fs.writeFileSync(path.join(output,'audio_2026-10-03T13-00-00-000Z_c.tsv'),'0\t1\tOtra jornada.');
    fs.writeFileSync(path.join(output,'audio_2026-10-04T12-00-00-000Z_a.txt'),'Clase de geometría.');
    fs.writeFileSync(path.join(output,'transcripcion_consolidada.txt'),'No debe incluirse.');
    const requests=[];
    class MockOpenAI {
        constructor(){this.chat={completions:{create:async request=>{requests.push(request);return {choices:[{message:{content:'## DOMINGO, 4 DE OCTUBRE DE 2026\n\n### Actividades\n\nTrabajé en clase.\n\n**Reflexión del día:** Acompañar favorece el aprendizaje.'},finish_reason:'stop'}]};}}};}
    }
    const config={lmStudioUrl:'http://localhost:1234/v1',modelName:'test'};
    const service=createDiaryService({config,outputPath:output,OpenAI:MockOpenAI,normalizeUrl:x=>x,dataDir:path.join(root,'data')});
    let consolidated=await service.consolidate();assert.equal(consolidated.fileCount,3);
    consolidated=await service.consolidate(()=>{},{filterDates:true,startDate:'2026-10-04',endDate:'2026-10-04'});assert.equal(consolidated.fileCount,2);
    const result=await service.generate('Mantén una voz cercana.');
    assert.ok(result.includes('Reflexión del día'));assert.equal(requests.length,1);
    assert.ok(requests[0].messages[1].content.includes('Clase de geometría.'));assert.ok(requests[0].messages[1].content.includes('Revisé trabajos.'));
    assert.ok(requests[0].messages[0].content.includes('Mantén una voz cercana.'));
    service.updateSettings({transcriptExtension:'.txt',diaryBasePrompt:'Prompt personalizado'});
    const nextConfig={};const next=createDiaryService({config:nextConfig,outputPath:output,OpenAI:MockOpenAI,normalizeUrl:x=>x,dataDir:path.join(root,'data')});
    assert.equal(nextConfig.diaryBasePrompt,'Prompt personalizado');assert.equal(nextConfig.transcriptExtension,'txt');
    consolidated=await next.consolidate();assert.equal(consolidated.fileCount,1);
    await assert.rejects(()=>next.consolidate(()=>{},{filterDates:true,startDate:'2026-10-05',endDate:'2026-10-04'}));
    console.log('Diario: formatos, fechas Colombia, filtros, consolidación sin duplicados, agrupación diaria y persistencia verificados.');
})().catch(error=>{console.error(error);process.exit(1);});
