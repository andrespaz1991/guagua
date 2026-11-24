@echo off
cd /d "%~dp0"
python -c "import fitz" 2>nul
if errorlevel 1 (
  echo Instala la dependencia con: python -m pip install -r requirements.txt
  pause
  exit /b 1
)
python server.py --open-browser
pause
