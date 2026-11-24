"""Servicio privado para Horizonte. Solo escucha en la interfaz local."""
import base64
import hashlib
import json
import re
import threading
from datetime import date
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import urlparse, parse_qs

ROOT = Path(__file__).resolve().parent
DATA = ROOT / 'documentos'
LOCK = threading.Lock()
MAX = 16 * 1024 * 1024


def money(raw):
    s = re.sub(r'[^\d,.\-]', '', raw)
    if ',' in s and '.' in s:
        s = s.replace('.', '').replace(',', '.') if s.rfind(',') > s.rfind('.') else s.replace(',', '')
    elif ',' in s or '.' in s:
        sep = ',' if ',' in s else '.'
        parts = s.split(sep)
        s = ''.join(parts) if len(parts[-1]) == 3 else ''.join(parts[:-1]) + '.' + parts[-1]
    return round(float(s), 2)


def analyze(path, month):
    import fitz
    with fitz.open(path) as pdf:
        if pdf.needs_pass:
            raise ValueError('PDF protegido. Guarda una copia sin contraseña y vuelve a subirla.')
        if len(pdf) > 100:
            raise ValueError('El límite es de 100 páginas por PDF.')
        text = '\n'.join(p.get_text(sort=True) for p in pdf)
    lines = [re.sub(r'\s+', ' ', s).strip() for s in text.splitlines() if s.strip()]
    rows, warnings = [], []
    opening = closing = None
    amount_re = r'[-+]?\$?\s*\d[\d.,]*(?:\s*(?:COP|CR|DB))?'
    for line in lines:
        low = line.lower()
        if re.search(r'saldo\s+(?:inicial|anterior|final|actual)', low):
            nums = re.findall(amount_re, line)
            if nums:
                value = money(nums[-1])
                if re.search(r'saldo\s+(?:inicial|anterior)', low): opening = value
                else: closing = value
            continue
        match = re.match(r'^(\d{4}[-/]\d{2}[-/]\d{2}|\d{2}[-/]\d{2}(?:[-/]\d{2,4})?)\s+(.+)', line)
        if not match: continue
        raw_date, rest = match.groups()
        parts = re.split('[-/]', raw_date)
        try:
            if len(parts[0]) == 4: day = date(*map(int, parts))
            else:
                year = int(parts[2]) if len(parts) == 3 else int(month[:4])
                day = date(year + 2000 if year < 100 else year, int(parts[1]), int(parts[0]))
        except ValueError:
            warnings.append('Fecha no reconocida: ' + line)
            continue
        tail = re.search(r'(' + amount_re + r')\s*$', rest)
        if not tail: continue
        value = money(tail.group(1))
        desc = rest[:tail.start()].strip(' |;')
        # Nunca interpretar silenciosamente la última columna (saldo) como importe.
        ambiguous = bool(re.search(r'\d[\d.,]*[,.]\d{2}\s*$', desc))
        norm = desc.lower()
        kind, category = 'pendiente', 'otros'
        if value < 0 or re.search(r'\b(debito|débito|compra|pago|retiro|comisi[oó]n)\b', norm): kind = 'gasto'
        if re.search(r'\b(n[oó]mina|salario|abono|ingreso|freelance|arriendo recibido)\b', norm): kind = 'ingreso'
        if 'transfer' in norm: kind = 'pendiente'
        if kind == 'ingreso':
            category = 'salario' if re.search('n[oó]mina|salario', norm) else 'freelance' if 'freelance' in norm else 'arriendos' if 'arriendo' in norm else 'otros'
        elif kind == 'gasto':
            for key, pattern in [('vivienda','arriendo|hipoteca|servicio|energía|agua'),('alimentacion','mercado|supermercado|restaurante'),('transporte','gasolina|uber|transporte|taxi'),('deuda','cuota|cr[eé]dito|tarjeta'),('ocio','cine|netflix|ocio')]:
                if re.search(pattern, norm): category = key; break
        if ambiguous:
            kind = 'pendiente'
            warnings.append('Varias columnas numéricas: verifica el importe contra el PDF: ' + line)
        rows.append({'id': hashlib.sha256((str(path)+str(len(rows))+line).encode()).hexdigest()[:20], 'date': day.isoformat(), 'description': desc or 'Movimiento por revisar', 'amount': abs(value), 'kind': kind, 'category': category, 'evidence': line, 'include': day.isoformat().startswith(month)})
    if len(text.strip()) < 30:
        warnings.append('PDF escaneado o sin texto extraíble. No se ejecutó OCR. Transcribe los movimientos en la revisión o sube una copia con texto seleccionable.')
    if not rows:
        warnings.append('No se reconocieron movimientos fechados. Añádelos manualmente usando el PDF como referencia. Formato admitido: fecha, descripción e importe en la misma línea.')
    if any(not r['include'] for r in rows): warnings.append('Hay movimientos de otro mes; se excluyeron de la selección.')
    return {'rows': rows, 'opening': opening, 'closing': closing, 'warnings': warnings, 'text': text[:80000]}


