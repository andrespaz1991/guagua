const assert = require('node:assert/strict');
const { loadHistoryInPage } = require('../apps/diario/extraction-history.cjs');

const message = (id, t) => ({ id: { toString: () => id }, t, type: 'ptt', mimetype: 'audio/ogg' });
async function scenario({ initial = [], recent, earlier, noEarlier = false, cap = 2000 }) {
    let models = initial;
    const state = { noEarlierMsgs: noEarlier };
    let recentCalls = 0, earlierCalls = 0;
    const chat = { msgs: { getModelsArray: () => models, msgLoadState: state } };
    const loader = {
        loadRecentMsgs: async () => { recentCalls++; return recent?.(value => { models = value; }, state); },
        loadEarlierMsgs: async () => { earlierCalls++; return earlier?.(value => { models = value; }, state, earlierCalls); }
    };
    global.window = {
        require: name => name === 'WAWebCollections' ? { Chat: { get: () => chat } } : loader,
        WWebJS: { getMessageModel: m => ({ id: {}, timestamp: m.t, type: m.type }) }
    };
    const result = await loadHistoryInPage('diario', 100, 200, cap, 1000, 0);
    return { ...result, recentCalls, earlierCalls };
}
(async () => {
    // Lazy chat: recent load hydrates one message; older load mutates the store
    // without returning an array. Both must be collected before range filtering.
    let result = await scenario({
        recent: set => set([message('last', 190)]),
        earlier: set => { set([message('old', 90), message('october', 150), message('last', 190)]); }
    });
    assert.deepEqual(result.messages.map(m => m.timestamp), [190, 150]);
    assert.equal(result.warning, null);
    assert.equal(result.recentCalls, 1);
    assert.equal(result.earlierCalls, 1);
    assert.equal(result.messages[1].id._serialized, 'october');
    result = await scenario({
        initial: [message('last', 190)],
        earlier: (set, state, call) => call === 1 ? [] : (set([message('old', 90), message('october', 150)]), [])
    });
    assert.equal(result.messages.length, 2);
    assert.equal(result.earlierCalls, 2);
    assert.equal(result.warning, null);
    result = await scenario({ initial: [message('last', 190)], earlier: () => [] });
    assert.match(result.warning, /incompleta/);
    assert.equal(result.earlierCalls, 3);
    result = await scenario({ initial: [message('last', 190)], noEarlier: true });
    assert.equal(result.warning, null);
    assert.equal(result.earlierCalls, 0);
    result = await scenario({ initial: [message('a', 190), message('b', 180)], cap: 2 });
    assert.match(result.warning, /límite/);
    result = await scenario({ initial: [message('last', 190)], earlier: () => { throw new Error('offline'); } });
    assert.equal(result.warning, 'offline');
    assert.equal(result.messages.length, 1);
    delete global.window;
    console.log('Diario: hidratación inicial, colecciones sin retorno, reintentos, deduplicación, límites y resultados parciales verificados.');
})().catch(error => { console.error(error); process.exitCode = 1; });
