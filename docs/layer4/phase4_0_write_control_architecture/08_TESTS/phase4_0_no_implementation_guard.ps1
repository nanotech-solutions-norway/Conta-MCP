$ErrorActionPreference = "Stop"
$root = Get-Location
$scan = Get-ChildItem -Path $root -Recurse -File | Where-Object {
  $_.FullName -notmatch "\\09_REF\\" -and $_.FullName -notmatch "\\10_MANIFEST\\"
}
$hardForbidden = @(
  "liveWritesAllowed\s*[:=]\s*true",
  "implementationAllowed\s*[:=]\s*true",
  "runtimeChangeAllowed\s*[:=]\s*true",
  "customGptWriteActionsAllowed\s*[:=]\s*true"
)
foreach($file in $scan){
  $raw = Get-Content -Raw -Path $file.FullName
  foreach($pattern in $hardForbidden){
    if($raw -match $pattern){ throw "Forbidden implementation flag found in $($file.FullName): $pattern" }
  }
}
Write-Host "PASSED: No Phase 4.0 file authorizes implementation or live writes."
