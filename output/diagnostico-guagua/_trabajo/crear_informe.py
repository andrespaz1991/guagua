from pathlib import Path
import json,re
from docx import Document
from docx.shared import Inches,Pt,RGBColor
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT,WD_CELL_VERTICAL_ALIGNMENT

ROOT=Path(r'D:\xampp\htdocs\guagua')
OUT=ROOT/'output/diagnostico-guagua'
OUT.mkdir(parents=True,exist_ok=True)
doc=Document()
sec=doc.sections[0]
sec.page_width=Inches(8.27);sec.page_height=Inches(11.69)
sec.top_margin=Inches(.72);sec.bottom_margin=Inches(.65)
sec.left_margin=Inches(.78);sec.right_margin=Inches(.78)
sec.header_distance=Inches(.28);sec.footer_distance=Inches(.3)
styles=doc.styles
for name in ['Normal','Title','Subtitle','Heading 1','Heading 2','Heading 3']:
    s=styles[name];s.font.name='Calibri';s.font.color.rgb=RGBColor(0,0,0)
    s.element.get_or_add_rPr().rFonts.set(qn('w:ascii'),'Calibri')
    s.element.rPr.rFonts.set(qn('w:hAnsi'),'Calibri')
    for border in s.element.xpath('.//w:pBdr'):
        border.getparent().remove(border)
styles['Normal'].font.size=Pt(10.5)
styles['Normal'].paragraph_format.space_after=Pt(7)
styles['Normal'].paragraph_format.line_spacing=1.12
styles['Title'].font.size=Pt(29);styles['Title'].font.bold=True
styles['Title'].paragraph_format.space_after=Pt(12)
styles['Subtitle'].font.size=Pt(13)
styles['Subtitle'].paragraph_format.space_after=Pt(12)
styles['Heading 1'].font.size=Pt(20);styles['Heading 1'].font.bold=True
styles['Heading 1'].paragraph_format.space_before=Pt(0)
styles['Heading 1'].paragraph_format.space_after=Pt(12)
styles['Heading 2'].font.size=Pt(12.5);styles['Heading 2'].font.bold=True
styles['Heading 2'].paragraph_format.space_before=Pt(10)
styles['Heading 2'].paragraph_format.space_after=Pt(6)
styles['Heading 3'].font.size=Pt(11);styles['Heading 3'].font.bold=True
styles['Heading 3'].paragraph_format.space_before=Pt(8)
styles['Heading 3'].paragraph_format.space_after=Pt(5)
for name in ['Normal','Title','Subtitle','Heading 1','Heading 2','Heading 3']:
    styles[name].paragraph_format.widow_control=True
cp=doc.core_properties
cp.title='Diagnóstico integral del flujo escolar de Guagua'
cp.subject='Organización funcional del ciclo escolar y racionalización de módulos'
cp.author='Guagua'
cp.keywords='diagnóstico, año escolar, períodos, matrícula, acudientes, módulos'
footer=sec.footer.paragraphs[0];footer.alignment=WD_ALIGN_PARAGRAPH.RIGHT
r=footer.add_run('Guagua  |  Diagnóstico  |  ');r.font.size=Pt(8);r.font.color.rgb=RGBColor.from_string('666666')
f=OxmlElement('w:fldSimple');f.set(qn('w:instr'),'PAGE');footer._p.append(f)

def p(text='',bold=False,size=None):
    x=doc.add_paragraph();r=x.add_run(text);r.bold=bold
    if size:r.font.size=Pt(size)
    return x
def h(text):doc.add_heading(text,2)
def sub(text):doc.add_heading(text,3)
def page(n,title):
    doc.add_page_break()
    x=p(f'{n:02d}  INFORME DE DIAGNÓSTICO',size=8.5);x.paragraph_format.space_after=Pt(8)
    doc.add_heading(title,1)
def table(headers,rows,widths,small=9):
    t=doc.add_table(rows=1,cols=len(headers));t.alignment=WD_TABLE_ALIGNMENT.CENTER;t.autofit=False
    props=t._tbl.tblPr
    tw=props.find(qn('w:tblW'));tw.set(qn('w:w'),str(round(sum(widths)*1440)));tw.set(qn('w:type'),'dxa')
    indent=OxmlElement('w:tblInd');indent.set(qn('w:w'),'0');indent.set(qn('w:type'),'dxa');props.append(indent)
    for c,w in zip(t.columns,widths):c.width=Inches(w)
    for i,text in enumerate(headers):t.rows[0].cells[i].text=text
    for row in rows:
        cells=t.add_row().cells
        for c,text,w in zip(cells,row,widths):c.text=str(text);c.width=Inches(w)
    for ri,row in enumerate(t.rows):
        trPr=row._tr.get_or_add_trPr()
        cant=OxmlElement('w:cantSplit');trPr.append(cant)
        if ri==0:
            rep=OxmlElement('w:tblHeader');trPr.append(rep)
        for ci,c in enumerate(row.cells):
            c.width=Inches(widths[ci])
            c.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
            tcPr=c._tc.get_or_add_tcPr()
            borders=OxmlElement('w:tcBorders')
            for edge in ['top','left','bottom','right']:
                e=OxmlElement('w:'+edge);e.set(qn('w:val'),'single');e.set(qn('w:sz'),'4');e.set(qn('w:color'),'D9D9D9');borders.append(e)
            tcPr.append(borders)
            margins=OxmlElement('w:tcMar')
            for edge,value in [('top','90'),('left','105'),('bottom','90'),('right','105')]:
                e=OxmlElement('w:'+edge);e.set(qn('w:w'),value);e.set(qn('w:type'),'dxa');margins.append(e)
            tcPr.append(margins)
            shade=OxmlElement('w:shd');shade.set(qn('w:fill'),'24384B' if ri==0 else ('F3F5F7' if ri%2==0 else 'FFFFFF'));tcPr.append(shade)
            for x in c.paragraphs:
                x.paragraph_format.space_after=Pt(0);x.paragraph_format.space_before=Pt(0);x.paragraph_format.line_spacing=1.05
                if widths[ci]<.8:x.alignment=WD_ALIGN_PARAGRAPH.CENTER
                for r in x.runs:
                    r.font.size=Pt(small);r.bold=ri==0;r.font.color.rgb=RGBColor.from_string('FFFFFF' if ri==0 else '111111')
    doc.add_paragraph().paragraph_format.space_after=Pt(0)
    return t
