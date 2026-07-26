import os
import shutil
import base64
import json
import re
import threading
from pathlib import Path
from flask import Flask, request, jsonify, send_from_directory, send_file
from flask_cors import CORS

app = Flask(__name__, static_folder='static', static_url_path='')
CORS(app)

# --- Global state for progress ---
progress_state = {
    "running": False,
    "total": 0,
    "processed": 0,
    "current_file": "",
    "results": [],
    "error": None
}
progress_lock = threading.Lock()

SUPPORTED_EXTENSIONS = {'.jpg', '.jpeg', '.png', '.webp', '.bmp', '.gif', '.tiff', '.tif'}

def get_image_files(folder_path):
    image_files = []
    try:
        for root, dirs, files in os.walk(folder_path):
            # Skip hidden directories
            dirs[:] = [d for d in dirs if not d.startswith('.')]
            for file in files:
                ext = Path(file).suffix.lower()
                if ext in SUPPORTED_EXTENSIONS:
                    image_files.append(os.path.join(root, file))
    except Exception as e:
        pass
    return image_files

def image_to_base64(image_path, max_size=800):
    """Convert image to base64 thumbnail."""
    try:
        from PIL import Image
        import io
        img = Image.open(image_path)
        img = img.convert('RGB')
        # Create thumbnail
        img.thumbnail((max_size, max_size), Image.LANCZOS)
        buffer = io.BytesIO()
        img.save(buffer, format='JPEG', quality=85)
        buffer.seek(0)
        return base64.b64encode(buffer.read()).decode('utf-8')
    except Exception as e:
        return None

def format_size(size_bytes):
    """Human-readable file size."""
    for unit in ['B', 'KB', 'MB', 'GB']:
        if size_bytes < 1024:
            return f"{size_bytes:.1f} {unit}"
        size_bytes /= 1024
    return f"{size_bytes:.1f} TB"

def run_clip_search(folder_path, query, threshold, max_results):
    """Run CLIP-based image search in background thread."""
    global progress_state

    try:
        import torch
        import clip
        from PIL import Image

        device = "cuda" if torch.cuda.is_available() else "cpu"
        
        with progress_lock:
            progress_state["current_file"] = "Cargando modelo CLIP..."
        
        model, preprocess = clip.load("ViT-B/32", device=device)
        
        # Encode text query
        text_tokens = clip.tokenize([query]).to(device)
        with torch.no_grad():
            text_features = model.encode_text(text_tokens)
            text_features /= text_features.norm(dim=-1, keepdim=True)
        
        image_files = get_image_files(folder_path)
        
        with progress_lock:
            progress_state["total"] = len(image_files)
            progress_state["processed"] = 0
            progress_state["results"] = []
            progress_state["current_file"] = f"Encontradas {len(image_files)} imágenes..."
        
        results = []
        
        for i, img_path in enumerate(image_files):
            try:
                with progress_lock:
                    progress_state["current_file"] = os.path.basename(img_path)
                    progress_state["processed"] = i + 1
                
                img = Image.open(img_path).convert('RGB')
                img_tensor = preprocess(img).unsqueeze(0).to(device)
                
                with torch.no_grad():
                    image_features = model.encode_image(img_tensor)
                    image_features /= image_features.norm(dim=-1, keepdim=True)
                
                similarity = (100.0 * image_features @ text_features.T).item()
                
                if similarity >= threshold:
                    thumbnail = image_to_base64(img_path)
                    results.append({
                        "path": img_path,
                        "filename": os.path.basename(img_path),
                        "score": round(similarity, 2),
                        "thumbnail": thumbnail,
                        "selected": True
                    })
            except Exception as e:
                pass
        
        # Sort by score descending
        results.sort(key=lambda x: x['score'], reverse=True)
        if max_results > 0:
            results = results[:max_results]
        
        with progress_lock:
            progress_state["results"] = results
            progress_state["running"] = False
            progress_state["current_file"] = "¡Búsqueda completada!"
    
    except ImportError as e:
        with progress_lock:
            progress_state["error"] = f"Dependencia faltante: {str(e)}. Ejecuta: pip install openai-clip torch torchvision pillow"
            progress_state["running"] = False
    except Exception as e:
        with progress_lock:
            progress_state["error"] = str(e)
            progress_state["running"] = False


