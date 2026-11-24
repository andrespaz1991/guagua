const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

class Element {
    constructor() { this.children = []; this.dataset = {}; this.listeners = {}; this.hidden = false; }
    appendChild(child) { this.children.push(child); }
    replaceChildren() { this.children = []; }
    addEventListener(name, callback) { this.listeners[name] = callback; }
}

async function run() {
    const controls = {};
    ['status', 'events', 'connect', 'refresh', 'disconnect'].forEach(name => controls[name] = new Element());
    const card = new Element();
    card.dataset = { clientId: 'test', timezone: 'America/Bogota',endpoint:'/calendar-api',connection:'/connection?service=calendar',csrf:'test-csrf' };
    card.querySelector = selector => controls[selector.match(/data-calendar-(\w+)/)[1]];
    const head = new Element();
    let oauth, request, revoked, pending, connectedCode;
    const context = {
        document: { getElementById: () => card, head, createElement: () => new Element() },
        window: new Element(), Date, Intl, URLSearchParams, AbortSignal,
        google: { accounts: { oauth2: {
            initCodeClient: options => { oauth = options; return { requestCode: () => {} }; },
            hasGrantedAllScopes: () => true,
            revoke: value => { revoked = value; }
        } } },
        fetch: async (url, options) => {
            if (url.includes('action=connect')) {
                connectedCode=JSON.parse(options.body).code;
                assert.equal(options.headers['X-CSRF-Token'],'test-csrf');
                return {ok:true,json:async()=>({connected:true})};
            }
            if (url.includes('action=disconnect')) {revoked=true;return {ok:true,json:async()=>({connected:false})};}
            request = { url, options };
            if (pending) return pending;
            return { ok: true, json: async () => ({ timeZone: 'America/Bogota', items: [
                { summary: '<img src=x onerror=alert(1)>', start: { date: '2026-10-19' }, htmlLink: 'javascript:alert(1)' },
                { summary: 'Reunión', start: { dateTime: '2026-10-20T15:00:00Z' }, htmlLink: 'https://calendar.google.com/calendar/event?id=test' },
                { summary: 'Cancelado', status: 'cancelled', start: { date: '2026-10-21' } }
            ] }) };
        }
    };
    vm.runInNewContext(fs.readFileSync('comun/js/google_calendar_card.js', 'utf8'), context);
    head.children[0].onload();
    await new Promise(setImmediate);
    assert.equal(request.url,'/calendar-api');
    assert.equal(request.options.headers,undefined);
    assert.equal(controls.connect.hidden,true,'Carga automáticamente al abrir, sin pulsar Conectar');
    await oauth.callback({code:'one-time-code'});
    assert.equal(connectedCode,'one-time-code');
    const list = controls.events.children[0];
    assert.equal(list.children.length, 2);
    assert.equal(list.children[0].children[0].textContent, '<img src=x onerror=alert(1)>');
    assert.equal(list.children[0].children[0].href, undefined);
    assert.match(list.children[0].children[1].textContent, /19.*Todo el día/);
    assert.match(list.children[1].children[1].textContent, /10:00/);
    assert.equal(list.children[1].children[0].rel, 'noopener noreferrer');
    let finish;
    pending = new Promise(resolve => { finish = resolve; });
    const loading = controls.refresh.listeners.click();
    const disconnecting=controls.disconnect.listeners.click();
    finish({ ok: true, json: async () => ({ items: [{ summary: 'Private' }] }) });
    await loading;
    await disconnecting;
    assert.equal(revoked, true);
    assert.equal(controls.events.children.length, 0);
    assert.equal(controls.connect.hidden, false);
    assert.equal(controls.status.textContent, 'Calendario desconectado.');
    pending = Promise.resolve({ ok: false, status: 401,json:async()=>({message:'Google requiere reconectar.'}) });
    await controls.refresh.listeners.click();
    assert.match(controls.status.textContent, /reconectar/);
    assert.equal(controls.events.children.length, 0);
    console.log('Google Calendar: carga automática, código único, texto seguro, fechas, desconexión y autorización revocada OK.');
}
run().catch(error => { console.error(error); process.exitCode = 1; });
