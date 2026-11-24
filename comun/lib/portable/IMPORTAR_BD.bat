@echo off
setlocal
chcp 65001 >nul
echo IMPORTAR BASES DE DATOS DE GUAGUA EN XAMPP
echo.
echo Usa una instalacion nueva: se reemplazaran tablas con los mismos nombres.
choice /C SN /N /M "Continuar con la importacion? [S/N]: "
if errorlevel 2 exit /b 0
set "GUAGUA_XAMPP=C:\xampp"
set /p "GUAGUA_XAMPP=Carpeta de XAMPP [C:\xampp]: "
set "GUAGUA_PORT=3306"
set /p "GUAGUA_PORT=Puerto de MySQL [3306]: "
if not exist "%GUAGUA_XAMPP%\mysql\bin\mysql.exe" (
    echo No se encontro mysql.exe. Comprueba la carpeta de XAMPP.
    pause
    exit /b 1
)
if not exist "%~dp0base_de_datos\*.sql" (
    echo Extrae todo el ZIP antes de ejecutar este archivo.
    pause
    exit /b 1
)
echo.
echo Usuario: root. Cuando pida la contrasena, pulsa Enter si esta vacia.
for %%F in ("%~dp0base_de_datos\*.sql") do (
    echo Importando %%~nxF ...
    "%GUAGUA_XAMPP%\mysql\bin\mysql.exe" --no-defaults --host=127.0.0.1 --port="%GUAGUA_PORT%" --user=root --password --default-character-set=utf8mb4 < "%%~fF"
    if errorlevel 1 (
        echo La importacion fallo. Revisa que MySQL este iniciado, el puerto y la contrasena.
        pause
        exit /b 1
    )
)
echo.
echo Bases importadas. Copia guagua a htdocs y abre http://localhost/guagua
echo Si usaste otro puerto o contrasena, actualiza la conexion en guagua\.env
pause
endlocal
