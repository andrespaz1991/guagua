// Motor autónomo para la lista de carga académica de SISGA.
window.SisgaPeriodosRuntime = function seleccionarPeriodoSISGA(opciones) {
    'use strict';
    const numero = Number(opciones?.periodo);
    if (!Number.isSafeInteger(numero) || numero < 1) throw new Error('Indica un número de período entero mayor que cero.');
    const periodo = String(numero);
    const tabla = document.getElementById('tabla-carga-academica');
    if (!tabla) throw new Error('Abre la lista de materias de «Evaluaciones parciales» en SISGA y pega allí el script.');
    const jq = window.jQuery;
    let datos = null;
    if (jq?.fn?.dataTable?.isDataTable?.(tabla)) datos = jq(tabla).DataTable();
    else if (window.DataTable?.isDataTable?.(tabla)) datos = new window.DataTable.Api(tabla);
    if (!datos && document.getElementById('tabla-carga-academica_wrapper')) {
        throw new Error('No se pudo acceder a la paginación de la lista. Espera a que termine de cargar y vuelve a ejecutar el script.');
    }
    if (datos?.settings()[0]?.oFeatures?.bServerSide) {
        throw new Error('Esta lista carga sus páginas desde el servidor. El script requiere la tabla completa usada en el HTML proporcionado. No se cambió ningún período.');
    }

    function filas() {
        if (!datos) return Array.from(tabla.querySelectorAll('tbody > tr'));
        // search:none y page:all incluyen materias filtradas y filas fuera del DOM.
        const todas = datos.rows({ search: 'none', page: 'all' });
        const nodos = todas.nodes().toArray();
        if (nodos.length !== todas.count() || nodos.some(nodo => !nodo)) {
            throw new Error('La tabla aún no ha cargado todas las filas. No se puede confirmar la selección de todas las materias.');
        }
        return nodos;
    }
    const iniciales = filas().filter(fila => fila.querySelector('td[data-gra], select.sel-periodo'));
    if (!iniciales.length) throw new Error('La lista no contiene materias. Espera a que termine de cargar.');
    window.PeriodosSISGA?.detener?.();
    const informe = [];

    function aplicar() {
        const registros = filas().filter(fila => fila.querySelector('td[data-gra], select.sel-periodo'));
        const resumen = { periodo, materias: registros.length, seleccionadas: 0, cambiadas: 0, yaSeleccionadas: 0, omitidas: 0 };
        informe.length = 0;
        for (const fila of registros) {
            const meta = fila.querySelector('td[data-gra]');
            const selector = fila.querySelector('select.sel-periodo');
            let estado;
            const opcion = selector && Array.from(selector.options).find(o => o.value === periodo);
            if (!selector) estado = 'Sin selector de período';
            else if (selector.matches(':disabled')) estado = 'Selector deshabilitado';
            else if (!opcion) estado = 'Período no disponible';
            else if (opcion.disabled || opcion.closest('optgroup')?.disabled) estado = 'Período deshabilitado';
            else {
                const anterior = selector.value;
                selector.value = periodo;
                // Actualiza los controles de la fila en la página visible.
                // Las filas separadas del DOM reciben el mismo evento al dibujarse.
                selector.dispatchEvent(new Event('input', { bubbles: true }));
                selector.dispatchEvent(new Event('change', { bubbles: true }));
                if (selector.value !== periodo) estado = 'La página no conservó el período';
                else {
                    resumen.seleccionadas++;
                    if (anterior === periodo) { resumen.yaSeleccionadas++; estado = 'Ya seleccionado'; }
                    else { resumen.cambiadas++; estado = 'Seleccionado'; }
                }
            }
            if (!['Seleccionado', 'Ya seleccionado'].includes(estado)) resumen.omitidas++;
            informe.push({
                Materia: meta?.dataset.sasi || fila.cells[3]?.textContent.trim() || 'Materia',
                Grado: meta?.dataset.sgra || fila.cells[1]?.textContent.trim() || '',
                Grupo: meta?.dataset.sgru || fila.cells[2]?.textContent.trim() || '',
                Periodo: selector?.value || '', Estado: estado
            });
        }
        api.resumen = resumen;
        return resumen;
    }

    function alDibujar() {
        try { aplicar(); }
        catch (error) { detener(); console.error('SISGA: ' + error.message); }
    }
    function detener() {
        if (datos && jq) jq(tabla).off('draw.dt.guaguaPeriodos', alDibujar);
        else if (datos) datos.off('draw.guaguaPeriodos', alDibujar);
    }
    const api = window.PeriodosSISGA = {
        periodo, resumen: null, aplicar,
        informe: () => { console.table(informe); return informe.map(fila => ({ ...fila })); },
        detener
    };
    if (datos && jq) jq(tabla).on('draw.dt.guaguaPeriodos', alDibujar);
    else if (datos) datos.on('draw.guaguaPeriodos', alDibujar);
    let resultado;
    try { resultado = aplicar(); }
    catch (error) { detener(); throw error; }
    (resultado.omitidas ? console.warn : console.info)(
        `SISGA: período ${periodo} seleccionado en ${resultado.seleccionadas} de ${resultado.materias} materias, incluidas todas las páginas. `
        + `${resultado.cambiadas} cambiadas; ${resultado.yaSeleccionadas} ya seleccionadas; ${resultado.omitidas} omitidas.`
    );
    if (resultado.omitidas) console.table(informe.filter(fila => !['Seleccionado', 'Ya seleccionado'].includes(fila.Estado)));
    console.info('La selección se mantiene al paginar y filtrar. Para dejar de mantenerla: PeriodosSISGA.detener(). Para ver el detalle: PeriodosSISGA.informe().');
    return resultado;
};
