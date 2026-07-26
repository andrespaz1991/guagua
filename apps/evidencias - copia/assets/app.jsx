const { useEffect, useMemo, useState } = React;

const areaStyles = {
    'Académica': { accent: 'border-sky-400', badge: 'bg-sky-100 text-sky-700', icon: 'fa-book-open' },
    'Administrativa': { accent: 'border-violet-400', badge: 'bg-violet-100 text-violet-700', icon: 'fa-clipboard-list' },
    'Comunitaria': { accent: 'border-emerald-400', badge: 'bg-emerald-100 text-emerald-700', icon: 'fa-people-group' },
    'Comportamental': { accent: 'border-amber-400', badge: 'bg-amber-100 text-amber-700', icon: 'fa-heart' },
};

const behavioralCatalog = [
    'Liderazgo',
    'Comunicación y relaciones interpersonales',
    'Trabajo en equipo',
    'Negociación y mediación',
    'Compromiso social e institucional',
    'Iniciativa',
    'Orientación al logro',
];

async function requestApi(action, options = {}) {
    const requestOptions = { method: options.method || 'GET' };
    let requestUrl = `api.php?action=${encodeURIComponent(action)}`;
    if (options.query) {
        requestUrl += `&${new URLSearchParams(options.query).toString()}`;
    }
    if (options.data) {
        requestOptions.headers = { 'Content-Type': 'application/json' };
        requestOptions.body = JSON.stringify(options.data);
    }
    if (options.formData) {
        requestOptions.body = options.formData;
    }
    const response = await fetch(requestUrl, requestOptions);
    const payload = await response.json();
    if (!payload.success) {
        throw new Error(payload.message || 'No fue posible completar la operación.');
    }
    return payload.data;
}

function ProgressRing({ value }) {
    const radius = 38;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (Math.min(100, value) / 100) * circumference;
    return <div className="relative h-24 w-24 shrink-0">
        <svg viewBox="0 0 96 96" className="h-24 w-24 -rotate-90">
            <circle cx="48" cy="48" r={radius} fill="none" stroke="#e2e8f0" strokeWidth="9" />
            <circle cx="48" cy="48" r={radius} fill="none" stroke="#0f766e" strokeWidth="9" strokeLinecap="round" strokeDasharray={circumference} strokeDashoffset={offset} />
        </svg>
        <span className="absolute inset-0 grid place-items-center text-lg font-bold text-slate-800">{value}%</span>
    </div>;
}

function MetricCard({ title, value, helper, icon, tone = 'teal' }) {
    const tones = {
        teal: 'bg-teal-50 text-teal-700',
        blue: 'bg-blue-50 text-blue-700',
        amber: 'bg-amber-50 text-amber-700',
    };
    return <article className="soft-shadow rounded-2xl bg-white p-5">
        <div className="flex items-start justify-between gap-3">
            <div><p className="text-sm font-medium text-slate-500">{title}</p><p className="mt-1 text-3xl font-bold text-slate-800">{value}</p><p className="mt-1 text-xs text-slate-500">{helper}</p></div>
            <span className={`grid h-10 w-10 place-items-center rounded-xl ${tones[tone]}`}><i className={`fa-solid ${icon}`}></i></span>
        </div>
    </article>;
}

function CompetencyCard({ competency, onSelect }) {
    const label = competency.tipo === 'Comportamental' ? 'Comportamental' : competency.area_gestion;
    const style = areaStyles[label] || areaStyles.Comportamental;
    const evidenceCount = competency.criterios.reduce((count, criterion) => count + criterion.evidencias.length, 0);
    const suppliedCount = competency.criterios.reduce((count, criterion) => count + criterion.evidencias.filter((evidence) => evidence.estado === 'Registrada').length, 0);
    return <button onClick={() => onSelect(competency)} className={`card-lift w-full border-l-4 ${style.accent} rounded-xl bg-white p-5 text-left soft-shadow`}>
        <div className="flex items-start justify-between gap-3"><span className={`rounded-full px-2.5 py-1 text-xs font-bold ${style.badge}`}><i className={`fa-solid ${style.icon} mr-1.5`}></i>{label}</span><span className="text-sm font-bold text-slate-600">{competency.puntaje_final ?? '—'}</span></div>
        <h3 className="mt-4 font-bold text-slate-800">{competency.nombre_competencia}</h3>
        <p className="mt-2 line-clamp-2 text-sm leading-5 text-slate-500">{competency.contribucion_individual}</p>
        <div className="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500"><span>{competency.criterios.length}/3 criterios</span><span className="font-semibold text-teal-700">{suppliedCount}/{evidenceCount} soportes</span></div>
    </button>;
}

