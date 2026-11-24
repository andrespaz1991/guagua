@echo off
REM Importador manual: todas las conexiones se leen de /.env.
python "%~dp0importar_ultimo_sql.py"
set GUAGUA_IMPORT_RESULT=%ERRORLEVEL%
pause
exit /b %GUAGUA_IMPORT_RESULT%
