param([string]$Source,[string]$Destination)
$ErrorActionPreference = 'Stop'
$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open($Source, $false, $true)
    $document.Repaginate()
    $document.ExportAsFixedFormat($Destination, 17)
    Write-Output ('Pages: ' + $document.ComputeStatistics(2))
    Write-Output ('PDF: ' + $Destination)
} finally {
    if ($document) { $document.Close(0); [void][Runtime.InteropServices.Marshal]::ReleaseComObject($document) }
    if ($word) { $word.Quit(); [void][Runtime.InteropServices.Marshal]::ReleaseComObject($word) }
}