function EvidenceItem({ evidence, disabled, onAttachment, onPreview }) {
    const [dragging, setDragging] = useState(false);
    const chooseFile = (event) => {
        const file = event.target.files?.[0];
        if (file) onAttachment(evidence, file, '');
        event.target.value = '';
    };
    const dropFile = (event) => {
        event.preventDefault();
        setDragging(false);
        const file = event.dataTransfer.files?.[0];
        if (file) onAttachment(evidence, file, '');
    };
    return <div className="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <div className="flex flex-wrap items-start justify-between gap-2"><div><p className="text-sm font-semibold text-slate-700">{evidence.titulo}</p><p className="mt-1 text-xs text-slate-500">{evidence.tipo} · {evidence.estado}</p></div><span className={evidence.estado === 'Registrada' ? 'rounded-full bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-700' : 'rounded-full bg-slate-200 px-2 py-1 text-xs font-bold text-slate-600'}>{evidence.estado === 'Registrada' ? 'Respaldada' : 'Pendiente'}</span></div>
        {evidence.adjuntos.length > 0 && <div className="mt-3 space-y-1">{evidence.adjuntos.map((attachment) => <button key={attachment.id} onClick={() => onPreview(attachment)} className="block max-w-full truncate text-left text-xs font-medium text-teal-700 hover:underline"><i className="fa-solid fa-paperclip mr-1"></i>{attachment.nombre_original}</button>)}</div>}
        {!disabled && <div onDragOver={(event) => { event.preventDefault(); setDragging(true); }} onDragLeave={() => setDragging(false)} onDrop={dropFile} className={`mt-3 rounded-md border border-dashed p-2 text-center text-xs ${dragging ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-slate-300 text-slate-500'}`}>
            <label className="cursor-pointer font-semibold text-teal-700 hover:underline"><i className="fa-solid fa-arrow-up-from-bracket mr-1"></i>Arrastra o selecciona un archivo<input type="file" className="hidden" onChange={chooseFile} /></label>
            <button onClick={() => { const url = window.prompt('Pega un enlace http(s) como evidencia:'); if (url) onAttachment(evidence, null, url); }} className="ml-3 font-semibold text-slate-600 hover:underline" type="button">Añadir enlace</button>
        </div>}
    </div>;
}

