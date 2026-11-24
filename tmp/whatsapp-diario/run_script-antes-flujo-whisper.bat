@echo off
:: Verificar si el script ya tiene permisos de administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Solicitando permisos de administrador...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

:: --- A PARTIR DE AQUÍ VA TU CÓDIGO ORIGINAL ---
cd /d "%~dp0"

echo Ejecutando script con permisos de administrador...
pause


@echo off
setlocal
rem RUTAS CONFIGURABLES: modifique estos dos valores si desea usar otras carpetas.
set "PROJECT_DIR=%~dp0"
set "WHISPER_INPUT_DIR=%PROJECT_DIR%input"
set "WHISPER_OUTPUT_DIR=%PROJECT_DIR%output"

rem Activates the virtual environment required for the project
echo Activating virtual environment...
if not exist "%PROJECT_DIR%venv\Scripts\activate.bat" (
    echo Virtual environment not found: %PROJECT_DIR%venv
    echo Run setup_environment.bat as administrator first.
    goto end
)
call "%PROJECT_DIR%venv\Scripts\activate.bat"
rem Checks if the virtual environment was activated correctly
if %errorlevel% neq 0 (
    echo Error activating the virtual environment.
    goto end
    rem If there's an error, displays a message and jumps to the :end label
)

rem Verifies that the transcription dependencies are available before processing files
python -c "import whisper, torch" > nul 2>&1
if %errorlevel% neq 0 (
    echo Whisper dependencies are not installed in venv.
    echo Run setup_environment.bat as administrator and let it finish.
    goto end
)

rem Executes the Python script that performs the desired task
echo Running Python script...
python "%PROJECT_DIR%transcribe.py"
rem Checks if the Python script was executed correctly
if %errorlevel% neq 0 (
    echo Error running the Python script.
    goto end
    rem If there's an error, displays a message and jumps to the :end label
)

rem Displays a success message if the Python script executed without errors
echo.
echo Script executed successfully.

:end
rem Label to jump to if there is an error in any of the operations above or upon finishing the script
echo Press any key to close....
pause > nul
