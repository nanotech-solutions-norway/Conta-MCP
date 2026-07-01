param(
  [Parameter(Mandatory=$true)][string]$BaseUrl,
  [string]$Year = "2024"
)
$ErrorActionPreference = "Stop"
$tok = Read-Host "Enter MCP bridge bearer token" -AsSecureString
$b = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($tok)
try { $plain = [Runtime.InteropServices.Marshal]::PtrToStringAuto($b) } finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($b) }
$headers = @{ Authorization = "Bearer $plain"; Accept = "application/json" }
function Invoke-BridgeGet($path){ Invoke-RestMethod -Uri ($BaseUrl.TrimEnd("/") + $path) -Headers $headers -Method GET }
$ws = Invoke-BridgeGet "/api/v2/write-status"
if($ws.writePaused -ne $true){ throw "writePaused must remain true" }
if($ws.runtimeWriteBlocked -ne $true){ throw "runtimeWriteBlocked must remain true" }
if($ws.openApiGetOnly -ne $true){ throw "openApiGetOnly must remain true" }
if($ws.writeActionsExposed -eq $true){ throw "writeActionsExposed must remain false" }
if($ws.statutorySubmissionExposed -eq $true){ throw "statutorySubmissionExposed must remain false" }
$blocked = @($ws.blockedMethods)
foreach($m in @("POST","PUT","PATCH","DELETE")){
  if($blocked -notcontains $m){ throw "blockedMethods missing $m" }
}
$ms = Invoke-BridgeGet "/api/v2/accounting/management-summary?year=$Year"
$s = $ms.summary
if([int]$s.incomeTotal -ne 1422862){ throw "incomeTotal regression: $($s.incomeTotal)" }
if([int]$s.expenseTotal -ne 1414895){ throw "expenseTotal regression: $($s.expenseTotal)" }
if([int]$s.result -ne 7967){ throw "result regression: $($s.result)" }
if($ms.officialContaReport -eq $true){ throw "officialContaReport must remain false" }
Write-Host "PASSED: Production baseline still matches Layer 3 read-only RC1 controls and 2024 baseline."
