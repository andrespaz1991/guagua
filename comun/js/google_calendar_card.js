(function () {
    'use strict';
    var card = document.getElementById('google-calendar-card');
    if (!card || !card.dataset.clientId) return;
    var status = card.querySelector('[data-calendar-status]');
    var events = card.querySelector('[data-calendar-events]');
    var connect = card.querySelector('[data-calendar-connect]');
    var refresh = card.querySelector('[data-calendar-refresh]');
    var disconnect = card.querySelector('[data-calendar-disconnect]');
    var scope = 'https://www.googleapis.com/auth/calendar.events.readonly';
    // Guagua conserva y renueva la autorización en el servidor.
    var generation = 0;
    var client;

    function reset(message) {
        generation++;
        events.replaceChildren();
        refresh.hidden = disconnect.hidden = true;
        refresh.disabled = false;
        connect.hidden = false;
        connect.disabled = !client;
        status.textContent = message;
    }

    function eventDate(event, timezone) {
        var start = event.start || {};
        var allDay = !start.dateTime;
        var date = new Date(allDay ? start.date + 'T00:00:00Z' : start.dateTime);
        if (isNaN(date.getTime())) return 'Fecha no disponible';
        var options = { day: 'numeric', month: 'short', timeZone: allDay ? 'UTC' : timezone };
        if (!allDay) { options.hour = '2-digit'; options.minute = '2-digit'; }
        return new Intl.DateTimeFormat('es-CO', options).format(date) + (allDay ? ' · Todo el día' : '');
    }

    async function loadEvents() {
        var current = ++generation;
        refresh.disabled = true;
        events.replaceChildren();
        status.textContent = 'Cargando próximos eventos…';
        try {
            var response = await fetch(card.dataset.endpoint, {
                cache: 'no-store', signal: AbortSignal.timeout(65000)
            });
            if (current !== generation) return;
            if (response.status === 401) {
                var problem = await response.json();
                reset(problem.message || 'Conecta Google Calendar para guardar la autorización.');
                return;
            }
            if (!response.ok) throw new Error(response.status === 403
                ? 'Google no permitió consultar tu calendario. Verifica el permiso de lectura y que Calendar API esté habilitada.'
                : 'No se pudo consultar tu agenda. Intenta actualizar de nuevo.');
            var data = await response.json();
            if (current !== generation) return;
            connect.hidden = true;
            refresh.hidden = disconnect.hidden = false;
            var items = (data.items || []).filter(function (event) { return event.status !== 'cancelled'; });
            status.textContent = items.length ? 'Próximos eventos · ' + (data.timeZone || card.dataset.timezone) : 'No tienes próximos eventos en este calendario.';
            var list = document.createElement('ul');
            list.className = 'list-group';
            items.forEach(function (event) {
                var item = document.createElement('li');
                item.className = 'list-group-item';
                var title = document.createElement('strong');
                title.textContent = event.summary || 'Sin título';
                if (event.htmlLink && /^https:\/\/([a-z0-9-]+\.)*google\.com\//i.test(event.htmlLink)) {
                    var link = document.createElement('a');
                    link.href = event.htmlLink;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.appendChild(title);
                    item.appendChild(link);
                } else item.appendChild(title);
                var details = document.createElement('div');
                details.className = 'text-muted small';
                details.textContent = eventDate(event, data.timeZone || card.dataset.timezone);
                item.appendChild(details);
                if (event.location) {
                    var location = document.createElement('div');
                    location.className = 'small';
                    location.textContent = event.location;
                    item.appendChild(location);
                }
                list.appendChild(item);
            });
            events.appendChild(list);
        } catch (error) {
            if (current === generation) status.textContent = error instanceof TypeError || error.name === 'TimeoutError'
                ? 'No se pudo conectar con Google. Revisa tu conexión e intenta actualizar.' : error.message;
        } finally {
            if (current === generation) refresh.disabled = false;
        }
    }

    async function connection(action, body) {
        var response = await fetch(card.dataset.connection + '&action=' + action, {
            method: body ? 'POST' : 'GET', cache: 'no-store',
            headers: {'Content-Type':'application/json','X-CSRF-Token':card.dataset.csrf,'X-Requested-With':'XMLHttpRequest'},
            body: body ? JSON.stringify(body) : undefined
        });
        var data = await response.json();
        if (!response.ok) throw new Error(data.message || 'No se pudo guardar la conexión.');
        return data;
    }
    connect.disabled = true;
    function initialize() {
        if (client) return;
        client = google.accounts.oauth2.initCodeClient({
            client_id: card.dataset.clientId, scope: scope, include_granted_scopes: false,
            ux_mode: 'popup',
            callback: async function (result) {
                if (result.error || !result.code) {reset('No se concedió acceso al calendario. Puedes volver a conectarlo.');return;}
                connect.disabled = true;
                status.textContent = 'Guardando la conexión para las próximas visitas…';
                try {await connection('connect',{code:result.code});await loadEvents();}
                catch(error) {reset(error.message);}
                finally {connect.disabled=false;}
            },
            error_callback: function () {reset('No se completó la conexión. Permite la ventana de Google e intenta de nuevo.');}
        });
        connect.disabled=false;
    }
    var script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.onload = function () {
                initialize();
    };
    script.onerror = function () { status.textContent = 'No se pudo cargar la conexión con Google. Recarga la página para intentar de nuevo.'; };
    if (window.google && google.accounts && google.accounts.oauth2) initialize();
    else document.head.appendChild(script);
    connect.addEventListener('click', function () {
        connect.disabled = true;
        status.textContent = 'Esperando autorización de Google…';
        try { client.requestCode(); }
        catch(error) {reset('No se pudo abrir Google. Permite las ventanas emergentes e intenta de nuevo.');}
    });
    refresh.addEventListener('click', loadEvents);
    disconnect.addEventListener('click', async function () {
        generation++;disconnect.disabled=true;
        try {await connection('disconnect',{});reset('Calendario desconectado.');}
        catch(error) {status.textContent=error.message;}
        finally {disconnect.disabled=false;}
    });
    loadEvents();
}());
