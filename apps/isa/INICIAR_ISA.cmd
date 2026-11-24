@echo off
setlocal EnableExtensions
title ISA - English Tutor

rem Starts the local services required by ISA and opens the application.
set "ISA_ROOT=%~dp0"
set "XAMPP_ROOT=%SystemDrive%\xampp"
if not exist "%XAMPP_ROOT%\xampp_start.exe" set "XAMPP_ROOT=D:\xampp"

set "NODE_EXE="
for /F "delims=" %%N in ('where node.exe 2^>nul') do if not defined NODE_EXE set "NODE_EXE=%%N"
if not defined NODE_EXE (
  echo.
  echo [ISA] Node.js no esta disponible. Instala Node.js 22+ y vuelve a abrir ISA.
  echo.
  pause
  exit /b 1
)

if not exist "%ISA_ROOT%apps\desktop\node_modules" (
  echo.
  echo [ISA] Faltan dependencias de ISA. Ejecuta primero: pnpm install
  echo.
  pause
  exit /b 1
)

if not exist "%XAMPP_ROOT%\apache_start.bat" (
  echo.
  echo [ISA] No se encontro XAMPP en %XAMPP_ROOT%.
  echo Instala XAMPP o ajusta XAMPP_ROOT en este archivo.
  echo.
  pause
  exit /b 1
)

tasklist /FI "IMAGENAME eq httpd.exe" /NH | find /I "httpd.exe" >nul
if errorlevel 1 (
  echo [ISA] Iniciando Apache...
  start "ISA Apache" /B cmd /C call "%XAMPP_ROOT%\apache_start.bat"
)

tasklist /FI "IMAGENAME eq mysqld.exe" /NH | find /I "mysqld.exe" >nul
if errorlevel 1 (
  echo [ISA] Iniciando MySQL...
  start "ISA MySQL" /B cmd /C call "%XAMPP_ROOT%\mysql_start.bat"
)

netstat -ano | findstr /R /C:":1420 .*LISTENING" >nul
if errorlevel 1 (
  echo [ISA] Iniciando interfaz local...
  start "ISA local server" /MIN "%ComSpec%" /D /C "cd /D ""%ISA_ROOT%apps\desktop"" ^&^& ""%NODE_EXE%"" node_modules\vite\bin\vite.js --host 127.0.0.1 --port 1420"
)

set "ISA_URL=http://127.0.0.1:1420/"
for /L %%I in (1,1,30) do (
  netstat -ano | findstr /R /C:":1420 .*LISTENING" >nul && goto open_isa
  ping 127.0.0.1 -n 2 >nul
)

echo.
echo [ISA] La interfaz no respondio en 30 segundos.
echo Revisa la ventana "ISA local server" para ver el error.
echo.
pause
exit /b 1

:open_isa
echo [ISA] Listo. Abriendo %ISA_URL%
start "" "%ISA_URL%"
exit /b 0
