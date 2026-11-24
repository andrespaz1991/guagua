@echo off
setlocal
set "PYTHON=%USERPROFILE%\.cache\codex-runtimes\codex-primary-runtime\dependencies\python\python.exe"
if exist "%PYTHON%" (
  "%PYTHON%" "%~dp0servidor.py"
) else (
  py "%~dp0servidor.py"
)
pause
