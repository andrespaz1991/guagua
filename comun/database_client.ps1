function Get-GuaguaDatabaseSettings {
    param([string]$Profile = 'default')
    $guaguaRoot = Split-Path -Parent $PSScriptRoot
    $guaguaPhp = Get-Command php -CommandType Application -ErrorAction SilentlyContinue
    $guaguaPhpPath = if ($guaguaPhp) { $guaguaPhp.Source } else { [IO.Path]::GetFullPath((Join-Path $guaguaRoot '../../php/php.exe')) }
    if (-not (Test-Path -LiteralPath $guaguaPhpPath)) { throw 'PHP CLI no está disponible para leer la configuración central.' }
    $guaguaBridge = Join-Path $guaguaRoot 'comun/database.settings.cli.php'
    $guaguaSettingsJson = & $guaguaPhpPath $guaguaBridge $Profile
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo leer el perfil de base de datos. Revise .env.' }
    # Never print the payload: it contains the credentials read through the private pipe.
    return ($guaguaSettingsJson | ConvertFrom-Json)
}
