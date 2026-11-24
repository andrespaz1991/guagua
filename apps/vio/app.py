import os
import shutil
import base64
import hashlib
import json
import re
import threading
import uuid
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
    "error": None,
    "cancel_requested": False,
    "cache_hits": 0,
    "new_embeddings": 0,
    "unreadable": 0,
    "job_id": None,
}
progress_lock = threading.Lock()

# iCloud Photos normally stores originals as HEIC.  Keeping this list here makes
# the scanner, duplicate finder and file actions agree on what is an image.
SUPPORTED_EXTENSIONS = {'.jpg', '.jpeg', '.png', '.webp', '.bmp', '.gif', '.tiff', '.tif', '.heic', '.heif'}
MODEL_NAME = 'ViT-B/32'
MODEL_LOCK = threading.Lock()
MODEL_CACHE = {"model": None, "preprocess": None, "device": None}
CACHE_ROOT = Path(os.environ.get('LOCALAPPDATA', Path.home())) / 'VisoSearch' / 'cache'


def configure_image_readers():
    """Enable HEIC/HEIF when pillow-heif is installed without making it mandatory."""
    try:
        from pillow_heif import register_heif_opener
        register_heif_opener()
    except ImportError:
        pass


configure_image_readers()


def get_clip_model():
    """Load CLIP only once. Loading it per query was the largest wait time."""
    import torch
    import clip

    with MODEL_LOCK:
        if MODEL_CACHE['model'] is None:
            device = 'cuda' if torch.cuda.is_available() else 'cpu'
            model, preprocess = clip.load(MODEL_NAME, device=device)
            model.eval()
            MODEL_CACHE.update(model=model, preprocess=preprocess, device=device)
    return MODEL_CACHE['model'], MODEL_CACHE['preprocess'], MODEL_CACHE['device']


def cache_file_for(folder_path):
    """Keep the index outside the photo library, so iCloud never syncs cache files."""
    key = hashlib.sha256(os.path.normcase(os.path.abspath(folder_path)).encode('utf-8')).hexdigest()
    return CACHE_ROOT / f'{key}.pt'


def load_embedding_cache(folder_path):
    try:
        import torch
        cache_path = cache_file_for(folder_path)
        if not cache_path.is_file():
            return {}
        payload = torch.load(cache_path, map_location='cpu', weights_only=False)
        if payload.get('model') != MODEL_NAME:
            return {}
        return payload.get('items', {})
    except Exception:
        # A corrupt or old cache should never prevent a search.
        return {}


def save_embedding_cache(folder_path, items):
    try:
        import torch
        CACHE_ROOT.mkdir(parents=True, exist_ok=True)
        target = cache_file_for(folder_path)
        temporary = target.with_suffix('.tmp')
        torch.save({'version': 1, 'model': MODEL_NAME, 'items': items}, temporary)
        os.replace(temporary, target)
    except Exception:
        pass


def image_signature(image_path):
    stat = os.stat(image_path)
    return stat.st_size, stat.st_mtime_ns


def search_was_cancelled(job_id):
    with progress_lock:
        return progress_state.get('job_id') != job_id or progress_state.get('cancel_requested', False)


def update_search_progress(job_id, **values):
    with progress_lock:
        if progress_state.get('job_id') == job_id:
            progress_state.update(values)


def finish_cancelled_search(job_id):
    """Always release the active job, including cancellation during model loading."""
    update_search_progress(job_id, running=False, current_file='Búsqueda detenida')

def get_image_files(folder_path, include_subfolders=True):
    image_files = []
    try:
        folder_path = os.path.abspath(os.path.expanduser(folder_path))
        if include_subfolders:
            for root, dirs, files in os.walk(folder_path):
                # Skip hidden directories
                dirs[:] = [d for d in dirs if not d.startswith('.')]
                for file in files:
                    ext = Path(file).suffix.lower()
                    if ext in SUPPORTED_EXTENSIONS:
                        image_files.append(os.path.abspath(os.path.join(root, file)))
        else:
            for file in os.listdir(folder_path):
                img_path = os.path.join(folder_path, file)
                if os.path.isfile(img_path) and Path(file).suffix.lower() in SUPPORTED_EXTENSIONS:
                    image_files.append(os.path.abspath(img_path))
    except Exception as e:
        pass
    return image_files

def image_to_base64(image_path, max_size=800):
    """Convert image to base64 thumbnail."""
    try:
        from PIL import Image, ImageOps
        import io
        with Image.open(image_path) as source:
            img = ImageOps.exif_transpose(source).convert('RGB')
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

