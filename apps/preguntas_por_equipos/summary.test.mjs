import test from 'node:test';
import assert from 'node:assert/strict';
import { createSession, transition, validateConfig, restoreSession } from './engine.mjs?v=3';
import { answerSummary } from './summary.mjs';

const config = () => ({
    title: 'Reto de Bogotá', seconds: 30, points: 10, penalty: 3,
    teams: [{ name: 'Águilas' }, { name: 'Cóndores' }],
    questions: [
        { question: '¿Primera?', answer: 'Sí.\nCon explicación.' },
        { question: '¿Segunda?', answer: 'Segunda respuesta.', points: 20 },
        { question: '¿Tercera?', answer: 'Tercera respuesta.' }
    ]
});

test('exporta el banco en orden de edición con tildes y respuestas completas', () => {
    const source = validateConfig(config());
    const report = answerSummary(source);
    assert.ok(report.includes('Reto de Bogotá\r\n'));
    assert.ok(report.includes('1. ¿Primera?\r\nRespuesta esperada: Sí.\r\nCon explicación.'));
    assert.ok(report.indexOf('¿Primera?') < report.indexOf('¿Segunda?'));
    assert.ok(!report.includes('Resultado:'));
    assert.ok(!report.includes('MARCADOR'));
});

test('exporta el orden sorteado, los grupos, evaluaciones y puntos sin alterar la partida', () => {
    let session = createSession(config(), () => 0);
    session = transition(transition(transition(session, 'reveal'), 'correct'), 'next');
    session = transition(transition(transition(session, 'reveal'), 'wrong'), 'next');
    const before = structuredClone(session);
    const report = answerSummary(null, session);
    assert.match(report, /1\. ¿Segunda\?\r\nRespuesta esperada: Segunda respuesta\.\r\nGrupo: Águilas\r\nResultado: Correcta · Puntos del turno: 20/);
    assert.match(report, /2\. ¿Tercera\?[\s\S]*Grupo: Cóndores\r\nResultado: Incorrecta · Puntos del turno: -3/);
    assert.match(report, /3\. ¿Primera\?[\s\S]*Resultado: Pendiente · Puntos del turno: 0/);
    assert.ok(report.includes('MARCADOR PARCIAL\r\nÁguilas: 20 puntos · 1 acierto\r\nCóndores: -3 puntos · 0 aciertos'));
    assert.deepEqual(session, before);
    assert.equal(answerSummary(null, restoreSession(JSON.parse(JSON.stringify(session)))), report);
});

test('el resumen final usa el banco de la partida y distingue preguntas omitidas', () => {
    let session = createSession(config(), () => 0);
    while (!session.finished) session = transition(transition(session, 'skip'), 'next');
    const edited = validateConfig(config()); edited.questions[0].answer = 'Edición posterior';
    const report = answerSummary(edited, session);
    assert.ok(report.includes('CLASIFICACIÓN FINAL'));
    assert.equal((report.match(/Resultado: Omitida/g) || []).length, 3);
    assert.ok(!report.includes('Pendiente'));
    assert.ok(!report.includes('Edición posterior'));
    assert.ok(report.includes('Sí.\r\nCon explicación.'));
});
