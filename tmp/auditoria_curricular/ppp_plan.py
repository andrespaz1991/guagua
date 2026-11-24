"""Plan de Emprendimiento de 6 a 9 basado en las dos cartillas PPP suministradas."""
from pathlib import Path
import json,hashlib

BASE=Path(r'C:\Users\Andres\OneDrive - UNIVERSIDAD DE SANTANDER - UDES\SEDUCA\La Josefina\Vallesol\Formatos Frecuentes\GUIAS DE POSTPRIMARIA\Proyectos productivos')
ROOT=Path(__file__).parent
OUT=ROOT.parent.parent/'output'/'auditoria-emprendimiento-ppp-2026-11-01'
TITLES={
'6_7':[
('¿Cómo es mi entorno?',12),('¿Cuáles son las características biofísicas de mi vereda?',20),('¿Qué actividades desarrollan los habitantes de mi vereda?',32),
('¿Cómo ha sido la relación de los seres humanos con el entorno?',50),('¿El único fin de un emprendimiento es ganar dinero?',56),('¿Cómo analizo la información?',64),
('¿Cómo identifico la idea del proyecto?',78),('¿Cuál es el propósito del proyecto?',88),('¿Cómo diseñar el proyecto?',96),
('¿Cómo selecciono las actividades del proyecto?',116),('¿Qué aspectos pueden orientar las actividades del plan de acción?',124),('¿Qué significa producir con responsabilidad?',130),
('¿Cómo verifico el cumplimiento de los compromisos del proyecto?',144),('¿Cómo registrar las cuentas del PPP?',152),('¿El proyecto cumplió lo previsto?',160),
('¿Qué alternativas de proyección comunitaria existen?',176),('¿Cómo se organiza la feria de proyectos?',180),('¿Cómo se beneficia la comunidad con los PPP?',186)],
'8_9':[
('¿Cómo funciona mi municipio?',12),('¿Cómo es la oferta climática del municipio?',20),('¿Quiénes conforman mi municipio?',28),
('¿Cómo identifico las posibilidades de emprendimiento en mi municipio?',40),('¿Cómo utilizo la información del entorno?',46),('¿Cómo identifico las alternativas de solución del problema?',52),
('¿Cómo organizo mi emprendimiento?',64),('¿Qué recursos necesito para llevar a cabo el emprendimiento?',72),('¿Cómo diseño emprendimientos sostenibles?',80),
('¿Cómo establezco mi plan de trabajo para el proyecto?',94),('¿Cómo y en qué periodo de tiempo debo realizar las actividades del proyecto?',100),('¿Cómo verifico el cumplimiento de todas las actividades planteadas?',106),
('Parámetros para medir proyectos',120),('¿En qué momento evalúo el emprendimiento?',126),('¿Qué aspectos evalúo?',132),
('¿Qué actividad de proyección se planea?',148),('Feria de emprendimiento',156),('¿Participación en fondos concursables?',164)]}