def finding(code,title,priority,evidence,observed,impact,action,accept):
    h(f'{code} {title}')
    p(f'{priority}  |  Evidencia {evidence}',bold=True,size=9)
    p(observed)
    p('Efecto en el flujo. '+impact)
    p('Recomendación. '+action)
    p('Criterio de cierre. '+accept,size=9.5)

p('GUAGUA',bold=True,size=10)
doc.add_paragraph('Diagnóstico integral del flujo escolar',style='Title')
doc.add_paragraph('Organización del ciclo anual y racionalización de módulos',style='Subtitle')
p('19 de noviembre de 2026  |  Versión 1.0',size=9)
h('Conclusión ejecutiva')
p('Guagua dispone de una base funcional aprovechable y de un asistente que ya organiza la preparación del año escolar en ocho pasos. La principal debilidad es que ese proceso convive con pantallas y reglas independientes para modificar los mismos datos. El resultado es un flujo con varias entradas, significados distintos para matrícula y curso, y controles que cambian según el módulo utilizado.')
p('La recomendación es adoptar un único ciclo de gestión escolar, con responsables, estados y validaciones compartidas. El asistente debe convertirse en la entrada oficial para preparar y abrir el año; los módulos de mantenimiento deben operar sobre ese mismo modelo. La simplificación del menú debe acompañar esta unificación y preservar los históricos.')
table(['Prioridad','Decisión recomendada','Hallazgos'],[
('P0','Alinear el instalador con el esquema actual y corregir la opción de inscripción automática.','F01'),
('P0','Unificar la activación de año y período y corregir la creación manual de períodos.','F02 F03'),
('P0','Completar el ingreso de acudientes y el control del estudiante seleccionado.','F06'),
('P1','Separar matrícula anual, grado o grupo e inscripción a asignaturas.','F04 F05'),
('P1','Centralizar horarios, fuentes de información, permisos y cierre escolar.','F07 a F10'),
],[.7,5.15,.85],9.4)
h('Qué sobra y qué se debe conservar')
p('Sobran accesos duplicados y formas paralelas de administrar una misma entidad. Deben integrarse las dos pantallas de relación con acudientes, las entradas de matrícula e inscripciones y los controles dispersos de apariencia. Los generadores de carpetas y las aplicaciones auxiliares deben salir del recorrido obligatorio del año escolar. La asistencia, la planeación, la malla curricular, la ficha del estudiante y los reportes siguen siendo funciones necesarias.')
p('P0 significa bloqueo prioritario de un recorrido esencial; P1, corrección estructural u operativa; P2, simplificación y mejora de experiencia. La prioridad expresa impacto potencial observado en el diseño, no incidentes comprobados en producción.',size=9)

page(2,'Alcance y lectura del sistema actual')
p('El diagnóstico examina el código del proyecto, la documentación funcional del inicio anual y la estructura de un respaldo local fechado el 19 de noviembre de 2026. Se revisaron los puntos de entrada y las operaciones relevantes de instalación, configuración, períodos, cursos, usuarios, acudientes, asistencia, planeación y reportes.')
p('El respaldo contiene 91 tablas y 61 registros de navegación en menu_items2. Son cifras de esa copia y no un inventario de módulos activos en producción. Los hallazgos se apoyan en código y esquema; los efectos de ejecución se presentan como riesgos o defectos de implementación que deben reproducirse en un ambiente de validación. Las referencias E01 a E20 permiten rastrear la evidencia.')
h('Flujo que existe hoy')
table(['Momento','Entradas identificadas','Consecuencia'],[
('Instalar','instalar/index.php y configuración central','El instalador conserva estructuras históricas diferentes a las consumidas por los módulos actuales.'),
('Preparar el año','Asistente anual, Año Lectivo, Asignaciones, Crear curso','Varias rutas crean o modifican la estructura académica.'),
('Vincular estudiantes','Matrícula, Inscripciones, inscripción desde cursos y asistente','El registro principal es por asignatura; no se distingue bien de la matrícula institucional.'),
('Operar','Asistencia, reportes, planeación y aplicaciones auxiliares','Comparten parte del contexto, pero mantienen parámetros y fuentes específicas.'),
('Cerrar y renovar','Estados de inscripción y herramientas de balance','El cierre con bloqueo, reapertura y promoción requiere un proceso formal.'),
],[1.1,2.6,3],9.2)
h('Capacidades que conviene aprovechar')
p('El asistente ya guarda borradores, controla revisiones concurrentes, copia estructura de otro año, valida cruces de fechas y horarios, permite preparar sin activar y reutiliza registros para evitar duplicaciones en reintentos. Restringe la activación general a administración y directivos. Estas capacidades deben mantenerse [E01].')
p('También existen una configuración central de conexión, funciones compartidas del año y período, actualización del contexto de sesión, un centro de configuración y pruebas dedicadas. No se recomienda rehacer todo el sistema: se recomienda extender esos componentes y llevar hacia ellos las reglas todavía dispersas [E02 E03 E04 E16 E18].')
h('Cómo interpretar las recomendaciones')
p('Conservar significa mantener la función. Integrar significa ofrecer una sola experiencia y un servicio compartido. Reubicar significa sacar una herramienta del flujo obligatorio. Retirar un acceso significa ocultar o redirigir su entrada; la eliminación de código requiere comprobar dependencias, uso y conservación de datos.')

