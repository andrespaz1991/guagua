(function () {
    'use strict';
    const $ = id => document.getElementById('sp-' + id);
    if (!$('periodos')) return;
    const clave = 'guagua.sisga.periodo-lista.v1';
    let generado = null;
    const estado = (mensaje, error = false) => { $('status').textContent = mensaje; $('status').dataset.error = String(error); };
    function generar(periodo) {
        const numero = Number(periodo);
        if (!Number.isSafeInteger(numero) || numero < 1) throw new Error('Indica un período entero mayor que cero.');
        return '// SISGA: ejecutar en la lista de materias de Evaluaciones parciales.\n'
            + '// Selecciona el período en todas las páginas y mantiene la selección al paginar.\n'
            + 'try { (' + window.SisgaPeriodosRuntime.toString() + ')('
            + JSON.stringify({ periodo: numero }, null, 2)
            + '); } catch (error) { console.error("SISGA:", error.message); }\n';
    }
    window.SisgaPeriodos = { generar };
    try {
        const guardado = Number(localStorage.getItem(clave));
        if (Number.isSafeInteger(guardado) && guardado > 0) $('periodo').value = String(guardado);
    } catch (_) { /* La selección funciona también sin almacenamiento local. */ }
    $('periodo').addEventListener('input', () => {
        generado = null; $('result').hidden = true; $('script').value = '';
        estado('Elige el período y genera un nuevo script.');
    });
    $('form').addEventListener('submit', evento => {
        evento.preventDefault();
        try {
            const numero = Number($('periodo').value);
            generado = { contenido: generar(numero), archivo: `seleccionar-periodo-${numero}-todas-las-materias.js` };
            try { localStorage.setItem(clave, String(numero)); } catch (_) { /* Opcional. */ }
            $('script').value = generado.contenido; $('filename').textContent = generado.archivo;
            $('summary').textContent = `Período ${numero} · Todas las materias y todas las páginas de la lista`;
            $('result').hidden = false; estado(`Script listo para seleccionar el período ${numero} en toda la lista de materias.`);
            $('result').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) { estado(error.message, true); }
    });
    $('copy').addEventListener('click', async () => {
        if (!generado) return;
        try { await navigator.clipboard.writeText(generado.contenido); estado('Script copiado. Pégalo en la consola de la lista de materias en SISGA.'); }
        catch (_) { $('script').focus(); $('script').select(); estado('Script seleccionado. Usa Ctrl+C para copiarlo.'); }
    });
    $('download').addEventListener('click', () => {
        if (!generado) return;
        const url = URL.createObjectURL(new Blob([generado.contenido], { type: 'text/javascript;charset=utf-8' }));
        const enlace = document.createElement('a'); enlace.href = url; enlace.download = generado.archivo;
        document.body.appendChild(enlace); enlace.click(); enlace.remove(); setTimeout(() => URL.revokeObjectURL(url), 10000);
        estado(`Descargado ${generado.archivo}.`);
    });
})();
