$ErrorActionPreference = 'Stop'
$project = Join-Path (Split-Path (Split-Path $PSScriptRoot -Parent) -Parent) 'wsp'
$node = (Get-Command node.exe -ErrorAction SilentlyContinue).Source
if (-not $node) { $node = Join-Path $env:ProgramFiles 'nodejs\node.exe' }
if (-not (Test-Path -LiteralPath $node)) { throw 'Node.js no está instalado.' }
if (-not (Test-Path -LiteralPath (Join-Path $project 'server.js'))) { throw 'No se encontró el servidor de Diario.' }
$logs = Join-Path (Split-Path $PSScriptRoot -Parent) 'tmp\whatsapp-diario'
New-Item -ItemType Directory -Path $logs -Force | Out-Null
# Evita dos procesos si varios navegadores abren el módulo a la vez.
$mutex = New-Object System.Threading.Mutex($false, 'Local\GuaguaWhatsAppDiarioStart')
$held = $false
try {
    $held = $mutex.WaitOne(10000)
    if (-not $held) { throw 'Hay otro arranque en curso.' }
    $tcp = New-Object System.Net.Sockets.TcpClient
    try { $tcp.Connect('127.0.0.1', 3000); $running = $true } catch { $running = $false } finally { $tcp.Dispose() }
    $pidFile = Join-Path $logs 'server.pid'
    $recent = $null
    if (Test-Path -LiteralPath $pidFile) {
        $savedProcessId = 0
        if ([int]::TryParse((Get-Content -LiteralPath $pidFile -Raw).Trim(), [ref]$savedProcessId)) {
            $recent = Get-Process -Id $savedProcessId -ErrorAction SilentlyContinue | Where-Object ProcessName -eq 'node'
        }
    }
    if (-not $running -and -not $recent) {
        $started = Start-Process -FilePath $node -ArgumentList ('"' + (Join-Path $project 'server.js') + '"') -WorkingDirectory $project -WindowStyle Hidden -RedirectStandardOutput (Join-Path $logs 'server.log') -RedirectStandardError (Join-Path $logs 'error.log') -PassThru
        Set-Content -LiteralPath $pidFile -Value $started.Id
    }
} finally {
    if ($held) { $mutex.ReleaseMutex() }
    $mutex.Dispose()
}