page(3,'Bloqueos de instalación y activación')
finding('F01','El instalador no representa el sistema actual','P0','E05 E01 E06',
'El instalador define asignacion_academica, mientras los flujos actuales trabajan con asignacion. Su definición de periodo solo incluye identificador y nombre; la gestión actual exige año, fechas, descripción y estado. Tampoco declara materia_oficial en su bloque de creación. Además, tanto la respuesta SI como la alternativa de inscribir_estudiantes asignan verdadero a la inscripción automática.',
'Una instalación nueva no dispone de un contrato de esquema coherente con las funciones centrales. Una elección negativa del usuario puede ejecutar una operación que no solicitó.',
'Separar la instalación técnica de la apertura anual. Crear una línea de migraciones versionadas que produzca el esquema vigente y comprobar requisitos, estructura y resultado antes de habilitar la operación académica. Corregir la condición de inscripción y mostrar una vista previa de sus efectos.',
'Una base vacía llega al esquema vigente, abre el asistente y permite preparar el primer año; elegir no inscribir genera cero inscripciones automáticas.')
finding('F02','El año y el período no cambian mediante una única regla','P0','E01 E03 E06 E07',
'El asistente activa año y período de forma conjunta. La pantalla histórica Año Lectivo modifica el estado del año sin coordinar la selección del período. Las funciones compartidas consultan un único año activo global, y la activación del asistente desactiva años y períodos sin filtrar institución.',
'Las rutas pueden producir estados distintos para el mismo cambio anual. En un uso con varias instituciones o calendarios independientes, la activación afecta un ámbito mayor que la sede seleccionada.',
'Usar un único servicio para preparar, activar y cerrar ciclos. Definir si Guagua será de institución única o admitirá instituciones con calendarios independientes. En este último caso, vincular explícitamente año y período con la institución. Redirigir los cambios de la pantalla histórica al servicio común.',
'Cambiar el año selecciona un período de ese mismo ciclo en una operación indivisible; los cambios respetan el alcance institucional definido.')

page(4,'Matrícula grados y períodos')
finding('F03','Las dos vías de creación de períodos no son equivalentes','P0','E01 E07 E19',
'El respaldo define periodo.id_periodo sin autoincremento. El asistente calcula y asigna el identificador; la pantalla de períodos hace un INSERT sin incluirlo. El asistente rechaza superposición de períodos, mientras el mantenimiento individual no aplica esa misma comprobación.',
'La creación individual puede fallar o producir una clave no válida según el modo SQL. También se pueden introducir calendarios que el asistente rechazaría.',
'Resolver la clave mediante una migración controlada y centralizar las validaciones de rango, pertenencia al año, secuencia, superposición y activación. Sustituir la eliminación directa por controles de uso e histórico.',
'Crear o editar un período desde cualquier entrada aplica las mismas reglas, no colisiona con claves existentes y no altera registros académicos cerrados.')
finding('F04','Matrícula e inscripción a asignaturas están confundidas','P1','E08 E09 E01',
'Matrícula en usuario/inscripcion.php y el gestor Planeador/inscripes.php escriben en inscripcion, asociando estudiante y asignación. El formulario histórico parte de Aprobado y el endpoint de inscripción de cursos también inserta Aprobado; el asistente crea En curso.',
'Inscribir a un estudiante puede presentarse como aprobación antes de su evaluación. Tampoco hay una referencia inequívoca de matrícula institucional anual en los recorridos examinados.',
'Crear la matrícula anual como vínculo estudiante, institución, ciclo y ubicación académica. Derivar desde ella las inscripciones a asignaturas. Separar el estado administrativo de matrícula del resultado académico final.',
'Una matrícula activa puede generar varias inscripciones; ninguna inscripción nueva implica aprobación automática.')
finding('F05','El grado actual no tiene una referencia anual estable','P1','E01 E08 E10 E19',
'El asistente y la inscripción masiva usan usuario.observaciones como grado. La pantalla histórica de matrícula presenta asignacion.id_curso como grupo, aunque el asistente carga allí el identificador de asignatura. Se mezclan ubicación del estudiante, catálogo de grados y asignación docente.',
'Cambiar las observaciones puede modificar la selección masiva de estudiantes. El mismo valor de curso adquiere significados diferentes entre pantallas.',
'Guardar grado y grupo en la matrícula anual. Diferenciar grado curricular, grupo operativo y asignatura. Permitir grupos multigrado como 6 a 8 sin perder el grado individual de cada estudiante.',
'El histórico de grado por año permanece estable y ninguna selección académica depende de observaciones libres.')