function CompetencyPanel({ competency, evaluation, onClose, onSaved, onPreview, notify }) {
    const [firstScore, setFirstScore] = useState(competency.puntaje_val_1 ?? '');
    const [secondScore, setSecondScore] = useState(competency.puntaje_val_2 ?? '');
    const [saving, setSaving] = useState(false);
    const locked = evaluation.estado === 'Notificado' || evaluation.estado_ano === 'Cerrado';
    const saveScores = async () => {
        setSaving(true);
        try {
            const dashboard = await requestApi('score', { method: 'POST', data: { competencia_id: competency.id, puntaje_val_1: firstScore, puntaje_val_2: secondScore } });
            onSaved(dashboard);
            notify('Puntajes actualizados.');
        } catch (error) { notify(error.message, true); } finally { setSaving(false); }
    };
    const attachEvidence = async (evidence, file, url) => {
        const formData = new FormData();
        formData.append('evidencia_id', evidence.id);
        if (file) formData.append('archivo', file);
        if (url) formData.append('url', url);
        try {
            const dashboard = await requestApi('attachment', { method: 'POST', formData });
            onSaved(dashboard);
            notify('Soporte incorporado a la evidencia.');
        } catch (error) { notify(error.message, true); }
    };
    return <div className="fixed inset-0 z-40 flex justify-end bg-slate-950/40" onMouseDown={onClose}>
        <aside className="h-full w-full max-w-2xl overflow-y-auto bg-white shadow-2xl" onMouseDown={(event) => event.stopPropagation()}>
            <div className="sticky top-0 z-10 flex items-start justify-between border-b border-slate-200 bg-white p-6"><div><p className="text-xs font-bold uppercase tracking-wider text-teal-700">{competency.tipo === 'Funcional' ? competency.area_gestion : 'Competencia comportamental'}</p><h2 className="mt-1 text-xl font-bold text-slate-800">{competency.nombre_competencia}</h2></div><button onClick={onClose} className="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200"><i className="fa-solid fa-xmark"></i></button></div>
            <div className="space-y-6 p-6"><section className="rounded-xl bg-teal-50 p-4"><p className="text-xs font-bold uppercase tracking-wider text-teal-800">Contribución individual</p><p className="mt-2 text-sm leading-6 text-slate-700">{competency.contribucion_individual}</p></section>
                <section><div className="flex items-center justify-between"><h3 className="font-bold text-slate-800">Criterios y evidencias</h3><span className="text-xs text-slate-500">{competency.criterios.length} criterios requeridos</span></div><div className="mt-3 space-y-4">{competency.criterios.map((criterion) => <article key={criterion.id} className="rounded-xl border border-slate-200 p-4"><p className="font-semibold text-slate-800"><span className="mr-2 text-teal-700">{criterion.orden}.</span>{criterion.descripcion}</p><div className="mt-3 space-y-2">{criterion.evidencias.map((evidence) => <EvidenceItem key={evidence.id} evidence={evidence} disabled={locked} onAttachment={attachEvidence} onPreview={onPreview} />)}</div></article>)}</div></section>
                <section className="rounded-xl border border-slate-200 p-5"><div className="flex items-center justify-between"><h3 className="font-bold text-slate-800">Valoración de la competencia</h3>{locked && <span className="text-xs font-semibold text-amber-700">Historial congelado</span>}</div><p className="mt-1 text-xs text-slate-500">Si se informan los días de ambos momentos, el sistema usa el promedio ponderado por duración.</p><div className="mt-4 grid grid-cols-2 gap-3"><label className="text-sm font-medium text-slate-600">Valoración 1<input disabled={locked} value={firstScore} onChange={(event) => setFirstScore(event.target.value)} type="number" min="0" max="100" step="0.01" className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 disabled:bg-slate-100" /></label><label className="text-sm font-medium text-slate-600">Valoración 2<input disabled={locked} value={secondScore} onChange={(event) => setSecondScore(event.target.value)} type="number" min="0" max="100" step="0.01" className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 disabled:bg-slate-100" /></label></div><div className="mt-4 flex items-center justify-between"><p className="text-sm text-slate-600">Puntaje final: <strong className="text-lg text-teal-700">{competency.puntaje_final ?? '—'}</strong></p>{!locked && <button disabled={saving} onClick={saveScores} className="rounded-lg bg-teal-700 px-4 py-2 text-sm font-bold text-white hover:bg-teal-800 disabled:opacity-50">{saving ? 'Guardando...' : 'Guardar valoración'}</button>}</div></section>
            </div>
        </aside>
    </div>;
}

function PreviewModal({ attachment, onClose }) {
    if (!attachment) return null;
    const type = attachment.tipo_archivo;
    const isImage = type === 'Imagen';
    const isVideo = type === 'Video';
    const isAudio = type === 'Audio';
    return <div className="fixed inset-0 z-50 grid place-items-center bg-slate-950/75 p-5" onMouseDown={onClose}><div className="max-h-full w-full max-w-5xl overflow-auto rounded-xl bg-white p-4" onMouseDown={(event) => event.stopPropagation()}><div className="mb-3 flex items-center justify-between gap-4"><h2 className="truncate font-bold text-slate-800">{attachment.nombre_original}</h2><button onClick={onClose} className="rounded-full bg-slate-100 px-3 py-1.5 text-sm"><i className="fa-solid fa-xmark"></i></button></div>{isImage && <img src={attachment.url_archivo} className="mx-auto max-h-[75vh] max-w-full object-contain" />}{isVideo && <video src={attachment.url_archivo} className="mx-auto max-h-[75vh] max-w-full" controls />}{isAudio && <audio src={attachment.url_archivo} className="w-full" controls />}{!isImage && !isVideo && !isAudio && <iframe title={attachment.nombre_original} src={attachment.url_archivo} className="h-[70vh] w-full border border-slate-200" />}{type === 'Enlace' && <p className="mt-3 text-sm"><a href={attachment.url_archivo} target="_blank" rel="noreferrer" className="font-semibold text-teal-700 underline">Abrir enlace en una pestaña nueva</a></p>}</div></div>;
}

