from pathlib import Path
import json,hashlib,collections,re
root=Path(__file__).parent
out=root.parent.parent/'output'/'auditoria-curricular-2026-11-01'
plan=json.loads((out/'plan-curricular.json').read_text(encoding='utf-8'))
receipt=json.loads((out/'resultado-aplicacion.json').read_text(encoding='utf-8'))
after=json.loads((out/'snapshot-aplicado.json').read_text(encoding='utf-8'))
records=plan['registros']; areas=plan['areas']
assert hashlib.sha256((out/'plan-curricular.json').read_bytes()).hexdigest()==receipt['plan_sha256']

seq=['# Distribución curricular por semanas','',
'Grados 6.º a 9.º. Cada materia comprende 40 semanas: cuatro periodos de diez. Las semanas 1-8 desarrollan las guías o unidades; la semana 9 integra y aplica; la semana 10 evalúa y refuerza. La evaluación formativa también acompaña las actividades de las semanas anteriores.',
'', 'La duración es una distribución institucional, no una prescripción temporal del MEN. Conserva la secuencia de cada cartilla o el periodo de cada malla. No se asignaron fechas de calendario ni se supuso una intensidad horaria que no consta en la solicitud.',
'','Los ejes de articulación simultánea permiten relacionar una guía con varios DBA. Se trabajan en las mismas semanas del eje principal y no se suman como tiempo adicional. Las tablas muestran una sola vez cada bloque de tiempo.','']
for area,(mid,name) in areas.items():
    seq += [f'## {name}','']
    for g in range(6,10):
        seq += [f'### Grado {g}','']
        for r in [x for x in records if x['area']==area and x['grado']==g]:
            p=r['periodo']; seq += [f'#### Periodo {p} · semanas anuales {(p-1)*10+1}-{p*10}','',f'**Estándar:** {r["estandar"]}','']
            if r['area'] in ['matematicas','sociales']: seq += [f'**Fuente:** {Path(r["source"]).name}. La numeración de módulos y guías corresponde a la cartilla.','']
            else: seq += [f'**Aprendizaje:** {r["dbas"][0]["nombre"]}',f'**Fuente:** {Path(r["source"]).name}, grado {g}, periodo {p}.','']
            seq += ['| Semanas del periodo | Unidad o eje principal | Temas |','|---|---|---|']
            axes=sorted([a for d in r['dbas'] for a in d['ejes'] if not a.get('transversal')],key=lambda a:a['inicio'])
            for a in axes:
                weeks=str(a['inicio']) if a['inicio']==a['fin'] else f'{a["inicio"]}-{a["fin"]}'
                topics='; '.join(a.get('temas',[]))
                if not topics and a.get('guia'):
                    match=re.search(r'p\. impresa (\d+)',a['descripcion']); topics='Página impresa '+match[1] if match else ''
                if not topics: topics='Aplicación con retroalimentación' if a['inicio']==9 else 'Evaluación, autoevaluación y refuerzo'
                seq.append(f'| {weeks} | {a["nombre"].replace("|","/")} | {topics.replace("|","/")} |')
            seq += ['','**Evidencias previstas:**','']
            evs=list(dict.fromkeys(e for d in r['dbas'] for e in d['evidencias']))
            seq += ['- '+ev for ev in evs]
            seq += ['']
(out/'distribucion-semanal.md').write_text('\n'.join(seq),encoding='utf-8')

# Los originales, y no las extracciones de texto, constituyen la referencia de trazabilidad.
manifest=json.loads((root/'manifest.json').read_text(encoding='utf-8'))
source_paths=sorted({r['source'] for r in records}|{x['source'] for x in manifest if Path(x['text']).name.startswith(('015-','066-'))})
sources=[]
for p in source_paths:
    data=Path(p).read_bytes()
    sources.append({'ruta':p,'sha256':hashlib.sha256(data).hexdigest(),'bytes':len(data)})
