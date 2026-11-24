"""Archive previous batches and run the installed Whisper without interactive shells."""
import argparse
import datetime
import json
import os
from pathlib import Path
import runpy
import shutil
import subprocess
import sys
import uuid

SUPPORTED = {'.mp3', '.wav', '.m4a', '.aac', '.ogg', '.mp4', '.mov', '.avi', '.mkv', '.flac'}


def archive_folders(folders):
    """Move only loose regular files; restore earlier moves if any move fails."""
    stamp = datetime.datetime.now(datetime.timezone(datetime.timedelta(hours=-5))).strftime('%Y-%m-%d_%H-%M-%S')
    moves, created, result = [], [], []
    try:
        for folder in folders:
            folder = Path(folder).absolute()
            if folder == Path(folder.anchor) or folder.is_symlink():
                raise ValueError(f'Carpeta de archivado no válida: {folder}')
            folder.mkdir(parents=True, exist_ok=True)
            files = sorted(p for p in folder.iterdir() if p.is_file() and not p.is_symlink())
            if not files:
                continue
            archive = folder / f'archivo_{stamp}_{uuid.uuid4().hex[:8]}'
            archive.mkdir()
            created.append(archive)
            for source in files:
                target = archive / source.name
                source.rename(target)
                moves.append((source, target))
            result.append({'folder': str(folder), 'archive': str(archive), 'count': len(files)})
    except Exception:
        for source, target in reversed(moves):
            target.rename(source)
        for archive in reversed(created):
            archive.rmdir()
        raise
    return result


def transcribe(project, input_dir, output_dir):
    project, input_dir, output_dir = map(lambda p: Path(p).resolve(), (project, input_dir, output_dir))
    if input_dir == output_dir or input_dir in output_dir.parents or output_dir in input_dir.parents:
        raise ValueError('Las carpetas input y output deben ser independientes.')
    script = project / 'transcribe.py'
    if not script.is_file():
        raise FileNotFoundError(f'No se encontró {script}')
    audios = [p for p in input_dir.iterdir() if p.is_file() and not p.is_symlink() and p.suffix.lower() in SUPPORTED]
    if not audios:
        raise ValueError(f'No hay audios o videos sueltos para transcribir en {input_dir}. Extraiga los audios primero.')
    if not shutil.which('ffmpeg'):
        raise RuntimeError('No se encontró FFmpeg en PATH. Instale FFmpeg antes de transcribir.')
    print(f'Preparando Whisper para {len(audios)} archivo(s).', flush=True)
    # Verify dependencies before moving previous output files.
    import whisper  # noqa: F401
    import torch  # noqa: F401
    archived = archive_folders([output_dir])
    for entry in archived:
        print(f'Archivados {entry["count"]} archivos anteriores en {entry["archive"]}', flush=True)
    os.environ['WHISPER_INPUT_DIR'] = str(input_dir)
    os.environ['WHISPER_OUTPUT_DIR'] = str(output_dir)
    runpy.run_path(str(script), run_name='__main__')
    expected = [output_dir / f'{audio.stem}.{ext}' for audio in audios for ext in ('tsv', 'txt', 'srt', 'vtt')]
    if not all(p.is_file() and p.stat().st_size > 0 for p in expected):
        raise RuntimeError('Whisper terminó sin generar todas las transcripciones esperadas.')
    print(f'Transcripción completada: {len(audios)} audios, {len(expected)} archivos generados.', flush=True)


def transcribe_faster(launcher, input_dir, output_dir):
    launcher, input_dir, output_dir = map(lambda p: Path(p).resolve(), (launcher, input_dir, output_dir))
    if not launcher.is_file():
        raise FileNotFoundError(f'No se encontró el lanzador de faster-whisper: {launcher}')
    if input_dir == output_dir or input_dir in output_dir.parents or output_dir in input_dir.parents:
        raise ValueError('Las carpetas input y output deben ser independientes.')
    audios = [p for p in input_dir.iterdir() if p.is_file() and not p.is_symlink() and p.suffix.lower() in SUPPORTED]
    if not audios:
        raise ValueError(f'No hay audios o videos sueltos para transcribir en {input_dir}. Extraiga los audios primero.')
    if os.name != 'nt':
        raise RuntimeError('El lanzador .cmd de faster-whisper requiere Windows.')
    # Quote paths for cmd.exe; reject expansion characters instead of changing paths.
    if any(char in str(p) for p in (launcher, input_dir, output_dir) for char in ('"', '%', '!', '\r', '\n')):
        raise ValueError('Las rutas del lanzador, input y output no pueden contener comillas, %, ! ni saltos de línea.')
    archived = archive_folders([output_dir])
    for entry in archived:
        print(f'Archivados {entry["count"]} archivos anteriores en {entry["archive"]}', flush=True)
    print(f'Ejecutando faster-whisper: {launcher}', flush=True)
    comspec = subprocess.list2cmdline([os.environ.get('COMSPEC', 'cmd.exe')])
    command = f'{comspec} /d /s /c ""{launcher}" --input "{input_dir}" --output "{output_dir}""'
    # The launcher's final PAUSE sees EOF, while its saved RESULT preserves the
    # transcription exit code. The user can still double-click the original CMD.
    subprocess.run(command, cwd=str(launcher.parent), stdin=subprocess.DEVNULL, check=True)
    expected = [output_dir / f'{audio.stem}.{ext}' for audio in audios for ext in ('tsv', 'txt', 'srt', 'vtt')]
    if not all(p.is_file() and p.stat().st_size > 0 for p in expected):
        raise RuntimeError('Faster-whisper terminó sin generar todas las transcripciones esperadas.')
    print(f'Transcripción completada con faster-whisper: {len(audios)} audios, {len(expected)} archivos generados.', flush=True)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('action', choices=['prepare-extraction', 'transcribe', 'transcribe-faster'])
    parser.add_argument('--project', required=True)
    parser.add_argument('--input', required=True)
    parser.add_argument('--output', required=True)
    parser.add_argument('--launcher')
    args = parser.parse_args()
    if args.action == 'prepare-extraction':
        print(json.dumps(archive_folders([args.input, args.output]), ensure_ascii=False))
    elif args.action == 'transcribe-faster':
        if not args.launcher:
            parser.error('Indique --launcher con la ruta de Probar-faster-whisper.cmd.')
        transcribe_faster(args.launcher, args.input, args.output)
    else:
        transcribe(args.project, args.input, args.output)


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(f'ERROR: {error}', file=sys.stderr, flush=True)
        sys.exit(1)
