@echo off
echo ==============================================
echo   VisoSearch - Instalador de dependencias
echo ==============================================
echo.

REM Check Python
python --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Python no esta instalado o no esta en el PATH.
    echo Descargalo en: https://www.python.org/downloads/
    pause
    exit /b 1
)

echo [1/4] Actualizando pip...
python -m pip install --upgrade pip

echo.
echo [2/4] Instalando Flask y dependencias web...
pip install flask flask-cors Pillow

echo.
echo [3/4] Instalando PyTorch...
echo (Esto puede tardar varios minutos, ~500MB-2GB dependiendo de si tienes GPU)
pip install torch torchvision --index-url https://download.pytorch.org/whl/cpu

echo.
echo [4/4] Instalando CLIP...
pip install openai-clip

echo.
echo ==============================================
echo   Instalacion completada!
echo   Ejecuta: python app.py
echo   Luego abre: http://localhost:5000
echo ==============================================
pause
