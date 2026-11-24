(function () {
    'use strict';
    var card = document.getElementById('plan-diario-card');
    if (!card) return;
    var find = function (name) { return card.querySelector('[data-plan-' + name + ']'); };
    var status = find('status'), errorBox = find('error'), tasks = find('tasks'), refresh = find('refresh');
    var picker = find('picker'), todayButton = find('today'), titleLink = document.getElementById('plan-diario-card-title');
    var selectedDate = picker.value, moduleUrl = new URL(titleLink.href);
    var data = null, busy = false, focusTask = null, focusControl = null;

    function lock(value) {
        if (value && document.activeElement && document.activeElement.dataset.planTask) focusTask = document.activeElement.dataset.planTask;
        if (value && [picker, todayButton, refresh].includes(document.activeElement)) focusControl = document.activeElement;
        busy = value;
        refresh.disabled = value;
        picker.disabled = value;
        todayButton.disabled = value;
        tasks.setAttribute('aria-busy', String(value));
        tasks.querySelectorAll('input').forEach(function (input) { input.disabled = value; });
        if (!value && focusTask) {
            var focused = tasks.querySelector('[data-plan-task="' + focusTask + '"]');
            if (focused) focused.focus();
            focusTask = null;
        }
        if (!value && focusControl) { focusControl.focus(); focusControl = null; }
    }
    function fail(error) { errorBox.textContent = error.message; errorBox.hidden = false; }
    async function request(body, date) {
        date = date || selectedDate;
        var controller = new AbortController(), timeout = setTimeout(function () { controller.abort(); }, 20000);
        try {
            var options = { cache: 'no-store', credentials: 'same-origin', signal: controller.signal };
            if (body) {
                options.method = 'POST';
                options.headers = { 'Content-Type': 'application/json', 'X-CSRF-Token': card.dataset.csrf };
                options.body = JSON.stringify(Object.assign({ view: 'home', action: 'complete', date: date }, body));
            }
            var response = await fetch(card.dataset.api + '?view=home&date=' + encodeURIComponent(date), options);
            var result;
            try { result = await response.json(); } catch (_) { throw new Error('No se pudo consultar tu plan. Intenta actualizar la tarjeta.'); }
            if (!response.ok || !result.ok) {
                var error = new Error(result.message || 'No se pudo actualizar tu plan.');
                error.status = response.status;
                throw error;
            }
            return result.data;
        } catch (error) {
            if (error.name === 'AbortError') throw new Error('La conexión tardó demasiado. Actualiza para consultar el estado guardado.');
            throw error;
        } finally { clearTimeout(timeout); }
    }
    function render(next, message) {
        data = next;
        selectedDate = next.date;
        picker.value = next.date;
        moduleUrl.searchParams.set('date', next.date);
        titleLink.href = moduleUrl.href;
        titleLink.textContent = next.date === card.dataset.today ? 'Mi plan diario · Hoy' : 'Mi plan diario';
        find('date').textContent = new Date(next.date + 'T12:00:00').toLocaleDateString('es-CO', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        find('summary').hidden = next.total === 0;
        find('count').textContent = next.done + ' de ' + next.total + ' tareas completadas';
        find('progress').max = next.total || 1;
        find('progress').value = next.done;
        status.textContent = message || (next.total === 0 ? 'No tienes tareas para esta fecha. Abre Mi plan diario para organizar ese día.' : next.done === next.total ? '¡Completaste todas las tareas de este día!' : 'Marca las tareas a medida que las completes.');
        tasks.replaceChildren();
        var groups = new Map();
        next.tasks.forEach(function (task) {
            var list = groups.get(task.category_id);
            if (!list) {
                var heading = document.createElement('h5');
                heading.className = 'pd-home-category'; heading.textContent = task.category;
                list = document.createElement('ul'); list.className = 'pd-home-list';
                tasks.appendChild(heading); tasks.appendChild(list); groups.set(task.category_id, list);
            }
            var row = document.createElement('li'), label = document.createElement('label'), input = document.createElement('input');
            row.className = 'pd-home-task' + (task.done ? ' is-done' : ''); row.dataset.highlight = task.highlight;
            input.type = 'checkbox'; input.checked = task.done; input.dataset.planTask = String(task.id); input.disabled = busy;
            var content = document.createElement('span'), title = document.createElement('span');
            content.className = 'pd-home-content'; title.className = 'pd-home-title'; title.textContent = task.title;
            content.appendChild(title);
            var meta = [task.task_time, task.priority === 'alta' ? 'Prioridad alta' : ''].filter(Boolean);
            if (meta.length) { var detail = document.createElement('span'); detail.className = 'pd-home-meta'; detail.textContent = meta.join(' · '); content.appendChild(detail); }
            label.appendChild(input); label.appendChild(content); row.appendChild(label); list.appendChild(row);
        });
    }
    async function load(date) {
        if (busy) return;
        date = date || selectedDate;
        lock(true); errorBox.hidden = true;
        status.textContent = 'Consultando las tareas de la fecha elegida…';
        try { render(await request(null, date)); }
        catch (error) { picker.value = selectedDate; status.textContent = data ? 'Se conserva la última fecha consultada.' : ''; fail(error); }
        finally { lock(false); }
    }
    tasks.addEventListener('change', async function (event) {
        var input = event.target, id = Number(input.dataset.planTask);
        if (!id || !data) return;
        var task = data.tasks.find(function (task) { return task.id === id; });
        if (busy) { if (task) input.checked = task.done; return; }
        var done = input.checked;
        focusTask = input.dataset.planTask;
        lock(true); errorBox.hidden = true; status.textContent = 'Guardando avance…';
        try { render(await request({ id: id, done: done, revision: data.revision }, data.date), 'Avance guardado.'); }
        catch (error) {
            if (task) input.checked = task.done;
            if (error.status === 409 || error.status === 422) {
                try { render(await request()); } catch (_) { /* Keep the last confirmed state. */ }
            }
            status.textContent = 'No se pudo guardar este cambio.'; fail(error);
        } finally { lock(false); }
    });
    picker.addEventListener('change', function () {
        if (!picker.value || !picker.validity.valid) {
            picker.value = selectedDate;
            fail(new Error('Elige una fecha válida entre 2000 y 2100.'));
            return;
        }
        load(picker.value);
    });
    todayButton.addEventListener('click', function () { load(card.dataset.today); });
    refresh.addEventListener('click', function () { load(); });
    window.addEventListener('focus', function () { load(); });
    document.addEventListener('visibilitychange', function () { if (!document.hidden) load(); });
    load();
}());
