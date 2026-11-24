from pathlib import Path
import json,re,math,unicodedata,hashlib
from guias import GUIDES,MODULE_ENDS,PERIOD_ENDS
from cursos import COURSES,STANDARDS
from complementarias import AXES

ROOT=Path(__file__).parent
OUT=ROOT.parent.parent/'output'/'auditoria-curricular-2026-11-01'
OUT.mkdir(exist_ok=True)
MAN=json.loads((ROOT/'manifest.json').read_text(encoding='utf-8'))
MALLAS=json.loads((ROOT/'mallas-registros.json').read_text(encoding='utf-8'))
ORIGINAL=json.loads((ROOT/'snapshot-original.json').read_text(encoding='utf-8'))
AREAS={
'matematicas':(1,'Matemáticas'),'sociales':(3,'Ciencias Sociales'),
'fisica_educacion':(4,'Educación Física'),'geometria':(5,'Geometría'),
'fisica':(6,'Física'),
'tecnologia':(7,'Tecnología e informática'),'urbanidad':(8,'Urbanidad'),
'emprendimiento':(9,'Emprendimiento'),'naturales':(10,'Ciencias Naturales'),
'artes':(11,'Educación Artística'),'lenguaje':(12,'Lengua Castellana'),
'ingles':(13,'Inglés'),'etica':(14,'Ética y Valores'),
'religion':(15,'Educación Religiosa'),'paz':(16,'Cátedra de la Paz')}

def clean(s):
    s=unicodedata.normalize('NFC',s or '').replace('\u00ad','').replace('\xa0',' ')
    s=re.sub(r'[•*\uf06c\uf046\uf0fc]','',s)
    s=re.sub(r'\((?:Proyecto|Tiempo Libre|Democracia|PRAE|Seguridad|Educaci[oó]n Sexual)[^)]*\)?','',s,flags=re.I)
    s=re.sub(r'^[\s.,:;"“”\-–—\d)]+','',s)
    s=re.sub(r'\s+',' ',s).strip(' .;:,"“”-–—')
    fixes={'Arte astracto':'Arte abstracto','a Autenticidad':'La autenticidad','colombina de la conquista':'colombiana de la Conquista',
    '((alores Institucionales)':'Valores institucionales','Fundamentación Axiológica':'Fundamentación axiológica',
    'La responsabilidad de ayudar El sentido del domingo':'La responsabilidad de ayudar; el sentido del domingo',
    'Chile un compromiso con la vida':'Compromiso con la vida',
    'Past present':'Simple past: contraste con el presente simple','litlle':'little','undislikes':'dislikes',
    'The demostratives':'Demonstratives','The comands':'Classroom commands','enviroment':'environment',
    'home thigs':'household objects','Vocabulay':'Vocabulary','Use to':'Used to','can to be':'can be',
    'Uses to ING':'Uses of -ing forms','El Scandisk':'Comprobación del almacenamiento en un entorno de práctica',
    'El Desfragmentador de disco':'Organización del almacenamiento y criterios de mantenimiento',
    'Formatear y copiar un disco':'Copias de seguridad y simulación del formateo de una unidad de práctica',
    'Diseñando con Microsoft Publisher':'Diseño de publicaciones con una herramienta disponible',
    'Microsoft Publisher':'herramientas de diseño de publicaciones',
    'La Inteligencia':'La inteligencia','La Originalidad':'La originalidad',
    'Conocimiento de las actividades a conciencia corporal, espacial y temporal':'Conciencia corporal, espacial y temporal',
    'Introducción a la danza atléticas de pista y campo':'Introducción a la danza y al atletismo de pista y campo',
    'El Auto concepto':'El autoconcepto','Formateo de documentos':'Formato de documentos','Formateo de datos':'Formato de datos'}
    fixes.update({'conjunciones y proposiciones':'conjunciones y preposiciones','paranomasia':'paronomasia',
      'el punto los, puntos suspensivos':'el punto, los puntos suspensivos',
      'Signos de puntuación (el punto':'Puntuación y recursos tipográficos (el punto',
      'Have / haven’t':'Have / do not have','Going to… (past)':'Was/were going to: planes pasados',
      'Present continuous time (6)':'Present continuous','There is / There are (5)':'There is / there are',
      'There was / there were. (6)':'There was / there were','Wh questions (3/4)':'Wh- questions',
      'Libertad Vs Libertinaje':'Libertad y responsabilidad','Los Valores (Valores Institucionales':'Valores institucionales',
      'Accesorios de Windows (WordPad, Paint, Bloc de notas y Calculadora)':'Aplicaciones básicas disponibles: editor de texto, dibujo y calculadora',
      'Numbers to ':'Numbers ','Number to ':'Numbers ','Continuous past':'Past continuous',
      'Body’s parts':'Body parts','body’s parts':'body parts','Continuous present time':'Present continuous'})
    for a,b in fixes.items(): s=s.replace(a,b)
    # Sustituye paréntesis mal cerrados del original por una redacción lineal.
    s=s.replace('(',': ').replace(')','')
    s=re.sub(r':\s*:',':',s)
    s=re.sub(r'\s+',' ',s).strip(' .;:,')
    return s[0].upper()+s[1:] if s else ''