# ---- Routes ----

@app.route('/')
def index():
    return send_from_directory('static', 'index.html')

@app.route('/api/browse-folder', methods=['POST'])
def browse_folder():
    """List images in a folder."""
    data = request.get_json()
    folder_path = data.get('path', '').strip()
    
    if not folder_path or not os.path.isdir(folder_path):
        return jsonify({"error": "Ruta de carpeta inválida o no encontrada."}), 400
    
    images = get_image_files(folder_path)
    # Quick thumbnails for preview (first 20)
    previews = []
    for img_path in images[:20]:
        thumb = image_to_base64(img_path, max_size=200)
        if thumb:
            previews.append({
                "path": img_path,
                "filename": os.path.basename(img_path),
                "thumbnail": thumb
            })
    
    return jsonify({
        "total": len(images),
        "previews": previews,
        "folder": folder_path
    })

@app.route('/api/search', methods=['POST'])
def search():
    """Start CLIP search."""
    global progress_state
    
    with progress_lock:
        if progress_state["running"]:
            return jsonify({"error": "Ya hay una búsqueda en progreso."}), 400
    
    data = request.get_json()
    folder_path = data.get('folder', '').strip()
    query = data.get('query', '').strip()
    threshold = float(data.get('threshold', 20.0))
    max_results = int(data.get('max_results', 0))
    
    if not folder_path or not os.path.isdir(folder_path):
        return jsonify({"error": "Ruta de carpeta inválida."}), 400
    if not query:
        return jsonify({"error": "La consulta no puede estar vacía."}), 400
    
    with progress_lock:
        progress_state = {
            "running": True,
            "total": 0,
            "processed": 0,
            "current_file": "Iniciando...",
            "results": [],
            "error": None
        }
    
    thread = threading.Thread(
        target=run_clip_search,
        args=(folder_path, query, threshold, max_results),
        daemon=True
    )
    thread.start()
    
    return jsonify({"status": "started"})

@app.route('/api/progress', methods=['GET'])
def get_progress():
    """Get current search progress."""
    with progress_lock:
        state = dict(progress_state)
        # Don't send all results in progress polling, only stats
        state_copy = {
            "running": state["running"],
            "total": state["total"],
            "processed": state["processed"],
            "current_file": state["current_file"],
            "result_count": len(state["results"]),
            "error": state["error"]
        }
    return jsonify(state_copy)

@app.route('/api/results', methods=['GET'])
def get_results():
    """Get final search results."""
    with progress_lock:
        results = list(progress_state["results"])
        error = progress_state["error"]
    return jsonify({"results": results, "error": error})

@app.route('/api/export', methods=['POST'])
def export_images():
    """Copy selected images to destination folder."""
    data = request.get_json()
    destination = data.get('destination', '').strip()
    image_paths = data.get('paths', [])
    create_subfolder = data.get('create_subfolder', True)
    subfolder_name = data.get('subfolder_name', 'visosearch_results').strip()
    
    if not destination:
        return jsonify({"error": "Carpeta destino requerida."}), 400
    if not image_paths:
        return jsonify({"error": "No hay imágenes seleccionadas."}), 400
    
    try:
        if create_subfolder and subfolder_name:
            # Sanitize folder name
            safe_name = re.sub(r'[^\w\s\-]', '', subfolder_name).strip()
            if not safe_name:
                safe_name = 'visosearch_results'
            dest_folder = os.path.join(destination, safe_name)
        else:
            dest_folder = destination
        
        os.makedirs(dest_folder, exist_ok=True)
        
        copied = 0
        skipped = 0
        errors = []
        
        for img_path in image_paths:
            try:
                if not os.path.isfile(img_path):
                    skipped += 1
                    continue
                
                filename = os.path.basename(img_path)
                dest_file = os.path.join(dest_folder, filename)
                
                # Handle duplicates
                if os.path.exists(dest_file):
                    base, ext = os.path.splitext(filename)
                    counter = 1
                    while os.path.exists(dest_file):
                        dest_file = os.path.join(dest_folder, f"{base}_{counter}{ext}")
                        counter += 1
                
                shutil.copy2(img_path, dest_file)
                copied += 1
            except Exception as e:
                errors.append(f"{os.path.basename(img_path)}: {str(e)}")
        
        return jsonify({
            "success": True,
            "copied": copied,
            "skipped": skipped,
            "errors": errors,
            "destination": dest_folder
        })
    
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/api/check-deps', methods=['GET'])
def check_deps():
    """Check if required dependencies are installed."""
    deps = {}
    
    try:
        import torch
        deps['torch'] = torch.__version__
    except ImportError:
        deps['torch'] = None
    
    try:
        import clip
        deps['clip'] = 'installed'
    except ImportError:
        deps['clip'] = None
    
    try:
        from PIL import Image
        import PIL
        deps['pillow'] = PIL.__version__
    except ImportError:
        deps['pillow'] = None
    
    all_ok = all(v is not None for v in deps.values())
    return jsonify({"deps": deps, "all_ok": all_ok})

