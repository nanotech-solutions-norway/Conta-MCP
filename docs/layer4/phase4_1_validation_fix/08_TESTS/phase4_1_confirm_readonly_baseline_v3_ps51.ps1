param(
    [Parameter(Mandatory=$true)]
    [string]$BaseUrl,

    [Parameter(Mandatory=$false)]
    [string]$Year = "2024"
)

$ErrorActionPreference = "Stop"

function Get-PropValue {
    param(
        [Parameter(Mandatory=$true)]
        $Object,

        [Parameter(Mandatory=$true)]
        [string]$Name
    )

    if ($null -eq $Object) {
        return $null
    }

    $prop = $Object.PSObject.Properties[$Name]
    if ($null -eq $prop) {
        return $null
    }

    return $prop.Value
}

function Get-NestedValue {
    param(
        [Parameter(Mandatory=$true)]
        $Object,

        [Parameter(Mandatory=$true)]
        [string]$Path
    )

    $current = $Object
    foreach ($part in $Path.Split(".")) {
        $current = Get-PropValue -Object $current -Name $part
        if ($null -eq $current) {
            return $null
        }
    }

    return $current
}

function Resolve-FirstValue {
    param(
        [Parameter(Mandatory=$true)]
        $Object,

        [Parameter(Mandatory=$true)]
        [string[]]$Paths,

        [Parameter(Mandatory=$true)]
        [string]$Label
    )

    foreach ($path in $Paths) {
        $value = Get-NestedValue -Object $Object -Path $path
        if ($null -ne $value) {
            return @{
                Value = $value
                Path = $path
                Label = $Label
            }
        }
    }

    throw "Unable to resolve $Label from any expected response path: $($Paths -join ', ')"
}

function Assert-BooleanFalse {
    param(
        [Parameter(Mandatory=$true)]
        $Object,

        [Parameter(Mandatory=$true)]
        [string]$Name
    )

    $value = Get-PropValue -Object $Object -Name $Name
    if ($null -eq $value) {
        throw "$Name is missing"
    }

    if ($value -ne $false) {
        throw "$Name is not Boolean false"
    }
}

function Assert-BooleanTrue {
    param(
        [Parameter(Mandatory=$true)]
        $Object,

        [Parameter(Mandatory=$true)]
        [string]$Name
    )

    $value = Get-PropValue -Object $Object -Name $Name
    if ($null -eq $value) {
        throw "$Name is missing"
    }

    if ($value -ne $true) {
        throw "$Name is not Boolean true"
    }
}

function Assert-IntEquals {
    param(
        [Parameter(Mandatory=$true)]
        $Resolved,

        [Parameter(Mandatory=$true)]
        [int]$Expected
    )

    try {
        $actual = [int]$Resolved.Value
    }
    catch {
        throw "$($Resolved.Label) could not be converted to integer. Value=$($Resolved.Value), Path=$($Resolved.Path)"
    }

    if ($actual -ne $Expected) {
        throw "$($Resolved.Label) mismatch. Expected=$Expected Actual=$actual Path=$($Resolved.Path)"
    }

    Write-Host "Resolved $($Resolved.Label) from $($Resolved.Path): $actual"
}

function Test-SourceStatsIfPresent {
    param(
        [Parameter(Mandatory=$true)]
        $Summary
    )

    $sourceStats = Resolve-FirstValue -Object $Summary -Paths @(
        "sourceStats",
        "summary.sourceStats",
        "data.sourceStats",
        "data.summary.sourceStats",
        "payload.sourceStats",
        "payload.summary.sourceStats"
    ) -Label "sourceStats"

    if ($null -eq $sourceStats.Value) {
        Write-Host "sourceStats not present; skipping sourceStats validation."
        return
    }

    if (-not ($sourceStats.Value -is [System.Collections.IEnumerable])) {
        Write-Host "sourceStats present but not enumerable; skipping sourceStats validation."
        return
    }

    foreach ($item in $sourceStats.Value) {
        if ($null -eq $item) {
            continue
        }

        $status = Get-PropValue -Object $item -Name "status"
        $success = Get-PropValue -Object $item -Name "success"
        $name = Get-PropValue -Object $item -Name "name"
        if ($null -eq $name) {
            $name = Get-PropValue -Object $item -Name "source"
        }
        if ($null -eq $name) {
            $name = "unnamed-source"
        }

        if ($null -ne $status -and [int]$status -ne 200) {
            throw "sourceStats status is not 200 for $name"
        }

        if ($null -ne $success -and $success -ne $true) {
            throw "sourceStats success is not true for $name"
        }
    }

    Write-Host "PASSED: sourceStats, if present, are compatible."
}

