const assert = require('node:assert/strict');
const fs = require('fs');
const os = require('os');
const path = require('path');
const { createDiaryService, formatDailyEntry, reflectionText } = require('../apps/diario/diario-service.cjs');
const date = '2026-10-15';
const body = '## 2026-10-15\n\n### Jornada deportiva\n\nPreparé las actividades con mis estudiantes.';

function fixture(responses) {
    const dataDir = fs.mkdtempSync(path.join(os.tmpdir(), 'diario-reflection-'));
    const requests = [], progress = [];
    fs.writeFileSync(path.join(dataDir, 'consolidado.json'), JSON.stringify({ sections: [
        { date, name: 'audio_octubre.tsv', text: 'Preparé las actividades con mis estudiantes.' }
    ] }));
    const draftPath = path.join(dataDir, 'ultimo-diario.json');
    fs.writeFileSync(draftPath, JSON.stringify({ text: 'Diario anterior revisado.' }));
    class MockOpenAI {
        constructor() {
            this.chat = { completions: { create: async request => {
                requests.push(request);
                const next = responses.shift();
                if (!next) throw new Error('Solicitud adicional inesperada.');
                return { choices: [{ message: { content: next.text ?? next }, finish_reason: next.finishReason || 'stop' }] };
            } } };
        }
    }
    const service = createDiaryService({ config: { modelName: 'test' }, outputPath: dataDir, dataDir, OpenAI: MockOpenAI, normalizeUrl: () => 'http://localhost:1234/v1' });
    return { generate: () => service.generate('', item => progress.push(item)), requests, progress, draft: () => JSON.parse(fs.readFileSync(draftPath, 'utf8')).text };
}

(async () => {
    for (const heading of ['### Reflexión pedagógica:', '__Reflexión final:__', '- **Reflexión del día:**', '4. Reflexión:']) {
        const result = formatDailyEntry(body + '\n\n' + heading + '\nLa preparación compartida favorece la participación.', date);
        assert.ok(result.includes('**Reflexión del día:**'));
        assert.ok(result.includes('La preparación compartida'));
        assert.equal((result.match(/\*\*Reflexión del día:\*\*/g) || []).length, 1);
    }
    for (const text of [body, body + '\n\n**Reflexión del día:**\n\n---']) {
        assert.throws(() => formatDailyEntry(text, date), error => error.code === 'DIARY_REFLECTION_MISSING');
    }
    assert.equal(reflectionText('## Jornada\nRelato que no se debe repetir.\n### Reflexión pedagógica:\nAcompañar favorece el aprendizaje.'), 'Acompañar favorece el aprendizaje.');

    let test = fixture([body, 'La preparación compartida favorece la participación.']);
    let result = await test.generate();
    assert.equal(test.requests.length, 2);
    assert.ok(test.requests[1].messages[1].content.includes(date));
    assert.ok(test.requests[1].messages[1].content.includes('FUENTE:'));
    assert.ok(test.requests[1].messages[1].content.includes('BORRADOR:'));
    assert.ok(result.includes('Preparé las actividades con mis estudiantes.'));
    assert.ok(result.includes('**Reflexión del día:** La preparación compartida'));
    assert.ok(test.progress.some(item => item.message.includes('Completando reflexión')));
    assert.equal(test.draft(), result);

    test = fixture([body + '\n\n### Reflexión final:\nAcompañar favorece el aprendizaje.']);
    await test.generate();
    assert.equal(test.requests.length, 1);

    test = fixture([body + '\n\n**Reflexión del día:**', '**Reflexión del día:**', '### Reflexión pedagógica:\nAcompañar favorece el aprendizaje.']);
    result = await test.generate();
    assert.equal(test.requests.length, 3);
    assert.equal((result.match(/\*\*Reflexión del día:\*\*/g) || []).length, 1);
    assert.ok(result.includes('Acompañar favorece el aprendizaje.'));

    test = fixture([body, '**Reflexión del día:**', '**Reflexión del día:**']);
    await assert.rejects(test.generate, error => error.code === 'DIARY_REFLECTION_MISSING');
    assert.equal(test.requests.length, 3);
    assert.equal(test.draft(), 'Diario anterior revisado.');

    test = fixture([{ text: body, finishReason: 'length' }]);
    await assert.rejects(test.generate, /cortó la respuesta/);
    assert.equal(test.requests.length, 1);
    assert.equal(test.draft(), 'Diario anterior revisado.');
    console.log('Diario: variantes de reflexión, contenido obligatorio, reparación selectiva, reintentos limitados y conservación del borrador verificados.');
})().catch(error => { console.error(error); process.exitCode = 1; });