page(5,'Familias y operación de asistencia')
finding('F06','El recorrido de acudientes está incompleto y duplicado','P0','E11 E12 E13 E19',
'Matrimonio y Acudientes y estudiantes administran la misma tabla puente. La primera captura parentesco; la segunda inserta únicamente los dos identificadores aunque el respaldo declara parentesco obligatorio. Persona.validar_acudiente remite a usuario/elegir_hijo.php, archivo que no está en el árbol revisado. Además, se usan hijo e id_hijo_seleccionado como contextos paralelos.',
'La familia puede encontrar una interrupción al entrar o cambiar de estudiante. El vínculo creado desde una interfaz puede carecer de información exigida por la otra.',
'Consolidar el módulo Familias y acudientes, con búsqueda de personas existentes, parentesco, contacto principal y vigencia del vínculo. Implementar un selector único de estudiantes autorizados y validar la relación en cada consulta. Registrar la razón de cambios de vínculo.',
'Un acudiente con dos estudiantes puede alternar entre ambos, conserva el contexto y solo consulta información de vínculos vigentes. Ambas formas históricas remiten al módulo único.')
finding('F07','La asistencia conserva una programación paralela al horario','P1','E14 E15',
'La asistencia general convive con una aplicación de planilla Excel y un sincronizador. La aplicación contiene una programación semanal escrita en código. El sincronizador mantiene un horario de respaldo para grados 6 a 11 y le da prioridad mientras aplica, en lugar de usar siempre el horario registrado.',
'Modificar el horario académico puede no cambiar la materia asignada a una importación de asistencia. La apertura de otro año o de otros grados exige revisar reglas ocultas en código.',
'Mantener una única fuente del horario y una tabla configurable de equivalencias para importar Excel. Unificar captura, importación, conciliación y reportes en Asistencia. Los respaldos temporales deben tener alcance y vencimiento visibles.',
'Una modificación del horario se refleja en captura y sincronización. Cada importación muestra altas, correcciones, rechazos y origen sin generar duplicados.')
h('Recorrido esperado para las familias')
p('Crear o localizar a la persona acudiente, vincularla al estudiante, confirmar canales de contacto, habilitar su acceso y comprobar la vista del estudiante. Estos pasos deben estar ligados a la matrícula y a la ficha familiar. El nombre Matrimonio debe desaparecer de la navegación porque no describe la función real.')
p('La ficha debe distinguir vínculo familiar, contacto preferente y permiso de consulta. Las excepciones de contacto o autorización deben ser administradas por personal responsable y quedar registradas. Esta es una regla de diseño propuesta, no una verificación de cumplimiento normativo.')

page(6,'Controles transversales y cierre escolar')
finding('F08','Los permisos no comparten un vocabulario único','P1','E04 E06 E08 E18',
'El centro de configuración incluye muchas opciones para admin y docente, pero no para directivo. La capa histórica transforma directivo en admin; el asistente sí reconoce directivo, mientras Crear curso solo admite admin y docente. El gestor de inscripciones reconoce además denominaciones que no admite el directorio de usuarios.',
'Un mismo perfil puede ver, perder o recibir opciones diferentes según la pantalla. La regla de acceso resulta difícil de comprobar y mantener.',
'Definir permisos por operación y alcance, con los mismos roles en menú, interfaz y servidor. Distinguir consulta, propuesta, aprobación, activación y reapertura. Mantener los límites por institución y asignaciones del docente.',
'Cada operación tiene una regla única y pruebas por rol; ocultar un botón no es el mecanismo de autorización.')
finding('F09','El esquema histórico exige una migración explícita','P1','E01 E19',
'El respaldo combina MyISAM e InnoDB; año lectivo y asignación figuran en MyISAM. En las definiciones examinadas, inscripcion y acudiente_estudiante no tienen claves únicas compuestas para evitar pares repetidos. El asistente convierte tablas al confirmar una instalación y aplica parte de la consistencia desde su propio código.',
'Los controles de una pantalla no protegen por sí solos escrituras hechas desde otra. La conversión de motores en una apertura anual mezcla mantenimiento técnico y operación escolar.',
'Planificar la conversión y las restricciones como migraciones previas, con respaldo y ensayo de restauración. Conciliar duplicados y referencias antes de añadir claves o relaciones. Conservar los controles de reintento del asistente.',
'Las entidades centrales soportan transacciones y las restricciones impiden duplicados según las reglas de negocio aprobadas.')
finding('F10','El cierre necesita un proceso distinto de activar o desactivar','P1','E01 E07 E08 E17',
'Las entradas centrales revisadas gestionan activación, inactivación y estados de inscripción. No exponen un cierre integral con verificación de pendientes, bloqueo de captura, reapertura motivada y promoción al siguiente ciclo. Balance anual calcula una proyección a partir de dos períodos y presupone cuatro períodos de igual peso.',
'Desactivar no demuestra que los datos estén completos ni protege un consolidado aprobado. Una proyección puede confundirse con una decisión final si no se comunica su alcance.',
'Definir cierre de período y cierre anual, con reglas de evaluación configurables, acta o registro de decisión y consolidado versionado. Presentar el balance y el simulador como apoyo hasta completar su integración.',
'Un período cerrado impide cambios ordinarios; reabrir exige responsable y motivo. La promoción genera la matrícula siguiente sin modificar el histórico.')

