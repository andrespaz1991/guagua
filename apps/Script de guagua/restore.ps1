. (Join-Path $PSScriptRoot '../../comun/database_client.ps1')
$guaguaConnectionSettings = Get-GuaguaDatabaseSettings -Profile 'default'
$guaguaPreviousMysqlPassword = [Environment]::GetEnvironmentVariable('MYSQL_PWD', 'Process')
$env:MYSQL_PWD = $guaguaConnectionSettings.password
try {
# Configuración
$RUTA_RESPALDO = "D:/xampp/htdocs/guagua/reportes/bd/automatico/"
$MYSQL_PATH = "D:/xampp/mysql/bin/mysql.exe"
$USUARIO = $guaguaConnectionSettings.user
$MYSQL_HOST = $guaguaConnectionSettings.host
$PUERTO = $guaguaConnectionSettings.port
$BASE_DATOS = $guaguaConnectionSettings.database
$MOSTRAR_MENSAJES = $true  # Bandera para mostrar mensajes y pausar ($true o $false)

# Función para mostrar mensajes (si está habilitado)
function Mostrar-Mensaje {
    param (
        [string]$mensaje
    )
    if ($MOSTRAR_MENSAJES) {
        Write-Host $mensaje
    }
}

# Función para mostrar errores (siempre se muestra, independientemente de la bandera)
function Mostrar-Error {
    param (
        [string]$mensaje
    )
    Write-Host $mensaje -ForegroundColor Red
    Read-Host "Presiona Enter para continuar..."
    exit 1
}

# Función para obtener la fecha y hora en un formato amigable
function Get-FechaAmigable {
    return Get-Date -Format "dd/MM/yyyy HH:mm:ss"
}

# Verificar si mysql existe
if (-Not (Test-Path $MYSQL_PATH)) {
    $MENSAJE_ERROR = "[$(Get-FechaAmigable)] Error: mysql no encontrado en $MYSQL_PATH"
    Add-Content -Path "$RUTA_RESPALDO/import_error.log" -Value $MENSAJE_ERROR
    Mostrar-Error $MENSAJE_ERROR
}

# Obtener el archivo .sql más reciente que no esté vacío
$archivoSQL = Get-ChildItem -Path $RUTA_RESPALDO -Filter *.sql | 
              Where-Object { $_.Length -gt 0 } | 
              Sort-Object CreationTime -Descending | 
              Select-Object -First 1

# Verificar si se encontró un archivo válido
if (-Not $archivoSQL) {
    $MENSAJE_ERROR = "[$(Get-FechaAmigable)] Error: No se encontró ningún archivo .sql válido en $RUTA_RESPALDO"
    Add-Content -Path "$RUTA_RESPALDO/import_error.log" -Value $MENSAJE_ERROR
    Mostrar-Error $MENSAJE_ERROR
}

# Crear la base de datos si no existe
Mostrar-Mensaje "Verificando si la base de datos $BASE_DATOS existe..."
& $MYSQL_PATH --user=$USUARIO --host=$MYSQL_HOST --port=$PUERTO --default-character-set=$($guaguaConnectionSettings.charset) -e "CREATE DATABASE IF NOT EXISTS $BASE_DATOS;"

# Verificar si la creación de la base de datos fue exitosa
if ($LASTEXITCODE -ne 0) {
    $MENSAJE_ERROR = "[$(Get-FechaAmigable)] Error: No se pudo crear la base de datos $BASE_DATOS."
    Add-Content -Path "$RUTA_RESPALDO/import_error.log" -Value $MENSAJE_ERROR
    Mostrar-Error $MENSAJE_ERROR
}

# Importar el archivo .sql más reciente
Mostrar-Mensaje "Importando el archivo $($archivoSQL.Name) en la base de datos $BASE_DATOS..."
Get-Content -Path $archivoSQL.FullName | & $MYSQL_PATH --user=$USUARIO --host=$MYSQL_HOST --port=$PUERTO --default-character-set=$($guaguaConnectionSettings.charset) --database=$BASE_DATOS

# Verificar si la importación fue exitosa
if ($LASTEXITCODE -eq 0) {
    Mostrar-Mensaje "Importación completada con éxito."
} else {
    $MENSAJE_ERROR = "[$(Get-FechaAmigable)] Error: La importación del archivo $($archivoSQL.Name) falló."
    Add-Content -Path "$RUTA_RESPALDO/import_error.log" -Value $MENSAJE_ERROR
    Mostrar-Error $MENSAJE_ERROR
}

# Pausar solo si $MOSTRAR_MENSAJES es $true
if ($MOSTRAR_MENSAJES) {
    Read-Host "Presiona Enter para cerrar..."
}
exit 0
} finally {
    [Environment]::SetEnvironmentVariable('MYSQL_PWD', $guaguaPreviousMysqlPassword, 'Process')
}
