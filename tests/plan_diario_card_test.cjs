const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

class Element {
    constructor() { this.children = []; this.dataset = {}; this.listeners = {}; this.validity = { valid: true }; }
    appendChild(child) { this.children.push(child); }
    replaceChildren() { this.children = []; }
    addEventListener(name, callback) { this.listeners[name] = callback; }
    setAttribute() {}
    focus() {}
    inputs() { return this.children.flatMap(child => [...(child.type === 'checkbox' ? [child] : []), ...child.inputs()]); }
    querySelectorAll() { return this.inputs(); }
    querySelector(selector) { return this.inputs().find(input => selector.includes('"' + input.dataset.planTask + '"')); }
}

async function run() {
    const controls = {};
    ['status','error','tasks','refresh','picker','today','date','summary','count','progress'].forEach(name => controls[name] = new Element());
    const card = new Element(), title = new Element();
    card.dataset = { api: 'http://localhost/guagua/apps/plan_diario/api.php', csrf: 'test-csrf', today: '2026-11-23' };
    card.querySelector = selector => controls[selector.match(/data-plan-(\w+)/)[1]];
    title.href = 'http://localhost/guagua/apps/plan_diario/';
    controls.picker.value = card.dataset.today;
    const days = {
        '2026-11-23': [{ id: 1, category_id: 10, category: 'Personal', title: 'Tarea de hoy', done: false }],
        '2026-11-22': [{ id: 2, category_id: 10, category: 'Personal', title: '<b>Tarea anterior</b>', done: false }]
    };
    const requests = [], window = new Element(), document = new Element();
    document.activeElement = null;
    document.getElementById = id => id === 'plan-diario-card' ? card : title;
    document.createElement = () => new Element();
    let revision = 5, rejectNext = false, conflictNext = false;
    const fetch = async (url, options) => {
        requests.push({ url, options });
        if (rejectNext) { rejectNext = false; throw new Error('Sin conexión'); }
        const date = new URL(url).searchParams.get('date');
        if (options.method === 'POST') {
            const body = JSON.parse(options.body);
            assert.equal(body.date, date);
            assert.equal(body.view, 'home');
            assert.equal(options.headers['X-CSRF-Token'], 'test-csrf');
            if (conflictNext) { conflictNext = false; return { ok: false, status: 409, json: async () => ({ message: 'El plan cambió en otra pestaña.' }) }; }
            const task = (days[date] || []).find(task => task.id === body.id);
            assert.ok(task, 'A checkbox targets a task from a different day');
            assert.equal(body.revision, revision);
            task.done = body.done; revision++;
        }
        const tasks = (days[date] || []).map(task => ({ ...task }));
        return { ok: true, json: async () => ({ ok: true, data: { date, tasks, total: tasks.length, done: tasks.filter(task => task.done).length, revision } }) };
    };
    vm.runInNewContext(fs.readFileSync('comun/js/plan_diario_card.js','utf8'), {
        document, window, fetch, URL, AbortController, setTimeout, clearTimeout
    });
    const flush = () => new Promise(setImmediate);
    await flush();
    assert.equal(controls.picker.value, '2026-11-23');
    assert.match(title.textContent, /Hoy/);
    assert.equal(new URL(title.href).searchParams.get('date'), '2026-11-23');
    assert.equal(controls.tasks.inputs()[0].dataset.planTask, '1');

    controls.picker.value = '2026-11-22'; controls.picker.listeners.change();
    assert.equal(controls.picker.disabled, true, 'The selected date can change during a request');
    await flush();
    assert.equal(controls.picker.disabled, false);
    assert.equal(title.textContent, 'Mi plan diario');
    assert.equal(new URL(title.href).searchParams.get('date'), '2026-11-22');
    assert.equal(controls.tasks.inputs()[0].dataset.planTask, '2');
    assert.equal(controls.tasks.children[1].children[0].children[0].children[1].children[0].textContent, '<b>Tarea anterior</b>');

    let input = controls.tasks.inputs()[0]; input.checked = true;
    await controls.tasks.listeners.change({ target: input });
    assert.equal(days['2026-11-22'][0].done, true);
    assert.equal(days['2026-11-23'][0].done, false);
    assert.equal(controls.count.textContent, '1 de 1 tareas completadas');
    window.listeners.focus(); await flush();
    assert.equal(controls.picker.value, '2026-11-22', 'Returning to the tab resets the selected date');
    controls.refresh.listeners.click(); await flush();
    assert.equal(new URL(requests.at(-1).url).searchParams.get('date'), '2026-11-22');

    conflictNext = true; input = controls.tasks.inputs()[0]; input.checked = false;
    await controls.tasks.listeners.change({ target: input });
    assert.equal(controls.tasks.inputs()[0].checked, true, 'A rejected change is shown as saved');
    assert.equal(controls.picker.value, '2026-11-22');
    assert.match(controls.error.textContent, /otra pestaña/);

    rejectNext = true; controls.picker.value = '2026-11-24'; controls.picker.listeners.change(); await flush();
    assert.equal(controls.picker.value, '2026-11-22', 'A failed date change labels the old tasks with a new date');
    assert.equal(new URL(title.href).searchParams.get('date'), '2026-11-22');
    assert.equal(controls.tasks.inputs()[0].dataset.planTask, '2');
    const count = requests.length;
    controls.picker.value = ''; controls.picker.listeners.change(); await flush();
    assert.equal(requests.length, count, 'An empty date should not issue a request');
    assert.equal(controls.picker.value, '2026-11-22');

    controls.picker.value = '2026-11-25'; controls.picker.listeners.change(); await flush();
    assert.equal(controls.tasks.inputs().length, 0);
    assert.equal(controls.summary.hidden, true);
    assert.match(controls.status.textContent, /esta fecha/);
    controls.today.listeners.click(); await flush();
    assert.equal(controls.picker.value, '2026-11-23');
    assert.equal(controls.tasks.inputs()[0].dataset.planTask, '1');
    assert.match(title.textContent, /Hoy/);
    assert.equal(controls.error.hidden, true);
    console.log('OK: selector de fecha, regreso a Hoy, enlace al día, cumplimiento por fecha, conflictos y recuperación de errores.');
}
run().catch(error => { console.error(error); process.exitCode = 1; });
