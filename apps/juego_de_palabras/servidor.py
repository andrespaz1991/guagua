"""Interfaz local para crear tarjetas de palabras en PPTX o PDF."""

from __future__ import annotations

import csv
import base64
import binascii
import errno
import io
import json
import re
import socket
import subprocess
import tempfile
import threading
import time
import webbrowser
import zipfile
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from PIL import Image, UnidentifiedImageError
from urllib.parse import parse_qs, urlsplit
from urllib.request import urlopen
from xml.etree import ElementTree as ET

from reportlab.lib.colors import HexColor
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfgen import canvas
from reportlab.lib.utils import ImageReader


ROOT = Path(__file__).resolve().parent
RUNTIME = Path.home() / ".cache/codex-runtimes/codex-primary-runtime/dependencies"
NODE = RUNTIME / "node/bin/node.exe"
ARTIFACT = RUNTIME / "node/node_modules/@oai/artifact-tool/dist/artifact_tool.mjs"
MAX_ITEMS = 500
MAX_BACKGROUND_BYTES = 1_500_000
PORT = 8766
COLOR = re.compile(r"^#[0-9A-Fa-f]{6}$")
UPLOADS = {}
UPLOAD_LOCK = threading.Lock()


def number(value, default, low, high):
    try:
        result = float(value)
    except (TypeError, ValueError):
        result = default
    if result != result:
        result = default
    return max(low, min(high, result))


def clean_color(value, default):
    return value if isinstance(value, str) and COLOR.fullmatch(value) else default


def parse_items(manual, csv_text, default_seconds):
    items = []
    for line in manual.splitlines():
        line = line.strip()
        if not line:
            continue
        # Quoted phrases may contain a comma; unquoted commas and semicolons
        # separate items just like line breaks.
        if re.fullmatch(r".+?\s*\|\s*\d+,\d+", line):
            pieces = [line]
        else:
            pieces = []
            for comma_part in next(csv.reader([line], delimiter=",")):
                pieces.extend(next(csv.reader([comma_part], delimiter=";")))
        for piece in pieces:
            for entry in re.split(r"\s+\|\s+(?!\d+(?:[.,]\d+)?\s*$)", piece):
                entry = entry.strip(" \t,;|")
                if not entry:
                    continue
                match = re.fullmatch(r"(.+?)\s*\|\s*(\d+(?:[.,]\d+)?)\s*", entry)
                if match:
                    label = match.group(1).strip(" \t,;|")
                    seconds = number(match.group(2).replace(",", "."), default_seconds, 1, 300)
                else:
                    label, seconds = entry, default_seconds
                if label:
                    items.append({"text": label[:300], "seconds": seconds})

    if csv_text.strip():
        sample = csv_text[:2048]
        try:
            dialect = csv.Sniffer().sniff(sample, delimiters=",;\t")
        except csv.Error:
            dialect = csv.excel
        rows = [[cell.strip() for cell in row] for row in csv.reader(io.StringIO(csv_text.lstrip("\ufeff")), dialect)]
        rows = [row for row in rows if any(row)]
        if rows:
            header = [cell.lower() for cell in rows[0]]
            word_names = {"palabra", "frase", "texto", "word", "phrase"}
            time_names = {"segundos", "segundo", "tiempo", "duracion", "duración", "seconds", "duration"}
            word_col = next((i for i, h in enumerate(header) if h in word_names), 0)
            time_col = next((i for i, h in enumerate(header) if h in time_names), None)
            has_header = any(h in word_names for h in header)
            data = rows[1:] if has_header else rows
            for row in data:
                if has_header:
                    labels = [row[word_col]] if word_col < len(row) else []
                    raw_seconds = row[time_col].replace(",", ".") if time_col is not None and time_col < len(row) else None
                elif len(row) == 2 and re.fullmatch(r"\d+(?:[.,]\d+)?", row[1]):
                    labels, raw_seconds = [row[0]], row[1].replace(",", ".")
                else:
                    labels, raw_seconds = row, None
                for label in labels:
                    label = label.strip(" \t,;|")
                    if label:
                        items.append({"text": label[:300], "seconds": number(raw_seconds, default_seconds, 1, 300)})
    if not items:
        raise ValueError("Ingresa al menos una palabra o frase.")
    if len(items) > MAX_ITEMS:
        raise ValueError(f"El máximo es {MAX_ITEMS} palabras o frases.")
    return items


