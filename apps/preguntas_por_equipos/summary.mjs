import { questionAt, scores } from './engine.mjs?v=3';

export function answerSummary(config, session = null) {
    const source = session ? session.config : config;
    const lines = [
        'RESUMEN DE RESPUESTAS', source.title, '',
        session ? (session.finished ? 'Partida finalizada' : 'Partida en curso · resultados parciales') : 'Banco de preguntas · orden de edición',
        `${source.questions.length} preguntas`,
        'Se incluyen las respuestas esperadas del banco de preguntas.', ''
    ];
    const questions = session ? session.turns.map((_, i) => questionAt(session, i)) : source.questions;
    questions.forEach((question, i) => {
        lines.push(`${i + 1}. ${question.question}`, `Respuesta esperada: ${question.answer}`);
        if (session) {
            const turn = session.turns[i];
            const team = source.teams.find(t => t.id === turn.teamId);
            const result = { correct: 'Correcta', wrong: 'Incorrecta', skip: 'Omitida' }[turn.result] || 'Pendiente';
            const points = turn.result === 'correct' ? question.points : turn.result === 'wrong' ? -source.penalty : 0;
            lines.push(`Grupo: ${team.name}`, `Resultado: ${result} · Puntos del turno: ${points}`);
        }
        lines.push('');
    });
    if (session) {
        lines.push(session.finished ? 'CLASIFICACIÓN FINAL' : 'MARCADOR PARCIAL');
        scores(session).sort((a, b) => b.score - a.score).forEach(team => {
            lines.push(`${team.name}: ${team.score} puntos · ${team.correct} ${team.correct === 1 ? 'acierto' : 'aciertos'}`);
        });
        lines.push('');
    }
    return lines.join('\n').replace(/\r?\n/g, '\r\n');
}