@app.route('/api/image-details', methods=['POST'])
def image_details():
    """Get EXIF and file metadata for an image."""
    data = request.get_json()
    img_path = data.get('path', '').strip()

    if not img_path or not os.path.isfile(img_path):
        return jsonify({'error': 'Archivo no encontrado'}), 404

    try:
        from PIL import Image, ExifTags
        import datetime

        stat = os.stat(img_path)
        result = {
            'filename':        os.path.basename(img_path),
            'extension':       os.path.splitext(img_path)[1].upper().lstrip('.'),
            'file_size':       stat.st_size,
            'file_size_human': format_size(stat.st_size),
            'modified':        datetime.datetime.fromtimestamp(stat.st_mtime).strftime('%Y-%m-%d %H:%M:%S'),
            'created':         datetime.datetime.fromtimestamp(stat.st_ctime).strftime('%Y-%m-%d %H:%M:%S'),
            'path':            img_path,
            'exif':            {},
        }

        with Image.open(img_path) as img:
            result['width']  = img.width
            result['height'] = img.height
            result['mode']   = img.mode
            result['format'] = img.format or result['extension']

            try:
                exif_raw = img._getexif()
                if exif_raw:
                    tag_map = {v: k for k, v in ExifTags.TAGS.items()}
                    readable = {}
                    for tag_id, value in exif_raw.items():
                        tag_name = ExifTags.TAGS.get(tag_id, str(tag_id))
                        if isinstance(value, bytes):
                            try: value = value.decode('utf-8', errors='replace')
                            except: continue
                        if isinstance(value, (str, int, float)):
                            readable[tag_name] = str(value)

                    def pick(*keys):
                        for k in keys:
                            v = readable.get(k)
                            if v: return v
                        return ''

                    result['exif'] = {
                        'date_taken':    pick('DateTimeOriginal', 'DateTime', 'DateTimeDigitized'),
                        'camera_make':   pick('Make'),
                        'camera_model':  pick('Model'),
                        'iso':           pick('ISOSpeedRatings', 'ISO'),
                        'aperture':      pick('FNumber', 'ApertureValue'),
                        'exposure':      pick('ExposureTime'),
                        'focal_length':  pick('FocalLength'),
                        'flash':         pick('Flash'),
                        'gps':           '✓ Disponible' if readable.get('GPSInfo') else '',
                        'software':      pick('Software'),
                    }
            except Exception:
                pass

        return jsonify(result)
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/api/move', methods=['POST'])
def move_images():
    """Move selected images to a destination folder."""
    data = request.get_json()
    destination      = data.get('destination', '').strip()
    image_paths      = data.get('paths', [])
    create_subfolder = data.get('create_subfolder', True)
    subfolder_name   = data.get('subfolder_name', 'visosearch_movidas').strip()

    if not destination:
        return jsonify({'error': 'Carpeta destino requerida.'}), 400
    if not image_paths:
        return jsonify({'error': 'No hay imágenes seleccionadas.'}), 400

    try:
        if create_subfolder and subfolder_name:
            safe_name = re.sub(r'[^\w\s\-]', '', subfolder_name).strip() or 'visosearch_movidas'
            dest_folder = os.path.join(destination, safe_name)
        else:
            dest_folder = destination

        os.makedirs(dest_folder, exist_ok=True)
        moved = 0; skipped = 0; errors = []

        for img_path in image_paths:
            try:
                if not os.path.isfile(img_path):
                    skipped += 1; continue
                filename  = os.path.basename(img_path)
                dest_file = os.path.join(dest_folder, filename)
                if os.path.exists(dest_file):
                    base, ext = os.path.splitext(filename)
                    c = 1
                    while os.path.exists(dest_file):
                        dest_file = os.path.join(dest_folder, f'{base}_{c}{ext}'); c += 1
                shutil.move(img_path, dest_file)
                moved += 1
            except Exception as e:
                errors.append(f'{os.path.basename(img_path)}: {str(e)}')

        return jsonify({'success': True, 'moved': moved, 'skipped': skipped,
                        'errors': errors, 'destination': dest_folder})
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/api/rotate', methods=['POST'])
def rotate_image():
    """Rotate an image on disk and return updated thumbnail."""
    data     = request.get_json()
    img_path = data.get('path', '').strip()
    degrees  = int(data.get('degrees', 90))

    if not img_path or not os.path.isfile(img_path):
        return jsonify({'error': 'Archivo no encontrado'}), 404

    try:
        from PIL import Image
        import io

        with Image.open(img_path) as img:
            img = img.convert('RGB')
            rotated = img.rotate(-degrees, expand=True)  # negative = clockwise

        ext = os.path.splitext(img_path)[1].lower()
        fmt = 'PNG' if ext == '.png' else 'JPEG'
        rotated.save(img_path, format=fmt, quality=95)

        # Return new thumbnail
        thumb = rotated.copy()
        thumb.thumbnail((800, 800), Image.LANCZOS)
        buf = io.BytesIO()
        thumb.save(buf, format='JPEG', quality=85)
        thumbnail = base64.b64encode(buf.getvalue()).decode('utf-8')

        return jsonify({'success': True, 'thumbnail': thumbnail})
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/api/serve-image')
def serve_image():
    """Serve an image file for individual download."""
    img_path = request.args.get('path', '').strip()
    if not img_path or not os.path.isfile(img_path):
        return jsonify({'error': 'Archivo no encontrado'}), 404
    try:
        return send_file(img_path, as_attachment=True,
                         download_name=os.path.basename(img_path))
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/api/export-names', methods=['POST'])
def export_names():
    """Export selected image filenames/paths as txt, csv, or json."""
    data         = request.get_json()
    image_paths  = data.get('paths', [])
    fmt          = data.get('format', 'txt')   # 'txt' | 'csv' | 'json'
    include_path = data.get('include_paths', True)

    if not image_paths:
        return jsonify({'error': 'No hay imágenes seleccionadas.'}), 400

    try:
        import io as _io

        if fmt == 'csv':
            rows = ['filename,path,size_bytes,size']
            for p in image_paths:
                fn   = os.path.basename(p)
                sz   = os.path.getsize(p) if os.path.isfile(p) else 0
                rows.append(f'"{fn}","{p}",{sz},"{format_size(sz)}"')
            content  = '\n'.join(rows)
            mime     = 'text/csv'
            dl_name  = 'visosearch_resultados.csv'

        elif fmt == 'json':
            items = []
            for p in image_paths:
                fn = os.path.basename(p)
                sz = os.path.getsize(p) if os.path.isfile(p) else 0
                items.append({'filename': fn, 'path': p,
                              'size_bytes': sz, 'size': format_size(sz)})
            content = json.dumps(items, ensure_ascii=False, indent=2)
            mime    = 'application/json'
            dl_name = 'visosearch_resultados.json'

        else:  # txt
            lines   = [p if include_path else os.path.basename(p) for p in image_paths]
            content = '\n'.join(lines)
            mime    = 'text/plain'
            dl_name = 'visosearch_resultados.txt'

        buf = _io.BytesIO(content.encode('utf-8'))
        buf.seek(0)
        return send_file(buf, mimetype=mime, as_attachment=True, download_name=dl_name)
    except Exception as e:
        return jsonify({'error': str(e)}), 500


if __name__ == '__main__':
    import sys
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    print("\n" + "="*50)
    print("  [*] VisoSearch - AI Image Search")
    print("="*50)
    print("  Abre tu navegador en: http://localhost:5000")
    print("="*50 + "\n")
    app.run(debug=False, host='0.0.0.0', port=5000, threaded=True)
