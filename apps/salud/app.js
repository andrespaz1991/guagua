(function () {
    'use strict';
    var $ = function (id) { return document.getElementById(id); };
    function iso(date) { return [date.getFullYear(), String(date.getMonth()+1).padStart(2,'0'), String(date.getDate()).padStart(2,'0')].join('-'); }
    var today = new Date();
    $('to').value = iso(today);
    today.setDate(today.getDate() - 29);
    $('from').value = iso(today);
    $('endpoint').value = new URL('api.php?action=import', window.location.href).href;
    function value(n) { return n === null ? '—' : Number(n).toLocaleString('es-CO', {maximumFractionDigits:2}); }
    async function request(action, method) {
        var url = new URL('api.php', window.location.href);
        url.searchParams.set('action', action);
        if (action === 'history') url.searchParams.set('source', $('source').value);
        if (action === 'history') { url.searchParams.set('from', $('from').value); url.searchParams.set('to', $('to').value); }
        var response = await fetch(url, {method: method || 'GET', cache:'no-store', headers:{'X-CSRF-Token':window.saludCsrf}});
        var data = await response.json();
        if (!response.ok) throw new Error(data.message || 'No se pudo completar la operación.');
        return data;
    }
    async function load() {
        $('status').textContent = 'Consultando historial…';
        try {
            var data = await request('history');
            $('history').replaceChildren();
            var metrics=Object.keys(data.metrics || {});
            var head=$('history-head'); head.replaceChildren();
            ['Fecha'].concat(metrics.map(function(key) {return data.metrics[key].label+(data.metrics[key].unit ? ' ('+data.metrics[key].unit+')' : '');})).forEach(function(label) {
                var th=document.createElement('th');th.textContent=label;head.appendChild(th);
            });
            data.days.forEach(function (day) {
                var row = document.createElement('tr');
                [day.date].concat(metrics.map(function(key) {return value(day[key]);})).forEach(function (text) {
                    var cell = document.createElement('td'); cell.textContent = text; row.appendChild(cell);
                });
                $('history').appendChild(row);
            });
            var steps = data.days.reduce(function(sum,day) { return sum + Number(day.steps || 0); },0);
            $('totals').textContent = data.days.length + ' días con datos · ' + value(steps) + ' pasos registrados en el rango';
            var overview=$('overview');overview.replaceChildren();
            metrics.forEach(function(key) {
                var metric=data.metrics[key];
                var recent=data.days.find(function(day) {return day[key]!==null && day[key]!==undefined;});
                var tile=document.createElement('div');tile.className='metric-tile';
                var label=document.createElement('small');label.textContent=metric.label;
                var number=document.createElement('strong');number.textContent=recent ? value(recent[key])+' '+metric.unit : 'Sin datos';
                var date=document.createElement('small');date.textContent=recent ? 'Último registro: '+recent.date : 'No recibido en este rango';
                tile.appendChild(label);tile.appendChild(number);tile.appendChild(date);overview.appendChild(tile);
            });
            $('paired').textContent = data.paired ? 'Hay una clave de envío activa.' : 'Todavía no hay una clave de envío activa.';
            $('revoke').disabled = !data.paired;
            $('status').textContent = data.days.length ? 'Historial actualizado.' : 'Aún no hay datos en este rango. Conecta Google Health o envía actividad desde el iPhone.';
        } catch(error) { $('status').textContent = error.message; }
    }
    $('filters').addEventListener('submit', function(event) { event.preventDefault(); load(); });
    document.addEventListener('guagua-health-updated',load);
    $('pair').addEventListener('click', async function () {
        $('pair').disabled = true;
        try {
            var data = await request('pair','POST');
            $('key').textContent = data.token;
            $('credentials').hidden = false;
            await load();
            $('status').textContent = 'Clave creada. Cópiala en el atajo del iPhone.';
        } catch(error) { $('status').textContent = error.message; }
        finally { $('pair').disabled = false; }
    });
    $('revoke').addEventListener('click', async function () {
        $('revoke').disabled = true;
        try {
            await request('revoke','POST');
            $('key').textContent = ''; $('credentials').hidden = true;
            await load(); $('status').textContent = 'Clave revocada. Tu historial se conserva.';
        } catch(error) { $('status').textContent = error.message; $('revoke').disabled = false; }
    });
    window.addEventListener('pagehide', function () { $('key').textContent = ''; $('credentials').hidden = true; });
    load();
}());
