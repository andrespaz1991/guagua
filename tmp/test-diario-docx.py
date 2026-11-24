import importlib.util
import hashlib
from pathlib import Path
from zipfile import ZipFile
from lxml import etree
from pypdf import PdfReader
spec = importlib.util.spec_from_file_location('diario_export','apps/diario/export_docx.py')
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)
reference = Path('comun/doc/Plantilla guía.docx')
before = hashlib.sha256(reference.read_bytes()).hexdigest()
pdf = PdfReader('tmp/Diario Agosto.pdf')
# Use the user's example to stress-test realistic paragraph length and wrapping;
# this is only a private QA fixture, never the user's generated draft.
def entry(index):
    text = pdf.pages[index].extract_text()
    lines = [line.strip() for line in text.splitlines() if line.strip()]
    date = lines[0]
    title = lines[1]
    reflection_index = next(i for i,line in enumerate(lines) if line.startswith('Reflexión del día:'))
    prose = ' '.join(lines[2:reflection_index])
    # Retain a useful multi-paragraph density in the QA fixture.
    sentences = prose.split('. ')
    groups = ['. '.join(sentences[i:i+4]) for i in range(0,len(sentences),4)]
    return '## '+date+'\n\n### '+title+'\n\n'+'\n\n'.join(groups)+'\n\n**Reflexión del día:** '+ ' '.join(lines[reflection_index:]).split(':',1)[1].strip()
sample = entry(1)+'\n\n'+entry(2)
content, filename = module.build_docx(sample)
Path('tmp/diario-word-verificacion.docx').write_bytes(content)
with ZipFile(reference) as original, ZipFile('tmp/diario-word-verificacion.docx') as result:
    assert original.namelist() == result.namelist()
    for part in original.namelist():
        if part != 'word/document.xml':
            assert original.read(part) == result.read(part), part
    xml = etree.fromstring(result.read('word/document.xml'))
    text = ''.join(xml.itertext())
    assert 'Octubre de 2026' in text
    ns={'w':module.W}
    assert etree.tostring(etree.fromstring(original.read('word/document.xml')).find('w:body/w:sectPr',ns)) == etree.tostring(xml.find('w:body/w:sectPr',ns))
    assert len(xml.findall('.//w:pageBreakBefore',ns)) == 2
assert hashlib.sha256(reference.read_bytes()).hexdigest() == before
print('Word verificado: portada, dos jornadas, plantilla original y partes conservadas intactas. '+filename)
