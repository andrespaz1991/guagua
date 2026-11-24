import test from 'node:test';
import assert from 'node:assert/strict';
import { createSession as createRandomSession, questionAt, transition, scores, remaining, restoreSession, validateConfig } from './engine.mjs';

// Las pruebas previas usan un sorteo fijo que conserva el orden del ejemplo.
const createSession = input => createRandomSession(input, () => 0.999999);

const config = () => ({ title: 'Prueba', seconds: 30, points: 10, penalty: 3, teams: [{ name: 'A' }, { name: 'B' }], questions: [{ question: 'Uno', answer: 'Respuesta 1' }, { question: 'Dos', answer: 'Respuesta 2', points: 20, teamId: 'g1' }, { question: 'Tres', answer: 'Respuesta 3' }] });
test('sortea todas las preguntas una vez sin alterar el banco ni sus datos', () => {
    const input = config();
    input.questions[1].seconds = 45;
    const original = structuredClone(input);
    const s = createRandomSession(input, () => 0);
    assert.deepEqual(s.turns.map(t => t.questionIndex), [1, 2, 0]);
    assert.deepEqual(input, original);
    assert.deepEqual(s.config, validateConfig(input));
    assert.equal(questionAt(s).question, 'Dos');
    assert.equal(questionAt(s).answer, 'Respuesta 2');
    assert.equal(s.turns[0].remaining, 45000);
    assert.deepEqual(s.turns.map(t => t.teamId), ['g1', 'g2', 'g1']);
    const graded = transition(transition(s, 'reveal'), 'correct');
    assert.equal(scores(graded)[0].score, 20);
    const timed = transition(s, { type: 'set-time', seconds: 60 }, 1000);
    assert.equal(timed.config.questions[1].seconds, 60);
    assert.equal(timed.config.questions[0].seconds, 30);
    assert.equal(timed.turns[0].deadline, 61000);
});
test('el sorteo permite las seis permutaciones de tres preguntas y una sola pregunta', () => {
    const orders = new Set();
    for (let first = 0; first < 3; first++) {
        for (let second = 0; second < 2; second++) {
            const draws = [(first + 0.5) / 3, (second + 0.5) / 2];
            const s = createRandomSession(config(), () => draws.shift());
            const order = s.turns.map(t => t.questionIndex);
            assert.deepEqual([...order].sort(), [0, 1, 2]);
            orders.add(order.join(','));
        }
    }
    assert.equal(orders.size, 6);
    const single = config(); single.questions.length = 1;
    assert.equal(questionAt(createRandomSession(single)).question, 'Uno');
});
test('restaurar conserva el sorteo, los puntos y el turno sin volver a mezclar', () => {
    let s = createRandomSession(config(), () => 0);
    s = transition(transition(transition(s, 'reveal'), 'correct'), 'next');
    const restored = restoreSession(JSON.parse(JSON.stringify(s)));
    assert.deepEqual(restored, s);
    assert.equal(questionAt(restored).question, 'Tres');
    assert.equal(scores(restored)[0].score, 20);
    assert.deepEqual(restoreSession(JSON.parse(JSON.stringify(restored))), restored);
});
test('restaura partidas anteriores y rechaza índices repetidos o fuera de rango', () => {
    const legacy = createSession(config());
    legacy.turns.forEach(t => delete t.questionIndex);
    assert.deepEqual(restoreSession(legacy).turns.map(t => t.questionIndex), [0, 1, 2]);
    for (const invalid of [-1, 3, 0.5, null, 'abc']) {
        const s = createSession(config()); s.turns[0].questionIndex = invalid;
        assert.throws(() => restoreSession(s));
    }
    const duplicate = createSession(config()); duplicate.turns[1].questionIndex = 0;
    assert.throws(() => restoreSession(duplicate));
});
test('turnos automáticos y asignación explícita', () => {
    const s = createSession(config());
    assert.deepEqual(s.turns.map(t => t.teamId), ['g1', 'g1', 'g1']);
    const c = config(); c.questions[1].teamId = 'auto';
    assert.deepEqual(createSession(c).turns.map(t => t.teamId), ['g1', 'g2', 'g1']);
});
test('reloj por fecha límite, pausa y revelación', () => {
    let s = transition(createSession(config()), 'timer', 1000);
    assert.equal(remaining(s.turns[0], 6000), 25000);
    s = transition(s, 'timer', 6000);
    assert.equal(s.turns[0].remaining, 25000);
    assert.equal(s.turns[0].deadline, null);
    s = transition(s, 'timer', 9000);
    s = transition(s, 'reveal', 14000);
    assert.equal(s.turns[0].remaining, 20000);
    assert.equal(s.turns[0].revealed, true);
    assert.equal(transition(s, 'timer', 15000), s);
});
test('agotarse no revela ni asigna puntos', () => {
    let s = transition(createSession(config()), 'timer', 0);
    s = transition(s, 'expire', 31000);
    assert.equal(s.turns[0].remaining, 0);
    assert.equal(s.turns[0].revealed, false);
    assert.equal(s.turns[0].result, null);
    assert.equal(scores(s)[0].score, 0);
});
test('evaluar requiere revelar, es idempotente y permite deshacer', () => {
    let s = createSession(config());
    assert.equal(transition(s, 'correct'), s);
    assert.equal(transition(s, 'next'), s);
    s = transition(transition(s, 'reveal'), 'correct');
    assert.equal(scores(s)[0].score, 10);
    assert.equal(transition(s, 'correct'), s);
    assert.equal(transition(s, 'wrong'), s);
    s = transition(s, 'undo');
    assert.equal(scores(s)[0].score, 0);
    s = transition(s, 'wrong');
    assert.equal(scores(s)[0].score, -3);
});
test('final y valores específicos por pregunta', () => {
    let s = createSession(config());
    s = transition(transition(transition(s, 'reveal'), 'correct'), 'next');
    s = transition(transition(transition(s, 'reveal'), 'correct'), 'next');
    s = transition(transition(s, 'skip'), 'next');
    assert.equal(s.finished, true);
    assert.equal(scores(s)[0].score, 30);
    assert.equal(scores(s)[0].correct, 2);
    assert.equal(transition(s, 'correct'), s);
});
test('restaurar pausa el reloj y reconstruye los puntos', () => {
    let s = transition(transition(createSession(config()), 'reveal'), 'correct');
    s = transition(s, 'next');
    s = transition(s, 'timer', Date.now());
    s.score = 999;
    const restored = restoreSession(JSON.parse(JSON.stringify(s)));
    assert.equal(restored.turns[1].deadline, null);
    assert.ok(restored.turns[1].remaining <= 30000);
    assert.equal(scores(restored)[0].score, 10);
});
test('rechaza configuraciones y partidas inválidas', () => {
    for (const patch of [{ teams: [{ name: 'A' }] }, { teams: [{ name: 'A' }, { name: 'a' }] }, { seconds: -5 }, { points: 0 }, { questions: [] }, { questions: [{ question: 'Q', answer: '' }] }, { questions: [{ question: 'Q', answer: 'A', teamId: 'g9' }] }]) assert.throws(() => validateConfig({ ...config(), ...patch }));
    const broken = createSession(config()); broken.turns[0].result = 'inventado';
    assert.throws(() => restoreSession(broken));
    const future = createSession(config()); future.turns[2].result = 'correct';
    assert.throws(() => restoreSession(future));
});

