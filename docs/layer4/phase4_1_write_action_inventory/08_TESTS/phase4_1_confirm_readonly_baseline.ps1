param(
  [Parameter(Mandatory=$true)] [string] $BaseUrl
)
$ErrorActionPreference = "Stop"
$Token = Read-Host "Enter MCP bridge bearer token" -AsSecureString
$Plain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($Token))
$Headers = @{ Authorization = "Bearer $Plain" }
$WriteStatus = Invoke-RestMethod -Method GET -Uri "$BaseUrl/api/v2/write-status" -Headers $Headers
if ($WriteStatus.writePaused -ne $true) { throw "writePaused is not true" }
if ($WriteStatus.runtimeWriteBlocked -ne $true) { throw "runtimeWriteBlocked is not true" }
if ($WriteStatus.openApiGetOnly -ne $true) { throw "openApiGetOnly is not true" }
foreach ($Method in @("POST","PUT","PATCH","DELETE")) {
  if ($WriteStatus.blockedMethods -notcontains $Method) { throw "blockedMethods missing $Method" }
}
if ($WriteStatus.writeActionsExposed -ne $false) { throw "writeActionsExposed is not false" }
if ($WriteStatus.statutorySubmissionExposed -ne $false) { throw "statutorySubmissionExposed is not false" }
$Summary = Invoke-RestMethod -Method GET -Uri "$BaseUrl/api/v2/accounting/management-summary?year=2024" -Headers $Headers
if ([int]$Summary.incomeTotal -ne 1422862) { throw "incomeTotal mismatch" }
if ([int]$Summary.expenseTotal -ne 1414895) { throw "expenseTotal mismatch" }
if ([int]$Summary.result -ne 7967) { throw "result mismatch" }
Write-Host "PASSED: Production baseline still matches Layer 3 read-only RC1 controls and 2024 baseline."