function NewEvaluationModal({ onClose, onCreated, notify }) {
    const [form, setForm] = useState({ ano: new Date().getFullYear() + 1, nombre_docente: 'HUGO ANDRES PAZ BURBANO', cedula: '1085290375', ciudad_concertacion: 'San Luis', fecha_inicio: `${new Date().getFullYear() + 1}-01-01`, ponderacion_academica: 30, ponderacion_administrativa: 20, ponderacion_comunitaria: 20, dias_valoracion_1: 0, dias_valoracion_2: 0, competencias_comportamentales: ['Trabajo en equipo', 'Iniciativa', 'Compromiso social e institucional'] });
    const [saving, setSaving] = useState(false);
    const update = (field, value) => setForm((currentForm) => ({ ...currentForm, [field]: value }));
    const toggleBehavior = (name) => setForm((currentForm) => {
        const selected = currentForm.competencias_comportamentales.includes(name);
        const names = selected ? currentForm.competencias_comportamentales.filter((item) => item !== name) : [...currentForm.competencias_comportamentales, name];
        return { ...currentForm, competencias_comportamentales: names };
    });
    const submit = async (event) => {
        event.preventDefault();
        setSaving(true);
        try { const dashboard = await requestApi('create_evaluation', { method: 'POST', data: form }); onCreated(dashboard); } catch (error) { notify(error.message, true); setSaving(false); }
    };
    const totalWeight = Number(form.ponderacion_academica) + Number(form.ponderacion_administrativa) + Number(form.ponderacion_comunitaria);
    return <div className="fixed inset-0 z-40 grid place-items-center bg-slate-950/40 p-4" onMouseDown={onClose}><form onSubmit={submit} onMouseDown={(event) => event.stopPropagation()} className="max-h-[95vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl"><div className="flex items-start justify-between"><div><h2 className="text-xl font-bold">Crear evaluación anual</h2><p className="mt-1 text-sm text-slate-500">Se clonarán las ocho competencias funcionales y se seleccionarán tres comportamentales.</p></div><button type="button" onClick={onClose} className="rounded-full bg-slate-100 px-3 py-1.5"><i className="fa-solid fa-xmark"></i></button></div><div className="mt-5 grid gap-4 sm:grid-cols-2"><Field label="Año lectivo" value={form.ano} onChange={(value) => update('ano', value)} type="number" /><Field label="Fecha de concertación" value={form.fecha_inicio} onChange={(value) => update('fecha_inicio', value)} type="date" /><Field label="Nombre del docente" value={form.nombre_docente} onChange={(value) => update('nombre_docente', value)} /><Field label="Cédula" value={form.cedula} onChange={(value) => update('cedula', value)} /><Field label="Ciudad" value={form.ciudad_concertacion} onChange={(value) => update('ciudad_concertacion', value)} /><Field label="Días valoración 1" value={form.dias_valoracion_1} onChange={(value) => update('dias_valoracion_1', value)} type="number" /><Field label="Académica (%)" value={form.ponderacion_academica} onChange={(value) => update('ponderacion_academica', value)} type="number" /><Field label="Administrativa (%)" value={form.ponderacion_administrativa} onChange={(value) => update('ponderacion_administrativa', value)} type="number" /><Field label="Comunitaria (%)" value={form.ponderacion_comunitaria} onChange={(value) => update('ponderacion_comunitaria', value)} type="number" /><Field label="Días valoración 2" value={form.dias_valoracion_2} onChange={(value) => update('dias_valoracion_2', value)} type="number" /></div><p className={`mt-3 text-sm font-semibold ${totalWeight === 70 ? 'text-emerald-700' : 'text-rose-700'}`}>Total funcional: {totalWeight}% (debe ser 70%)</p><fieldset className="mt-5"><legend className="font-bold text-slate-800">Competencias comportamentales ({form.competencias_comportamentales.length}/3)</legend><div className="mt-3 grid gap-2 sm:grid-cols-2">{behavioralCatalog.map((name) => <label key={name} className="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm"><input type="checkbox" checked={form.competencias_comportamentales.includes(name)} onChange={() => toggleBehavior(name)} /><span>{name}</span></label>)}</div></fieldset><div className="mt-6 flex justify-end gap-3"><button type="button" onClick={onClose} className="rounded-lg px-4 py-2 font-semibold text-slate-600">Cancelar</button><button disabled={saving || totalWeight !== 70 || form.competencias_comportamentales.length !== 3} className="rounded-lg bg-teal-700 px-4 py-2 font-bold text-white disabled:opacity-50">{saving ? 'Creando...' : 'Crear evaluación'}</button></div></form></div>;
}

