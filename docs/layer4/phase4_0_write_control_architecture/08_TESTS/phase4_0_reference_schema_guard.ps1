param([string]$OpenApiPath = ".\09_REF\openapi_layer3_readonly_reference_only.json")
$ErrorActionPreference = "Stop"
if(-not(Test-Path $OpenApiPath)){ throw "OpenAPI reference not found: $OpenApiPath" }
$raw = Get-Content -Raw -Path $OpenApiPath
$j = $raw | ConvertFrom-Json -Depth 100
foreach($path in $j.paths.PSObject.Properties){
  foreach($method in $path.Value.PSObject.Properties){
    if($method.Name.ToUpperInvariant() -ne "GET"){
      throw "Non-GET method found in read-only reference schema: $($method.Name) $($path.Name)"
    }
  }
}
$forbidden = @("CONTA_API_KEY", "conta_config.local.php", "apiKey:", "organizationId", "companyId", "Bearer ")
foreach($pat in $forbidden){
  if($raw.Contains($pat)){ throw "Forbidden value found in schema: $pat" }
}
Write-Host "PASSED: Layer 3 read-only OpenAPI reference remains GET-only and credential-free."
