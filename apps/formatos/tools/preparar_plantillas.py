"""Prepara copias editables de los formatos Word oficiales sin alterar los originales."""
from pathlib import Path
from docx import Document
from copy import deepcopy


BASE = Path(__file__).resolve().parents[1]
OUT = BASE / "plantillas"
OUT.mkdir(exist_ok=True)


def set_paragraph(paragraph, text):
    """Solo reescribe el texto del slot; conserva las propiedades del primer run."""
    if paragraph.runs:
        paragraph.runs[0].text = text
        for run in paragraph.runs[1:]:
            run.text = ""
    else:
        paragraph.add_run(text)


def set_cell(cell, text):
    set_paragraph(cell.paragraphs[0], text)
    for paragraph in cell.paragraphs[1:]:
        set_paragraph(paragraph, "")


def remove_rows_after(table, start_row):
    """Deja una única fila de combinación bajo el encabezado."""
    for row in list(table.rows)[start_row + 1:]:
        table._tbl.remove(row._tr)


def save_observer():
    doc = Document(BASE / "22 Ficha observador páginas.docx")
    for p in doc.paragraphs:
        if "FICHA OBSERVADOR DEL ESTUDIANTE" in p.text:
            set_paragraph(p, "FICHA OBSERVADOR DEL ESTUDIANTE: ${ESTUDIANTE}")
            break
    table = doc.tables[0]
    set_cell(table.cell(0, 0), "Año lectivo 202${ANO_FINAL}")
    set_cell(table.cell(0, 1), "Grado: ${GRADO}")
    behaviours = [
        "puntualidad", "uniforme", "tareas", "excusas", "comportamiento", "sustancias",
        "armas", "conducto_regular", "reincidencia", "informacion_acudiente", "llamados_atencion",
        "trato_respetuoso", "agresion", "fraude", "cuidado_bienes", "basura", "rendimiento",
    ]
    for index, key in enumerate(behaviours, start=3):
        for offset, value in enumerate(("N", "AV", "S"), start=1):
            set_cell(table.cell(index, offset), "${O_%s_%s}" % (key.upper(), value))
    set_cell(table.cell(20, 1), "${OBSERVACIONES}")
    doc.save(OUT / "observador.docx")


def save_cover():
    doc = Document(BASE / "21 Portada Observador.docx")
    for p in doc.paragraphs:
        if p.text.strip().lower().startswith("estudiante"):
            set_paragraph(p, "Estudiante: ${ESTUDIANTE}")
            break
    doc.save(OUT / "portada_observador.docx")


def save_back_cover():
    doc = Document(BASE / "23 Contraportada Observador.docx")
    replacements = {
        "estudiante": "Estudiante: ${ESTUDIANTE}",
        "acudiente": "Acudiente: ${ACUDIENTE}",
        "condici": "Condición física o discapacidad: ${CONDICION_FISICA}",
        "grupo sangu": "Grupo sanguíneo y RH: ${SANGRE}",
    }
    for p in doc.paragraphs:
        normalized = p.text.strip().lower()
        for start, replacement in replacements.items():
            if normalized.startswith(start):
                set_paragraph(p, replacement)
                break
    table = doc.tables[0]
    values = ("202${ANO_FINAL}", "${GRADO}", "${DIRECCION}", "${CELULAR}", "${EPS}")
    for index, value in enumerate(values):
        set_cell(table.cell(1, index), value)
    doc.save(OUT / "contraportada_observador.docx")


def save_commission():
    doc = Document(BASE / "24 IERLJ_FA_24 COMISIÓN Y EVALUACIÓN V1.0.docx")
    slots = {
        2: "Periodo académico: ${PERIODO}\tAño lectivo: 20${ANO_FINAL}\tSede: ${SEDE}",
        3: "Grado(s): ${GRADOS}\tHora: ${HORA}\tDía: ${DIA} Mes: ${MES}",
        6: "${SITUACION}",
        10: "Padres de familia: ${PADRES}",
        13: "Representantes de grupo: ${REPRESENTANTES}",
        16: "Director de grupo: ${DIRECTOR_RECOMENDACIONES}",
        24: "_________________________________\t\t${DIRECTOR}",
    }
    for index, value in slots.items():
        set_paragraph(doc.paragraphs[index], value)
    doc.save(OUT / "comision.docx")