(out/'fuentes-verificadas.json').write_text(json.dumps(sources,ensure_ascii=False,indent=2),encoding='utf-8')
old=receipt['conteos_originales']; new=receipt['conteos_resultado']
official_count=sum(d.get('referencia_men') is not None and r['area'] in ['matematicas','sociales'] for r in records for d in r['dbas'])
report=['# Revisión curricular aplicada a Guagua','',
f'La base local **{receipt["database"]}** fue actualizada para **{len(areas)} materias de los grados 6.º a 9.º**, con **{len(records)} contextos de materia, grado y periodo**. El guardado se realizó mediante transacción y se conservaron los registros curriculares de otros grados. Momento del registro final: {receipt.get("fecha_ajuste_ppp",receipt.get("fecha_ampliacion",receipt["fecha"]))}.','',
'## Cambios principales','',
'- Se sustituyeron títulos de unidades usados como estándares por formulaciones de competencia. Las descripciones identifican que son contextualizaciones a partir de las fuentes.',
'- Se reconstruyó la secuencia completa de las ocho cartillas de Postprimaria de Sociales y Matemáticas. Se incluyen 162 guías en su orden original, con módulo, guía y página impresa de referencia.',
'- Se corrigieron desajustes concretos: en Matemáticas de 7.º se recuperaron media, mediana y moda; en 8.º se ubicaron correctamente álgebra, factorización, congruencia, semejanza, sólidos y probabilidad. En Sociales de 8.º se reemplazaron unidades que no correspondían a su cartilla.',
'- Tecnología de 8.º sigue ahora presentaciones, diseño de publicaciones, seguridad informática y servicios de Internet; 9.º sigue hojas de cálculo, estructuras, análisis de datos y comunicación web, como indica la malla.',
'- Según la precisión del usuario, Emprendimiento de 6.º a 9.º sigue las cartillas PPP_6_7.pdf y PPP_8_9.pdf. Se distribuyen sus seis módulos y dieciocho guías por cartilla con progresión entre años: 6.º y 8.º formulan; 7.º y 9.º gestionan, evalúan y proyectan. Educación Física conserva su malla institucional. [Consultar el ajuste detallado](../auditoria-emprendimiento-ppp-2026-11-01/informe-ajuste.md).',
'- Los contenidos de Ciencias Naturales que figuraban bajo Física se asignaron al área correcta. Se creó el catálogo de Lengua Castellana y se recuperó la asociación de los registros que tenían una materia inexistente.',
'- Se incorporaron al catálogo Ciencias Naturales, Educación Artística, Lengua Castellana, Inglés, Ética y Valores, Educación Religiosa y Cátedra de la Paz; todas tienen los cuatro periodos de cada grado.',
'- Física cuenta con sus propios cuatro periodos de 6.º a 9.º a partir de `Plan-de-asignatura-Física.pdf`. Este archivo, suministrado en la carpeta de mallas, identifica a la Institución Educativa San Rafael. Se registra como referencia externa; no se presenta como documento aprobado por La Josefina. Sus unidades de diez semanas se corresponden con los periodos. Los aprendizajes no se atribuyen a DBA oficiales de grados distintos.',
'- Se corrigieron problemas de redacción de las propias mallas: pronombres mezclados, fragmentos inconclusos, términos mal escritos y evidencias que no correspondían a los temas. En Naturales de 7.º se hicieron explícitos los ciclos del agua y del nitrógeno que aparecían en el DBA y faltaban en los ejes.',
'- Los casos coloniales de Sociales de 7.º se sitúan después del siglo XV; se conserva su comparación con sociedades medievales sin presentar ambas etapas como contemporáneas.',
'- Las evidencias se redactaron como desempeños observables: interpretar, comparar, construir, representar, argumentar y verificar productos o procedimientos. Se retiraron de los campos activos propuestas genéricas o ajenas al aprendizaje.','',
'## Alcance y cantidades','',
'| Registro de 6.º a 9.º | Antes | Después |','|---|---:|---:|',
f'| Estándares | {old["estandar"]} | {new["estandar"]} |',
f'| DBA y aprendizajes contextualizados | {old["dba"]} | {new["dba"]} |',
f'| Ejes temáticos | {old["eje_tematico"]} | {new["eje_tematico"]} |',
f'| Evidencias de aprendizaje | {old["evidencia_de_aprendizaje"]} | {new["evidencia_de_aprendizaje"]} |','',
'La variación de cantidades refleja consolidación de registros superpuestos, incorporación de áreas y separación de temas y evidencias que antes estaban acumulados en un solo campo. Los ejes incluyen bloques de desarrollo, aplicación y evaluación, y articulaciones simultáneas entre una guía y varios DBA; no representan semanas adicionales.','',
'| Materia | Grados | Periodos por grado | Semanas anuales por grado |','|---|---|---:|---:|']
report += [f'| {name} | 6, 7, 8 y 9 | 4 | 40 |' for _,name in areas.values()]
report += ['','## Distribución temporal','',
'Cada periodo cubre exactamente diez semanas: ocho para desarrollo de guías o unidades, una de aplicación e integración y una de evaluación, autoevaluación y refuerzo. La exploración de saberes previos y la evaluación formativa acompañan el desarrollo de los temas. La duración de las guías se distribuyó considerando su extensión y su trabajo conceptual; los bloques de las mallas mantienen el periodo de origen.',
'','[Consultar la distribución semanal completa](distribucion-semanal.md).','',
'## Referentes oficiales y formulaciones institucionales','',
f'En Sociales y Matemáticas hay {official_count} registros de DBA articulados con los documentos oficiales del MEN. Un mismo DBA puede retomarse en varios periodos con diferentes ejes y evidencias. Se normalizaron palabras separadas por la extracción del PDF y puntuación sin cambiar el sentido de los enunciados. Las evidencias y las semanas son contextualizaciones institucionales, no transcripciones oficiales.',
'','Los temas de Postprimaria sin correspondencia directa con un DBA se identifican expresamente como aprendizajes complementarios. En las demás materias, los aprendizajes revisados se identifican como contextualizaciones de las mallas; no se les atribuye una condición de DBA oficial que su fuente no demuestra. En Educación Religiosa se conserva el enfoque de la malla y se evalúan comprensión y argumentación, sin exigir adhesión religiosa.',
'','Los complementos de las mallas permiten articular los DBA 8 y 10 de Matemáticas de 8.º con modelos de variación, y el DBA 1 de Sociales de 8.º con cooperación entre Estados. El DBA 4 de Matemáticas de 9.º se desarrolla en Geometría con volumen y capacidad. Esta asociación es una cobertura de planeación; no certifica que los estudiantes hayan alcanzado los aprendizajes.',
'','Fuentes oficiales de contraste: [DBA Matemáticas V.2](https://www.colombiaaprende.edu.co/sites/default/files/files_public/2022-06/DBA_Matematicas-min.pdf) y [DBA Ciencias Sociales V.1](https://www.colombiaaprende.edu.co/sites/default/files/files_public/2022-06/DBA_C.Sociales-V2.pdf). El nombre del archivo de Sociales contiene “V2”, pero su portada y páginas identifican el documento como V.1.','',
'## Comprobaciones realizadas','',
'- Simulación de escritura con reversión completa y comparación exacta con los datos anteriores.',
f'- Validación de las {len(records)} secuencias: semanas 1 a 10, sin huecos ni doble conteo de articulaciones simultáneas.',
'- Todas las guías de las ocho cartillas están incluidas, una sola vez como eje temporal principal.',
'- Todos los nuevos DBA y aprendizajes tienen ejes y evidencias; todos los estándares de 6.º a 9.º tienen una materia válida.',
'- No se generaron DBA, ejes ni evidencias huérfanos.',
'- Se comprobó que las filas curriculares de otros grados permanecieran idénticas.',
'- Se conservaron los identificadores principales de estándar por materia, grado y periodo y, cuando existían, el primer DBA asociado. La bitácora conserva todos los identificadores anteriores y posteriores.',
'- El contenido aplicado quedó registrado junto con el hash del plan y los hashes de los documentos fuente.','',
'## Hallazgo histórico conservado','',
f'Existían **{receipt["evidencias_historicas_sin_dba_conservadas"]} evidencias sin un DBA existente**, anteriores a esta revisión. La BD actual no permite establecer con certeza su materia y grado. Se conservaron sin cambios y no se asociaron por suposición. No se incluyen en las {new["evidencia_de_aprendizaje"]} evidencias del alcance corregido. Su detalle puede consultarse en el respaldo original; el módulo ya las excluía de su lectura por utilizar una unión con la tabla DBA.','',
'## Respaldo y trazabilidad','',
'- `respaldo-original.json`: estado original de las tablas curriculares y del catálogo.',
'- `plan-curricular.json`: estándares, aprendizajes, ejes, evidencias, semanas y fuentes de los registros aplicados.',
'- `resultado-aplicacion.json`: cantidades, validaciones e identificadores de la operación.',
'- `snapshot-aplicado.json`: estado persistido esperado después de la actualización.',
'- `fuentes-verificadas.json`: rutas, tamaños y hashes de los documentos utilizados.',
'','Verificación de lectura desde la raíz del proyecto: `php tmp/auditoria_curricular/migrate.php --verify`. La restauración protegida se ejecuta con `--restore`; exige que no haya cambios posteriores y recupera exactamente las filas originales. No debe ejecutarse para consultar el resultado.','',
'## Archivos fuente utilizados','']
for s in sources: report.append('- '+s['ruta'])
(out/'informe-revision.md').write_text('\n'.join(report)+'\n',encoding='utf-8')
print(json.dumps({'fuentes':len(sources),'guias':sum(1 for r in records if r['area'] in ['matematicas','sociales'] for d in r['dbas'] for a in d['ejes'] if a.get('guia') and not a.get('transversal')),'dbas_men_por_periodo':official_count,'informe':str(out/'informe-revision.md')},ensure_ascii=False))