class Handler(BaseHTTPRequestHandler):
    def reply(self, value, status=200):
        body = json.dumps(value, ensure_ascii=False).encode()
        self.send_response(status); self.send_header('Content-Type', 'application/json; charset=utf-8'); self.send_header('Cache-Control', 'no-store'); self.end_headers(); self.wfile.write(body)

    def do_GET(self):
        route = urlparse(self.path)
        if route.path == '/api/documents':
            files = []
            for p in DATA.glob('*/*.json'):
                try: files.append(json.loads(p.read_text('utf-8')))
                except (ValueError, OSError): pass
            return self.reply({'documents': files})
        if route.path == '/api/pdf':
            ident = parse_qs(route.query).get('id', [''])[0]
            if not re.fullmatch(r'\d{4}-\d{2}/[a-f0-9]{64}', ident): return self.reply({'error':'Documento inválido'}, 400)
            path = DATA / (ident + '.pdf')
            if not path.is_file(): return self.reply({'error':'No encontrado'},404)
            body=path.read_bytes(); self.send_response(200); self.send_header('Content-Type','application/pdf'); self.send_header('Content-Disposition','inline; filename="documento.pdf"'); self.end_headers(); self.wfile.write(body); return
        allowed={'/':'index.html','/index.html':'index.html','/favicon.ico':None}
        if route.path not in allowed: return self.reply({'error':'No encontrado'},404)
        if allowed[route.path] is None: self.send_response(204); self.end_headers(); return
        p=ROOT/allowed[route.path]
        self.send_response(200); self.send_header('Content-Type','text/html; charset=utf-8' if p.suffix=='.html' else 'text/javascript'); self.send_header('Cache-Control','no-store'); self.end_headers(); self.wfile.write(p.read_bytes())

    def do_POST(self):
        # JSON + origen local: impide subidas iniciadas desde otras webs.
        if self.headers.get('Origin') and self.headers['Origin'] != 'http://' + self.headers.get('Host',''):
            return self.reply({'error':'Origen no permitido'},403)
        if self.headers.get('Content-Type','').split(';')[0] != 'application/json': return self.reply({'error':'Se requiere JSON'},415)
        try:
            length=int(self.headers.get('Content-Length',0))
            if length<=0 or length>MAX: return self.reply({'error':'Petición demasiado grande; máximo 10 MB por PDF'},413)
            payload=json.loads(self.rfile.read(length))
            if self.path == '/api/upload':
                month=payload['month']
                if not re.fullmatch(r'20\d{2}-(0[1-9]|1[0-2])',month): raise ValueError('Mes inválido')
                raw=base64.b64decode(payload['data'], validate=True)
                if len(raw)>10*1024*1024 or not raw.startswith(b'%PDF-'): raise ValueError('Solo se admiten PDFs válidos de hasta 10 MB')
                import fitz
                with fitz.open(stream=raw,filetype='pdf') as check:
                    if check.needs_pass: raise ValueError('PDF protegido con contraseña')
                digest=hashlib.sha256(raw).hexdigest(); folder=DATA/month
                meta={'id':month+'/'+digest,'name':Path(payload['name'].replace('\\','/')).name[:160], 'month':month,'size':len(raw)}
                with LOCK:
                    folder.mkdir(parents=True,exist_ok=True)
                    (folder/(digest+'.pdf')).write_bytes(raw)
                    (folder/(digest+'.json')).write_text(json.dumps(meta,ensure_ascii=False),encoding='utf-8')
                return self.reply(meta)
            if self.path == '/api/analyze':
                ident=payload['id']
                if not re.fullmatch(r'20\d{2}-(0[1-9]|1[0-2])/[a-f0-9]{64}',ident): raise ValueError('Documento inválido')
                return self.reply(analyze(DATA/(ident+'.pdf'),ident[:7]))
            return self.reply({'error':'No encontrado'},404)
        except (ValueError, KeyError, OSError, RuntimeError) as exc:
            return self.reply({'error':str(exc)},400)


if __name__=='__main__':
    import argparse
    parser=argparse.ArgumentParser(); parser.add_argument('--port',type=int,default=8765); parser.add_argument('--open-browser',action='store_true'); args=parser.parse_args()
    httpd = ThreadingHTTPServer(('127.0.0.1',args.port),Handler)
    if args.open_browser:
        import webbrowser
        threading.Timer(0.8, lambda: webbrowser.open(f'http://localhost:{args.port}')).start()
    print(f'Horizonte disponible en http://localhost:{args.port}',flush=True)
    httpd.serve_forever()