def source(prefix): return next(x['source'] for x in MAN if Path(x['text']).name.startswith(prefix+'-'))

def official(area):
    text=(ROOT/'oficiales'/f'{area}.txt').read_text(encoding='utf-8'); out={}
    for page in re.split(r'=== PÁGINA PDF \d+ ===',text):
        gm=re.search(r'Grado (\d+)',page)
        if not gm: continue
        g=int(gm[1])
        for m in re.finditer(r'\n(\d+)\.\s*\n(.*?)\nEvidencias de\s+aprendizaje',page,re.S):
            value=' '.join(m[2].split())
            if len(value)<650: out[(g,int(m[1]))]=value
    if area=='matematicas':
        out[(6,1)]='Interpreta los números enteros y racionales (en sus representaciones de fracción y de decimal) con sus operaciones, en diferentes contextos, al resolver problemas de variación, repartos, particiones, estimaciones, etc. Reconoce y establece diferentes relaciones de orden y equivalencia y las utiliza para argumentar procedimientos.'
        out[(6,5)]='Propone y desarrolla estrategias de estimación, medición y cálculo de diferentes cantidades (ángulos, longitudes, áreas, volúmenes, etc.) para resolver problemas.'
    else:
        out[(6,5)]='Analiza los legados que las sociedades americanas prehispánicas dejaron en diversos campos.'
        out[(7,1)]='Comprende que las representaciones del mundo han cambiado a partir de las visiones de quienes las elaboran y de los avances de la tecnología.'
        out[(7,7)]='Comprende la responsabilidad que tiene una sociedad democrática para evitar la violación de los derechos fundamentales de sus ciudadanos.'
        out[(8,7)]='Evalúa hechos trascendentales para la dignidad humana (abolición de la esclavitud, reconocimiento de los derechos de las mujeres, derechos de las minorías) y describe las discriminaciones que aún se presentan.'
        out[(8,8)]='Comprende la importancia de las asociaciones, los gremios, los movimientos y organizaciones sindicales en la defensa de los derechos colectivos.'
    return out

OFF={a:official(a) for a in ['matematicas','sociales']}
OFF_URL={'matematicas':'https://www.colombiaaprende.edu.co/sites/default/files/files_public/2022-06/DBA_Matematicas-min.pdf','sociales':'https://www.colombiaaprende.edu.co/sites/default/files/files_public/2022-06/DBA_C.Sociales-V2.pdf'}

