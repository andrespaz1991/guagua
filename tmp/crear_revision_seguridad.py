import json
from pathlib import Path
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.enum.text import WD_ALIGN_PARAGRAPH

root = Path('D:/xampp/htdocs/guagua')
bank = json.loads((root / 'apps/preguntas_por_equipos/seguridad-informatica.json').read_text(encoding='utf-8'))['config']
doc = Document()
sec = doc.sections[0]
sec.page_width, sec.page_height = Inches(8.5), Inches(11)
sec.top_margin, sec.bottom_margin = Inches(.65), Inches(.65)
sec.left_margin, sec.right_margin = Inches(.8), Inches(.8)
sec.footer_distance = Inches(.28)

for name in ['Normal', 'Title', 'Subtitle', 'Heading 1', 'Heading 2']:
    style = doc.styles[name]
    style.font.name = 'Calibri'
    style.font.color.rgb = RGBColor(0, 0, 0)
    style.paragraph_format.space_after = Pt(5)
doc.styles['Normal'].font.size = Pt(11)
doc.styles['Normal'].paragraph_format.line_spacing = 1.05
doc.styles['Title'].font.size = Pt(22)
doc.styles['Title'].font.bold = True
doc.styles['Heading 1'].font.size = Pt(14)
doc.styles['Heading 1'].paragraph_format.space_before = Pt(0)
for style in doc.styles:
    for border in list(style.element.iter(qn('w:pBdr'))):
        border.getparent().remove(border)

doc.core_properties.title = 'Preguntas de seguridad informática y protección de jóvenes'
doc.core_properties.subject = 'Revisión docente de las 36 preguntas del módulo Reto por equipos'
doc.core_properties.author = ''

for start in range(0, 36, 4):
    if start:
        doc.add_page_break()
        doc.add_heading(f'Preguntas {start + 1:02d} a {start + 4:02d}', level=1)
    else:
        doc.add_paragraph('Preguntas de seguridad informática', style='Title')
        intro = doc.add_paragraph('Protección de jóvenes y navegación segura', style='Subtitle')
        intro.paragraph_format.space_after = Pt(5)
        doc.add_paragraph('Revisión docente de las 36 preguntas del módulo Reto por equipos. Incluye las respuestas esperadas y espacio para anotar ajustes. Los casos se presentan sin detalles gráficos.')
    for i in range(start, start + 4):
        q = bank['questions'][i]
        p = doc.add_paragraph()
        p.paragraph_format.space_before = Pt(9)
        p.paragraph_format.space_after = Pt(4)
        p.paragraph_format.keep_with_next = True
        p.add_run(f'Pregunta {i + 1:02d}').bold = True
        p.add_run(f'     Tiempo {q["seconds"]} segundos').font.size = Pt(10)
        p = doc.add_paragraph(q['question'])
        p.paragraph_format.keep_with_next = True
        p.paragraph_format.keep_together = True
        for r in p.runs:
            r.bold = True
        p = doc.add_paragraph()
        p.paragraph_format.keep_with_next = True
        p.paragraph_format.keep_together = True
        p.add_run('Respuesta esperada: ').bold = True
        p.add_run(q['answer'])
        p = doc.add_paragraph('Observaciones: _________________________________________________________')
        p.paragraph_format.space_after = Pt(4)
        for r in p.runs:
            r.font.size = Pt(9)
            r.font.color.rgb = RGBColor.from_string('666666')

footer = sec.footer.paragraphs[0]
footer.alignment = WD_ALIGN_PARAGRAPH.RIGHT
run = footer.add_run('Página ')
run.font.size = Pt(9)
field = OxmlElement('w:fldSimple')
field.set(qn('w:instr'), 'PAGE')
footer._p.append(field)
path = root / 'output/Preguntas_seguridad_informatica.docx'
doc.save(path)
check = Document(path)
content = '\n'.join(p.text for p in check.paragraphs)
assert len(bank['questions']) == 36
assert all(q['question'] in content and q['answer'] in content for q in bank['questions'])
print(path)
print('36 preguntas y respuestas coinciden con el banco del módulo.')
