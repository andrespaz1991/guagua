export const COLORS = ['#7165ed', '#129b8c', '#e09236', '#d85d8a', '#418de0', '#9b68ca', '#739b36', '#c66a47'];

const number = (value, min, max, label) => {
    const n = Number(value);
    if (!Number.isInteger(n) || n < min || n > max) throw new Error(`${label}: usa un número entero entre ${min} y ${max}.`);
    return n;
};
const text = (value, max, label) => {
    if (typeof value !== 'string' || !value.trim() || value.length > max) throw new Error(`${label}: completa el texto (máximo ${max} caracteres).`);
    return value.trim();
};

export function validateConfig(input) {
    if (!input || !Array.isArray(input.teams) || input.teams.length < 2 || input.teams.length > 12) throw new Error('Crea entre 2 y 12 grupos.');
    const names = new Set();
    const teams = input.teams.map((team, i) => {
        const name = text(team.name, 50, 'Nombre del grupo');
        if (names.has(name.toLocaleLowerCase('es'))) throw new Error('Los nombres de los grupos deben ser distintos.');
        names.add(name.toLocaleLowerCase('es'));
        return { id: `g${i + 1}`, name };
    });
    if (!Array.isArray(input.questions) || input.questions.length < 1 || input.questions.length > 200) throw new Error('Añade entre 1 y 200 preguntas.');
    const seconds = number(input.seconds, 5, 3600, 'Tiempo predeterminado');
    const points = number(input.points, 1, 1000, 'Puntos por acierto');
    const penalty = number(input.penalty, 0, 1000, 'Descuento por error');
    const questions = input.questions.map((q, i) => {
        const teamId = q.teamId || 'auto';
        if (teamId !== 'auto' && !teams.some(t => t.id === teamId)) throw new Error(`Pregunta ${i + 1}: el grupo asignado ya no existe.`);
        return { id: `q${i + 1}`, question: text(q.question, 2000, `Pregunta ${i + 1}`), answer: text(q.answer, 4000, `Respuesta ${i + 1}`), seconds: number(q.seconds === '' || q.seconds == null ? seconds : q.seconds, 5, 3600, `Tiempo de la pregunta ${i + 1}`), points: number(q.points === '' || q.points == null ? points : q.points, 1, 1000, `Puntos de la pregunta ${i + 1}`), teamId };
    });
    return { title: text(input.title, 100, 'Título'), seconds, points, penalty, teams, questions };
}

function buildSession(config, order) {
    return { config, index: 0, finished: false, turns: order.map((questionIndex, i) => {
        const q = config.questions[questionIndex];
        return { questionIndex, teamId: q.teamId === 'auto' ? config.teams[i % config.teams.length].id : q.teamId, remaining: q.seconds * 1000, deadline: null, revealed: false, result: null };
    }) };
}
export function createSession(input, random = Math.random) {
    const config = validateConfig(input);
    const order = config.questions.map((_, i) => i);
    // Fisher–Yates: cada pregunta aparece una vez, con la misma probabilidad en cada posición.
    for (let i = order.length - 1; i > 0; i--) {
        const j = Math.floor(random() * (i + 1));
        [order[i], order[j]] = [order[j], order[i]];
    }
    return buildSession(config, order);
}
export function questionAt(session, index = session.index) {
    return session.config.questions[session.turns[index].questionIndex ?? index];
}
export function remaining(turn, now = Date.now()) {
    return Math.max(0, turn.deadline === null ? turn.remaining : turn.deadline - now);
}
export function scores(session) {
    return session.config.teams.map(team => ({ ...team, score: session.turns.reduce((sum, turn, i) => sum + (turn.teamId !== team.id ? 0 : turn.result === 'correct' ? questionAt(session, i).points : turn.result === 'wrong' ? -session.config.penalty : 0), 0), correct: session.turns.filter(t => t.teamId === team.id && t.result === 'correct').length }));
}
export function transition(previous, action, now = Date.now()) {
    const s = structuredClone(previous);
    const turn = s.turns[s.index];
    if (s.finished) return previous;
    const pause = () => { turn.remaining = remaining(turn, now); turn.deadline = null; };
    switch (typeof action === 'string' ? action : action?.type) {
        case 'timer-reset': case 'set-time':
            if (turn.result) return previous;
            if (action?.type === 'set-time') questionAt(s).seconds = number(action.seconds, 5, 3600, 'Tiempo de esta pregunta');
            turn.remaining = questionAt(s).seconds * 1000;
            turn.deadline = now + turn.remaining;
            turn.revealed = false;
            break;
        case 'timer':
            if (turn.result || turn.revealed || remaining(turn, now) === 0) return previous;
            if (turn.deadline !== null) pause();
            else turn.deadline = now + turn.remaining;
            break;
        case 'expire':
            if (turn.deadline === null || remaining(turn, now) > 0) return previous;
            pause();
            break;
        case 'reveal':
            pause(); turn.revealed = true;
            break;
        case 'correct': case 'wrong': case 'skip':
            if (turn.result || (action !== 'skip' && !turn.revealed)) return previous;
            pause(); turn.result = action;
            break;
        case 'undo':
            if (!turn.result) return previous;
            turn.result = null;
            break;
        case 'next':
            if (!turn.result) return previous;
            if (s.index === s.turns.length - 1) s.finished = true;
            else s.index++;
            break;
        default: return previous;
    }
    return s;
}

export function restoreSession(value) {
    // Reconstruir el marcador a partir de resultados válidos; nunca confiar en puntos importados.
    const config = validateConfig(value.config);
    if (!Array.isArray(value.turns) || value.turns.length !== config.questions.length) throw new Error('La partida guardada está incompleta.');
    // Los respaldos anteriores no incluían índices: conservan su orden original.
    const order = value.turns.map((turn, i) => {
        const index = turn.questionIndex === undefined ? i : turn.questionIndex;
        if (!Number.isInteger(index) || index < 0 || index >= config.questions.length) throw new Error('El orden guardado de las preguntas no es válido.');
        return index;
    });
    if (new Set(order).size !== order.length) throw new Error('El orden guardado contiene preguntas repetidas.');
    const base = buildSession(config, order);
    base.index = number(value.index, 0, base.turns.length - 1, 'Turno guardado');
    base.turns = base.turns.map((turn, i) => {
        const saved = value.turns[i];
        if (![null, 'correct', 'wrong', 'skip'].includes(saved.result) || typeof saved.revealed !== 'boolean' || saved.teamId !== turn.teamId) throw new Error('La partida guardada no es válida.');
        if (['correct', 'wrong'].includes(saved.result) && !saved.revealed) throw new Error('Una evaluación guardada no tiene respuesta revelada.');
        if (i < base.index && !saved.result) throw new Error('La partida contiene turnos pendientes fuera de orden.');
        if (i > base.index && (saved.result || saved.revealed)) throw new Error('La partida contiene turnos futuros alterados.');
        const time = saved.deadline === null ? saved.remaining : Number(saved.deadline) - Date.now();
        if (!Number.isFinite(time) || !Number.isFinite(saved.remaining)) throw new Error('El tiempo guardado no es válido.');
        return { ...turn, remaining: Math.max(0, Math.min(config.questions[turn.questionIndex].seconds * 1000, time)), revealed: saved.revealed, result: saved.result, deadline: null };
    });
    base.finished = value.finished === true && base.turns.every(t => t.result);
    return base;
}