def file_sha256(file_path):
    """Calculate SHA-256 for exact duplicate detection."""
    h = hashlib.sha256()
    with open(file_path, 'rb') as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b''):
            h.update(chunk)
    return h.hexdigest()

def run_clip_search(job_id, folder_path, query, threshold, max_results, include_subfolders=True):
    """Search cached CLIP embeddings, encoding only changed or new photographs."""
    try:
        import torch
        import clip
        from PIL import Image, ImageOps

        update_search_progress(job_id, current_file='Cargando modelo CLIP…')
        model, preprocess, device = get_clip_model()
        if search_was_cancelled(job_id):
            finish_cancelled_search(job_id)
            return

        # A semicolon separates alternate descriptions for one category. The
        # best score is used, which improves recall for labels such as uniforms.
        queries = [q.strip() for q in query.split(';') if q.strip()]
        text_tokens = clip.tokenize(queries).to(device)
        with torch.no_grad():
            text_features = model.encode_text(text_tokens).float()
            text_features /= text_features.norm(dim=-1, keepdim=True)

        image_files = get_image_files(folder_path, include_subfolders=include_subfolders)
        cache = load_embedding_cache(folder_path)
        embeddings, to_index, cache_hits = {}, [], 0
        for img_path in image_files:
            try:
                size, mtime_ns = image_signature(img_path)
                entry = cache.get(img_path)
                if entry and entry.get('size') == size and entry.get('mtime_ns') == mtime_ns:
                    embeddings[img_path] = entry['embedding']
                    cache_hits += 1
                else:
                    to_index.append((img_path, size, mtime_ns))
            except OSError:
                continue

        update_search_progress(
            job_id, total=len(image_files), processed=cache_hits, results=[],
            cache_hits=cache_hits, new_embeddings=0,
            current_file=(f'Índice listo: {cache_hits} reutilizadas · {len(to_index)} por analizar')
        )

        # CLIP is much faster in batches. A smaller CPU batch avoids memory spikes.
        batch_size = 48 if device == 'cuda' else 12
        valid_cache = {path: cache[path] for path in embeddings if path in cache}
        unreadable = 0
        for start in range(0, len(to_index), batch_size):
            if search_was_cancelled(job_id):
                finish_cancelled_search(job_id)
                return
            batch = to_index[start:start + batch_size]
            tensors, valid = [], []
            for img_path, size, mtime_ns in batch:
                try:
                    with Image.open(img_path) as source:
                        image = ImageOps.exif_transpose(source).convert('RGB')
                        tensors.append(preprocess(image))
                        valid.append((img_path, size, mtime_ns))
                except Exception:
                    unreadable += 1
                    continue
            if tensors:
                with torch.no_grad():
                    features = model.encode_image(torch.stack(tensors).to(device)).float()
                    features /= features.norm(dim=-1, keepdim=True)
                for (img_path, size, mtime_ns), feature in zip(valid, features.cpu()):
                    vector = feature.tolist()
                    embeddings[img_path] = vector
                    valid_cache[img_path] = {'size': size, 'mtime_ns': mtime_ns, 'embedding': vector}
            processed = min(start + len(batch), len(to_index)) + cache_hits
            update_search_progress(
                job_id, processed=processed, new_embeddings=processed - cache_hits,
                current_file=os.path.basename(batch[-1][0]) if batch else '',
                unreadable=unreadable,
            )

        if search_was_cancelled(job_id):
            finish_cancelled_search(job_id)
            return

        # Discard deleted files from the persistent index before scoring them.
        valid_cache = {path: valid_cache[path] for path in image_files if path in valid_cache}
        save_embedding_cache(folder_path, valid_cache)
        results = []
        for img_path in image_files:
            vector = embeddings.get(img_path)
            if vector is None:
                continue
            feature = torch.tensor(vector, dtype=torch.float32, device=device)
            score = (100.0 * (text_features @ feature).max()).item()
            if score >= threshold:
                results.append({
                    'path': img_path, 'filename': os.path.basename(img_path),
                    'score': round(score, 2), 'thumbnail': image_to_base64(img_path),
                    'selected': True
                })

        results.sort(key=lambda item: item['score'], reverse=True)
        if max_results > 0:
            results = results[:max_results]
        update_search_progress(
            job_id, results=results, running=False, processed=len(image_files),
            current_file='¡Búsqueda completada!'
        )
    except ImportError as error:
        update_search_progress(job_id, error=(f'Dependencia faltante: {error}. Ejecuta: '
                                                'pip install openai-clip torch torchvision pillow pillow-heif'), running=False)
    except Exception as error:
        update_search_progress(job_id, error=str(error), running=False)