def make_config(payload):
    options = payload.get("options") or {}
    config = {
        "background": clean_color(options.get("background"), "#12182B"),
        "background_mode": "image" if options.get("background_mode") == "image" else "color",
        "background_overlay": number(options.get("background_overlay"), 20, 0, 70),
        "foreground": clean_color(options.get("foreground"), "#FFFFFF"),
        "accent": clean_color(options.get("accent"), "#F6C85F"),
        "font_size": number(options.get("font_size"), 72, 28, 120),
        "seconds": number(options.get("seconds"), 8, 1, 300),
        "ratio": "4:3" if options.get("ratio") == "4:3" else "16:9",
        "uppercase": bool(options.get("uppercase", False)),
        "bold": bool(options.get("bold", True)),
        "show_number": bool(options.get("show_number", True)),
        "show_time": bool(options.get("show_time", False)),
        "auto_advance": bool(options.get("auto_advance", True)),
    }
    return config


def parse_background_image(payload, config):
    if config["background_mode"] != "image":
        return None
    data_url = payload.get("background_image")
    prefix = "data:image/jpeg;base64,"
    if not isinstance(data_url, str) or not data_url.startswith(prefix):
        raise ValueError("Selecciona una imagen de fondo JPG, PNG o WebP.")
    try:
        image_bytes = base64.b64decode(data_url[len(prefix):], validate=True)
        if len(image_bytes) > MAX_BACKGROUND_BYTES:
            raise ValueError("La imagen procesada es demasiado grande.")
        with Image.open(io.BytesIO(image_bytes)) as picture:
            if picture.format != "JPEG" or picture.width > 2000 or picture.height > 2000:
                raise ValueError("La imagen de fondo no tiene un formato válido.")
            picture.verify()
    except (ValueError, binascii.Error, UnidentifiedImageError, OSError) as exc:
        raise ValueError(f"No se pudo leer la imagen de fondo: {exc}") from exc
    return image_bytes


def wrap_text(text, font, size, available_width):
    words = text.split()
    lines = []
    current = ""
    for word in words:
        trial = f"{current} {word}".strip()
        if pdfmetrics.stringWidth(trial, font, size) <= available_width:
            current = trial
            continue
        if current:
            lines.append(current)
            current = ""
        while word and pdfmetrics.stringWidth(word, font, size) > available_width:
            part = ""
            for char in word:
                if pdfmetrics.stringWidth(part + char, font, size) > available_width:
                    break
                part += char
            if not part:
                part = word[0]
            lines.append(part)
            word = word[len(part):]
        current = word
    if current:
        lines.append(current)
    return lines or [""]


def ensure_fonts():
    regular = Path("C:/Windows/Fonts/arial.ttf")
    bold = Path("C:/Windows/Fonts/arialbd.ttf")
    if not regular.exists():
        raise RuntimeError("No se encontró Arial en Windows para crear el PDF.")
    if "JuegoArial" not in pdfmetrics.getRegisteredFontNames():
        pdfmetrics.registerFont(TTFont("JuegoArial", str(regular)))
    if bold.exists() and "JuegoArialBold" not in pdfmetrics.getRegisteredFontNames():
        pdfmetrics.registerFont(TTFont("JuegoArialBold", str(bold)))


def create_pdf(path, items, config, background_image=None):
    ensure_fonts()
    width, height = (960, 540) if config["ratio"] == "16:9" else (720, 540)
    font = "JuegoArialBold" if config["bold"] and "JuegoArialBold" in pdfmetrics.getRegisteredFontNames() else "JuegoArial"
    doc = canvas.Canvas(str(path), pagesize=(width, height), pageCompression=1)
    doc.setTitle("Juego de adivinar palabras")
    total = len(items)
    for index, item in enumerate(items, 1):
        if background_image is None:
            doc.setFillColor(HexColor(config["background"]))
            doc.rect(0, 0, width, height, fill=1, stroke=0)
        else:
            doc.drawImage(ImageReader(io.BytesIO(background_image)), 0, 0, width, height)
            if config["background_overlay"]:
                doc.saveState()
                doc.setFillAlpha(config["background_overlay"] / 100)
                doc.setFillColorRGB(0, 0, 0)
                doc.rect(0, 0, width, height, fill=1, stroke=0)
                doc.restoreState()
        label = item["text"].upper() if config["uppercase"] else item["text"]
        max_width = width * 0.83
        max_height = height * 0.65
        size = config["font_size"]
        while size > 12:
            lines = wrap_text(label, font, size, max_width)
            if len(lines) * size * 1.16 <= max_height:
                break
            size -= 2
        doc.setFillColor(HexColor(config["foreground"]))
        doc.setFont(font, size)
        line_height = size * 1.16
        first_baseline = height / 2 + (len(lines) - 1) * line_height / 2 - size * 0.35
        for line_no, line in enumerate(lines):
            doc.drawCentredString(width / 2, first_baseline - line_no * line_height, line)
        if config["show_number"]:
            doc.setFillColor(HexColor(config["accent"]))
            doc.setFont("JuegoArial", 14)
            doc.drawString(36, 27, f"{index:02d} / {total:02d}")
        if config["show_time"]:
            doc.setFillColor(HexColor(config["accent"]))
            doc.setFont("JuegoArial", 14)
            doc.drawRightString(width - 36, 27, f"{item['seconds']:g} s")
        doc.showPage()
    doc.save()