page(7,'Módulos centrales que deben integrarse')
p('La función determina qué conservar; el número de pantallas no debe determinar la estructura del sistema. Las decisiones siguientes son de organización funcional y sustitución gradual de accesos.')
table(['Entradas actuales','Decisión','Destino propuesto'],[
('Asistente de inicio de año','Conservar y ampliar','Centro del ciclo escolar. Añadir responsables, matrícula anual y controles previos a la apertura.'),
('Año Lectivo y Períodos','Integrar','Ciclos y calendario. Usar el mismo servicio de activación y mantenimiento del asistente.'),
('Crear curso y Asignaciones','Integrar','Oferta académica y carga docente. Mantener una vista individual y otra masiva dentro del mismo módulo.'),
('Matrícula e Inscripciones','Integrar y redefinir','Matrícula anual como cabecera e inscripciones a asignaturas como detalle.'),
('Retirar materias','Integrar','Acción dentro de matrícula o inscripción, con fecha, motivo y preservación de evidencias.'),
('Grados y áreas','Conservar y ordenar','Estructura académica. Diferenciar grados, grupos, áreas y asignaturas.'),
('Usuarios y filtros por rol','Conservar','Directorio de personas y cuentas, con vistas filtradas; no tratarlos como módulos separados.'),
('Matrimonio y Acudientes y estudiantes','Unificar','Familias y acudientes. Mantener una única validación y una ficha de vínculos.'),
('Asistencia y planilla para Sisga','Unificar la experiencia','Asistencia con captura, importaciones, conciliación e informes.'),
('Malla e Importar malla','Conservar e integrar acceso','Currículo. La importación es una acción del catálogo curricular.'),
('Calendario de planes y planeación','Conservar y aclarar','Planeación pedagógica. Relacionar con períodos, sin confundirla con apertura de año.'),
('Hoja de vida y seguimiento','Conservar y conectar','Ficha del estudiante con trayectoria, asistencia, seguimiento y acceso a la familia.'),
],[2.05,1.08,3.57],9.1)
p('No conviene eliminar el asistente ni convertir la organización en una simple limpieza visual. La integración debe compartir validaciones, datos y permisos; las pantallas anteriores pueden mantenerse como redirecciones durante la transición.',size=9.5)

page(8,'Accesos innecesarios y herramientas auxiliares')
p('Estas funciones pueden aportar valor sin formar parte de la instalación o de la apertura escolar. Las mejoras de navegación son P2: reducir su presencia en el recorrido principal y conservarlas por necesidad institucional o del docente.')
table(['Entrada o familia','Acción propuesta','Justificación'],[
('Observador del estudiante y Seguimiento','Un solo acceso','Los dos registros del respaldo apuntan a seguimiento/citas.php [E20].'),
('Dos informes de inasistencias','Un solo acceso','Dos registros apuntan a reportes/informe_asistencia.php [E20].'),
('Generación de cursos y Estructura de inicio de año','Integrar en Archivos académicos','Generan carpetas; sus nombres sugieren creación de oferta o instalación, que no realizan [E15].'),
('Interfaz, personalizador de menú, iconos y tarjetas','Agrupar en Apariencia','Son ajustes relacionados y no pasos de la gestión académica [E04].'),
('Contenidos, objetivos y recursos','Integrar en Planeación','Son catálogos necesarios como apoyo; no requieren tres accesos principales.'),
('Plan anual institucional y Calendario de planes','Mantener separados con nombres claros','La ruta de compromisos institucionales y la planeación de clases tienen propósitos distintos.'),
('Notas, balance y simulador','Agrupar en Evaluación e informes','Distinguir registro oficial, auditoría de importación y proyección. Evitar presentarlos como fuentes equivalentes.'),
('Ruleta, bingo, fracciones, sopas y otras actividades','Mover a Recursos de aula','Su uso es opcional y no condiciona la preparación escolar.'),
('Collage, PDF, fotos, carnet y generadores','Mover a Herramientas','Son utilidades de apoyo. Habilitar por perfil y necesidad.'),
('Mercado, finanzas, salud y herramientas personales','Evaluar y separar del núcleo','Confirmar finalidad y responsable; mover al espacio personal cuando no formen parte de un servicio escolar.'),
('PAE e inventario tecnológico','Conservar si se usan institucionalmente','Ubicar en Servicios institucionales, con permisos propios; no descartarlos por ser auxiliares.'),
],[2.03,1.6,3.07],9)
h('Criterio para retirar código')
p('La duplicación funcional y de accesos sí está identificada. La falta de uso de una aplicación no está demostrada. Antes de retirar archivos, comprobar dependencias, tareas programadas, accesos directos, datos conservados y responsable del proceso. Los directorios con nombres de copia son candidatos a revisión, no evidencia suficiente para borrado.')