# ---- Routes ----

@app.route('/')
def index():
    return send_from_directory('static', 'index.html')

@app.route('/api/browse-folder', methods=['POST'])
def browse_folder():
    """List images in a folder."""
    data = request.get_json()
    folder_path = data.get('path', '').strip()
    include_subfolders = bool(data.get('include_subfolders', True))
    
    if not folder_path or not os.path.isdir(folder_path):
        return jsonify({"error": "Ruta de carpeta inválida o no encontrada."}), 400
    
    images = get_image_files(folder_path, include_subfolders=include_subfolders)
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

@app.route('/api/select-folder', methods=['GET'])
def select_folder():
    """Open a native folder picker on the server machine."""
    try:
        import tkinter as tk
        from tkinter import filedialog

        root = tk.Tk()
        root.withdraw()
        root.attributes('-topmost', True)
        title = request.args.get('title', 'Selecciona carpeta de imágenes')
        folder_path = filedialog.askdirectory(title=title)
        root.destroy()

        if not folder_path:
            return jsonify({"cancelled": True, "path": ""})

        return jsonify({"cancelled": False, "path": folder_path})
    except Exception as e:
        return jsonify({"error": f"No se pudo abrir el selector de carpetas: {str(e)}"}), 500

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
    try:
        threshold = float(data.get('threshold', 20.0))
        max_results = int(data.get('max_results', 0))
    except (TypeError, ValueError):
        return jsonify({'error': 'El umbral y el máximo de resultados deben ser números válidos.'}), 400
    include_subfolders = bool(data.get('include_subfolders', True))
    
    if not folder_path or not os.path.isdir(folder_path):
        return jsonify({"error": "Ruta de carpeta inválida."}), 400
    if not query:
        return jsonify({"error": "La consulta no puede estar vacía."}), 400
    if not 0 <= threshold <= 100:
        return jsonify({'error': 'El umbral debe estar entre 0 y 100.'}), 400
    if max_results < 0:
        return jsonify({'error': 'El máximo de resultados no puede ser negativo.'}), 400

    job_id = uuid.uuid4().hex
    
    with progress_lock:
        progress_state = {
            "running": True,
            "total": 0,
            "processed": 0,
            "current_file": "Iniciando...",
            "results": [],
            "error": None,
            "cancel_requested": False,
            "cache_hits": 0,
            "new_embeddings": 0,
            "unreadable": 0,
            "job_id": job_id,
        }
    
    thread = threading.Thread(
        target=run_clip_search,
        args=(job_id, folder_path, query, threshold, max_results, include_subfolders),
        daemon=True
    )
    thread.start()
    
    return jsonify({"status": "started", "job_id": job_id})


@app.route('/api/cancel-search', methods=['POST'])
def cancel_search():
    """Stop the active job immediately; a loading worker discards its late result."""
    data = request.get_json(silent=True) or {}
    job_id = data.get('job_id')
    with progress_lock:
        if not progress_state['running']:
            return jsonify({'success': True, 'already_finished': True})
        if job_id and progress_state.get('job_id') != job_id:
            return jsonify({'error': 'La búsqueda solicitada ya no es la activa.'}), 409
        progress_state['cancel_requested'] = True
        # CLIP model loading cannot be interrupted safely. Marking the job as
        # stopped right away lets the user start another search; its job id
        # prevents the old worker from ever publishing results.
        progress_state['running'] = False
        progress_state['current_file'] = 'Búsqueda detenida'
    return jsonify({'success': True})

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
            "error": state["error"],
            "cancel_requested": state.get('cancel_requested', False),
            "cache_hits": state.get('cache_hits', 0),
            "new_embeddings": state.get('new_embeddings', 0),
            "unreadable": state.get('unreadable', 0),
            "job_id": state.get('job_id'),
        }
    return jsonify(state_copy)

@app.route('/api/results', methods=['GET'])
def get_results():
    """Get final search results."""
    with progress_lock:
        results = list(progress_state["results"])
        error = progress_state["error"]
        unreadable = progress_state.get('unreadable', 0)
    return jsonify({"results": results, "error": error, "unreadable": unreadable})

