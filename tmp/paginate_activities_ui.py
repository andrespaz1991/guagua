from pathlib import Path

path = Path('apps/reporte_actividades/assets/app.js')
code = path.read_text(encoding='utf-8-sig')

def update_function(name, next_name, edits):
    global code
    start = code.index('  function ' + name + '(')
    end = code.index('  function ' + next_name + '(', start)
    part = code[start:end]
    for old, new in edits:
        if part.count(old) != 1:
            raise RuntimeError(f'{name}: expected unique fragment {old[:90]}')
        part = part.replace(old, new, 1)
    code = code[:start] + part + code[end:]

update_function('studentTable', 'activityTable', [
    ("    return viewHead(", "    const page = pageOf(groups, 'students');\n    return viewHead("),
    ('<div class="ra-panel ra-table-panel"><div class="ra-table-wrap">', '<div class="ra-panel ra-table-panel" data-page-region="students">${tableBanner(\'Seguimiento individual\', groups.length, \'estudiantes\', \'users\')}<div class="ra-table-wrap">'),
    ('${groups.map(s => {', '${page.items.map(s => {'),
    ("}).join('')}</tbody></table></div></div>`;", "}).join('')}</tbody></table></div>${pagination('students', page, 'estudiantes')}</div>`;"),
])
update_function('activityTable', 'draw', [
    ("    return viewHead(", "    const page = pageOf(groups, 'activities');\n    return viewHead("),
    ('<div class="ra-panel ra-table-panel"><div class="ra-table-wrap">', '<div class="ra-panel ra-table-panel" data-page-region="activities">${tableBanner(\'Resultados por actividad\', groups.length, \'actividades\', \'list\')}<div class="ra-table-wrap">'),
    ('${groups.map(s => {', '${page.items.map(s => {'),
    ("}).join('')}</tbody></table></div></div>${legend()}`;", "}).join('')}</tbody></table></div>${pagination('activities', page, 'actividades')}</div>${legend()}`;"),
])
update_function('matrix', 'studentTable', [
    ('    const f = filters(), lookup', "    const sectionPage = pageOf(sets, 'matrix', 5);\n    const f = filters(), lookup"),
    (' + sets.map((set, index) => {', ' + sectionPage.items.map((set, index) => {'),
    ('      return `<details', "      const rowKey = 'matrix-rows-' + set.id, studentPage = pageOf(students, rowKey);\n      return `<details"),
    ('class="ra-panel ra-matrix-panel" ${f.subject || f.student || sets.length <= 3 || index === 0 ? \'open\' : \'\'}', 'class="ra-panel ra-matrix-panel" data-matrix-id="${escape(set.id)}" data-page-region="${escape(rowKey)}" ${(matrixOpen.has(set.id) ? matrixOpen.get(set.id) : f.subject || f.student || sets.length <= 3 || index === 0) ? \'open\' : \'\'}'),
    ('${students.map(student => {', '${studentPage.items.map(student => {'),
    ("}).join('')}</tbody></table></div><p class=", "}).join('')}</tbody></table></div>${pagination(rowKey, studentPage, 'estudiantes', true)}<p class="),
    ("    }).join('') + legend();", "    }).join('') + pagination('matrix', sectionPage, 'secciones') + legend();"),
])
update_function('drawStudentDialog', 'showStudent', [
    ('    const bySubject = grouped(rows,', "    const page = pageOf(rows, 'student-detail');\n    const bySubject = grouped(page.items,"),
    ('Este estudiante no tiene registros en ese estado.</p>\'}`;', "Este estudiante no tiene registros en ese estado.</p>'}${pagination('student-detail', page, 'actividades')}`;"),
])
update_function('drawDiagnostics', 'notify', [
    ("    $('diagnostics').hidden = false;", "    const page = pageOf(data.sources, 'sources');\n    $('diagnostics').hidden = false;"),
    ('${data.sources.map(s =>', '${page.items.map(s =>'),
    ("</tbody></table></div>${warnings.length", "</tbody></table></div>${pagination('sources', page, 'archivos')}${warnings.length"),
])
path.write_text(code, encoding='utf-8')