# Cada fila: guía, tema de trabajo aplicado, evidencia observable.
BLOCKS={
6:[
'''1|Territorio, cultura y entorno escolar|Representa su entorno en un croquis y describe rasgos culturales con respeto por la diversidad.
2|Características biofísicas: clima, suelo, agua y seres vivos|Registra observaciones de la vereda y relaciona sus condiciones biofísicas con usos del territorio.
3|Actividades productivas, sociales y culturales de la vereda|Organiza información sobre actividades de la comunidad e identifica recursos y necesidades.
1,2,3|Diagnóstico veredal inicial y fuentes de información|Presenta un diagnóstico inicial que diferencia observaciones, información consultada y preguntas por resolver.''',
'''4|Relaciones entre actividades humanas y ambiente|Explica un impacto ambiental de una actividad local y propone una alternativa de cuidado.
5|Emprendimientos económicos, sociales y culturales|Compara iniciativas y argumenta por qué sus beneficios no se reducen a obtener dinero.
6|Organización y análisis de la información recolectada|Clasifica datos del diagnóstico y explica una relación entre necesidad, recursos y oportunidad.
4,5,6|Diagnóstico argumentado y priorización de necesidades|Prioriza una necesidad de la vereda sustentándola con información y criterios acordados.''',
'''7|Problema, causas y alternativas de solución|Construye un árbol de problemas que diferencie causas, problema central y efectos.
7|Selección de la idea de proyecto|Compara alternativas usando criterios de pertinencia, recursos y beneficio comunitario.
8|Propósito, objetivo general y objetivos específicos|Formula objetivos coherentes con el problema e identifica resultados que permitirían evaluar su logro.
7,8|Coherencia entre problema, idea y propósito del PPP|Sustenta una idea de proyecto y revisa sus objetivos a partir de observaciones de sus compañeros.''',
'''9|Perfil del proyecto: identificación, integrantes y localización|Redacta la identificación del PPP vinculando título, producto o servicio, integrantes y territorio.
9|Justificación, beneficiarios y resultados esperados|Explica a quién beneficia el proyecto y por qué responde al diagnóstico construido.
9|Actividades, recursos y organización inicial|Organiza una propuesta inicial de actividades y recursos consistente con los objetivos.
9|Integración y revisión del perfil del PPP|Presenta un perfil de proyecto coherente y registra ajustes para continuar su gestión en el grado siguiente.'''],
7:[
'''10|Recuperación del perfil y selección de actividades|Revisa el perfil formulado y selecciona actividades necesarias para alcanzar sus objetivos.
10|Plan de acción, responsabilidades y recursos|Elabora un plan de acción con tareas, responsables, recursos y criterios de cumplimiento.
11|Buenas prácticas agrícolas y de manufactura aplicables|Identifica buenas prácticas pertinentes al proyecto y explica cómo favorecen calidad y cuidado del entorno.
12|Producción responsable y puesta en práctica del plan|Aplica o simula una actividad del plan y registra decisiones de uso responsable de recursos.''',
'''13|Compromisos, tiempos y control de actividades|Construye un registro de seguimiento que permita contrastar compromisos y avances.
13|Desviaciones y acciones de ajuste|Identifica una dificultad del proyecto y propone un ajuste con responsables y plazo.
14|Costos, gastos e ingresos del PPP|Clasifica movimientos del proyecto como costos, gastos o ingresos y justifica su decisión.
14|Registro y comprobación de las cuentas|Elabora un registro sencillo de cuentas y verifica operaciones y soportes de los datos utilizados.''',
'''15|Objetivos y criterios de evaluación del proyecto|Define criterios para evaluar formulación, ejecución y resultados del PPP.
15|Resultados y cumplimiento de lo previsto|Compara resultados registrados con los objetivos y explica diferencias usando evidencias.
15|Balance social, ambiental y económico|Elabora un balance que relacione beneficios, costos e impactos del proyecto.
15|Mejoras y decisiones de continuidad|Propone mejoras o decisiones de continuidad sustentadas en la evaluación del proyecto.''',
'''16|Alternativas de proyección comunitaria|Selecciona una actividad de proyección y explica su relación con necesidades de la comunidad.
17|Organización de una feria escolar de proyectos|Diseña una feria con propósito, responsables, recursos y una forma de comunicar los resultados.
18|Beneficios del PPP para la comunidad|Explica beneficios y posibles límites del proyecto desde las perspectivas de sus participantes.
16,17,18|Socialización, retroalimentación y memoria del PPP|Presenta resultados del proyecto y documenta aportes de la comunidad y recomendaciones para un nuevo ciclo.'''],
8:[
'''1|Organización y funcionamiento del municipio|Representa actores e instituciones del municipio y explica su relación con actividades productivas.
2|Oferta climática y condiciones del territorio municipal|Interpreta información climática y territorial e identifica posibilidades y restricciones para un emprendimiento.
3|Actores sociales, cultura y sectores productivos|Caracteriza actores y actividades del municipio con información de fuentes identificadas.
1,2,3|Diagnóstico municipal para identificar oportunidades|Integra información territorial y social en un diagnóstico que sustente oportunidades de emprendimiento.''',
'''4|Posibilidades de emprendimiento municipal|Compara iniciativas locales e identifica necesidades, recursos y posibles beneficiarios.
5|Uso de información y árbol de problemas|Construye un árbol de problemas apoyado en datos y diferencia causas y efectos.
6|Alternativas y criterios de selección|Evalúa alternativas de solución considerando recursos, pertinencia y efectos sociales y ambientales.
4,5,6|Selección argumentada del emprendimiento|Sustenta la selección de una iniciativa y explica por qué responde al diagnóstico municipal.''',
'''7|Plan de negocios: resumen ejecutivo y mercadeo|Elabora un resumen de la iniciativa y caracteriza destinatarios, oferta y necesidades del mercado.
7|Operación y organización del emprendimiento|Representa actividades operativas, responsabilidades y recursos necesarios para ofrecer el producto o servicio.
8|Recursos, inversión y presupuesto de operación|Calcula un presupuesto didáctico de inversión y operación con datos y supuestos explícitos.
8|Ingresos, costos y proyecciones financieras básicas|Organiza una proyección sencilla de ingresos, costos y flujo de dinero y explica sus supuestos.''',
'''9|Impactos ambientales y uso de recursos|Construye una matriz sencilla de impactos y propone medidas preventivas o de mejora.
9|Sostenibilidad social y beneficios comunitarios|Analiza efectos sobre participantes y comunidad e identifica condiciones para una participación responsable.
9|Cronograma y articulación de componentes del plan|Integra cronograma, operación, presupuesto y sostenibilidad en una versión coherente del plan de negocios.
9|Revisión y sustentación del plan de negocios|Sustenta el plan y registra ajustes para continuar su gestión e implementación en el grado siguiente.'''],
9:[
'''10|Recuperación del plan de negocios y tareas del proyecto|Revisa el plan formulado y define tareas necesarias para lograr sus resultados.
11|Secuencia, responsables y cronograma|Elabora un cronograma que establezca orden, duración y responsables de las actividades.
12|Instrumentos para verificar el cumplimiento|Diseña una lista o registro que permita verificar cada actividad sin confundir ejecución y resultado.
10,11,12|Ejecución o simulación y seguimiento inicial|Ejecuta o simula una etapa del plan y documenta avances, dificultades y ajustes acordados.''',
'''13|Parámetros, indicadores y unidades de medida|Define indicadores con criterios de medida y metas coherentes con los objetivos del emprendimiento.
13|Registros y contraste de avances|Organiza datos del seguimiento e interpreta diferencias entre resultados y metas.
14|Momentos y clases de evaluación|Distingue evaluación inicial, de proceso y final y explica su utilidad para el proyecto.
14|Plan de evaluación y decisiones oportunas|Organiza momentos, responsables e instrumentos de evaluación y propone ajustes a partir de sus resultados.''',
'''15|Aspectos sociales de la evaluación|Analiza participación, cooperación y efectos comunitarios usando criterios y evidencias explícitos.
15|Aspectos ambientales de la evaluación|Evalúa impactos y medidas de cuidado comparando lo previsto con información del proyecto.
15|Aspectos financieros y uso de recursos|Interpreta registros o escenarios financieros y explica qué muestran sobre el uso de recursos.
15|Síntesis evaluativa y mejoramiento|Presenta un informe con resultados, límites y acciones de mejora sustentadas en las evaluaciones realizadas.''',
'''16|Planeación de la proyección comunitaria|Selecciona una actividad de proyección con objetivos, destinatarios y resultados esperados.
17|Feria de emprendimiento y comunicación de resultados|Prepara una muestra o feria con una presentación argumentada del proyecto y sus resultados.
18|Fondos concursables y mecanismos de apoyo|Compara requisitos de mecanismos de apoyo descritos en una lectura o convocatoria suministrada sin asumir que estén vigentes o disponibles.
16,17,18|Responsabilidad, retroalimentación y continuidad|Sustenta una propuesta de continuidad identificando responsabilidades, recursos y aportes de la comunidad.''']}

