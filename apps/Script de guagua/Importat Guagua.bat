@echo off
:: Script para importar el archivo SQL más reciente a la base de datos 'guagua'

SET MYSQL_BIN=D:\xampp\mysql\bin
SET HOST=127.0.0.1
SET PORT=7000
SET USER=root
SET PASSWORD=
SET DATABASE=guagua
SET SQL_DIR=D:\xampp\htdocs\guagua\reportes\bd
SET COUNTDOWN=10  :: Tiempo estimado en segundos

:: Buscar el archivo SQL más reciente
FOR /F "delims=" %%I IN ('DIR /B /O-D "%SQL_DIR%\*.sql" 2^>nul') DO (
    SET SQL_FILE=%SQL_DIR%\%%I
    GOTO :FOUND
)

echo [ERROR] No se encontró ningún archivo SQL en %SQL_DIR%.
pause
exit /b 1

:FOUND
echo [INFO] Archivo más reciente encontrado: %SQL_FILE%

:: Comprobar si MySQL está funcionando
echo [INFO] Verificando conexión con MySQL...
"%MYSQL_BIN%\mysql" -u %USER% -h %HOST% --port=%PORT% -e "exit"
IF ERRORLEVEL 1 (
    echo [ERROR] No se pudo conectar a MySQL. Revisa el servicio o las credenciales.
    pause
    exit /b 1
)

:: Crear la base de datos si no existe
echo [INFO] Creando la base de datos %DATABASE% si no existe...
"%MYSQL_BIN%\mysql" -u %USER% -h %HOST% --port=%PORT% -e "CREATE DATABASE IF NOT EXISTS %DATABASE%;"

:: Mensaje de inicio
echo.
echo =======================================
echo ==        CARGANDO... ESPERA...      ==
echo =======================================
echo.

:: Ejecutar la importación mientras muestra el contador
(
    "%MYSQL_BIN%\mysql" -u %USER% -h %HOST% --port=%PORT% %DATABASE% < "%SQL_FILE%"
) | (
    FOR /L %%I IN (%COUNTDOWN%,-1,1) DO (
        echo Tiempo restante: %%I segundos...
        PING -n 2 127.0.0.1 >nul
    )
)

:: Verificar si hubo error en la importación
IF ERRORLEVEL 1 (
    echo [ERROR] No se pudo importar la base de datos.
    pause
    exit /b 1
)

echo.
echo =======================================
echo ==  IMPORTACIÓN COMPLETADA CON ÉXITO  ==
echo =======================================
pause
