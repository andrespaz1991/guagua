const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { webcrypto } = require('node:crypto');
const source = fs.readFileSync(path.join(__dirname, '../reportes/descargar_guagua.js'), 'utf8');

function setup(responses) {
    let now = 0;
    let nextTimer = 0;
    let submit;
    const timers = new Map();
    const requests = [];
    const button = { disabled: false };
    const form = { elements: { descarga: { value: 'anterior' } }, querySelector: () => button,
        addEventListener: (event, handler) => { if (event === 'submit') submit = handler; } };
    const nodes = { 'portable-form': form };
    for (const name of ['status', 'progress', 'bar', 'percent', 'elapsed', 'remaining']) {
        nodes['portable-' + name] = { textContent: '', value: 0, hidden: true };
    }
    const document = { cookie: 'guagua_descarga=anterior', getElementById: id => nodes[id] };
    const schedule = (fn, ms, interval = false) => {
        const id = ++nextTimer;
        timers.set(id, { fn, at: now + ms, ms, interval });
        return id;
    };
    const context = vm.createContext({ document, crypto: webcrypto, Date: { now: () => now }, AbortController,
        window: { setTimeout: (fn, ms) => schedule(fn, ms), setInterval: (fn, ms) => schedule(fn, ms, true),
            clearTimeout: id => timers.delete(id), clearInterval: id => timers.delete(id) },
        fetch: async (url, options) => {
            requests.push({ url, options });
            const response = responses.shift() ?? { status: 'pending' };
            if (response instanceof Error) throw response;
            return { ok: !response.http || response.http < 400, status: response.http ?? 200, json: async () => response };
        }
    });
    vm.runInContext(source, context);
    submit.call(form);
    return { nodes, button, requests, document, timers, form, async advance(ms) {
        const until = now + ms;
        while (true) {
            const next = [...timers].filter(([, timer]) => timer.at <= until).sort((a, b) => a[1].at - b[1].at)[0];
            if (!next) break;
            const [id, timer] = next;
            now = timer.at;
            timers.delete(id);
            if (timer.interval) timers.set(id, { ...timer, at: now + timer.ms });
            await timer.fn();
        }
        now = until;
    } };
}

(async () => {
    const ui = setup([
        { status: 'preparing', percent: 40, stage: 'Exportando las bases…', remaining_seconds: 120 },
        { status: 'preparing', percent: 75, stage: 'Comprimiendo los archivos…', remaining_seconds: 12 },
        { status: 'ready', percent: 100, stage: 'ZIP listo.', remaining_seconds: 0 }
    ]);
    assert.equal(ui.button.disabled, true);
    assert.equal(ui.nodes['portable-progress'].hidden, false);
    assert.match(ui.form.elements.descarga.value, /^[a-f0-9]{32}$/);
    assert.equal(ui.nodes['portable-percent'].textContent, '0 %');
    assert.equal(ui.nodes['portable-remaining'].textContent, 'Calculando…');
    await ui.advance(500);
    assert.equal(ui.nodes['portable-bar'].value, 40);
    assert.equal(ui.nodes['portable-remaining'].textContent, '2 min 0 s');
    assert.equal(ui.nodes['portable-status'].textContent, 'Exportando las bases…');
    assert.equal(ui.requests[0].url, 'descargar_guagua_progreso.php?descarga=' + ui.form.elements.descarga.value);
    assert.equal(ui.requests[0].options.cache, 'no-store');
    assert.equal(ui.requests[0].options.credentials, 'same-origin');
    await ui.advance(2000);
    assert.equal(ui.nodes['portable-percent'].textContent, '75 %');
    assert.equal(ui.nodes['portable-status'].textContent, 'Comprimiendo los archivos…');
    await ui.advance(2000);
    assert.equal(ui.nodes['portable-percent'].textContent, '100 %');
    assert.equal(ui.nodes['portable-remaining'].textContent, '0 s');
    assert.equal(ui.button.disabled, true, 'ZIP listo todavía debe esperar el inicio de la descarga.');
    ui.document.cookie = 'guagua_descarga=' + ui.form.elements.descarga.value;
    await ui.advance(1000);
    assert.equal(ui.button.disabled, false);
    assert.match(ui.nodes['portable-status'].textContent, /Descarga iniciada.*LEEME-XAMPP.txt/);
    assert.equal(ui.timers.size, 0, 'Se siguen consultando estados después de iniciar la descarga.');

    const retry = setup([new Error('Sin red'), { status: 'preparing', percent: 25, stage: 'Exportando…', remaining_seconds: 1 }]);
    await retry.advance(500);
    assert.match(retry.nodes['portable-status'].textContent, /Reintentando/);
    assert.equal(retry.button.disabled, true);
    await retry.advance(2000);
    assert.equal(retry.nodes['portable-bar'].value, 25);
    await retry.advance(1500);
    assert.equal(retry.nodes['portable-remaining'].textContent, 'Actualizando estimación…');
    assert.equal(retry.nodes['portable-bar'].value, 25, 'El tiempo transcurrido no debe inventar avance.');

    const failed = setup([{ status: 'error', stage: 'No se pudo preparar el ZIP.' }]);
    await failed.advance(500);
    assert.equal(failed.button.disabled, false);
    assert.equal(failed.nodes['portable-remaining'].textContent, 'No disponible');
    assert.equal(failed.timers.size, 0);
    for (const http of [401, 403]) {
        const expired = setup([{ http }]);
        await expired.advance(500);
        assert.equal(expired.button.disabled, false);
        assert.match(expired.nodes['portable-status'].textContent, /sesión/);
        assert.equal(expired.timers.size, 0);
    }
    const pending = setup([{ status: 'pending' }]);
    await pending.advance(500);
    assert.equal(pending.nodes['portable-bar'].value, 0);
    assert.equal(pending.button.disabled, true, 'No debe reconocer la cookie de una descarga anterior.');
    await pending.advance(30 * 60 * 1000);
    assert.equal(pending.button.disabled, false);
    assert.match(pending.nodes['portable-status'].textContent, /tardando más/);
    assert.equal(pending.timers.size, 0);
    console.log('OK: progreso y tiempos en pantalla, descarga automática, reintentos, errores, sesión y limpieza de consultas.');
})().catch(error => { console.error(error); process.exitCode = 1; });