def set_transitions(source, dest, items, auto_advance):
    # OOXML requires transition after clrMapOvr (when present) and before timing.
    with zipfile.ZipFile(source) as zin, zipfile.ZipFile(dest, "w") as zout:
        for member in zin.infolist():
            data = zin.read(member.filename)
            match = re.fullmatch(r"ppt/slides/slide(\d+)\.xml", member.filename)
            if match:
                number = int(match.group(1))
                if number <= len(items):
                    xml = data.decode("utf-8")
                    xml = re.sub(r"<p:transition\b[^>]*(?:/>|>.*?</p:transition>)", "", xml, flags=re.S)
                    attrs = f'advClick="1" advTm="{round(items[number-1]["seconds"] * 1000)}"' if auto_advance else 'advClick="1"'
                    transition = f"<p:transition {attrs}/>"
                    if "</p:clrMapOvr>" in xml:
                        xml = xml.replace("</p:clrMapOvr>", "</p:clrMapOvr>" + transition, 1)
                    elif "<p:clrMapOvr " in xml and "/>" in xml.split("<p:clrMapOvr ", 1)[1]:
                        xml = re.sub(r"(<p:clrMapOvr\b[^>]*/>)", r"\1" + transition, xml, count=1)
                    else:
                        xml = xml.replace("</p:cSld>", "</p:cSld>" + transition, 1)
                    ET.fromstring(xml)
                    data = xml.encode("utf-8")
            zout.writestr(member, data)


def create_pptx(path, items, config, background_image=None):
    if not NODE.exists() or not ARTIFACT.exists():
        raise RuntimeError("No se encontró el entorno de presentaciones de Codex en este equipo.")
    with tempfile.TemporaryDirectory() as temp:
        temp = Path(temp)
        request = temp / "request.json"
        draft = temp / "draft.pptx"
        background_path = None
        if background_image is not None:
            background_path = temp / "background.jpg"
            background_path.write_bytes(background_image)
        request.write_text(json.dumps({"items": items, "config": config, "artifact": str(ARTIFACT), "background_image_path": str(background_path) if background_path else None}), encoding="utf-8")
        result = subprocess.run([str(NODE), str(ROOT / "crear_pptx.mjs"), str(request), str(draft)], capture_output=True, text=True, timeout=120)
        if result.returncode:
            raise RuntimeError("No se pudo crear el PowerPoint: " + (result.stderr.strip()[-600:] or result.stdout.strip()[-600:]))
        set_transitions(draft, path, items, config["auto_advance"])


class ExclusiveServer(ThreadingHTTPServer):
    allow_reuse_address = False

    def server_bind(self):
        if hasattr(socket, "SO_EXCLUSIVEADDRUSE"):
            self.socket.setsockopt(socket.SOL_SOCKET, socket.SO_EXCLUSIVEADDRUSE, 1)
        super().server_bind()