test('reiniciar permite otro intento tras agotarse el tiempo o revelar', () => {
    let s = transition(createSession(config()), 'timer', 0);
    s = transition(s, 'expire', 31000);
    s = transition(s, 'reveal', 32000);
    s = transition(s, 'timer-reset', 40000);
    assert.equal(s.turns[0].revealed, false);
    assert.equal(s.turns[0].remaining, 30000);
    assert.equal(s.turns[0].deadline, 70000);
    assert.equal(remaining(s.turns[0], 45000), 25000);
    assert.equal(scores(s)[0].score, 0);
});

test('cambiar tiempo reinicia solo la pregunta actual y se conserva al restaurar', () => {
    let s = transition(createSession(config()), { type: 'set-time', seconds: '45' }, Date.now());
    assert.equal(s.config.questions[0].seconds, 45);
    assert.equal(s.config.questions[1].seconds, 30);
    assert.equal(s.turns[0].remaining, 45000);
    const restored = restoreSession(JSON.parse(JSON.stringify(s)));
    assert.equal(restored.config.questions[0].seconds, 45);
    assert.ok(restored.turns[0].remaining <= 45000);
    assert.equal(restored.turns[0].deadline, null);
    for (const seconds of [0, 4, 3601, 'abc', 2.5]) assert.throws(() => transition(s, { type: 'set-time', seconds }));
});

test('reiniciar un turno evaluado requiere deshacerlo y no duplica puntos', () => {
    let s = transition(transition(createSession(config()), 'reveal'), 'correct');
    assert.equal(transition(s, 'timer-reset'), s);
    assert.equal(transition(s, { type: 'set-time', seconds: 60 }), s);
    assert.equal(scores(s)[0].score, 10);
    s = transition(s, 'undo');
    s = transition(s, 'timer-reset', 1000);
    assert.equal(s.turns[0].revealed, false);
    assert.equal(scores(s)[0].score, 0);
    s = transition(transition(s, 'reveal', 2000), 'correct', 2000);
    assert.equal(scores(s)[0].score, 10);
});
