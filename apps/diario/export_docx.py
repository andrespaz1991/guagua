"""Create a copy of the retained template; change only word/document.xml."""
import base64
import copy
import io
import json
import re
import sys
from datetime import date
from pathlib import Path
from zipfile import ZipFile
from lxml import etree

ROOT = Path(__file__).resolve().parents[2]
TEMPLATE = ROOT / 'comun' / 'doc' / 'Plantilla guía.docx'
W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
NS = {'w': W}
MONTHS = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre']

def diary_period(text):
    """Use entry dates, ignoring month references in the narrative and download date."""
    periods = set()
    for line in text.splitlines():
        heading = re.match(r'^\s*#{1,2}(?!#)\s*(.+)$', line)
        if not heading:
            continue
        label = heading.group(1).replace('*', '').replace('_', '').lower()
        iso = re.search(r'(?<!\d)(20\d{2})-(\d{2})-(\d{2})(?!\d)', label)
        spanish = re.search(r'\b(\d{1,2})\s+de\s+(' + '|'.join(MONTHS) + r')\s+(?:de(?:l)?\s+)?(20\d{2})\b', label)
        try:
            if iso:
                year, month, day = map(int, iso.groups())
            elif spanish:
                day, month_name, year = spanish.groups()
                day, month, year = int(day), MONTHS.index(month_name) + 1, int(year)
            else:
                continue
            date(year, month, day)
            periods.add((year, month))
        except ValueError:
            continue
    if not periods:
        return 'Periodo por confirmar', 'Diario_periodo_por_confirmar.docx'
    first, last = min(periods), max(periods)
    first_year, first_month = first
    last_year, last_month = last
    if first == last:
        return f'{MONTHS[first_month-1].capitalize()} de {first_year}', f'Diario_{MONTHS[first_month-1]}_{first_year}.docx'
    if first_year == last_year:
        return (f'{MONTHS[first_month-1].capitalize()} a {MONTHS[last_month-1]} de {first_year}',
                f'Diario_{MONTHS[first_month-1]}_a_{MONTHS[last_month-1]}_{first_year}.docx')
    return (f'{MONTHS[first_month-1].capitalize()} de {first_year} a {MONTHS[last_month-1]} de {last_year}',
            f'Diario_{MONTHS[first_month-1]}_{first_year}_a_{MONTHS[last_month-1]}_{last_year}.docx')

def element(name, **attributes):
    node = etree.Element('{%s}%s' % (W, name))
    for key, value in attributes.items():
        node.set('{%s}%s' % (W, key), str(value))
    return node

def paragraph(text, kind='body', page_break=False):
    p = element('p')
    props = element('pPr')
    p.append(props)
    props.append(element('pStyle', val='Ttulo' if kind == 'cover' else 'Normal'))
    props.append(element('spacing', before=2400 if kind == 'cover' else (180 if kind == 'title' else 0), after=180 if kind in ('date', 'title') else 160, line=276, lineRule='auto'))
    props.append(element('jc', val='center' if kind.startswith('cover') else ('both' if kind == 'body' else 'left')))
    if kind in ('date', 'title', 'cover'):
        props.append(element('keepNext'))
    if page_break:
        props.append(element('pageBreakBefore'))
    sizes = {'cover':52, 'cover_subtitle':32, 'cover_date':36, 'date':28, 'title':26, 'body':24, 'reflection':24}
    for token in re.split(r'(\*\*[^*]+\*\*|\*[^*]+\*)', text):
        if not token:
            continue
        bold = token.startswith('**') and token.endswith('**')
        italic = not bold and token.startswith('*') and token.endswith('*')
        token = token[2:-2] if bold else (token[1:-1] if italic else token)
        run = element('r')
        rprops = element('rPr')
        rprops.append(element('rFonts', ascii='Times New Roman', hAnsi='Times New Roman', cs='Times New Roman'))
        rprops.append(element('sz', val=sizes.get(kind,24)))
        rprops.append(element('szCs', val=sizes.get(kind,24)))
        rprops.append(element('color', val='000000'))
        if bold or kind in ('date','title','cover'):
            rprops.append(element('b'))
        if italic or kind == 'reflection':
            rprops.append(element('i'))
        run.append(rprops)
        t = element('t')
        t.set('{http://www.w3.org/XML/1998/namespace}space','preserve')
        t.text = token
        run.append(t)
        p.append(run)
    return p

def build_docx(text):
    if not text.strip() or len(text) > 800000:
        raise ValueError('El diario está vacío o es demasiado grande.')
    month_year, filename = diary_period(text)
    with ZipFile(TEMPLATE) as source:
        doc = etree.fromstring(source.read('word/document.xml'))
        body = doc.find('w:body',NS)
        section = copy.deepcopy(body.find('w:sectPr',NS))
        # The retained template contains four blank paragraphs and its letterhead
        # in the default header. Preserve every other ZIP part byte for byte.
        for child in list(body):
            body.remove(child)
        body.append(paragraph('Diario de Campo Pedagógico','cover'))
        body.append(paragraph('Sede Vallesol','cover_subtitle'))
        body.append(paragraph('Bitácora de Experiencias y Reflexiones Docentes','cover_subtitle'))
        body.append(paragraph(month_year,'cover_date'))
        text = re.sub(r'^```(?:markdown|md)?\s*\n|\n```\s*$', '',text.strip(),flags=re.I)
        text = re.sub(r'(\*\*Reflexi[oó]n del d[ií]a:\*\*)\s*\n+\s*', r'\1 ', text, flags=re.I)
        blocks = re.split(r'\n\s*\n', text)
        first_content = True
        for block in blocks:
            # Keep headings and their following paragraphs distinct even when
            # the model omits blank lines between them.
            buffer = []
            def flush():
                nonlocal first_content
                if buffer:
                    line = ' '.join(buffer).strip()
                    kind = 'reflection' if re.match(r'^(?:\*\*)?Reflexi[oó]n del d[ií]a',line,re.I) else 'body'
                    body.append(paragraph(line,kind,first_content))
                    first_content = False
                    buffer.clear()
            for line in block.splitlines():
                line = line.strip()
                if not line or line in ('---','***'):
                    continue
                if line.startswith('#'):
                    flush()
                    level = len(line)-len(line.lstrip('#'))
                    label = line.lstrip('#').strip()
                    if re.match(r'^Reflexi[oó]n del d[ií]a',label,re.I):
                        body.append(paragraph('**'+label.rstrip(':')+':**','reflection',first_content))
                        first_content = False
                        continue
                    kind = 'date' if level <= 2 else 'title'
                    body.append(paragraph(label,kind,first_content or kind == 'date'))
                    first_content = False
                else:
                    buffer.append(line)
            flush()
        body.append(section)
        replacement = etree.tostring(doc,encoding='UTF-8',xml_declaration=True,standalone=True)
        target = io.BytesIO()
        with ZipFile(target,'w') as result:
            for info in source.infolist():
                result.writestr(info,replacement if info.filename == 'word/document.xml' else source.read(info.filename))
    return target.getvalue(), filename

if __name__ == '__main__':
    try:
        payload = json.loads(sys.stdin.buffer.read().decode('utf-8'))
        content, filename = build_docx(str(payload.get('text','')))
        sys.stdout.write(json.dumps({'filename':filename,'base64':base64.b64encode(content).decode('ascii')}))
    except Exception as error:
        sys.stderr.write(str(error))
        sys.exit(1)
