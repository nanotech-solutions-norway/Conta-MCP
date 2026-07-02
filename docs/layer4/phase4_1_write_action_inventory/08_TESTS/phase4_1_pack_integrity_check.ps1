$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$Required = @(
  "README.md",
  "state.json",
  "00_START\START.md",
  "01_INVENTORY\WRITE_ACTION_INVENTORY.md",
  "01_INVENTORY\write_action_inventory.csv",
  "02_RISK\RISK_CLASSIFICATION_RULES.md",
  "02_RISK\RISK_CLASS_SUMMARY.md",
  "03_SOURCES\SOURCE_GAP_REGISTER.md",
  "03_SOURCES\REPOSITORY_EVIDENCE_NOTES.md",
  "04_DECISION\PHASE4_1_DECISION_REGISTER.md",
  "05_PHASE42\PHASE4_2_INPUTS.md",
  "10_MANIFEST\manifest.json",
  "10_MANIFEST\sha256.txt"
)
$Missing = @()
foreach ($Rel in $Required) {
  if (-not (Test-Path (Join-Path $Root $Rel))) { $Missing += $Rel }
}
if ($Missing.Count -gt 0) {
  Write-Error ("FAILED: Missing required files: " + ($Missing -join ", "))
}
Write-Host "PASSED: Phase 4.1 package integrity verified."
