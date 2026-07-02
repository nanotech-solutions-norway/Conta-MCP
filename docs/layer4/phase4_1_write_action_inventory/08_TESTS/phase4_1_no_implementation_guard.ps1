$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$Self = $MyInvocation.MyCommand.Path
$Files = Get-ChildItem -Path $Root -Recurse -File | Where-Object { 
  $_.FullName -notmatch "10_MANIFEST" -and $_.FullName -ne $Self
}
$Forbidden = @(
  "writes implemented",
  "runtime modified",
  "active schema updated",
  "POST exposed in active",
  "PUT exposed in active",
  "PATCH exposed in active",
  "DELETE exposed in active",
  "live writes enabled",
  "production write activation approved"
)
$Hits = @()
foreach ($File in $Files) {
  $Text = Get-Content $File.FullName -Raw -ErrorAction SilentlyContinue
  foreach ($Pattern in $Forbidden) {
    if ($Text -match [regex]::Escape($Pattern)) { $Hits += "$($File.FullName): $Pattern" }
  }
}
if ($Hits.Count -gt 0) {
  Write-Error ("FAILED: Potential implementation authorization text found: " + ($Hits -join "; "))
}
Write-Host "PASSED: No Phase 4.1 file authorizes implementation or live writes."