page(9,'Flujo integral propuesto para el año escolar')
p('El ciclo debe permitir preparar el año siguiente mientras continúa abierto el actual. Guardar un borrador, instalar una estructura preparada y activar la operación son decisiones distintas. La activación debe concluir con una verificación por perfil.')
table(['Paso','Responsable funcional','Resultado y condición para avanzar'],[
('1 Instalar la plataforma','Administrador técnico','Requisitos y esquema vigentes, institución, cuenta inicial y respaldo configurados. No se matriculan estudiantes por defecto.'),
('2 Preparar el ciclo','Dirección o coordinación','Crear o copiar un ciclo en borrador. Definir alcance institucional y responsables.'),
('3 Configurar calendario y evaluación','Coordinación','Fechas, períodos, cortes, ponderaciones y reglas completas. Sin solapamientos ni contradicciones.'),
('4 Organizar la oferta','Coordinación','Grados, grupos, asignaturas, carga docente y horarios. Atender organización multigrado y conflictos.'),
('5 Revisar personas y familias','Secretaría o responsable de matrícula','Identidades y cuentas conciliadas; vínculos y contactos de acudientes comprobados.'),
('6 Matricular y distribuir','Secretaría con coordinación','Matrícula por estudiante y año. Inscripciones derivadas de su ubicación y oferta, con vista previa.'),
('7 Verificar y abrir','Dirección','Resolver errores bloqueantes, documentar excepciones permitidas y activar año y período en una operación.'),
('8 Operar y cerrar períodos','Docentes y coordinación','Capturar actividad, evaluación y asistencia; atender familias, conciliar pendientes y cerrar cada período.'),
('9 Cerrar y preparar continuidad','Dirección y secretaría','Aprobar consolidado, registrar promoción o retiro, bloquear histórico y preparar matrículas del ciclo siguiente.'),
],[1.45,1.55,3.7],9.3)
h('Qué debe comprobar la revisión final')
p('Oferta y docentes válidos; estudiantes sin ubicación o duplicados; inscripciones esperadas y faltantes; familias con datos pendientes; períodos completos; horarios sin cruces; permisos consistentes; rutas de importación y archivos probadas. La ausencia de un vínculo familiar debe tratarse según una política institucional explícita, con excepciones documentadas.')
p('El asistente actual cubre parte importante de los pasos 2, 3, 4, 6 y 7. Debe ampliar la verificación y enlazar las tareas de personas y familias, sin convertirse en una pantalla que duplique todos los formularios del sistema.')

page(10,'Modelo de información y estados propuestos')
p('El modelo debe permitir responder quién es el estudiante, dónde está matriculado este año, qué asignaturas cursa y quién puede consultar su información. Cada respuesta necesita una referencia propia y estable.')
table(['Entidad','Responsabilidad','Regla esencial'],[
('Persona y cuenta','Identidad, contacto y acceso','La persona conserva su identidad al cambiar de año o rol. No guardar el grado en observaciones.'),
('Institución y ciclo escolar','Contexto académico anual','Definir si la institución contiene sedes o si cada sede funciona como institución independiente.'),
('Período','Tramo evaluativo del ciclo','Identificador propio, número u orden, fechas, estado y reglas del ciclo.'),
('Grado y grupo','Nivel curricular y organización operativa','Un grupo puede ser multigrado; cada matrícula conserva el grado individual.'),
('Asignatura y oferta docente','Qué se enseña y quién lo atiende','La asignación referencia ciclo, institución, grupo o grado, asignatura y docente.'),
('Matrícula anual','Pertenencia escolar por ciclo','Una matrícula institucional vigente por estudiante y ciclo, con movimientos y excepciones definidos.'),
('Inscripción','Participación en una asignación','Referencia a la matrícula y a la oferta. Evitar pares duplicados dentro de la regla aprobada.'),
('Vínculo con acudiente','Relación y alcance de consulta','Admite varios estudiantes y varios acudientes, con vigencia y contacto preferente.'),
('Registro y cierre','Asistencia, evaluación y decisiones','Referencia académica, fecha, autor y origen. El cierre conserva una versión aprobada.'),
],[1.32,2.13,3.25],9.3)
h('Estados que deben diferenciarse')
p('Ciclo escolar: borrador, preparado, abierto, cerrado y archivado. Período: programado, abierto y cerrado; la reapertura es una operación autorizada que conserva su historial. Matrícula: pendiente, activa, trasladada, retirada o finalizada. Resultado académico: pendiente, aprobado o no aprobado, según las reglas institucionales.')
h('Reglas que deben ser comunes')
p('El período pertenece al ciclo seleccionado; consultar un año anterior no cambia el año operativo; una inscripción se deriva de una matrícula válida; un retiro no borra actividad histórica; una importación conserva origen y resultado; un cambio de docente no crea dos ofertas equivalentes sin decisión expresa. La aplicación y la base deben reforzar estas reglas de forma coordinada.')