CORE_STD={
('matematicas',6):['Utilizo números racionales y enteros y sus representaciones para resolver y argumentar situaciones de medida y cambio.','Justifico procedimientos aritméticos con números enteros utilizando relaciones y propiedades de las operaciones.','Relaciono propiedades numéricas y geométricas con sistemas de medida para resolver situaciones del entorno.','Construyo e interpreto representaciones geométricas y estadísticas para plantear y resolver problemas.'],
('matematicas',7):['Represento relaciones entre cantidades y resuelvo problemas mediante ecuaciones y operaciones con racionales.','Utilizo representaciones y propiedades de los números racionales para resolver problemas de comparación y proporcionalidad.','Analizo variaciones y relaciones métricas y espaciales mediante tablas, gráficas y mediciones.','Relaciono transformaciones geométricas y representaciones de datos con situaciones de mi entorno.'],
('matematicas',8):['Utilizo números reales y lenguaje algebraico para representar y justificar relaciones matemáticas.','Construyo expresiones algebraicas equivalentes y justifico procedimientos de operación y factorización.','Relaciono equivalencias algebraicas, propiedades geométricas y medidas para resolver problemas.','Generalizo procedimientos de medida e interpreto datos y probabilidades mediante representaciones y argumentos.'],
('matematicas',9):['Modelo situaciones mediante ecuaciones y sistemas lineales y verifico sus soluciones.','Relaciono expresiones algebraicas, funciones y representaciones gráficas para interpretar situaciones de variación.','Analizo propiedades de funciones y relaciones de semejanza y proporcionalidad en la resolución de problemas.','Selecciono representaciones y procedimientos estadísticos y probabilísticos y verifico sus resultados con herramientas.'],
('sociales',6):['Reconozco y valoro legados culturales y explicaciones sobre el mundo mediante fuentes y comparaciones respetuosas.','Relaciono organización social, cultura y territorio en sociedades antiguas y pueblos originarios.','Analizo relaciones entre producción, población, recursos y formas de poder en distintos contextos históricos.','Comparo formas de gobierno y participo en alternativas democráticas de resolución de conflictos y respeto de la diversidad.'],
('sociales',7):['Reconozco legados de sociedades medievales, precolombinas y renacentistas y los ubico en su contexto histórico.','Relaciono territorio, tecnología, ambiente y producción en sociedades medievales y coloniales respetando sus cronologías.','Analizo cambios demográficos, economía y organización política de sociedades medievales y coloniales.','Interpreto protestas y conflictos históricos y aplico principios de participación, derechos y mediación.'],
('sociales',8):['Analizo relaciones entre Ilustración, revoluciones, capitalismo e independencias en la formación de sociedades modernas.','Relaciono modernización, ambiente y migraciones con transformaciones sociales y económicas del país.','Analizo causas y consecuencias de la violencia en Colombia y valoro alternativas dialogadas de transformación del conflicto.','Comparo cambios constitucionales y formas de participación y promuevo el respeto de la diversidad y de los derechos.'],
('sociales',9):['Analizo relaciones entre modelos económicos, globalización, conflictos y transformaciones sociales del siglo XX.','Relaciono problemas económicos, ambientales y migratorios con condiciones de vida y desigualdad en los territorios.','Analizo organización social y defensa del territorio y de los derechos mediante fuentes y perspectivas diversas.','Evalúo reivindicaciones de derechos y construcciones de identidad con criterios de igualdad, diversidad y participación.']}

NAT_STD={
6:['Clasifico seres vivos utilizando criterios celulares y relaciono su diversidad con la organización de los ecosistemas.','Relaciono estructuras celulares y niveles de organización con funciones de nutrición, respiración y reproducción.','Clasifico materiales y explico relaciones entre sus propiedades, condiciones físicas y métodos de separación.','Explico fenómenos eléctricos y magnéticos mediante modelos de interacción y experiencias seguras.'],
7:['Explico relaciones entre nutrición, fotosíntesis, respiración y flujos de materia y energía en ecosistemas.','Relaciono ciclos biogeoquímicos y actividades humanas con el equilibrio y cuidado de los ecosistemas.','Explico transformaciones y transferencias de energía en sistemas mecánicos utilizando modelos y observaciones.','Relaciono modelos atómicos y organización periódica con propiedades e interacción de los elementos.'],
8:['Relaciono variables de estado de los gases y transferencias de energía con el funcionamiento de máquinas térmicas.','Explico la reorganización de átomos y los intercambios de energía en cambios químicos.','Relaciono sistemas de órganos con procesos de coordinación y regulación de las funciones vitales.','Comparo mecanismos de reproducción y desarrollo y los relaciono con continuidad de la vida y cuidado de la salud.'],
9:['Analizo explicaciones de la evolución utilizando evidencias, modelos y argumentos científicos.','Relaciono información genética, herencia y expresión de rasgos con diversidad y cambio de los organismos.','Describo y predigo movimientos mediante sistemas de referencia, mediciones y modelos matemáticos.','Relaciono composición y concentración de soluciones con propiedades ácidas y básicas y sus usos.']}