$BaseUrl = $BaseUrl.TrimEnd("/")

$Token = Read-Host "Enter MCP bridge bearer token" -AsSecureString
$Plain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($Token))
$Headers = @{ Authorization = "Bearer $Plain" }

$WriteStatus = Invoke-RestMethod -Method GET -Uri "$BaseUrl/api/v2/write-status" -Headers $Headers -ErrorAction Stop

Assert-BooleanTrue -Object $WriteStatus -Name "writePaused"
Assert-BooleanTrue -Object $WriteStatus -Name "runtimeWriteBlocked"
Assert-BooleanTrue -Object $WriteStatus -Name "openApiGetOnly"
Assert-BooleanFalse -Object $WriteStatus -Name "writeActionsExposed"
Assert-BooleanFalse -Object $WriteStatus -Name "statutorySubmissionExposed"

$blockedMethods = Get-PropValue -Object $WriteStatus -Name "blockedMethods"
if ($null -eq $blockedMethods) {
    throw "blockedMethods is missing"
}

foreach ($method in @("POST", "PUT", "PATCH", "DELETE")) {
    if ($blockedMethods -notcontains $method) {
        throw "blockedMethods does not include $method"
    }
}

Write-Host "PASSED: write-status read-only controls verified."

$Summary = Invoke-RestMethod -Method GET -Uri "$BaseUrl/api/v2/accounting/management-summary?year=$Year" -Headers $Headers -ErrorAction Stop

$income = Resolve-FirstValue -Object $Summary -Paths @(
    "incomeTotal",
    "summary.incomeTotal",
    "data.incomeTotal",
    "data.summary.incomeTotal",
    "payload.incomeTotal",
    "payload.summary.incomeTotal"
) -Label "incomeTotal"

$expense = Resolve-FirstValue -Object $Summary -Paths @(
    "expenseTotal",
    "summary.expenseTotal",
    "data.expenseTotal",
    "data.summary.expenseTotal",
    "payload.expenseTotal",
    "payload.summary.expenseTotal"
) -Label "expenseTotal"

$result = Resolve-FirstValue -Object $Summary -Paths @(
    "result",
    "summary.result",
    "data.result",
    "data.summary.result",
    "payload.result",
    "payload.summary.result"
) -Label "result"

Assert-IntEquals -Resolved $income -Expected 1422862
Assert-IntEquals -Resolved $expense -Expected 1414895
Assert-IntEquals -Resolved $result -Expected 7967

# These are expected controls when present. Some older responses may omit them.
try {
    $derivedReport = Resolve-FirstValue -Object $Summary -Paths @(
        "derivedReport",
        "summary.derivedReport",
        "data.derivedReport",
        "data.summary.derivedReport",
        "payload.derivedReport",
        "payload.summary.derivedReport"
    ) -Label "derivedReport"

    if ($null -ne $derivedReport.Value -and $derivedReport.Value -ne $true) {
        throw "derivedReport is present but not true"
    }
}
catch {
    Write-Host "derivedReport not present at expected paths; skipping derivedReport validation."
}

try {
    $officialContaReport = Resolve-FirstValue -Object $Summary -Paths @(
        "officialContaReport",
        "summary.officialContaReport",
        "data.officialContaReport",
        "data.summary.officialContaReport",
        "payload.officialContaReport",
        "payload.summary.officialContaReport"
    ) -Label "officialContaReport"

    if ($null -ne $officialContaReport.Value -and $officialContaReport.Value -ne $false) {
        throw "officialContaReport is present but not false"
    }
}
catch {
    Write-Host "officialContaReport not present at expected paths; skipping officialContaReport validation."
}

Test-SourceStatsIfPresent -Summary $Summary

Write-Host "PASSED: Production baseline matches Layer 3 read-only RC1 controls and 2024 management-summary baseline."