page(11,'Responsabilidades y navegación objetivo')
p('La separación propuesta es funcional. Secretaría puede implementarse como un rol nuevo o como permisos asignados a personal autorizado. La administración técnica no debe confundirse con la aprobación de decisiones académicas.')
table(['Operación','Responsable principal','Control o participación'],[
('Instalación y migraciones','Administrador técnico','Dirección recibe el resultado y autoriza la puesta en operación institucional.'),
('Calendario, oferta y horarios','Coordinación','Docentes proponen o revisan su carga; dirección aprueba apertura.'),
('Matrícula y vínculos familiares','Secretaría','Coordinación resuelve ubicación; la familia confirma sus datos de contacto.'),
('Captura académica','Docente','Solo su carga y estudiantes autorizados; coordinación supervisa.'),
('Cierre y reapertura','Dirección o coordinación delegada','Requiere pendientes resueltos, motivo en reapertura e historial de cambios.'),
('Consulta de información','Estudiante o acudiente','Solo su información o la de estudiantes con vínculo vigente.'),
],[1.85,1.7,3.15],9.4)
h('Navegación principal propuesta')
table(['Sección','Contenido'],[
('Inicio','Estado del ciclo, pendientes y acciones del perfil. Contexto visible de institución, año y período.'),
('Gestión escolar','Asistente anual, calendario, grados y grupos, oferta, carga docente y matrícula.'),
('Estudiantes y familias','Directorio, ficha del estudiante, acudientes, vínculos y movimientos.'),
('Trabajo académico','Mis cursos, planeación, currículo, recursos y actividades de aula.'),
('Asistencia y seguimiento','Captura, importaciones, conciliación, observador e intervenciones.'),
('Evaluación e informes','Valoraciones, auditoría, simulaciones identificadas como tales, consolidados y cierres.'),
('Administración y herramientas','Permisos, apariencia, respaldos, integraciones, servicios institucionales y utilidades opcionales.'),
],[1.75,4.95],9.3)
p('El docente debe empezar por sus cursos, estudiantes y pendientes. La familia debe empezar por su estudiante seleccionado y sus novedades. Solo los perfiles responsables necesitan ver apertura, matrícula general, cambios globales o mantenimiento técnico. Los favoritos pueden complementar esta estructura sin sustituirla.')

page(12,'Hoja de ruta de reorganización')
p('La secuencia reduce el riesgo de reorganizar pantallas antes de estabilizar sus datos. Cada etapa debe cerrar con una demostración funcional y un criterio comprobable; no se proponen fechas sin estimar esfuerzo, dependencias y disponibilidad del equipo.')
table(['Etapa','Entregable','Condición de salida'],[
('1 Estabilizar recorridos esenciales','Instalador vigente; activación común; creación de períodos; selector de estudiantes de acudientes.','Instalación vacía, cambio de ciclo e ingreso familiar completados en ambiente de validación. Cerrar F01 F02 F03 F06.'),
('2 Consolidar el modelo académico','Matrícula anual, grado y grupo; estados separados; migración de referencias.','Históricos conciliados y conteos comparados. Ningún proceso nuevo depende del grado en observaciones. Cerrar F04 F05.'),
('3 Integrar operación y controles','Horario común; importaciones conciliadas; permisos uniformes; restricciones del esquema.','Captura e importación coinciden; controles por institución y rol; duplicados prevenidos. Cerrar F07 F08 F09.'),
('4 Completar el ciclo de cierre','Reglas de evaluación, cierre y reapertura; consolidado y promoción.','Cerrar un período, reabrir con motivo y preparar continuidad sin alterar históricos. Cerrar F10 y sus dependencias.'),
('5 Simplificar accesos','Menú por perfil, nombres claros, redirecciones y catálogo de herramientas.','Cada tarea tiene una entrada principal; enlaces conservados o redirigidos; herramientas opcionales fuera del recorrido anual.'),
],[1.18,2.7,2.82],9.4)
h('Transición de información')
p('Inventariar las referencias actuales; conciliar grado, estudiante y año; diseñar la migración; ensayarla sobre una copia; comparar totales y muestras; verificar reportes y permisos; documentar la reversión; ejecutar la transición en una ventana acordada. Los casos ambiguos deben quedar en una lista de revisión, sin asignaciones automáticas por suposición.')
h('Controles del cierre')
p('Para cerrar un período: validar captura y pendientes, conciliar importaciones, generar el consolidado, registrar aprobación y bloquear cambios ordinarios. Para cerrar el año: completar períodos, resolver resultados pendientes, aprobar decisiones de continuidad, generar el archivo histórico y preparar las matrículas del siguiente ciclo. Un nuevo año puede prepararse antes del cierre, pero su activación debe respetar el alcance y las reglas de operación.')
p('La primera entrega debe priorizar recorridos completos. El cambio de etiquetas o colores se incorpora cuando las rutas y reglas ya producen el mismo resultado.')

