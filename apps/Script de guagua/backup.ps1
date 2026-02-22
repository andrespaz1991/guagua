# Configuración
$RUTA_RESPALDO = "D:/xampp/htdocs/guagua/reportes/bd/automatico/"
$NOMBRE_BASE = "backup_automatico_guagua"
$MYSQLDUMP_PATH = "D:/xampp/mysql/bin/mysqldump.exe"
$USUARIO = "root"
$MYSQL_HOST = "127.0.0.1"  # Cambiado de $HOST a $MYSQL_HOST
$PUERTO = "7000"  # Puerto por defecto de MySQL
$BASE_DATOS = "guagua"
$MAX_ARCHIVOS = 10  # Cantidad máxima de archivos .sql que se conservan
$MOSTRAR_MENSAJES = $false  # Bandera para mostrar mensajes y pausar ($true o $false)

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

# Generar nombre de archivo con fecha y hora
$FECHA_HORA = Get-Date -Format "yyyyMMdd_HHmmss"
$NOMBRE_ARCHIVO = "$NOMBRE_BASE`_$FECHA_HORA.sql"
$RUTA_COMPLETA = "$RUTA_RESPALDO$NOMBRE_ARCHIVO"

# Verificar si mysqldump existe
if (-Not (Test-Path $MYSQLDUMP_PATH)) {
    $MENSAJE_ERROR = "[$(Get-FechaAmigable)] Error: mysqldump no encontrado en $MYSQLDUMP_PATH"
    Add-Content -Path "$RUTA_RESPALDO/backup_error.log" -Value $MENSAJE_ERROR
    Mostrar-Error $MENSAJE_ERROR
}

# Ejecutar mysqldump con manejo de errores
Mostrar-Mensaje "Realizando respaldo de la base de datos $BASE_DATOS..."
& $MYSQLDUMP_PATH --user=$USUARIO --host=$MYSQL_HOST --port=$PUERTO --routines --events --triggers --single-transaction --databases $BASE_DATOS 2>&1 | Out-File -FilePath $RUTA_COMPLETA

# Verificar si el archivo de respaldo está vacío
if ((Get-Item $RUTA_COMPLETA).Length -eq 0) {
    Remove-Item -Path $RUTA_COMPLETA -Force
    $MENSAJE_ERROR = "[$(Get-FechaAmigable)] Error: El respaldo de la base de datos $BASE_DATOS generó un archivo vacío."
    Add-Content -Path "$RUTA_RESPALDO/backup_error.log" -Value $MENSAJE_ERROR
    Mostrar-Error $MENSAJE_ERROR
}

# Verificar el código de salida de mysqldump
if ($LASTEXITCODE -eq 0) {
    Mostrar-Mensaje "Respaldo de la base de datos $BASE_DATOS creado con éxito en: $RUTA_COMPLETA"
} else {
    $ERROR_LOG = "[$(Get-FechaAmigable)] Error: El respaldo de la base de datos $BASE_DATOS falló. Detalles: $(Get-Content "$RUTA_RESPALDO/backup_error.log" -Tail 1)"
    Add-Content -Path "$RUTA_RESPALDO/backup_error.log" -Value $ERROR_LOG
    Mostrar-Error $ERROR_LOG
}

# Eliminar archivos .sql antiguos si hay más de $MAX_ARCHIVOS
$archivosSQL = Get-ChildItem -Path $RUTA_RESPALDO -Filter *.sql | Sort-Object CreationTime -Descending
if ($archivosSQL.Count -gt $MAX_ARCHIVOS) {
    Mostrar-Mensaje "Hay más de $MAX_ARCHIVOS archivos .sql. Eliminando los más antiguos..."
    $archivosSQL | Select-Object -Skip $MAX_ARCHIVOS | ForEach-Object {
        Mostrar-Mensaje "Eliminando archivo: $($_.FullName)"
        Remove-Item -Path $_.FullName -Force
    }
}

# Pausar solo si $MOSTRAR_MENSAJES es $true
if ($MOSTRAR_MENSAJES) {
    Read-Host "Presiona Enter para cerrar..."
}
exit 0