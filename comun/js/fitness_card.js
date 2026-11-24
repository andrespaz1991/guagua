(function () {
    'use strict';
    var card = document.getElementById('fitness-card');
    if (!card) return;
    var status = card.querySelector('[data-fitness-status]');
    var summary = card.querySelector('[data-fitness-summary]');
    var button = card.querySelector('[data-fitness-refresh]');
    async function load() {
        button.disabled = true;
        try {
            var response = await fetch(card.dataset.api + '?source=google', { cache: 'no-store' });
            var data = await response.json();
            var source = 'Google Health';
            if (response.ok && !data.days.length) {
                response = await fetch(card.dataset.api, { cache: 'no-store' });
                data = await response.json(); source = 'iPhone';
            }
            if (!response.ok) throw new Error(data.message || 'No se pudo consultar tu actividad.');
            summary.replaceChildren();
            if (!data.days.length) { status.textContent = 'Aún no has enviado actividad. Abre «Historial y conectar iPhone» para empezar.'; return; }
            var day = data.days[0];
            status.textContent = source + ' · Último día recibido: ' + day.date;
            summary.style.display='grid'; summary.style.gridTemplateColumns='repeat(2,minmax(0,1fr))'; summary.style.gap='8px';
            Object.keys(data.metrics || {}).forEach(function (key) {
                var metric=data.metrics[key];
                var recent=data.days.find(function(row) {return row[key]!==null && row[key]!==undefined;});
                var p = document.createElement('p');
                p.style.margin='0'; p.style.padding='8px'; p.style.background='#f3f8f5'; p.style.borderRadius='6px';
                var label=document.createElement('small'); label.textContent=metric.label; label.style.display='block';
                var number=document.createElement('strong');
                number.textContent=recent ? Number(recent[key]).toLocaleString('es-CO',{maximumFractionDigits:2})+' '+metric.unit : 'Sin datos';
                p.appendChild(label);p.appendChild(number);
                if(recent && recent.date!==day.date) {var date=document.createElement('small'); date.textContent=recent.date;date.style.display='block';p.appendChild(date);}
                summary.appendChild(p);
            });
            var update = document.createElement('small');
            update.className = 'text-muted';
            update.style.gridColumn='1 / -1';
            update.textContent = 'Recibido: ' + new Date(day.updated).toLocaleString('es-CO');
            summary.appendChild(update);
        } catch (error) { status.textContent = error.message; }
        finally { button.disabled = false; }
    }
    button.addEventListener('click', load);
    document.addEventListener('guagua-health-updated', load);
    load();
}());