def save_permission():
    doc = Document(BASE / "3 IERLJ_PD_03 FORMATO DE PERMISO V1.0.docx")
    identity, absence, signature = doc.tables
    set_cell(identity.cell(0, 1), "${DOCENTE}")
    set_cell(identity.cell(1, 1), "${DOCUMENTO}")
    set_cell(identity.cell(2, 1), "${SEDE}")
    set_cell(absence.cell(0, 1), "${MOTIVO}")
    set_cell(absence.cell(1, 1), "${FECHA_AUSENCIA}")
    set_cell(absence.cell(2, 1), "${TIEMPO} ${UNIDAD}")
    set_cell(absence.cell(3, 1), "${SOPORTE}")
    set_cell(signature.cell(0, 0), "SOLICITÓ: Docente\n${DOCENTE}")
    set_cell(signature.cell(1, 0), "Fecha de Solicitud:\n${FECHA_SOLICITUD}")
    doc.save(OUT / "permiso.docx")


def save_dropout():
    doc = Document(BASE / "5 IERLJ_DE_05 DESERCIÓN ESCOLAR V1.0.docx")
    table = doc.tables[0]
    set_cell(table.cell(0, 0), "Director de Grupo: ${DIRECTOR}")
    set_cell(table.cell(0, 4), "Fecha: ${FECHA}")
    for cell, marker in zip(table.rows[3].cells, ("${NUM}", "${ESTUDIANTE}", "${GRADO}", "${FECHA_RETIRO}", "${ACCIONES}")):
        set_cell(cell, marker)
    remove_rows_after(table, 3)
    doc.save(OUT / "desercion.docx")


def save_recovery():
    doc = Document(BASE / "11 IERLJ_RR_11 PLANILLA DE REGISTRO REFUERZO Y RECUPERACIONES V1.0.docx")
    set_paragraph(doc.paragraphs[0], "Fecha: ${FECHA}               Recuperación: ${RECUPERACION}                    Refuerzo: ${REFUERZO}")
    table = doc.tables[0]
    markers = ("${ESTUDIANTE}", "${GRADO}", "${GRUPO}", "${JORNADA}", "${MATERIA}", "${COMPETENCIA}", "${TALLER}", "${ORAL}", "${ESCRITA}", "${OTRA}")
    for index, marker in enumerate(markers):
        set_cell(table.cell(2, index), marker)
    remove_rows_after(table, 2)
    doc.save(OUT / "refuerzo.docx")


def save_academic_report():
    source = next(BASE.glob("25 IERLJ_IA_25*INFORME*V1.0.docx"))
    doc = Document(source)
    set_paragraph(doc.paragraphs[0], "Año: 20${ANO_FINAL}   Periodo académico: ${PERIODO} \tDocente: ${DOCENTE} \tSede: ${SEDE}")
    table = doc.tables[0]
    for index, value in enumerate(("${ESTUDIANTE}", "${GRADO}", "${AREA}", "${DBA}", "${DIFICULTAD_ESTUDIO}", "${COMPETENCIAS}", "${COMPORTAMIENTO}", "${FAMILIA}", "${AUSENCIAS}")):
        set_cell(table.cell(1, index), value)
    remove_rows_after(table, 1)
    doc.save(OUT / "informe_academico.docx")


if __name__ == "__main__":
    save_observer()
    save_cover()
    save_back_cover()
    save_commission()
    save_permission()
    save_dropout()
    save_recovery()
    save_academic_report()
    print("Plantillas preparadas en", OUT)
