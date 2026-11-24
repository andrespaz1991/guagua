@echo off
setlocal
set "PROJECT_DIR=%~dp0"
set "WHISPER_INPUT_DIR=%PROJECT_DIR%input"
set "WHISPER_OUTPUT_DIR=%PROJECT_DIR%output"
set "PYTHONUTF8=1"
set "PYTHONIOENCODING=utf-8"
if not exist "%PROJECT_DIR%venv\Scripts\python.exe" (
    echo No se encontro el Python de Whisper. Ejecute setup_environment.bat primero.
    exit /b 1
)
"%PROJECT_DIR%venv\Scripts\python.exe" -u "%PROJECT_DIR%..\guagua\apps\diario\whisper_workflow.py" transcribe --project "%PROJECT_DIR%." --input "%WHISPER_INPUT_DIR%" --output "%WHISPER_OUTPUT_DIR%"
set "WHISPER_RESULT=%ERRORLEVEL%"
if not "%~1"=="--non-interactive" pause
exit /b %WHISPER_RESULT%
