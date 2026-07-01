# Conta Bridge v2 — Layer 4 / Phase 4.0 Write-Control Architecture Plan — 14:04, 01.07.2026

## Purpose

This package continues Conta Bridge v2 after completed Layer 3 / Phase 3.10.

Current accepted production baseline:

```text
Conta Bridge v2 Layer 3 Read-Only RC1
Tag: conta-bridge-v2-layer3-readonly-rc1-20260701
Production base: https://mcp.atlas-ai.no
```

## Phase 4.0 rule

Phase 4.0 is planning only.

This package does not implement writes, does not modify production runtime, does not expose write methods in the active Custom GPT schema, and does not activate live accounting writes.

## Validated 2024 baseline

```text
incomeTotal  = 1422862
expenseTotal = 1414895
result       = 7967
```

## Preserved read-only controls

```text
writePaused=true
runtimeWriteBlocked=true
openApiGetOnly=true
blockedMethods includes POST, PUT, PATCH, DELETE
genericHealthExposed=false
officialContaReport=false
formalRouteVerified=false
comparisonEnabled=false
writeActionsExposed=false
statutorySubmissionExposed=false
```

## Package contents

| Path | Purpose |
|---|---|
| `00_START/START.md` | Operator start instructions |
| `01_ARCH/ARCHITECTURE.md` | Full write-control architecture |
| `02_RISK/WRITE_ACTION_RISK_MATRIX.md` | Risk taxonomy and future action classes |
| `02_RISK/write_action_inventory_seed.csv` | Phase 4.1 seed matrix |
| `03_DRYRUN/DRYRUN_CONTRACT.md` | Dry-run contract design |
| `03_DRYRUN/dryrun_response_template.json` | Non-deployable response template |
| `04_APPROVAL/APPROVAL_WORKFLOW.md` | Human approval workflow |
| `05_AUDIT/AUDIT_IDEMPOTENCY_MODEL.md` | Audit and idempotency architecture |
| `05_AUDIT/audit_record_template.json` | Non-deployable audit template |
| `06_GATES/CONTROL_GATES.md` | Phase gates and hard stops |
| `07_SOURCE_REQ/REQUIRED_SOURCE_DOCUMENTS.md` | Source documents needed before any future implementation |
| `08_TESTS/*.ps1` | Local/operator validation guard scripts |
| `09_REF/*` | Layer 3 read-only reference evidence copied unchanged |
| `10_MANIFEST/*` | Hash manifest and validation summary |

## Important

The reference PHP and OpenAPI files in `09_REF/` are evidence copies only. They are not deployment files.
