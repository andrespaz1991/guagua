(function () {
    'use strict';

    const same = (a, b) => String(a) === String(b);
    const byParent = (rows, key) => {
        const index = new Map();
        for (const row of rows) {
            const id = String(row[key]);
            if (!index.has(id)) index.set(id, []);
            index.get(id).push(row);
        }
        return index;
    };

    function selectData(data, filters = {}, date = new Date()) {
        const estandares = data.estandares.filter(e =>
            (!filters.grado || same(e.grado, filters.grado)) &&
            (!filters.materia || same(e.id_materia_oficial, filters.materia)) &&
            (!filters.periodo || same(e.id_periodo, filters.periodo))
        );
        const standardIds = new Set(estandares.map(e => String(e.id_estandar)));
        const dbas = data.dbas.filter(d => standardIds.has(String(d.id_estandar)));
        const dbaIds = new Set(dbas.map(d => String(d.id_dba)));
        const ejes = data.ejes_tematicos.filter(e => dbaIds.has(String(e.id_dba)));
        const evidencias = (data.evidencias || []).filter(e => dbaIds.has(String(e.id_dba)));
        const gradeIds = new Set(estandares.map(e => String(e.grado)));
        const subjectIds = new Set(estandares.map(e => String(e.id_materia_oficial)));
        return {
            version: 1,
            exportado_en: date.toISOString(),
            filtros: { grado: filters.grado || null, materia: filters.materia || null, periodo: filters.periodo || null },
            totales: { estandares: estandares.length, dbas: dbas.length, ejes_tematicos: ejes.length, evidencias: evidencias.length },
            grados: data.grados.filter(g => gradeIds.has(String(g.id_grado))),
            materias: data.materias.filter(m => subjectIds.has(String(m.id_materia))),
            estandares, dbas, ejes_tematicos: ejes, evidencias
        };
    }

    // Cada entidad aparece una vez; ejes y evidencias son hijos independientes del DBA.
    function flatRows(data) {
        const rows = [];
        const grades = new Map(data.grados.map(g => [String(g.id_grado), g.nombre]));
        const subjects = new Map(data.materias.map(m => [String(m.id_materia), m.nombre_materia]));
        const dbas = byParent(data.dbas, 'id_estandar');
        const ejes = byParent(data.ejes_tematicos, 'id_dba');
        const evidencias = byParent(data.evidencias, 'id_dba');
        for (const e of data.estandares) {
            const context = {
                id_grado: e.grado, grado: grades.get(String(e.grado)) || e.grado,
                id_materia: e.id_materia_oficial, materia: subjects.get(String(e.id_materia_oficial)) || e.id_materia_oficial,
                periodo: e.id_periodo, id_estandar: e.id_estandar,
                estandar: e.nombre_estandar, descripcion_estandar: e.descripcion_estandar
            };
            rows.push({ ...context, tipo_registro: 'estandar' });
            for (const d of dbas.get(String(e.id_estandar)) || []) {
                const parent = { ...context, id_dba: d.id_dba, dba: d.nombre_dba, descripcion_dba: d.descripcion_dba, palabras_clave: d.palabras_clave };
                rows.push({ ...parent, tipo_registro: 'dba' });
                for (const eje of ejes.get(String(d.id_dba)) || []) {
                    rows.push({ ...parent, tipo_registro: 'eje_tematico', id_eje_tematico: eje.id_eje_tematico, eje_tematico: eje.nombre_eje_tematico, descripcion_eje: eje.descripcion_eje_tematico });
                }
                for (const ev of evidencias.get(String(d.id_dba)) || []) {
                    rows.push({ ...parent, tipo_registro: 'evidencia', id_evidencia_aprendizaje: ev.id_evidencia_aprendizaje,
                        evidencia: ev.descripcion_evidencia, origen: ev.origen, modelo: ev.modelo, fecha_creacion: ev.fecha_creacion,
                        datos_evidencia: JSON.stringify(ev) });
                }
            }
        }
        return rows;
    }

    const columns = ['tipo_registro', 'id_grado', 'grado', 'id_materia', 'materia', 'periodo',
        'id_estandar', 'estandar', 'descripcion_estandar', 'id_dba', 'dba', 'descripcion_dba', 'palabras_clave',
        'id_eje_tematico', 'eje_tematico', 'descripcion_eje', 'id_evidencia_aprendizaje', 'evidencia',
        'origen', 'modelo', 'fecha_creacion', 'datos_evidencia'];

    function csvCell(value) {
        let text = String(value ?? '');
        // Las hojas de cálculo no deben ejecutar textos curriculares como fórmulas.
        if (/^[\s\uFEFF]*[=+@-]/u.test(text) || /^[\t\r\n]/.test(text)) text = "'" + text;
        return '"' + text.replace(/"/g, '""') + '"';
    }

    function toCsv(data) {
        return '\uFEFF' + [columns, ...flatRows(data).map(row => columns.map(key => row[key]))]
            .map(row => row.map(csvCell).join(',')).join('\r\n') + '\r\n';
    }

    function toTxt(data) {
        const lines = ['MALLA CURRICULAR', `Exportado: ${data.exportado_en}`,
            `Filtros (IDs): grado ${data.filtros.grado || 'todos'} | materia ${data.filtros.materia || 'todas'} | periodo ${data.filtros.periodo || 'todos'}`,
            `Totales: ${data.totales.estandares} estándares, ${data.totales.dbas} DBA, ${data.totales.ejes_tematicos} ejes temáticos, ${data.totales.evidencias} evidencias.`, ''];
        const detail = (label, value, indent = '') => {
            if (value !== undefined && value !== null && value !== '') {
                lines.push(`${indent}${label}: ${String(value).replace(/\r\n|\r/g, '\n').replace(/\n/g, '\n' + indent + '  ')}`);
            }
        };
        for (const row of flatRows(data)) {
            if (row.tipo_registro === 'estandar') {
                lines.push('='.repeat(64), `Grado: ${row.grado} (#${row.id_grado}) | Materia: ${row.materia} (#${row.id_materia}) | Periodo: ${row.periodo}`);
                detail(`Estándar #${row.id_estandar}`, row.estandar);
                detail('Descripción del estándar', row.descripcion_estandar);
            } else if (row.tipo_registro === 'dba') {
                detail(`DBA #${row.id_dba}`, row.dba, '  ');
                detail('Descripción del DBA', row.descripcion_dba, '  ');
                detail('Palabras clave', row.palabras_clave, '  ');
            } else if (row.tipo_registro === 'eje_tematico') {
                detail(`Eje temático #${row.id_eje_tematico}`, row.eje_tematico, '    ');
                detail('Descripción del eje', row.descripcion_eje, '    ');
            } else {
                detail(`Evidencia #${row.id_evidencia_aprendizaje}`, row.evidencia, '    ');
                for (const [key, value] of Object.entries(JSON.parse(row.datos_evidencia))) {
                    if (!['id_dba', 'id_evidencia_aprendizaje', 'descripcion_evidencia'].includes(key)) detail(key, value, '      ');
                }
            }
            lines.push('');
        }
        return lines.join('\r\n');
    }

    function serialize(data, format) {
        if (format === 'json') return { text: JSON.stringify(data, null, 2), type: 'application/json;charset=utf-8' };
        if (format === 'csv') return { text: toCsv(data), type: 'text/csv;charset=utf-8' };
        if (format === 'txt') return { text: toTxt(data), type: 'text/plain;charset=utf-8' };
        throw new Error('Formato de exportación no admitido.');
    }

    if (typeof module !== 'undefined' && module.exports) module.exports = { selectData, flatRows, serialize };
    if (typeof document === 'undefined') return;

    document.addEventListener('DOMContentLoaded', () => {
        const $ = id => document.getElementById(id), ws = window.dbaWorkspace;
        const controls = ['export-grado', 'export-materia', 'export-periodo', 'export-formato'].map($);
        let busy = false;
        const filters = () => ({ grado: $('export-grado').value, materia: $('export-materia').value, periodo: $('export-periodo').value });
        const status = (text, error = false) => {
            $('export-result').textContent = text;
            $('export-result').className = `import-result ${error ? 'error' : 'success'}`;
        };
        function render() {
            const data = selectData(ws.getData(), filters()), t = data.totales;
            $('export-summary').textContent = t.estandares
                ? `${t.estandares} estándares · ${t.dbas} DBA · ${t.ejes_tematicos} ejes temáticos · ${t.evidencias} evidencias`
                : 'No hay registros para los filtros seleccionados.';
            $('btn-exportar').disabled = busy || !t.estandares;
        }
        function fill(id, rows, valueKey, labelKey, empty) {
            const select = $(id), value = select.value;
            select.replaceChildren(new Option(empty, ''), ...rows.map(row => new Option(row[labelKey], row[valueKey])));
            select.value = value;
        }
        document.addEventListener('dba:data', () => {
            const data = ws.getData();
            fill('export-grado', data.grados, 'id_grado', 'nombre', 'Todos los grados');
            fill('export-materia', data.materias, 'id_materia', 'nombre_materia', 'Todas las materias');
            render();
        });
        controls.forEach(control => control.addEventListener('change', () => { status(''); render(); }));
        $('export-use-editor').addEventListener('click', () => {
            $('export-grado').value = $('select-grado').value;
            $('export-materia').value = $('select-materia').value;
            status('Se aplicaron el grado y la materia del panel lateral.');
            render();
        });
        $('btn-exportar').addEventListener('click', async () => {
            if (busy) return;
            const selected = filters(), format = $('export-formato').value;
            busy = true;
            controls.forEach(control => { control.disabled = true; });
            $('export-use-editor').disabled = true;
            render();
            status('Actualizando los datos para preparar la descarga…');
            try {
                if (!await ws.refresh()) throw new Error('No se pudieron actualizar los datos. Intente descargar de nuevo.');
                const data = selectData(ws.getData(), selected);
                if (!data.estandares.length) throw new Error('No hay registros para los filtros seleccionados.');
                const file = serialize(data, format);
                const url = URL.createObjectURL(new Blob([file.text], { type: file.type }));
                const link = document.createElement('a');
                link.href = url;
                link.download = `malla_grado-${selected.grado || 'todos'}_materia-${selected.materia || 'todas'}_periodo-${selected.periodo || 'todos'}_${data.exportado_en.slice(0, 10)}.${format}`;
                link.hidden = true;
                document.body.appendChild(link);
                try { link.click(); } finally { link.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000); }
                status(`Archivo ${format.toUpperCase()} preparado. Descarga iniciada.`);
            } catch (error) {
                status(error.message || 'No fue posible preparar el archivo.', true);
            } finally {
                busy = false;
                controls.forEach(control => { control.disabled = false; });
                $('export-use-editor').disabled = false;
                render();
            }
        });
    });
})();
