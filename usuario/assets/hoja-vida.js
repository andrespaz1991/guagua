(function () {
    'use strict';
    var dialog = document.getElementById('hv-card-dialog');
    var status = document.getElementById('hv-export-status');
    var previouslyClosed = [];
    var reportPrepared = false;
    function expandReport() {
        if (document.body.classList.contains('print-card') || reportPrepared) return;
        reportPrepared = true;
        previouslyClosed = Array.from(document.querySelectorAll('details:not([open])'));
        previouslyClosed.forEach(function (detail) { detail.open = true; });
    }
    window.addEventListener('beforeprint', expandReport);
    window.addEventListener('afterprint', function () {
        document.body.classList.remove('print-card');
        previouslyClosed.forEach(function (detail) { detail.open = false; });
        previouslyClosed = [];
        reportPrepared = false;
    });
    document.querySelectorAll('[data-print]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.dataset.print === 'card') dialog.showModal();
            else { if (dialog.open) dialog.close(); document.body.classList.remove('print-card'); window.print(); }
        });
    });
    document.getElementById('hv-card-close').addEventListener('click', function () { dialog.close(); });
    document.getElementById('hv-card-print').addEventListener('click', async function () {
        var image = dialog.querySelector('img');
        if (image && typeof image.decode === 'function') { try { await image.decode(); } catch (error) { /* Missing photos do not prevent printing the profile. */ } }
        document.body.classList.add('print-card');
        window.print();
    });
    document.getElementById('hv-export').addEventListener('click', function () {
        var button = this;
        button.disabled = true;
        status.textContent = 'Preparando archivo Excel…';
        try {
            if (typeof XLSX === 'undefined') throw new Error('La librería de Excel no está disponible. Actualice la página.');
            var data = JSON.parse(document.getElementById('hv-export-data').textContent);
            var workbook = XLSX.utils.book_new();
            Object.keys(data.hojas).forEach(function (name) {
                var rows = data.hojas[name];
                var metadata = [[data.periodo], ['Desde', data.inicio, 'Hasta', data.fin], ['Criterio', 'Actividades por entrega (o publicación); asistencia por fecha de clase.'], []];
                var sheet = XLSX.utils.aoa_to_sheet(metadata.concat(rows));
                sheet['!cols'] = rows[0].map(function (_, column) { return { wch: column === 0 ? 30 : 24 }; });
                sheet['!autofilter'] = { ref: XLSX.utils.encode_range({s: {r:4,c:0}, e:{r:4 + rows.length - 1,c:rows[0].length - 1}}) };
                // IDs and phone numbers remain strings; user text never becomes a formula.
                XLSX.utils.book_append_sheet(workbook, sheet, name);
            });
            XLSX.writeFile(workbook, data.archivo, { bookType:'xlsx', compression:true });
            status.textContent = 'Archivo Excel generado para ' + data.periodo + '.';
        } catch (error) {
            status.textContent = error.message || 'No se pudo generar el archivo Excel.';
        } finally { button.disabled = false; }
    });
})();