STDS={
6:['Reconozco características culturales, sociales y biofísicas de mi entorno y organizo información para construir un diagnóstico.','Relaciono actividades humanas, recursos y necesidades y argumento oportunidades de emprendimiento con beneficios colectivos.','Identifico problemas y comparo alternativas para formular una idea de proyecto con objetivos pertinentes.','Integro y comunico información para formular un proyecto coherente con las necesidades y recursos de mi comunidad.'],
7:['Organizo actividades y responsabilidades para gestionar un proyecto mediante buenas prácticas y uso responsable de recursos.','Registro y verifico compromisos, actividades y cuentas para tomar decisiones durante el desarrollo de un proyecto.','Comparo resultados con objetivos y evalúo efectos sociales, ambientales y económicos para proponer mejoras.','Comunico resultados y construyo propuestas de proyección comunitaria mediante participación y argumentación.'],
8:['Analizo características del municipio y relaciono actores, actividades y condiciones territoriales con oportunidades de emprendimiento.','Uso información del entorno y criterios explícitos para seleccionar alternativas de solución a un problema.','Organizo información operativa, de mercado y financiera para estructurar un plan de negocios pertinente.','Relaciono decisiones económicas, ambientales y sociales y sustento la sostenibilidad de un plan de negocios.'],
9:['Organizo y verifico actividades, tiempos y responsabilidades para implementar un proyecto y ajustar su desarrollo.','Construyo indicadores y registros para evaluar avances y orientar decisiones de seguimiento de un proyecto.','Interpreto evidencias de evaluación social, ambiental y financiera para sustentar resultados y acciones de mejora.','Comunico resultados y evalúo alternativas de proyección y apoyo con criterios de responsabilidad comunitaria.']}
LEARNING={
6:['Elabora un diagnóstico inicial de la vereda relacionando territorio, cultura, condiciones biofísicas y actividades de sus habitantes.','Analiza la información del diagnóstico, reconoce fines sociales y económicos del emprendimiento y prioriza una necesidad.','Selecciona una idea de PPP y formula objetivos coherentes con un problema identificado y sus alternativas de solución.','Formula el perfil de un PPP con identificación, objetivos, justificación, beneficiarios, actividades y recursos iniciales.'],
7:['Organiza y aplica un plan de acción del PPP con responsabilidades y criterios de producción responsable.','Verifica compromisos y registra costos, gastos e ingresos para ajustar la gestión del PPP.','Evalúa si el PPP cumplió lo previsto y propone mejoras a partir de resultados e impactos documentados.','Organiza la socialización y la proyección comunitaria del PPP y evalúa sus beneficios para la comunidad.'],
8:['Construye un diagnóstico municipal que relacione organización, oferta climática, actores y sectores productivos.','Selecciona un emprendimiento mediante análisis de información, identificación de problemas y evaluación de alternativas.','Estructura componentes de mercado, operación, organización y recursos financieros de un plan de negocios escolar.','Integra criterios de sostenibilidad ambiental y social y consolida un plan de negocios con proyecciones y cronograma.'],
9:['Gestiona la implementación del proyecto mediante un plan de trabajo, cronograma y registros de cumplimiento.','Diseña indicadores y momentos de evaluación y utiliza sus resultados para orientar el seguimiento del emprendimiento.','Evalúa aspectos sociales, ambientales y financieros del emprendimiento y sustenta un plan de mejora.','Proyecta el emprendimiento hacia la comunidad mediante una feria y el análisis de posibles mecanismos de apoyo.']}

