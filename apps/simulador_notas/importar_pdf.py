"""Importa el PDF tabular de SISGA; Python solo se necesita para actualizar la base."""
import argparse
import hashlib
import json
import re
from datetime import datetime
from pathlib import Path

import pdfplumber


def extract_report(path):
    records, identities, pages = [], set(), []
    report_date = None
    with pdfplumber.open(path) as pdf:
        for number, page in enumerate(pdf.pages, 1):
            text = page.extract_text() or ""
            group = re.search(r"Secci[oó]?n\s+(.+)", text)
            subject = re.search(r"Asignatura\s+C[oó]digo:\s*\S+\s+Nombre:\s*(.+)", text)
            teacher = re.search(r"Docente\s+C[oó]digo:\s*\S+\s+Nombre:\s*(.+)", text)
            date = re.search(r"\b(\d{2}/\d{2}/20\d{2})\b", text)
            if not all((group, subject, teacher, date)):
                raise ValueError(f"Página {number}: encabezado de SISGA incompleto")
            year = re.search(r"\b(20\d{2})\b", group[1])
            if not year:
                raise ValueError(f"Página {number}: sección sin año")
            if report_date is not None and report_date != date[1]:
                raise ValueError("El PDF mezcla fechas de reporte")
            report_date = date[1]
            tables = page.extract_tables()
            if len(tables) != 1 or tables[0][1][7:12] != ["1", "2", "3", "4", "5"]:
                raise ValueError(f"Página {number}: no se reconocen las columnas P1–P4")
            rows = tables[0][2:]
            for ordinal, row in enumerate(rows, 1):
                if row[0] != str(ordinal) or not row[1] or not row[4]:
                    raise ValueError(f"Página {number}: fila {ordinal} incompleta o fuera de orden")
                record = {
                    "year": year[1], "group": group[1].strip(), "code": row[1].strip(),
                    "student": " ".join(row[4].split()), "subject": subject[1].strip(),
                    "teacher": teacher[1].strip(), "source_page": number,
                }
                for period in range(1, 5):
                    value = (row[6 + period] or "").strip()
                    if value and not re.fullmatch(r"[1-5](?:[.,]\d+)?", value):
                        raise ValueError(f"Página {number}, fila {ordinal}, P{period}: nota inválida")
                    grade = float(value.replace(",", ".")) if value else None
                    if grade is not None and not 1 <= grade <= 5:
                        raise ValueError(f"Página {number}, fila {ordinal}, P{period}: fuera de escala")
                    record[f"p{period}"] = grade
                # Compatible con las identidades del simulador, sin publicar documentos personales.
                identity = "|".join(record[key] for key in ("year", "group", "code", "subject"))
                record["id"] = hashlib.sha256(identity.encode("utf-8")).hexdigest()
                if identity in identities:
                    raise ValueError(f"Página {number}: estudiante y materia duplicados")
                identities.add(identity)
                records.append(record)
            pages.append({"page": number, "group": group[1].strip(), "subject": subject[1].strip(), "records": len(rows)})
    if not records:
        raise ValueError("El PDF no contiene notas reconocibles")
    return {
        "source": path.name, "source_type": "pdf", "source_sha256": hashlib.sha256(path.read_bytes()).hexdigest(),
        "report_date": datetime.strptime(report_date, "%d/%m/%Y").date().isoformat(),
        "imported_at": datetime.now().astimezone().isoformat(), "source_pages": pages, "records": records,
    }


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("pdf", type=Path)
    parser.add_argument("--output", type=Path, default=Path(__file__).parent / "data" / "inicial.php")
    args = parser.parse_args()
    data = extract_report(args.pdf)
    # Una cadena PHP de comillas simples preserva exactamente Unicode, decimales y valores nulos.
    encoded = json.dumps(data, ensure_ascii=False, indent=2).replace("\\", "\\\\").replace("'", "\\'")
    content = "<?php\nreturn json_decode('" + encoded + "', true, 512, JSON_THROW_ON_ERROR);\n"
    temporary = args.output.with_suffix(".tmp")
    temporary.write_text(content, encoding="utf-8")
    temporary.replace(args.output)
    print(json.dumps({"pages": len(data["source_pages"]), "records": len(data["records"]),
        "students": len({(r["year"], r["group"], r["code"]) for r in data["records"]}),
        "grades": {f"p{p}": sum(r[f"p{p}"] is not None for r in data["records"]) for p in range(1, 5)}}, ensure_ascii=False))


if __name__ == "__main__":
    main()
