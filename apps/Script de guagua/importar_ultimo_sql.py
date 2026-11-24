"""Importación manual del SQL más reciente, usando la configuración privada central."""
from pathlib import Path
import sys
import subprocess

ROOT = next(p for p in Path(__file__).resolve().parents if (p / "comun" / "database_client.py").is_file())
sys.path.insert(0, str(ROOT / "comun"))
from database_client import settings, mysql_command


def main():
    directory = ROOT / "reportes" / "bd"
    files = list(directory.glob("*.sql"))
    if not files:
        print("No se encontró un archivo SQL para importar.")
        return 1
    source = max(files, key=lambda p: p.stat().st_mtime)
    executable = ROOT.parent.parent / "mysql" / "bin" / "mysql.exe"
    if not executable.is_file():
        print("No se encontró el cliente MySQL de XAMPP.")
        return 1
    config = settings()
    command, environment = mysql_command(executable, include_database=False)
    name = config["database"].replace("`", "``")
    subprocess.run(command + ["--execute=CREATE DATABASE IF NOT EXISTS `" + name + "`"], env=environment, check=True)
    command, environment = mysql_command(executable)
    print("Importando:", source.name)
    with source.open("rb") as sql_file:
        subprocess.run(command, env=environment, stdin=sql_file, check=True)
    print("Importación completada.")
    return 0


if __name__ == "__main__":
    try:
        sys.exit(main())
    except (RuntimeError, subprocess.CalledProcessError):
        print("No se pudo importar. Revise el servicio MySQL y la configuración en .env.")
        sys.exit(1)
