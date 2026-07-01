$ErrorActionPreference = "Stop"
$required = @(
  ".\README.md",
  ".\00_START\START.md",
  ".\01_ARCH\ARCHITECTURE.md",
  ".\02_RISK\WRITE_ACTION_RISK_MATRIX.md",
  ".\02_RISK\write_action_inventory_seed.csv",
  ".\03_DRYRUN\DRYRUN_CONTRACT.md",
  ".\03_DRYRUN\dryrun_response_template.json",
  ".\04_APPROVAL\APPROVAL_WORKFLOW.md",
  ".\05_AUDIT\AUDIT_IDEMPOTENCY_MODEL.md",
  ".\05_AUDIT\audit_record_template.json",
  ".\06_GATES\CONTROL_GATES.md",
  ".\07_SOURCE_REQ\REQUIRED_SOURCE_DOCUMENTS.md",
  ".\09_REF\api_v2_layer3_rc1_reference_only.php",
  ".\09_REF\openapi_layer3_readonly_reference_only.json",
  ".\10_MANIFEST\manifest.json",
  ".\10_MANIFEST\sha256.txt"
)
$missing = @()
foreach($p in $required){ if(-not(Test-Path $p)){ $missing += $p } }
if($missing.Count -gt 0){
  $missing | ForEach-Object { Write-Host "Missing: $_" }
  throw "Phase 4.0 package integrity failed"
}
Write-Host "PASSED: Phase 4.0 architecture package integrity verified."