page(13,'Validación y criterios de éxito')
p('Los siguientes escenarios son pruebas de aceptación propuestas para la reorganización. Constituyen una pauta para demostrar el resultado; no se presentan como pruebas ejecutadas de los cambios recomendados.')
table(['Escenario','Resultado esperado'],[
('Instalación desde cero','Esquema vigente, cuenta inicial y primer ciclo preparados sin correcciones manuales de SQL.'),
('Inscripción automática deshabilitada','No se crea ninguna inscripción automática.'),
('Preparar sin activar','El año en uso y sus rutas siguen operativos; el siguiente queda preparado.'),
('Activación desde dos entradas','Asistente y mantenimiento aplican la misma regla de año y período.'),
('Períodos y reintentos','Sin claves repetidas ni fechas superpuestas; repetir confirmación no duplica datos.'),
('Matrícula y multigrado','El estudiante conserva grado individual y grupo operativo; sus asignaturas se generan de forma coherente.'),
('Cambio de grado o retiro','Se registra fecha y motivo; la información anterior sigue consultable.'),
('Acudiente con dos estudiantes','Puede alternar; cada consulta valida el vínculo. Un estudiante ajeno queda fuera de alcance.'),
('Cambio de horario e importación','Captura y sincronización usan la programación vigente y reportan diferencias.'),
('Roles e instituciones','Docente, directivo, secretaría y familia tienen resultados consistentes en menú y servidor.'),
('Cierre y reapertura','El cierre bloquea captura; reabrir exige permiso, motivo y registro.'),
('Continuidad al siguiente ciclo','La promoción o renovación prepara la nueva matrícula sin sobrescribir el año anterior.'),
],[2.05,4.65],9.4)
h('Indicadores para seguimiento')
p('Medir estudiantes activos sin matrícula, inscripciones duplicadas, vínculos familiares pendientes, diferencias de conciliación, registros sin referencia académica y cambios posteriores al cierre. Los objetivos propuestos de integridad son cero duplicados y cero referencias huérfanas; deben comprobarse en la base y en los recorridos, sin asumir que hoy se alcanzan.')
p('Registrar también cuántas tareas del inicio anual se completan desde el asistente y cuántas requieren intervención técnica. La línea base se obtiene antes de implementar. El éxito debe demostrarse al completar el ciclo escolar con trazabilidad y menos pasos manuales, no solo al reducir iconos.')

page(14,'Referencias y trazabilidad de hallazgos')
p('Las rutas son relativas a la raíz del proyecto D:\\xampp\\htdocs\\guagua. Las referencias de código corresponden a la revisión del 19 de noviembre de 2026. El respaldo es evidencia de esquema y navegación de una copia, no certificación del estado desplegado.',size=9.2)
refs=[
('E01','Planeador/lib/InicioAnual.php','Asistente, borradores, validación, instalación y activación.'),
('E02','Planeador/INICIO_ANUAL.md','Descripción funcional y alcance de los ocho pasos.'),
('E03','comun/academico.php y comun/sesion_contexto.php','Año y período compartidos y contexto de sesión.'),
('E04','comun/configuracion_opciones.php y comun/configuracion.php','Opciones del centro y filtros por rol.'),
('E05','instalar/index.php','Esquema histórico y condición de inscripción automática.'),
('E06','cursos/ano_lectivo.php y cursos/crear_curso.php','Mantenimiento del año y creación individual de oferta.'),
('E07','Planeador/periodo.php','Creación, activación, edición y eliminación de períodos.'),
('E08','Planeador/inscripes.php','Inscripciones individuales y masivas; grado en observaciones.'),
('E09','usuario/inscripcion.php y cursos/inscribir_estudiante.php','Matrícula por asignación y estado Aprobado en altas.'),
('E10','Planeador/genasig.php y cursos/mis_cursos.php','Asignaciones y consulta por perfil y contexto.'),
('E11','usuario/matrimonio.php','Vínculos con parentesco e importación.'),
('E12','usuario/acudiente_estudiante.php','Segundo CRUD de vínculos sin captura de parentesco.'),
('E13','clases/Persona.Class.php y clases/Academico.Class.php','Selección del estudiante y variables de contexto familiar.'),
('E14','asistencia/index.php y apps/asistencia/sincronizar.php','Captura y sincronización con programación de respaldo.'),
('E15','apps/asistencia/index.php y apps/generador_vallesol/index.php y apps/generacion_cursos/index.php','Programación y generación de carpetas académicas.'),
('E16','comun/BASE_DE_DATOS.md y comun/database.php','Configuración central y conexiones.'),
('E17','apps/balance_anual/index.php y apps/notas/lib/NotasAuditoriaService.php','Proyección anual e historial de importaciones.'),
('E18','comun/permisos.php y usuario/lib/usuarios_repository.php','Roles históricos y vocabulario de roles del directorio.'),
('E19','reportes/bd/automatico/guagua_20261119_183002.sql','Esquema de respaldo usado para contrastar entidades, motores y claves.'),
('E20','menu_items2 del respaldo E19 y apps/planeador_anual/api.php','Entradas duplicadas del menú y propósito del plan institucional.'),
]
for code,path,desc in refs:
    x=doc.add_paragraph();x.paragraph_format.space_after=Pt(6);x.paragraph_format.line_spacing=1.03
    r=x.add_run(code+'  ');r.bold=True;r.font.size=Pt(9)
    r=x.add_run(path);r.font.size=Pt(8.7)
    r=x.add_run('\n'+desc);r.font.size=Pt(9)

output=OUT/'Diagnostico_integral_Guagua_2026-11-19.docx'
doc.save(output)
words=sum(len(x.text.split()) for x in doc.paragraphs)+sum(len(c.text.split()) for t in doc.tables for row in t.rows for c in row.cells)
print(json.dumps({'archivo':str(output),'palabras_aproximadas':words,'paginas_planificadas':14,'tablas':len(doc.tables)},ensure_ascii=False))
