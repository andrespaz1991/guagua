from pathlib import Path
import json
root=Path(__file__).parent
t=json.loads((root/'snapshot-original.json').read_text(encoding='utf-8'))['tables']
for grade in range(6,10):
    text=[]
    for s in t['estandar']['rows']:
        if s['grado']!=grade: continue
        text.append(f"\nEST {s['id_estandar']} M{s['id_materia_oficial']} P{s['id_periodo']} {s['nombre_estandar']}\nDESC: {s['descripcion_estandar']}")
        for d in t['dba']['rows']:
            if d['id_estandar']!=s['id_estandar']: continue
            text.append(f"DBA {d['id_dba']}: {d['nombre_dba']}\nDESC: {d['descripcion_dba']}\nCLAVES: {d['palabras_clave']}")
            for e in t['eje_tematico']['rows']:
                if e['id_dba']==d['id_dba']: text.append(f"EJE {e['id_eje_tematico']}: {e['nombre_eje_tematico']} | {e['descripcion_eje_tematico']}")
            for e in t['evidencia_de_aprendizaje']['rows']:
                if e['id_dba']==d['id_dba']: text.append(f"EVID {e['id_evidencia_aprendizaje']}: {e['descripcion_evidencia']} | {e['origen']}")
    (root/f'bd-grado-{grade}.txt').write_text('\n'.join(text),encoding='utf-8')