@app.route('/api/find-duplicates', methods=['POST'])
def find_duplicates():
    """Find exact duplicate images in a folder by file hash."""
    data = request.get_json()
    folder_path = data.get('folder', '').strip()
    include_subfolders = bool(data.get('include_subfolders', True))

    if not folder_path or not os.path.isdir(folder_path):
        return jsonify({"error": "Ruta de carpeta inválida."}), 400

    try:
        image_files = get_image_files(folder_path, include_subfolders=include_subfolders)
        by_size = {}
        for img_path in image_files:
            try:
                size = os.path.getsize(img_path)
                by_size.setdefault(size, []).append(img_path)
            except OSError:
                pass

        by_hash = {}
        for same_size_files in by_size.values():
            if len(same_size_files) < 2:
                continue
            for img_path in same_size_files:
                try:
                    digest = file_sha256(img_path)
                    by_hash.setdefault(digest, []).append(img_path)
                except OSError:
                    pass

        groups = []
        results = []
        group_number = 1

        for digest, paths in by_hash.items():
            if len(paths) < 2:
                continue

            paths = sorted(paths, key=lambda p: (os.path.basename(p).lower(), p.lower()))
            group_id = f"dup-{group_number}"
            group_items = []

            for idx, img_path in enumerate(paths):
                stat = os.stat(img_path)
                item = {
                    "path": img_path,
                    "filename": os.path.basename(img_path),
                    "score": 100,
                    "thumbnail": image_to_base64(img_path, max_size=400),
                    "selected": idx > 0,
                    "duplicate_group": group_id,
                    "duplicate_index": idx + 1,
                    "duplicate_count": len(paths),
                    "duplicate_role": "original" if idx == 0 else "copy",
                    "hash": digest,
                    "file_size": stat.st_size,
                    "file_size_human": format_size(stat.st_size),
                }
                results.append(item)
                group_items.append(item)

            groups.append({
                "id": group_id,
                "hash": digest,
                "count": len(group_items),
                "file_size": group_items[0]["file_size"],
                "file_size_human": group_items[0]["file_size_human"],
                "items": group_items,
            })
            group_number += 1

        return jsonify({
            "total_images": len(image_files),
            "duplicate_groups": len(groups),
            "duplicate_files": sum(max(0, g["count"] - 1) for g in groups),
            "groups": groups,
            "results": results,
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500

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

    try:
        import pillow_heif
        deps['pillow_heif'] = pillow_heif.__version__
    except ImportError:
        deps['pillow_heif'] = None
    
    # HEIC is optional so that an existing JPG/PNG installation remains usable.
    all_ok = all(deps[name] is not None for name in ('torch', 'clip', 'pillow'))
    return jsonify({"deps": deps, "all_ok": all_ok,
                    "heif_enabled": deps['pillow_heif'] is not None})

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
        from PIL import Image, ImageOps
        import io

        with Image.open(img_path) as img:
            img = ImageOps.exif_transpose(img)
            rotated = img.rotate(-degrees, expand=True)  # negative = clockwise

        ext = os.path.splitext(img_path)[1].lower()
        fmt_map = {
            '.jpg': 'JPEG',
            '.jpeg': 'JPEG',
            '.png': 'PNG',
            '.webp': 'WEBP',
            '.bmp': 'BMP',
            '.gif': 'GIF',
            '.tif': 'TIFF',
            '.tiff': 'TIFF',
        }
        fmt = fmt_map.get(ext, 'JPEG')
        if fmt == 'JPEG' and rotated.mode not in ('RGB', 'L'):
            rotated = rotated.convert('RGB')
        elif fmt in ('PNG', 'WEBP') and rotated.mode == 'P':
            rotated = rotated.convert('RGBA')

        save_kwargs = {'quality': 95} if fmt in ('JPEG', 'WEBP') else {}
        rotated.save(img_path, format=fmt, **save_kwargs)

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


@app.route('/api/delete', methods=['POST'])
def delete_images():
    """Delete selected images from disk."""
    data = request.get_json()
    image_paths = data.get('paths', [])

    if not image_paths:
        return jsonify({'error': 'No hay imágenes seleccionadas.'}), 400

    deleted = 0
    skipped = 0
    errors = []
    deleted_paths = []

    for img_path in image_paths:
        try:
            if not img_path or not os.path.isfile(img_path):
                skipped += 1
                continue

            ext = Path(img_path).suffix.lower()
            if ext not in SUPPORTED_EXTENSIONS:
                skipped += 1
                continue

            os.remove(img_path)
            deleted += 1
            deleted_paths.append(img_path)
        except Exception as e:
            errors.append(f'{os.path.basename(img_path)}: {str(e)}')

    return jsonify({
        'success': True,
        'deleted': deleted,
        'deleted_paths': deleted_paths,
        'skipped': skipped,
        'errors': errors,
    })


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
