from pathlib import Path
import json,hashlib,shutil
root=Path(__file__).parent
base=root.parent.parent/'output'
main=base/'auditoria-curricular-2026-11-01'
out=base/'auditoria-emprendimiento-ppp-2026-11-01'
receipt=json.loads((out/'resultado-aplicacion.json').read_text(encoding='utf-8'))
new=json.loads((out/'plan-ppp.json').read_text(encoding='utf-8'))['registros']
before=json.loads((out/'respaldo-anterior.json').read_text(encoding='utf-8'))
after=json.loads((out/'snapshot-aplicado.json').read_text(encoding='utf-8'))
full=json.loads((main/'plan-curricular.json').read_text(encoding='utf-8'))
old_receipt=json.loads((main/'resultado-aplicacion.json').read_text(encoding='utf-8'))
current=json.loads((main/'snapshot-aplicado.json').read_text(encoding='utf-8'))
history=out/'metadata-anterior';history.mkdir(exist_ok=True)
if current!=after:
    assert current==before,'El informe general no coincide con el estado anterior respaldado.'
    for name in ['plan-curricular.json','resultado-aplicacion.json','snapshot-aplicado.json','informe-revision.md','distribucion-semanal.md','fuentes-verificadas.json']:
        assert not (history/name).exists(),'No se sobrescribe la historia del informe.'
        shutil.copy2(main/name,history/name)
    full['registros']=[r for r in full['registros'] if r['area']!='emprendimiento']+new
    full['registros'].sort(key=lambda r:(r['materia'],r['grado'],r['periodo']))
    (main/'plan-curricular.json').write_text(json.dumps(full,ensure_ascii=False,indent=2),encoding='utf-8')
    old_receipt['contextos']=[c for c in old_receipt['contextos'] if c['area']!='emprendimiento']+receipt['contextos']
    for table,ids in receipt['ids_anteriores'].items():
        old_receipt['ids_resultado'][table]=[i for i in old_receipt['ids_resultado'][table] if i not in ids]+receipt['ids_resultado'][table]
        old_receipt['conteos_resultado'][table]=len(old_receipt['ids_resultado'][table])
    old_receipt['fecha_ajuste_ppp']=receipt['fecha']
    old_receipt['ajuste_ppp']='Emprendimiento de 6 a 9 según cartillas PPP; Educación Física continúa según malla.'
    old_receipt['plan_sha256']=hashlib.sha256((main/'plan-curricular.json').read_bytes()).hexdigest()
    (main/'resultado-aplicacion.json').write_text(json.dumps(old_receipt,ensure_ascii=False,indent=2),encoding='utf-8')
    (main/'snapshot-aplicado.json').write_text(json.dumps(after,ensure_ascii=False,indent=2),encoding='utf-8')

report=['# Ajuste de Educación Física y Emprendimiento','',
'Educación Física de 6.º a 9.º se mantiene basada en la malla institucional. Emprendimiento de 6.º a 9.º fue corregido para seguir las cartillas de Proyectos Pedagógicos Productivos de Postprimaria, según la indicación del usuario.',
'',f'Ajuste guardado: {receipt["fecha"]}. Se preservaron los identificadores de los dieciséis estándares y aprendizajes de Emprendimiento y las filas de las otras materias permanecieron idénticas al respaldo.',
'','## Fuentes y progresión','',
'| Grado | Fuente | Secuencia anual |','|---|---|---|',
'| 6.º | PPP_6_7.pdf | Módulos 1-3, guías 1-9: diagnóstico veredal y formulación del perfil del proyecto. |',
'| 7.º | PPP_6_7.pdf | Módulos 4-6, guías 10-18: gestión, registro de cuentas, evaluación y proyección comunitaria. |',
'| 8.º | PPP_8_9.pdf | Módulos 1-3, guías 1-9: diagnóstico municipal, selección del emprendimiento y plan de negocios sostenible. |',
'| 9.º | PPP_8_9.pdf | Módulos 4-6, guías 10-18: cronograma, implementación, indicadores, evaluación, feria y mecanismos de apoyo. |','',
'Cada cartilla contiene seis módulos y dieciocho guías. La distribución entre años conserva la progresión de formulación y posterior gestión: en la cartilla de 8.º–9.º la guía 7 identifica expresamente el plan de negocios de 8.º (página impresa 64); las guías 10 y 11 ubican la gestión en 9.º y retoman el emprendimiento trabajado en 8.º (páginas impresas 94 y 100). Para 6.º–7.º se aplica la misma continuidad entre formulación y gestión; la guía 18 se refiere a los PPP de 7.º. La asignación precisa a periodos y semanas es una contextualización institucional, no una calendarización prescrita por el MEN.',
'','Los aprendizajes guardados en el campo DBA se identifican como formulaciones contextualizadas de las guías; no se presentan como DBA oficiales específicos de Emprendimiento. Cada periodo tiene diez semanas: cuatro bloques de dos semanas, aplicación en la novena y evaluación y refuerzo en la décima.',
'','## Educación Física','',
'Se comprobaron sus dieciséis contextos de grado y periodo. Todos identifican como fuente la malla `Educación física secundaria 6-11.docx`. Se compararon todas las filas curriculares anteriores y posteriores: el ajuste PPP no modificó estándares, aprendizajes, ejes ni evidencias de Educación Física.',
'','## Distribución de Emprendimiento','']
for r in new:
    report += [f'### Grado {r["grado"]} · periodo {r["periodo"]}','',f'**Estándar:** {r["estandar"]}','',f'**Aprendizaje:** {r["dbas"][0]["nombre"]}','', '| Semanas del periodo | Bloque | Guías de referencia |','|---|---|---|']
    for a in r['dbas'][0]['ejes']:
        weeks=str(a['inicio']) if a['inicio']==a['fin'] else f'{a["inicio"]}-{a["fin"]}'
        refs=', '.join(map(str,a.get('guias',[]))) or 'Integración del periodo'
        report.append(f'| {weeks} | {a["nombre"]} | {refs} |')
    report += ['','**Evidencias:**','']+['- '+e for e in r['dbas'][0]['evidencias']]+['']
report += ['## Respaldo y comprobación','',
'- Se guardó `respaldo-anterior.json` antes de modificar los datos.',
'- Se probó la escritura en una transacción revertida y se comprobó la reversión exacta.',
'- Se aplicó el ajuste y se verificó su persistencia desde una conexión nueva.',
'- Se conservaron las otras materias, los registros de otros grados y las evidencias históricas sin asociación.',
'- Emprendimiento queda con 16 estándares, 16 aprendizajes, 96 ejes y 64 evidencias; cada grado cubre 40 semanas.',
'- Las dos cartillas quedan cubiertas en su totalidad a través de la progresión 6.º–7.º y 8.º–9.º, sin repetir toda la cartilla en ambos grados.',
'- El informe general y la distribución semanal se actualizaron; sus versiones anteriores están en `metadata-anterior`.',
'','Verificación de lectura: `php tmp/auditoria_curricular/migrate_ppp.php --verify`.','']
(out/'informe-ajuste.md').write_text('\n'.join(report),encoding='utf-8')
print('Informe de ajuste y datos de trazabilidad actualizados.')