class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        route = urlsplit(self.path)
        if route.path == "/api/estado":
            self.send_json({"app": "juego_de_palabras", "root": str(ROOT.resolve())})
            return
        if route.path == "/api/cargar":
            try:
                query = parse_qs(route.query)
                session = query.get("session", [""])[0]
                part = int(query.get("part", ["-1"])[0])
                total = int(query.get("total", ["0"])[0])
                data = query.get("data", [""])[0]
                if not re.fullmatch(r"[a-zA-Z0-9-]{16,64}", session) or not 0 <= part < total <= 1000 or len(data) > 6000:
                    raise ValueError("Solicitud de carga inválida.")
                with UPLOAD_LOCK:
                    for key in list(UPLOADS):
                        if time.time() - UPLOADS[key]["created"] > 300:
                            del UPLOADS[key]
                    upload = UPLOADS.setdefault(session, {"created": time.time(), "total": total, "parts": {}})
                    if upload["total"] != total:
                        raise ValueError("La carga cambió durante el envío.")
                    upload["parts"][part] = data
                    if sum(map(len, upload["parts"].values())) > 4_000_000:
                        del UPLOADS[session]
                        raise ValueError("La lista es demasiado grande.")
                self.send_json({"ok": True})
            except (ValueError, KeyError) as exc:
                self.send_json_error(str(exc), 400)
            return
        if route.path == "/api/generar":
            try:
                session = parse_qs(route.query).get("session", [""])[0]
                with UPLOAD_LOCK:
                    upload = UPLOADS.pop(session, None)
                if not upload or len(upload["parts"]) != upload["total"]:
                    raise ValueError("La carga quedó incompleta. Intenta descargar otra vez.")
                encoded = "".join(upload["parts"][i] for i in range(upload["total"]))
                payload = json.loads(base64.urlsafe_b64decode(encoded).decode("utf-8"))
                self.respond_generate(payload)
            except (ValueError, KeyError, UnicodeError, json.JSONDecodeError) as exc:
                self.send_json_error(str(exc), 400)
            return
        if route.path not in ("/", "/index.html"):
            self.send_json_error("Ruta no encontrada.", 404)
            return
        body = (ROOT / "index.html").read_bytes()
        self.send_response(200)
        self.send_header("Content-Type", "text/html; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_POST(self):
        if self.path != "/api/generar":
            self.send_error(404)
            return
        try:
            length = int(self.headers.get("Content-Length", "0"))
            if not 0 < length < 4_000_000:
                raise ValueError("La lista es demasiado grande o está vacía.")
            payload = json.loads(self.rfile.read(length))
            self.respond_generate(payload)
        except (ValueError, RuntimeError, json.JSONDecodeError, subprocess.TimeoutExpired) as exc:
            self.send_json_error(str(exc), 400)
        except Exception as exc:
            self.send_json_error(f"Error al generar el archivo: {exc}", 500)

    def respond_generate(self, payload):
        try:
            config = make_config(payload)
            background_image = parse_background_image(payload, config)
            items = parse_items(str(payload.get("manual", "")), str(payload.get("csv_text", "")), config["seconds"])
            fmt = payload.get("format")
            if fmt not in ("pdf", "pptx"):
                raise ValueError("Selecciona PDF o PowerPoint.")
            with tempfile.TemporaryDirectory() as temp:
                path = Path(temp) / f"juego_de_palabras.{fmt}"
                if fmt == "pdf":
                    create_pdf(path, items, config, background_image)
                else:
                    create_pptx(path, items, config, background_image)
                body = path.read_bytes()
            self.send_response(200)
            self.send_header("Content-Type", "application/pdf" if fmt == "pdf" else "application/vnd.openxmlformats-officedocument.presentationml.presentation")
            self.send_header("Content-Disposition", f'attachment; filename="juego_de_palabras.{fmt}"')
            self.send_header("Content-Length", str(len(body)))
            self.end_headers()
            self.wfile.write(body)
        except (ValueError, RuntimeError, json.JSONDecodeError, subprocess.TimeoutExpired) as exc:
            self.send_json_error(str(exc), 400)
        except Exception as exc:
            self.send_json_error(f"Error al generar el archivo: {exc}", 500)

    def send_json(self, payload, code=200):
        body = json.dumps(payload, ensure_ascii=False).encode("utf-8")
        self.send_response(code)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def send_json_error(self, message, code):
        self.send_json({"error": message}, code)

    def log_message(self, format, *args):
        # Las listas viajan en fragmentos GET; no incluir esos datos en la consola.
        if self.path.startswith("/api/cargar"):
            return
        super().log_message(format, *args)


def main():
    for port in range(PORT, PORT + 20):
        url = f"http://127.0.0.1:{port}"
        try:
            server = ExclusiveServer(("127.0.0.1", port), Handler)
            break
        except OSError as exc:
            if exc.errno != errno.EADDRINUSE and getattr(exc, "winerror", None) != 10048:
                raise
            try:
                with urlopen(url + "/api/estado", timeout=1) as response:
                    status = json.load(response)
                if status.get("app") == "juego_de_palabras" and Path(status.get("root", "")).resolve() == ROOT.resolve():
                    print(f"El juego ya está abierto en {url}", flush=True)
                    webbrowser.open(url)
                    return
            except (OSError, ValueError, json.JSONDecodeError):
                pass
    else:
        raise RuntimeError(f"No hay puertos disponibles entre {PORT} y {PORT + 19}.")
    print(f"Juego de palabras: {url}", flush=True)
    threading.Timer(0.8, lambda: webbrowser.open(url)).start()
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        pass
    finally:
        server.server_close()


if __name__ == "__main__":
    main()