PLAN=[]
for (area,g),raw in GUIDES.items():
    guide_rows=[]
    for n,line in enumerate(raw.splitlines(),1):
        title,page,refs,evidence=line.split('|')
        # Estos temas son complementos de la cartilla; no se les atribuye un DBA sin relación directa.
        numbers=[int(r) for r in refs.split(',')]
        if (area,g,n) in [('matematicas',7,15),('matematicas',7,16),('matematicas',7,17),('sociales',6,12),('sociales',6,13),('sociales',7,7),('sociales',8,7),('sociales',8,8),('sociales',8,9)]: numbers=[0]
        if (area,g,n)==('matematicas',9,6): numbers=[1]
        if (area,g,n)==('sociales',8,5): numbers=[5]
        if (area,g,n)==('sociales',6,7): numbers=[5]
        module=next(i+1 for i,end in enumerate(MODULE_ENDS[(area,g)]) if n<=end)
        guide_rows.append(dict(n=n,title=title,page=int(page),refs=numbers,evidence=evidence,module=module))
    file_source=next(x['source'] for x in MAN if Path(x['source']).name==('MT_' if area=='matematicas' else 'CS_')+f'Grado{g}.pdf')
    start=0
    for period,end in enumerate(PERIOD_ENDS[(area,g)],1):
        selected=guide_rows[start:end]; start=end
        record=dict(area=area,materia=AREAS[area][0],nombre_materia=AREAS[area][1],grado=g,periodo=period,estandar=CORE_STD[(area,g)][period-1],source=file_source,dbas=[])
        dmap={}
        durations=[1]*len(selected)
        lengths=[]
        for i,guide in enumerate(selected):
            next_page=guide_rows[guide['n']]['page'] if guide['n']<len(guide_rows) else guide['page']+8
            lengths.append(next_page-guide['page'])
        for i in sorted(range(len(selected)),key=lambda i:(-lengths[i],i))[:8-len(selected)]: durations[i]+=1
        if area=='matematicas' and g==9 and period==1: durations=[1,2,2,1,2]
        def get_dba(ref,guide):
            key=ref if ref else f"guia-{guide['n']}"
            if key not in dmap:
                if ref:
                    assert (g,ref) in OFF[area],(area,g,ref)
                    name=f'DBA {ref}: '+OFF[area][(g,ref)]
                    desc=f'Referente MEN: {"Matemáticas V.2" if area=="matematicas" else "Ciencias Sociales V.1"}, grado {g}, DBA {ref}. Se retoma por periodos según la secuencia de Postprimaria. Se normalizan espaciado y puntuación sin cambiar el sentido. Las evidencias y la distribución temporal son contextualizaciones institucionales. Fuente oficial: {OFF_URL[area]}'
                else:
                    name='Aprendizaje de Postprimaria: '+guide['evidence']
                    desc='Aprendizaje complementario de la cartilla de Postprimaria; formulación institucional. No se presenta como un DBA oficial del MEN.'
                dmap[key]=dict(nombre=name,descripcion=desc,evidencias=[],ejes=[],referencia_men=ref or None)
                record['dbas'].append(dmap[key])
            return dmap[key]
        for i,gr in enumerate(selected):
            a=1+sum(durations[:i]); b=sum(durations[:i+1])
            locator=f"{Path(file_source).name}, módulo {gr['module']}, guía {gr['n']}, p. impresa {gr['page']}"
            for ref in gr['refs']:
                d=get_dba(ref,gr)
                ev=gr['evidence']
                if area=='matematicas' and g==6 and gr['n']==24 and ref==12: ev='Realiza repeticiones de un experimento sencillo, compara frecuencias observadas y esperadas y explica diferencias. Complemento de la malla institucional.'
                if area=='matematicas' and g==8 and gr['n']==22 and ref==11: ev='Agrupa datos en intervalos, interpreta su tabla de frecuencias y justifica una medida de tendencia central representativa. Complemento de la malla institucional.'
                if area=='matematicas' and g==7 and gr['n']==23 and ref==9: ev='Representa resultados de un experimento con un diagrama de árbol y aplica el principio multiplicativo para calcular una probabilidad. Complemento de la malla institucional.'
                d['evidencias'].append(ev)
                desc=f"Periodo {period}. Semanas {a}-{b} del periodo (semanas {(period-1)*10+a}-{(period-1)*10+b} del año). {locator}. Tema: {gr['title']}. La primera sesión incorpora exploración de saberes previos; se desarrollan actividades guiadas y práctica con evaluación formativa."
                if area=='sociales' and g==7 and gr['n'] in [9,10,12,14,15]: desc+=' Los casos coloniales americanos se ubican después del siglo XV; se comparan con los medievales sin confundir sus cronologías.'
                # Un único eje temporal por guía evita contar dos veces las semanas cuando se articulan varios DBA.
                if ref==gr['refs'][0]:
                    d['ejes'].append(dict(nombre=f"Módulo {gr['module']} · Guía {gr['n']}: {gr['title']}",descripcion=desc,inicio=a,fin=b,guia=gr['n']))
                else:
                    d['ejes'].append(dict(nombre=f"Articulación · Guía {gr['n']}: {gr['title']}",descripcion=desc+' Articulación simultánea con el eje principal de esta guía; no agrega semanas ni actividades independientes al horario.',inicio=a,fin=b,guia=gr['n'],transversal=True))
        first=record['dbas'][0]
        names='; '.join(gr['title'] for gr in selected)
        first['ejes'] += [dict(nombre='Aplicación e integración del periodo',descripcion=f'Periodo {period}. Semana 9 (semana {(period-1)*10+9} del año). Proyecto o tarea integradora de las guías {selected[0]["n"]}-{selected[-1]["n"]}. Retoma {names}. Incluye socialización, retroalimentación y recuperación de aprendizajes durante el proceso.',inicio=9,fin=9,guia=None),dict(nombre='Evaluación, autoevaluación y refuerzo',descripcion=f'Periodo {period}. Semana 10 (semana {period*10} del año). Resolución de situaciones nuevas de las guías {selected[0]["n"]}-{selected[-1]["n"]}; revisión de evidencias, autoevaluación, coevaluación y actividades diferenciadas de refuerzo.',inicio=10,fin=10,guia=None)]
        record['descripcion_estandar']=f'Estándar contextualizado a partir de las competencias de la cartilla. Periodo {period}: 10 semanas. Desarrollo de guías {selected[0]["n"]}-{selected[-1]["n"]} en semanas 1-8, integración en semana 9 y evaluación y refuerzo en semana 10. Fuente: {file_source}. Las semanas se cuentan dentro de cada periodo; el año comprende 40 semanas.'
        PLAN.append(record)

