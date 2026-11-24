import { COLORS, createSession, questionAt, validateConfig, restoreSession, scores, remaining, transition } from './engine.mjs?v=3';
import { answerSummary } from './summary.mjs?v=1';
import { derechosHumanos as sample } from './derechos-humanos.mjs?v=1';
import { seguridadInformatica } from './seguridad-informatica.mjs?v=1';

const $ = id => document.getElementById(id);
const key = `guagua-team-quiz-v1:${document.body.dataset.storageScope}`;
let session = null;
let storedSession = null;
let draft = structuredClone(sample);
let draftTimer;
const announce = message => { $('notice').textContent = message; };
function confirmAction(message) {
    const dialog = $('confirm-dialog');
    if (dialog.open) return Promise.resolve(false);
    if (session && !session.finished && session.turns[session.index].deadline !== null) {
        session = transition(session, 'timer');
        save();
        if (!$('play').hidden) renderPlay();
    }
    $('confirm-message').textContent = message;
    dialog.returnValue = 'cancel';
    return new Promise(resolve => {
        dialog.addEventListener('close', () => resolve(dialog.returnValue === 'confirm'), { once: true });
        dialog.showModal();
    });
}
const colorFor = teamId => COLORS[Number(teamId.slice(1)) - 1] || COLORS[0];