function Field({ label, value, onChange, type = 'text' }) {
    return <label className="text-sm font-medium text-slate-600">{label}<input required value={value} onChange={(event) => onChange(event.target.value)} type={type} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100" /></label>;
}

function App() {
    const [evaluations, setEvaluations] = useState([]);
    const [dashboard, setDashboard] = useState(null);
    const [selectedCompetency, setSelectedCompetency] = useState(null);
    const [previewAttachment, setPreviewAttachment] = useState(null);
    const [newEvaluationOpen, setNewEvaluationOpen] = useState(false);
    const [notice, setNotice] = useState(null);
    const [loading, setLoading] = useState(true);
    const [settings, setSettings] = useState(null);

    const notify = (message, error = false) => { setNotice({ message, error }); window.setTimeout(() => setNotice(null), 5000); };
    const loadEvaluations = async (preferId = null) => {
        const availableEvaluations = await requestApi('evaluations');
        setEvaluations(availableEvaluations);
        const target = preferId || dashboard?.evaluacion.id || availableEvaluations[0]?.id;
        if (target) {
            const data = await requestApi('dashboard', { query: { evaluation_id: target } });
            setDashboard(data);
            setSettings(data.evaluacion);
        }
    };
    useEffect(() => { loadEvaluations().catch((error) => notify(error.message, true)).finally(() => setLoading(false)); }, []);
    const applyDashboard = (data) => { setDashboard(data); setSettings(data.evaluacion); setSelectedCompetency(null); loadEvaluations(data.evaluacion.id).catch(() => null); };
    const grouped = useMemo(() => {
        if (!dashboard) return {};
        return {
            'Académica': dashboard.competencias.filter((competency) => competency.area_gestion === 'Académica'),
            'Administrativa': dashboard.competencias.filter((competency) => competency.area_gestion === 'Administrativa'),
            'Comunitaria': dashboard.competencias.filter((competency) => competency.area_gestion === 'Comunitaria'),
            'Comportamental': dashboard.competencias.filter((competency) => competency.tipo === 'Comportamental'),
        };
    }, [dashboard]);
    const saveSettings = async () => { try { const data = await requestApi('settings', { method: 'POST', data: { ...settings, evaluacion_id: dashboard.evaluacion.id } }); applyDashboard(data); notify('Condiciones de concertación actualizadas.'); } catch (error) { notify(error.message, true); } };
    const changeState = async (state) => { try { const data = await requestApi('state', { method: 'POST', data: { evaluacion_id: dashboard.evaluacion.id, estado: state } }); applyDashboard(data); notify(state === 'Notificado' ? 'Evaluación notificada y congelada como historial.' : 'Estado actualizado.'); } catch (error) { notify(error.message, true); } };
    if (loading) return <div className="grid min-h-screen place-items-center text-teal-700"><div className="text-center"><i className="fa-solid fa-circle-notch fa-spin text-3xl"></i><p className="mt-3 font-medium">Cargando evaluación docente...</p></div></div>;
    if (!dashboard) return <main className="mx-auto mt-20 max-w-xl rounded-2xl bg-white p-8 text-center soft-shadow"><i className="fa-solid fa-database text-4xl text-teal-700"></i><h1 className="mt-4 text-2xl font-bold text-slate-800">Módulo sin instalar</h1><p className="mt-2 text-slate-600">Cree las tablas y la plantilla 2026 antes de ingresar al tablero.</p><a href="setup/install.php" className="mt-5 inline-block rounded-lg bg-teal-700 px-4 py-2 font-bold text-white">Abrir instalador</a></main>;
    const evaluation = dashboard.evaluacion;
    const summary = dashboard.resumen;
    const locked = evaluation.estado === 'Notificado' || evaluation.estado_ano === 'Cerrado';
    return <div className="min-h-screen text-slate-800"><header className="border-b border-slate-200 bg-white"><div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4"><div className="flex items-center gap-3"><span className="grid h-11 w-11 place-items-center rounded-xl bg-teal-700 text-xl text-white"><i className="fa-solid fa-folder-open"></i></span><div><h1 className="font-bold text-slate-800">Evidencias Docente</h1><p className="text-xs text-slate-500">Evaluación anual · Decreto 1278</p></div></div><div className="flex flex-wrap items-center gap-2"><select value={evaluation.id} onChange={(event) => loadEvaluations(event.target.value).catch((error) => notify(error.message, true))} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium">{evaluations.map((item) => <option key={item.id} value={item.id}>{item.ano} · {item.nombre}</option>)}</select><a href={`report.php?evaluation_id=${evaluation.id}`} target="_blank" className="rounded-lg border border-teal-700 px-3 py-2 text-sm font-bold text-teal-700 hover:bg-teal-50"><i className="fa-solid fa-file-pdf mr-1"></i>Protocolo</a><button onClick={() => setNewEvaluationOpen(true)} className="rounded-lg bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><i className="fa-solid fa-plus mr-1"></i>Nuevo año</button></div></div></header>
        <main className="mx-auto max-w-7xl px-5 py-7"><section className="rounded-2xl bg-gradient-to-r from-teal-800 to-cyan-700 p-6 text-white soft-shadow"><div className="flex flex-wrap items-start justify-between gap-5"><div><p className="text-sm font-semibold text-teal-100">Año lectivo {evaluation.ano} · {evaluation.estado}</p><h2 className="mt-1 text-2xl font-bold">{evaluation.docente_nombre}</h2><p className="mt-1 text-sm text-teal-100">C.C. {evaluation.docente_cedula} · Concertación: {evaluation.ciudad_concertacion || 'Sin ciudad'}, {evaluation.fecha_inicio}</p></div><div className="rounded-xl bg-white/15 px-5 py-3 backdrop-blur"><p className="text-xs font-semibold uppercase tracking-wider text-teal-100">Resultado proyectado</p><p className="mt-1 text-4xl font-bold">{summary.nota_proyectada}</p><p className="text-sm text-teal-100">{summary.categoria}</p></div></div></section>
            <section className="mt-6 grid gap-4 md:grid-cols-3"><MetricCard title="Evidencias respaldadas" value={`${dashboard.progreso_evidencias.porcentaje}%`} helper={`${dashboard.progreso_evidencias.criterios_respaldados} de ${dashboard.progreso_evidencias.criterios_totales} criterios`} icon="fa-paperclip" /><MetricCard title="Calificación registrada" value={`${summary.competencias_calificadas}/${summary.competencias_totales}`} helper="Competencias con puntaje final" icon="fa-star" tone="blue" /><MetricCard title="Soportes adjuntos" value={dashboard.progreso_evidencias.adjuntos} helper="Archivos o enlaces cargados" icon="fa-cloud-arrow-up" tone="amber" /></section>
            <section className="mt-7 grid gap-6 lg:grid-cols-[1fr_330px]"><div className="space-y-7">{Object.entries(grouped).map(([area, competencies]) => <section key={area}><div className="mb-3 flex items-center justify-between"><h2 className="text-lg font-bold text-slate-800"><i className={`fa-solid ${areaStyles[area].icon} mr-2 text-teal-700`}></i>{area}</h2>{area !== 'Comportamental' && <span className="text-sm font-semibold text-slate-500">{summary.areas[area].ponderacion}% · promedio {summary.areas[area].promedio ?? '—'}</span>}</div><div className="grid gap-4 md:grid-cols-2">{competencies.map((competency) => <CompetencyCard key={competency.id} competency={competency} onSelect={setSelectedCompetency} />)}</div></section>)}</div>
                <aside className="h-fit rounded-2xl bg-white p-5 soft-shadow"><div className="flex items-center justify-between"><h2 className="font-bold text-slate-800">Concertación</h2><ProgressRing value={dashboard.progreso_evidencias.porcentaje} /></div><p className="mt-2 text-sm text-slate-500">Las áreas funcionales deben sumar 70%. Las comportamentales representan el 30% restante.</p><div className="mt-5 grid grid-cols-3 gap-2">{[['ponderacion_academica', 'Académica'], ['ponderacion_administrativa', 'Administrativa'], ['ponderacion_comunitaria', 'Comunitaria']].map(([field, label]) => <label key={field} className="text-center text-xs font-semibold text-slate-500">{label}<input disabled={locked} type="number" min="0" max="70" value={settings[field]} onChange={(event) => setSettings((currentSettings) => ({ ...currentSettings, [field]: event.target.value }))} className="mt-1 w-full rounded-lg border border-slate-300 px-2 py-2 text-center text-sm text-slate-800 disabled:bg-slate-100" /></label>)}</div><label className="mt-4 block text-xs font-semibold text-slate-500">Días de valoración 1<input disabled={locked} type="number" min="0" value={settings.dias_valoracion_1} onChange={(event) => setSettings((currentSettings) => ({ ...currentSettings, dias_valoracion_1: event.target.value }))} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 disabled:bg-slate-100" /></label><label className="mt-3 block text-xs font-semibold text-slate-500">Días de valoración 2<input disabled={locked} type="number" min="0" value={settings.dias_valoracion_2} onChange={(event) => setSettings((currentSettings) => ({ ...currentSettings, dias_valoracion_2: event.target.value }))} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 disabled:bg-slate-100" /></label>{!locked && <button onClick={saveSettings} className="mt-4 w-full rounded-lg bg-slate-800 px-3 py-2 text-sm font-bold text-white hover:bg-slate-900">Guardar concertación</button>}<div className="mt-5 border-t border-slate-100 pt-4"><label className="text-xs font-semibold text-slate-500">Estado de la evaluación<select disabled={locked} value={evaluation.estado} onChange={(event) => changeState(event.target.value)} className="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 disabled:bg-slate-100"><option>En Concertación</option><option>Valoración 1</option><option>Valoración 2</option><option>Notificado</option></select></label>{locked && <p className="mt-3 rounded-lg bg-amber-50 p-3 text-xs leading-5 text-amber-800"><i className="fa-solid fa-lock mr-1"></i>Este historial quedó congelado al notificar la evaluación.</p>}</div></aside></section>
        </main>{notice && <div className={`fixed bottom-5 right-5 z-50 max-w-sm rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-xl ${notice.error ? 'bg-rose-600' : 'bg-slate-800'}`}><i className={`fa-solid ${notice.error ? 'fa-circle-exclamation' : 'fa-circle-check'} mr-2`}></i>{notice.message}</div>}{selectedCompetency && <CompetencyPanel competency={dashboard.competencias.find((competency) => competency.id === selectedCompetency.id) || selectedCompetency} evaluation={evaluation} onClose={() => setSelectedCompetency(null)} onSaved={applyDashboard} onPreview={setPreviewAttachment} notify={notify} />}{previewAttachment && <PreviewModal attachment={previewAttachment} onClose={() => setPreviewAttachment(null)} />}{newEvaluationOpen && <NewEvaluationModal onClose={() => setNewEvaluationOpen(false)} onCreated={(data) => { applyDashboard(data); setNewEvaluationOpen(false); notify('Evaluación anual creada.'); }} notify={notify} />}</div>;
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