for area,grades in COURSES.items():
    for g,raw in grades.items():
        lines=raw.splitlines(); assert len(lines)==4,(area,g)
        for p,line in enumerate(lines,1):
            learning,ev1,ev2=line.split('|')
            sr=next((r for r in MALLAS if r['area']==area and r['grado']==g and r['periodo']==p),None)
            if area=='urbanidad': path=source('119'); axis_text=AXES[area][g][p-1]
            elif area=='geometria': path=source('066'); axis_text=AXES[area][g][p-1]
            elif area=='fisica': path=source('120'); axis_text=AXES[area][g][p-1]
            else: assert sr; path=sr['source']; axis_text=sr['ejes']
            items=[]
            for part in re.split(r'[\n;]+',axis_text):
                item=clean(part)
                if item and item not in items and len(item)>2 and not item.lower().startswith(('review','repaso')): items.append(item)
            if area=='naturales' and g==7 and p==2:
                # La propia malla enuncia agua y nitrógeno en el DBA pero los omite en los ejes.
                items=['Ciclos del agua, carbono y nitrógeno','Intercambio de materia entre componentes del ecosistema','Ciclo del azufre como ampliación','Contaminación ambiental y afectación de especies']
            if area=='ingles' and g==6 and p==4: items=[i.replace('Future To Be','Planes con will be y going to') for i in items]
            if area=='religion' and g==9 and p==1: items=list(dict.fromkeys(items))
            assert items,(area,g,p)
            if area=='fisica_educacion' and g==8 and p==4:
                items=['Estilos de vida saludable: actividad, descanso e hidratación','Plan personal de actividad física y recreación','Registro y evaluación de hábitos saludables','Ajustes del plan según posibilidades y recursos']
            # Hasta cuatro bloques, sin repetir un tema para llenar artificialmente el periodo.
            chunks=[]
            block_count=min(4,len(items))
            for k in range(block_count):
                a=math.floor(k*len(items)/block_count); b=math.floor((k+1)*len(items)/block_count)
                chunks.append(items[a:b])
            eje=[]
            for k,topics in enumerate(chunks):
                a=math.floor(k*8/block_count)+1; b=math.floor((k+1)*8/block_count)
                name=f'Unidad {k+1}: '+topics[0]
                name=name[:250] if len(name)>250 else name
                eje.append(dict(nombre=name,descripcion=f'Periodo {p}. Semanas {a}-{b} del periodo (semanas {(p-1)*10+a}-{(p-1)*10+b} del año). Temas: '+ '; '.join(topics)+'. Desarrollo con exploración de saberes previos, actividades guiadas, práctica y retroalimentación. Fuente: '+path+'.',inicio=a,fin=b,temas=topics))
            eje += [dict(nombre='Aplicación e integración del periodo',descripcion=f'Periodo {p}. Semana 9 (semana {(p-1)*10+9} del año). Desarrollo, presentación y retroalimentación de una tarea que integra las unidades y recoge las evidencias previstas.',inicio=9,fin=9),dict(nombre='Evaluación, autoevaluación y refuerzo',descripcion=f'Periodo {p}. Semana 10 (semana {p*10} del año). Evaluación de desempeños mediante las evidencias del periodo; autoevaluación y actividades de refuerzo según las dificultades observadas.',inicio=10,fin=10)]
            std=NAT_STD[g][p-1] if area=='naturales' else STANDARDS[area][p-1]
            descr=f'Estándar contextualizado a partir de la malla institucional de {AREAS[area][1]}, grado {g}, periodo {p}. Diez semanas: {block_count} unidades distribuidas en semanas 1-8, aplicación en semana 9 y evaluación y refuerzo en semana 10. Fuente: {path}.'
            ddesc=f'Aprendizaje contextualizado de la malla institucional, grado {g}, periodo {p}. Formulación revisada para que corresponda a los ejes y evidencias; no es una transcripción de un DBA oficial del MEN. Fuente: {path}.'
            if area=='fisica':
                page={6:3,7:8,8:13,9:18}[g]+p-1
                descr=f'Estándar contextualizado del plan de referencia de Física, grado {g}, unidad {p}, página PDF {page}. Cada unidad de la fuente tiene diez semanas y se asigna a un periodo: desarrollo en semanas 1-8, aplicación en 9 y evaluación y refuerzo en 10. Fuente: {path}. El archivo identifica a la Institución Educativa San Rafael; se utiliza como referencia suministrada, sin atribuirlo a La Josefina.'
                ddesc=f'Aprendizaje contextualizado a partir del plan de referencia de Física de la Institución Educativa San Rafael, grado {g}, unidad {p}, página PDF {page}, disponible en la carpeta suministrada. No se presenta como DBA oficial del MEN para este grado. Fuente: {path}.'
            if area=='religion': ddesc+=' Se evalúan comprensión, análisis y argumentación; no se exige adhesión religiosa ni práctica de culto.'
            if area=='tecnologia' and g==6 and p==3: ddesc+=' Los procedimientos de almacenamiento se abordan mediante simulación o copias de práctica, con respaldo previo y sin intervenir datos reales.'
            PLAN.append(dict(area=area,materia=AREAS[area][0],nombre_materia=AREAS[area][1],grado=g,periodo=p,estandar=std,descripcion_estandar=descr,source=path,dbas=[dict(nombre=learning,descripcion=ddesc,evidencias=[ev1,ev2],ejes=eje,referencia_men=None)]))