def build_ppp():
    records=[]
    for g,periods in BLOCKS.items():
        band='6_7' if g in [6,7] else '8_9'; src=BASE/f'PPP_{band}.pdf'
        for p,raw in enumerate(periods,1):
            eje=[]; evid=[]; guide_set=set()
            for i,line in enumerate(raw.splitlines()):
                nums,topic,ev=line.split('|'); guides=[int(x) for x in nums.split(',')];guide_set.update(guides)
                loc='; '.join(f'módulo {(n-1)//3+1}, guía {n}: {TITLES[band][n-1][0]} (p. impresa {TITLES[band][n-1][1]}, PDF {TITLES[band][n-1][1]+1})' for n in guides)
                a=i*2+1;b=a+1
                eje.append(dict(nombre=f'Bloque {i+1}: '+topic,descripcion=f'Periodo {p}. Semanas {a}-{b} del periodo (semanas {(p-1)*10+a}-{(p-1)*10+b} del año). {loc}. Trabajo aplicado: {topic}. Fuente: {src}.',inicio=a,fin=b,temas=[topic],guias=guides))
                evid.append(ev)
            assert len(eje)==4
            eje += [dict(nombre='Aplicación e integración del PPP',descripcion=f'Periodo {p}. Semana 9 (semana {(p-1)*10+9} del año). Elaboración o revisión del producto del periodo, socialización y retroalimentación. Retoma las guías {", ".join(map(str,sorted(guide_set)))} de {src.name}.',inicio=9,fin=9),dict(nombre='Evaluación, autoevaluación y refuerzo',descripcion=f'Periodo {p}. Semana 10 (semana {p*10} del año). Revisión del producto, evidencias y diario de campo; autoevaluación, coevaluación y refuerzo de los aprendizajes del periodo.',inicio=10,fin=10)]
            mods=sorted({(n-1)//3+1 for n in guide_set})
            stage='caracterización y formulación' if g in [6,8] else 'gestión, evaluación y proyección del proyecto formulado'
            desc=f'Estándar contextualizado a partir de los referentes interdisciplinarios de la cartilla PPP de Postprimaria del MEN. Grado {g}, periodo {p}: {stage}; módulos {", ".join(map(str,mods))}, guías {", ".join(map(str,sorted(guide_set)))}. Diez semanas: desarrollo aplicado en 1-8, integración en 9 y evaluación y refuerzo en 10. Fuente: {src}. La distribución anual es institucional y conserva la continuidad entre grados de la cartilla compartida.'
            dd=f'Aprendizaje derivado de la cartilla de Proyectos Pedagógicos Productivos, grado {g}, periodo {p}. Formulación contextualizada; no se presenta como DBA oficial específico de Emprendimiento. Fuente: {src}. Cada cartilla tiene seis módulos y dieciocho guías. Los grados 6 y 8 trabajan guías 1-9; los grados 7 y 9 continúan con 10-18. La gestión utiliza el proyecto formulado en el grado anterior; al iniciar el año se recupera y ajusta ese insumo.'
            records.append(dict(area='emprendimiento',materia=9,nombre_materia='Emprendimiento',grado=g,periodo=p,estandar=STDS[g][p-1],descripcion_estandar=desc,source=str(src),dbas=[dict(nombre=LEARNING[g][p-1],descripcion=dd,palabras_clave=', '.join(a['temas'][0] for a in eje if 'temas' in a),evidencias=evid,ejes=eje,referencia_men=None)]))
    for r in records:
        weeks=[w for d in r['dbas'] for a in d['ejes'] for w in range(a['inicio'],a['fin']+1)]
        assert weeks==list(range(1,11))
        assert all(len(a['nombre'])<=255 for d in r['dbas'] for a in d['ejes'])
    for band,grades in [('6_7',[6,7]),('8_9',[8,9])]:
        covered={n for r in records if r['grado'] in grades for d in r['dbas'] for a in d['ejes'] for n in a.get('guias',[])}
        assert covered==set(range(1,19)),(band,covered)
    return records

if __name__=='__main__':
    OUT.mkdir(exist_ok=True)
    plan=build_ppp()
    (OUT/'plan-ppp.json').write_text(json.dumps({'registros':plan},ensure_ascii=False,indent=2),encoding='utf-8')
    sources=[{'ruta':str(p),'sha256':hashlib.sha256(p.read_bytes()).hexdigest()} for p in [BASE/'PPP_6_7.pdf',BASE/'PPP_8_9.pdf']]
    (OUT/'fuentes-verificadas.json').write_text(json.dumps(sources,ensure_ascii=False,indent=2),encoding='utf-8')
    print(json.dumps({'periodos':len(plan),'ejes':sum(len(d['ejes']) for r in plan for d in r['dbas']),'evidencias':sum(len(d['evidencias']) for r in plan for d in r['dbas'])}))
