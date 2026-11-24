document.getElementById('portable-form').addEventListener('submit', function () {
    const button = this.querySelector('button');
    const status = document.getElementById('portable-status');
    const panel = document.getElementById('portable-progress');
    const bar = document.getElementById('portable-bar');
    const percent = document.getElementById('portable-percent');
    const elapsed = document.getElementById('portable-elapsed');
    const remaining = document.getElementById('portable-remaining');
    const token = this.elements.descarga;
    // Un identificador distinto por envío evita confundirlo con una descarga anterior.
    token.value = Array.from(crypto.getRandomValues(new Uint8Array(16)), n => n.toString(16).padStart(2, '0')).join('');
    const id = token.value;
    const startedAt = Date.now();
    let finished = false;
    let lastUpdate = null;
    let pollTimer;
    let request;
    let timeout;
    let timer;
    const duration = seconds => {
        seconds = Math.max(0, Math.ceil(seconds));
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return (hours ? hours + ' h ' : '') + (minutes ? minutes + ' min ' : '') + (seconds % 60) + ' s';
    };
    const finish = message => {
        if (finished) return;
        finished = true;
        window.clearInterval(timer);
        window.clearTimeout(pollTimer);
        window.clearTimeout(timeout);
        if (request) request.abort();
        button.disabled = false;
        status.textContent = message;
    };
    const renderTime = () => {
        elapsed.textContent = duration((Date.now() - startedAt) / 1000);
        if (!lastUpdate || lastUpdate.remaining_seconds === null) {
            remaining.textContent = 'Calculando…';
        } else if (lastUpdate.status === 'ready') {
            remaining.textContent = '0 s';
        } else {
            const seconds = lastUpdate.remaining_seconds - (Date.now() - lastUpdate.receivedAt) / 1000;
            remaining.textContent = seconds > 0 ? duration(seconds) : 'Actualizando estimación…';
        }
    };
    const poll = async () => {
        if (finished) return;
        request = new AbortController();
        const abortTimer = window.setTimeout(() => request.abort(), 10000);
        try {
            const response = await fetch('descargar_guagua_progreso.php?descarga=' + id, {
                credentials: 'same-origin', cache: 'no-store', signal: request.signal
            });
            if (finished) return;
            if (response.status === 401 || response.status === 403) {
                remaining.textContent = 'Sin conexión';
                finish('La sesión terminó o ya no tiene acceso. Inicia sesión para volver a intentarlo.');
                return;
            }
            if (!response.ok) throw new Error('No se pudo consultar el avance.');
            const data = await response.json();
            if (finished) return;
            if (data.status === 'error') {
                remaining.textContent = 'No disponible';
                finish(data.stage);
                return;
            }
            if (data.status === 'preparing' || data.status === 'ready') {
                const value = Math.max(bar.value, Math.min(100, Number(data.percent) || 0));
                bar.value = value;
                bar.textContent = percent.textContent = value + ' %';
                if (status.textContent !== data.stage) status.textContent = data.stage;
                lastUpdate = { ...data, receivedAt: Date.now() };
                renderTime();
            }
        } catch (error) {
            if (!finished) status.textContent = 'La copia sigue preparándose. Reintentando la consulta del avance…';
        } finally {
            window.clearTimeout(abortTimer);
            request = null;
            if (!finished) pollTimer = window.setTimeout(poll, 2000);
        }
    };
    button.disabled = true;
    panel.hidden = false;
    bar.value = 0;
    bar.textContent = percent.textContent = '0 %';
    elapsed.textContent = '0 s';
    remaining.textContent = 'Calculando…';
    status.textContent = 'Comprobando las bases de datos… La descarga comenzará automáticamente.';
    timer = window.setInterval(() => {
        if (document.cookie.split(';').some(cookie => cookie.trim() === 'guagua_descarga=' + id)) {
            bar.value = 100;
            bar.textContent = percent.textContent = '100 %';
            remaining.textContent = '0 s';
            finish('Descarga iniciada. Al terminar, encontrarás la guía LEEME-XAMPP.txt dentro del ZIP.');
            return;
        }
        renderTime();
    }, 1000);
    pollTimer = window.setTimeout(poll, 500);
    timeout = window.setTimeout(() => {
        remaining.textContent = 'No disponible';
        finish('La preparación está tardando más de lo esperado. Revisa tu conexión antes de volver a intentarlo.');
    }, 30 * 60 * 1000);
});