# Completa referentes que la malla incluye y que no tienen una guía específica en la cartilla.
for area,g,p,refs,evs,prefix in [
 ('matematicas',8,2,[8,10],['Relaciona expresiones algebraicas de áreas con tablas y gráficas y explica cambios de parámetros.','Propone un modelo funcional entre dos magnitudes, representa pares de valores y argumenta la covariación observada.'],'066'),
 ('sociales',8,2,[1],['Compara una iniciativa de cooperación entre Estados y explica sus efectos económicos o políticos en un problema migratorio o ambiental.'],'015')]:
    r=next(x for x in PLAN if x['area']==area and x['grado']==g and x['periodo']==p)
    axis=next(a for d in r['dbas'] for a in d['ejes'] if a['inicio']==9)
    for d in r['dbas']: d['ejes']=[a for a in d['ejes'] if a is not axis]
    for j,(ref,ev) in enumerate(zip(refs,evs)):
        main=dict(axis)
        main['nombre']='Aplicación: variación y modelos funcionales' if area=='matematicas' else 'Aplicación: cooperación entre Estados'
        main['descripcion']=f'Periodo {p}. Semana 9 (semana {(p-1)*10+9} del año). Integración de las guías del periodo con el complemento de la malla institucional. {ev} Fuente complementaria: {source(prefix)}.'
        if j: main['transversal']=True; main['descripcion']+=' Desarrollo simultáneo con el proyecto integrador; no añade semanas.'
        r['dbas'].append(dict(nombre=f'DBA {ref}: '+OFF[area][(g,ref)],descripcion=f'Referente MEN, grado {g}, DBA {ref}. Complemento de la malla institucional articulado con la aplicación de Postprimaria; evidencia contextualizada. Fuente: {source(prefix)}. Fuente oficial: {OFF_URL[area]}',evidencias=[ev],ejes=[main],referencia_men=ref))
