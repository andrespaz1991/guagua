"""Python adapters consume the same .env/settings as PHP, without copies of credentials."""
from pathlib import Path
import json
import os
import shutil
import subprocess


def settings(profile="default"):
    root = Path(__file__).resolve().parent.parent
    php = shutil.which("php")
    if not php:
        bundled = root.parent.parent / "php" / "php.exe"
        if bundled.is_file():
            php = str(bundled)
    if not php:
        raise RuntimeError("PHP CLI no está disponible para leer la configuración central.")
    result = subprocess.run(
        [php, str(root / "comun" / "database.settings.cli.php"), profile],
        capture_output=True, text=True, encoding="utf-8", timeout=15,
    )
    if result.returncode != 0:
        raise RuntimeError("No se pudo leer el perfil de base de datos. Revise .env.")
    return json.loads(result.stdout)


def connect_mysql(profile="default"):
    import mysql.connector
    config = settings(profile)
    connection = mysql.connector.connect(
        host=config["host"], port=config["port"], user=config["user"],
        password=config["password"], database=config["database"],
        charset=config["charset"], connection_timeout=config["connect_timeout"],
    )
    try:
        cursor = connection.cursor()
        try:
            cursor.execute("SET time_zone = %s", (config["time_zone"],))
        finally:
            cursor.close()
    except Exception:
        connection.close()
        raise
    return connection


def mysql_command(executable, profile="default", arguments=None, include_database=True):
    config = settings(profile)
    command = [str(executable), "--host=" + config["host"], "--port=" + str(config["port"]),
               "--user=" + config["user"], "--default-character-set=" + config["charset"]]
    if include_database and config["database"]:
        command.append(config["database"])
    command.extend(arguments or [])
    environment = os.environ.copy()
    environment["MYSQL_PWD"] = config["password"]
    return command, environment