function save() {
    try {
        localStorage.setItem(key, JSON.stringify({ version: 1, config: draft, session: session || storedSession }));
        $('save-state').textContent = 'Guardado en este navegador';
    } catch {
        $('save-state').textContent = 'No se pudo guardar';
        announce('El navegador no permite guardar la partida. Exporta un respaldo antes de cerrar esta página.');
    }
}
function readDraft() {
    return {
        title: $('game-title').value, seconds: $('seconds').value, points: $('points').value, penalty: $('penalty').value,
        teams: $('teams').value.split('\n').map(name => name.trim()).filter(Boolean).map((name, i) => ({ id: `g${i + 1}`, name })),
        questions: [...document.querySelectorAll('.question-edit')].map(row => ({ question: row.querySelector('.question-input').value, answer: row.querySelector('.answer-input').value, seconds: row.querySelector('.question-seconds').value, points: row.querySelector('.question-points').value, teamId: row.querySelector('.question-team').value }))
    };
}
function updateTeams() {
    const teams = $('teams').value.split('\n').map(t => t.trim()).filter(Boolean);
    document.querySelectorAll('.question-team').forEach(select => {
        const value = select.value || 'auto';
        select.replaceChildren(new Option('Automático · por turnos', 'auto'));
        teams.forEach((name, i) => select.add(new Option(name, `g${i + 1}`)));
        if (value !== 'auto' && ![...select.options].some(o => o.value === value)) select.add(new Option('Grupo eliminado · reasigna esta pregunta', value));
        select.value = value;
    });
}
function updateTimeHints() {
    const general = $('seconds').value || '—';
    document.querySelectorAll('.question-edit').forEach(row => {
        const input = row.querySelector('.question-seconds');
        input.placeholder = `${general} s`;
        row.querySelector('.question-time-hint').textContent = input.value ? `Esta pregunta tiene ${input.value} segundos.` : `Sin tiempo propio: usará el tiempo general (${general} s).`;
    });
}
function questionNumbers() {
    const rows = [...document.querySelectorAll('.question-edit')];
    rows.forEach((row, i) => {
        row.querySelector('.question-number').textContent = `Pregunta ${String(i + 1).padStart(2, '0')}`;
        row.querySelector('.remove-question').setAttribute('aria-label', `Eliminar pregunta ${i + 1}`);
        row.querySelector('.remove-question').disabled = rows.length === 1;
    });
    $('question-count').textContent = `${rows.length} preguntas`;
}
function addQuestion(q = {}, focus = false) {
    const row = $('question-template').content.firstElementChild.cloneNode(true);
    row.querySelector('.question-input').value = q.question || '';
    row.querySelector('.answer-input').value = q.answer || '';
    row.querySelector('.question-seconds').value = q.seconds || '';
    row.querySelector('.question-points').value = q.points || '';
    row.querySelector('.question-team').add(new Option('Automático · por turnos', 'auto'));
    $('question-list').append(row);
    updateTeams();
    if (q.teamId && q.teamId !== 'auto') {
        const select = row.querySelector('.question-team');
        if (![...select.options].some(o => o.value === q.teamId)) select.add(new Option('Grupo eliminado · reasigna esta pregunta', q.teamId));
        select.value = q.teamId;
    }
    questionNumbers(); updateTimeHints();
    if (focus) row.querySelector('textarea').focus();
}
function fillSetup(config) {
    $('game-title').value = config.title || '';
    $('teams').value = (config.teams || []).map(t => t.name).join('\n');
    $('seconds').value = config.seconds;
    $('points').value = config.points;
    $('penalty').value = config.penalty;
    $('question-list').replaceChildren();
    (config.questions?.length ? config.questions : [{}]).forEach(q => addQuestion(q));
}
function show(view) {
    ['setup', 'play', 'results'].forEach(id => { $(id).hidden = id !== view; });
    announce('');
    if (view !== 'setup') window.scrollTo({ top: 0 });
}
function resultLabel(result) {
    return { correct: 'Correcta', wrong: 'Incorrecta', skip: 'Omitida' }[result] || 'Pendiente';
}
function renderScores(container, final = false) {
    const ranked = scores(session).sort((a, b) => b.score - a.score);
    container.replaceChildren();
    ranked.forEach((team, index) => {
        const row = document.createElement('li');
        row.style.setProperty('--team-color', colorFor(team.id));
        row.className = !final && session.turns[session.index].teamId === team.id ? 'score-row active' : 'score-row';
        const rank = document.createElement('span'); rank.className = 'rank'; rank.textContent = String(index + 1).padStart(2, '0');
        const info = document.createElement('div');
        const name = document.createElement('strong'); name.textContent = team.name;
        const sub = document.createElement('small'); sub.textContent = `${team.correct} ${team.correct === 1 ? 'acierto' : 'aciertos'}`;
        info.append(name, sub);
        const points = document.createElement('span'); points.className = 'team-score'; points.textContent = String(team.score);
        const unit = document.createElement('small'); unit.textContent = 'pts'; points.append(unit);
        row.append(rank, info, points); container.append(row);
    });
}
function renderHistory() {
    $('history').replaceChildren();
    const turns = session.turns.map((turn, i) => ({ ...turn, index: i })).filter(t => t.result).slice(-5).reverse();
    if (!turns.length) {
        const li = document.createElement('li'); li.textContent = 'Los turnos evaluados aparecerán aquí.'; $('history').append(li);
    }
    turns.forEach(turn => {
        const li = document.createElement('li');
        const label = document.createElement('strong'); label.textContent = `P${turn.index + 1} · ${session.config.teams.find(t => t.id === turn.teamId).name}`;
        const detail = document.createElement('span'); detail.textContent = resultLabel(turn.result); detail.className = `history-${turn.result}`;
        li.append(label, detail); $('history').append(li);
    });
}
function renderTime() {
    if (!session || session.finished || $('play').hidden) return;
    const turn = session.turns[session.index];
    const q = questionAt(session);
    const ms = remaining(turn);
    const seconds = Math.ceil(ms / 1000);
    $('timer').textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    $('timer-progress').style.width = `${Math.min(100, ms / (q.seconds * 1000) * 100)}%`;
    $('timer-progress').classList.toggle('urgent', seconds <= 5);
    $('timer-button').textContent = turn.deadline !== null ? 'Ⅱ Pausar tiempo' : turn.remaining < q.seconds * 1000 ? '▶ Reanudar tiempo' : '▶ Iniciar tiempo';
    $('timer-button').disabled = !!turn.result || turn.revealed || ms === 0;
    $('time-state').textContent = turn.result ? 'Turno registrado' : ms === 0 ? 'Tiempo agotado · revela y evalúa la respuesta' : turn.revealed ? 'Respuesta revelada · tiempo detenido' : turn.deadline !== null ? 'El tiempo está corriendo' : turn.remaining < q.seconds * 1000 ? 'Temporizador en pausa' : 'Listos para responder';
}
function renderPlay() {
    if (session.finished) { renderResults(); return; }
    const q = questionAt(session);
    const turn = session.turns[session.index];
    const team = session.config.teams.find(t => t.id === turn.teamId);
    $('play').style.setProperty('--current-team', colorFor(team.id));
    $('live-question-seconds').value = q.seconds;
    $('live-question-seconds').disabled = !!turn.result;
    $('apply-question-time').disabled = !!turn.result;
    $('reset-timer').disabled = !!turn.result;
    $('live-time-help').textContent = turn.result ? 'Para repetir el intento, deshaz primero la evaluación. Los puntos se actualizarán al volver a evaluar.' : 'Aplicar o reiniciar comienza un nuevo intento y vuelve a ocultar la respuesta, sin modificar los puntos.';
    $('play-title').textContent = session.config.title;
    $('round-heading').textContent = `Ahora juega ${team.name}`;
    $('question-progress').textContent = `PREGUNTA ${String(session.index + 1).padStart(2, '0')} / ${String(session.turns.length).padStart(2, '0')}`;
    $('question-points').textContent = `+${q.points} puntos en juego`;
    $('current-team').textContent = team.name;
    $('question-text').textContent = q.question;
    $('answer-box').hidden = !turn.revealed;
    // La respuesta no se inserta en la diapositiva mientras permanece oculta.
    $('answer-text').textContent = turn.revealed ? q.answer : '';
    $('reveal').hidden = turn.revealed || !!turn.result;
    $('grade-controls').hidden = !turn.revealed || !!turn.result;
    $('skip').hidden = !!turn.result;
    $('undo').hidden = !turn.result;
    $('next').disabled = !turn.result;
    $('next').textContent = session.index === session.turns.length - 1 ? 'Ver resultados →' : 'Siguiente pregunta →';
    const delta = turn.result === 'correct' ? q.points : turn.result === 'wrong' ? -session.config.penalty : 0;
    $('turn-result').textContent = turn.result ? `${resultLabel(turn.result)} · ${delta > 0 ? '+' : ''}${delta} puntos para ${team.name}` : '';
    $('turn-result').className = `result-message ${turn.result || ''}`;
    renderTime(); renderScores($('scores')); renderHistory();
}
function renderResults() {
    show('results');
    const ranked = scores(session).sort((a, b) => b.score - a.score);
    const winners = ranked.filter(t => t.score === ranked[0].score);
    $('winner').textContent = winners.length > 1 ? `¡Empate entre ${winners.map(t => t.name).join(', ')}!` : `¡${winners[0].name} gana el reto!`;
    $('results-caption').textContent = `${session.config.title} · ${session.turns.length} preguntas · ${ranked[0].score} puntos en la primera posición`;
    renderScores($('final-scores'), true);
}
function act(action) {
    if (!session) return;
    announce('');
    const oldIndex = session.index;
    try {
        const previousQuestion = questionAt(session);
        const updated = transition(session, action);
        if (updated !== session && action?.type === 'set-time') {
            const questionIndex = session.turns[session.index].questionIndex;
            const questionDraft = draft.questions[questionIndex];
            if (questionDraft && questionDraft.question.trim() === previousQuestion.question && questionDraft.answer.trim() === previousQuestion.answer) {
                questionDraft.seconds = questionAt(updated).seconds;
                const row = document.querySelectorAll('.question-edit')[questionIndex];
                if (row) row.querySelector('.question-seconds').value = questionDraft.seconds;
                updateTimeHints();
            }
        }
        session = updated;
    } catch (error) { announce(error.message); return; }
    save(); renderPlay();
    if (session.index !== oldIndex) $('round-heading').focus({ preventScroll: true });
}
function start(config) {
    clearTimeout(draftTimer);
    session = createSession(config); storedSession = null;
    show('play'); save(); renderPlay(); $('round-heading').focus({ preventScroll: true });
}
function offerResume() {
    const old = $('resume-banner'); if (old) old.remove();
    const saved = session || storedSession;
    if (!saved) return;
    const banner = document.createElement('div'); banner.id = 'resume-banner'; banner.className = 'resume-banner';
    const text = document.createElement('span'); text.textContent = saved.finished ? `Resultados guardados: ${saved.config.title}` : `Partida en pausa: ${saved.config.title} · pregunta ${saved.index + 1} de ${saved.turns.length}`;
    const button = document.createElement('button'); button.className = 'button primary'; button.type = 'button'; button.textContent = saved.finished ? 'Ver resultados' : 'Continuar partida';
    button.onclick = () => { session = saved; storedSession = null; show(session.finished ? 'results' : 'play'); renderPlay(); };
    banner.append(text, button); $('setup').prepend(banner);
}
function pauseForSetup() {
    if (session && !session.finished && session.turns[session.index].deadline !== null) session = transition(session, 'timer');
    show('setup'); save(); offerResume(); window.scrollTo({ top: 0 });
}
function download(name, content, mime) {
    const url = URL.createObjectURL(new Blob([content], { type: mime }));
    const a = document.createElement('a'); a.href = url; a.download = name; a.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

$('setup-form').addEventListener('input', () => {
    updateTimeHints();
    clearTimeout(draftTimer);
    draftTimer = setTimeout(() => { draft = readDraft(); save(); }, 300);
});
$('teams').addEventListener('input', updateTeams);
$('add-question').onclick = () => {
    if (document.querySelectorAll('.question-edit').length >= 200) { announce('El límite es de 200 preguntas.'); return; }
    addQuestion({}, true); draft = readDraft(); save();
};
$('question-list').addEventListener('click', async event => {
    const button = event.target.closest('.remove-question');
    if (!button) return;
    const row = button.closest('.question-edit');
    if ((row.querySelector('.question-input').value || row.querySelector('.answer-input').value) && !await confirmAction('¿Eliminar esta pregunta del banco?')) return;
    row.remove(); questionNumbers(); draft = readDraft(); save();
});
$('setup-form').onsubmit = async event => {
    event.preventDefault();
    try {
        draft = readDraft();
        const config = validateConfig(draft);
        if ((session || storedSession) && !await confirmAction('Se iniciará una partida nueva con el marcador en cero. ¿Continuar?')) return;
        start(config);
    } catch (error) { announce(error.message); $('notice').scrollIntoView({ block: 'center' }); }
};
$('timer-button').onclick = () => act('timer');
$('reset-timer').onclick = () => act('timer-reset');
$('question-time-form').onsubmit = event => {
    event.preventDefault();
    act({ type: 'set-time', seconds: $('live-question-seconds').value });
};
$('reveal').onclick = () => act('reveal');
document.querySelectorAll('[data-grade]').forEach(button => { button.onclick = () => act(button.dataset.grade); });
$('skip').onclick = async () => { if (await confirmAction('¿Omitir esta pregunta sin asignar ni descontar puntos?')) act('skip'); };
$('undo').onclick = () => act('undo');
$('next').onclick = () => act('next');
$('return-setup').onclick = pauseForSetup;
$('edit-game').onclick = pauseForSetup;
$('restart').onclick = async () => { if (await confirmAction('¿Iniciar esta partida con un nuevo orden al azar y poner los puntos en cero?')) start(session.config); };
$('play-again').onclick = () => start(session.config);
document.querySelectorAll('[data-export-summary]').forEach(button => {
    button.onclick = () => {
        try {
            // La preparación exporta lo que se está editando; la partida usa su propio banco y sorteo.
            const current = button.dataset.exportSummary === 'session' ? session : null;
            if (button.dataset.exportSummary === 'session' && !current) return;
            const config = current ? current.config : validateConfig(readDraft());
            download('resumen-respuestas.txt', '\uFEFF' + answerSummary(config, current), 'text/plain;charset=utf-8');
            announce('Resumen de respuestas descargado en TXT.');
        } catch (error) { announce(error.message); }
    };
});
$('export-button').onclick = () => {
    try {
        draft = readDraft(); const config = validateConfig(draft);
        download('reto-por-equipos.json', JSON.stringify({ version: 1, config, session: session || storedSession }, null, 2), 'application/json');
        save(); announce('Respaldo descargado: preguntas, grupos y progreso de la partida.');
    } catch (error) { announce(error.message); }
};
$('import-button').onclick = () => $('import-file').click();
async function loadQuestionBank(bank, label, message) {
    if (!await confirmAction(`¿Cargar ${bank.questions.length} preguntas de ${label}? Se reemplazarán las preguntas y se borrará el progreso de la partida. Se conservarán tus equipos y reglas. Puedes cancelar y exportar un respaldo primero.`)) return;
    clearTimeout(draftTimer);
    draft = { ...readDraft(), title: bank.title, questions: structuredClone(bank.questions) };
    session = null; storedSession = null;
    fillSetup(draft); save(); offerResume();
    announce(message);
}
$('load-human-rights').onclick = () => loadQuestionBank(sample, 'derechos humanos', '30 casos cargados. Cada respuesta identifica el derecho o principio y su artículo de la DUDH.');
$('load-cybersecurity').onclick = () => loadQuestionBank(seguridadInformatica, 'seguridad informática y protección de jóvenes', '36 preguntas cargadas, con respuestas concretas y tiempos de 45 o 60 segundos.');
$('import-file').onchange = async event => {
    const file = event.target.files[0]; if (!file) return;
    try {
        if (file.size > 1024 * 1024) throw new Error('El respaldo no puede superar 1 MB.');
        const data = JSON.parse(await file.text());
        if (data.version !== 1) throw new Error('El formato del respaldo no es compatible.');
        const config = validateConfig(data.config);
        const restored = data.session ? restoreSession(data.session) : null;
        if (!await confirmAction('¿Reemplazar las preguntas y la partida guardada por este respaldo?')) return;
        clearTimeout(draftTimer); draft = config; session = null; storedSession = restored;
        fillSetup(draft); save(); offerResume(); announce('Respaldo importado. Revisa las preguntas o continúa la partida guardada.');
    } catch (error) { announce(`No se pudo importar: ${error.message}`); }
    finally { event.target.value = ''; }
};
$('download-results').onclick = () => {
    const cell = value => {
        const raw = typeof value === 'number' ? String(value) : String(value).replace(/^[=+\-@\t\r]/, "'$&");
        return `"${raw.replaceAll('"', '""')}"`;
    };
    const rows = [['Grupo', 'Puntos', 'Aciertos'], ...scores(session).sort((a, b) => b.score - a.score).map(t => [t.name, t.score, t.correct]), [], ['Pregunta', 'Grupo', 'Resultado', 'Puntos del turno'], ...session.turns.map((t, i) => [i + 1, session.config.teams.find(team => team.id === t.teamId).name, resultLabel(t.result), t.result === 'correct' ? questionAt(session, i).points : t.result === 'wrong' ? -session.config.penalty : 0])];
    download('resultados-reto.csv', '\uFEFF' + rows.map(row => row.map(cell).join(';')).join('\r\n'), 'text/csv;charset=utf-8');
};
if (document.fullscreenEnabled) {
    $('fullscreen').hidden = false;
    $('fullscreen').onclick = async () => {
        try { if (document.fullscreenElement) await document.exitFullscreen(); else await document.documentElement.requestFullscreen(); }
        catch { announce('No se pudo activar la pantalla completa en este navegador.'); }
    };
    document.addEventListener('fullscreenchange', () => { $('fullscreen').textContent = document.fullscreenElement ? 'Salir de pantalla completa' : 'Pantalla completa'; });
}
setInterval(() => {
    if (!session || session.finished || $('play').hidden) return;
    const turn = session.turns[session.index];
    if (turn.deadline !== null && remaining(turn) === 0) { act('expire'); announce('Tiempo agotado. Puedes revelar la respuesta y evaluar al grupo.'); }
    else renderTime();
}, 200);
document.addEventListener('keydown', event => {
    if ($('play').hidden || $('confirm-dialog').open || event.repeat || event.ctrlKey || event.metaKey || event.altKey || event.target.closest('input,textarea,select,[contenteditable="true"]')) return;
    const action = { p: 'timer', r: 'reveal', ArrowRight: 'next' }[event.key];
    if (!action) return;
    if (action === 'reveal' && session.turns[session.index].result) return;
    event.preventDefault(); act(action);
});
window.addEventListener('pagehide', () => { clearTimeout(draftTimer); if (!$('setup').hidden) draft = readDraft(); save(); });
try {
    const saved = localStorage.getItem(key);
    if (saved) {
        const data = JSON.parse(saved);
        if (data.version !== 1 || typeof data.config?.title !== 'string' || !Array.isArray(data.config?.teams) || !Array.isArray(data.config?.questions) || data.config.questions.length > 200 || data.config.teams.length > 12 || !data.config.teams.every(t => t && typeof t.name === 'string' && t.name.length <= 50) || !data.config.questions.every(q => q && typeof q.question === 'string' && typeof q.answer === 'string' && q.question.length <= 2000 && q.answer.length <= 4000)) throw new Error('Formato no compatible.');
        draft = data.config;
        if (data.session) storedSession = restoreSession(data.session);
    }
} catch { announce('No se pudo recuperar el guardado anterior. Puedes importar un respaldo o preparar otra partida.'); }
fillSetup(draft); offerResume();