g9=next(r for r in PLAN if r['area']=='geometria' and r['grado']==9 and r['periodo']==4)['dbas'][0]
g9['descripcion']+=' Articulación con Matemáticas V.2, grado 9, DBA 4 (volumen y capacidad de cuerpos redondos): '+OFF['matematicas'][(9,4)]+' Fuente: '+OFF_URL['matematicas']
g9['referencia_men']=4

# Preferencia explícita del usuario: Emprendimiento hasta 9 se basa en PPP.
from ppp_plan import build_ppp
PLAN=[r for r in PLAN if r['area']!='emprendimiento']+build_ppp()
PLAN.sort(key=lambda x:(x['materia'],x['grado'],x['periodo']))
assert len(PLAN)==len(AREAS)*16,len(PLAN)
for r in PLAN:
    weeks=[week for d in r['dbas'] for a in d['ejes'] if not a.get('transversal') for week in range(a['inicio'],a['fin']+1)]
    assert sorted(weeks)==list(range(1,11)),(r['area'],r['grado'],r['periodo'],weeks)
    assert r['estandar'] and r['descripcion_estandar']
    for d in r['dbas']:
        assert d['nombre'] and d['evidencias'] and d['ejes']
        d['evidencias']=list(dict.fromkeys(d['evidencias']))
        words=[]
        for eje in d['ejes']:
            assert len(eje['nombre'])<=255
            if eje['inicio']<9: words.append(eje['nombre'].split(':',1)[-1].strip())
        d['palabras_clave']=', '.join(words) if words else r['nombre_materia']+', '+d['nombre'].split(':',1)[-1][:150]

(OUT/'plan-curricular.json').write_text(json.dumps({'areas':AREAS,'registros':PLAN},ensure_ascii=False,indent=2),encoding='utf-8')
(OUT/'respaldo-original.json').write_text(json.dumps(ORIGINAL,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({'periodos':len(PLAN),'dbas':sum(len(r['dbas']) for r in PLAN),'ejes':sum(len(d['ejes']) for r in PLAN for d in r['dbas']),'evidencias':sum(len(d['evidencias']) for r in PLAN for d in r['dbas']),'areas':len(AREAS)},ensure_ascii=False))
