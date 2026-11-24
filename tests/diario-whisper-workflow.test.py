import importlib.util
import os
from pathlib import Path
import sys
import subprocess
import tempfile
import types
import unittest
from unittest.mock import patch

spec = importlib.util.spec_from_file_location('workflow', Path(__file__).parents[1] / 'apps/diario/whisper_workflow.py')
workflow = importlib.util.module_from_spec(spec)
spec.loader.exec_module(workflow)


class WorkflowTest(unittest.TestCase):
    def test_archive_preserves_subfolders_and_file_bytes(self):
        with tempfile.TemporaryDirectory() as root:
            source = Path(root) / 'input'
            (source / 'octubre').mkdir(parents=True)
            (source / 'octubre' / 'previo.ogg').write_bytes(b'old audio')
            (source / 'audio.ogg').write_bytes(b'OggS-current')
            (source / 'manifiesto.json').write_bytes(b'{}')
            result = workflow.archive_folders([source])
            self.assertEqual(result[0]['count'], 2)
            self.assertEqual((Path(result[0]['archive']) / 'audio.ogg').read_bytes(), b'OggS-current')
            self.assertEqual((source / 'octubre' / 'previo.ogg').read_bytes(), b'old audio')
            self.assertEqual(workflow.archive_folders([source]), [])

    def test_failed_second_folder_restores_all_previous_files(self):
        with tempfile.TemporaryDirectory() as root:
            source, output = Path(root) / 'input', Path(root) / 'output'
            source.mkdir(); output.mkdir()
            (source / 'audio.ogg').write_bytes(b'audio')
            (output / 'anterior.tsv').write_bytes(b'text')
            rename = Path.rename

            def fail_second(path, target):
                if path == output / 'anterior.tsv':
                    raise PermissionError('archivo ocupado')
                return rename(path, target)

            with patch.object(Path, 'rename', fail_second):
                with self.assertRaises(PermissionError):
                    workflow.archive_folders([source, output])
            self.assertEqual(list(source.iterdir()), [source / 'audio.ogg'])
            self.assertEqual(list(output.iterdir()), [output / 'anterior.tsv'])

    def test_conversion_archives_output_and_preserves_input(self):
        with tempfile.TemporaryDirectory() as root:
            project = Path(root)
            source, output = project / 'input', project / 'output'
            source.mkdir(); output.mkdir()
            (source / 'audio.ogg').write_bytes(b'OggS-source')
            (output / 'anterior.tsv').write_text('previous batch')
            (project / 'transcribe.py').write_text(
                "import os\nfrom pathlib import Path\n"
                "out = Path(os.environ['WHISPER_OUTPUT_DIR'])\n"
                "assert not (out / 'anterior.tsv').exists()\n"
                "for ext in ('tsv','txt','srt','vtt'):\n"
                "    (out / ('audio.' + ext)).write_text('transcript')\n"
            )
            with patch.dict(sys.modules, {'whisper': types.ModuleType('whisper'), 'torch': types.ModuleType('torch')}), patch.object(workflow.shutil, 'which', return_value='ffmpeg'), patch.dict(workflow.os.environ):
                workflow.transcribe(project, source, output)
            self.assertEqual((source / 'audio.ogg').read_bytes(), b'OggS-source')
            self.assertEqual(len(list(output.glob('archivo_*/anterior.tsv'))), 1)
            self.assertEqual(len(list(output.glob('audio.*'))), 4)

    def test_empty_input_keeps_previous_output(self):
        with tempfile.TemporaryDirectory() as root:
            project = Path(root)
            source, output = project / 'input', project / 'output'
            source.mkdir(); output.mkdir()
            (project / 'transcribe.py').touch()
            (output / 'previo.txt').write_text('previous')
            with self.assertRaisesRegex(ValueError, 'No hay audios'):
                workflow.transcribe(project, source, output)
            self.assertTrue((output / 'previo.txt').is_file())

    @unittest.skipUnless(os.name == 'nt', 'Windows CMD integration')
    def test_faster_cmd_paths_with_spaces_pause_and_output_archiving(self):
        with tempfile.TemporaryDirectory(prefix='diario faster ') as root:
            project = Path(root)
            source, output = project / 'input con espacios', project / 'output con espacios'
            source.mkdir(); output.mkdir()
            (source / 'audio de prueba.ogg').write_bytes(b'OggS-source')
            (output / 'previo.tsv').write_text('previous batch')
            launcher = project / 'Probar faster whisper.cmd'
            writer = project / 'generar formatos.py'
            writer.write_text(
                "import argparse\nfrom pathlib import Path\n"
                "p=argparse.ArgumentParser(); p.add_argument('--input'); p.add_argument('--output'); a=p.parse_args()\n"
                "assert not (Path(a.output)/'previo.tsv').exists()\n"
                "for audio in Path(a.input).glob('*.ogg'):\n"
                "    for ext in ('tsv','txt','srt','vtt'):\n"
                "        (Path(a.output)/(audio.stem+'.'+ext)).write_text('transcript')\n"
            )
            launcher.write_text(f'@echo off\n"{sys.executable}" "{writer}" %*\nset "RESULT=%ERRORLEVEL%"\npause\nexit /b %RESULT%\n')
            workflow.transcribe_faster(launcher, source, output)
            self.assertEqual((source / 'audio de prueba.ogg').read_bytes(), b'OggS-source')
            self.assertEqual(len(list(output.glob('archivo_*/previo.tsv'))), 1)
            self.assertEqual(len(list(output.glob('audio de prueba.*'))), 4)

    def test_missing_faster_launcher_preserves_output(self):
        with tempfile.TemporaryDirectory() as root:
            project = Path(root)
            source, output = project / 'input', project / 'output'
            source.mkdir(); output.mkdir()
            (source / 'audio.ogg').write_bytes(b'OggS-source')
            (output / 'previo.tsv').write_text('previous batch')
            with self.assertRaises(FileNotFoundError):
                workflow.transcribe_faster(project / 'missing.cmd', source, output)
            self.assertTrue((output / 'previo.tsv').is_file())

    @unittest.skipUnless(os.name == 'nt', 'Windows CMD integration')
    def test_faster_failure_keeps_original_exit_code_and_archived_output(self):
        with tempfile.TemporaryDirectory() as root:
            project = Path(root)
            source, output = project / 'input', project / 'output'
            source.mkdir(); output.mkdir()
            (source / 'audio.ogg').write_bytes(b'OggS-source')
            (output / 'previo.tsv').write_text('previous batch')
            launcher = project / 'failure.cmd'
            launcher.write_text('@echo off\nexit /b 7\n')
            with self.assertRaises(subprocess.CalledProcessError) as error:
                workflow.transcribe_faster(launcher, source, output)
            self.assertEqual(error.exception.returncode, 7)
            self.assertEqual(len(list(output.glob('archivo_*/previo.tsv'))), 1)


if __name__ == '__main__':
    unittest.main()
