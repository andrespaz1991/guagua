param([string]$Source, [string]$Output)
$ErrorActionPreference = 'Stop'
$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open($Source, $false, $true)
    $document.ExportAsFixedFormat($Output, 17)
} finally {
    if ($document) { $document.Close(0) }
    if ($word) { $word.Quit() }
}
