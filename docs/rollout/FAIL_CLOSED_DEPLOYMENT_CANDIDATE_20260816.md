# Conta MCP Fail-Closed Deployment Candidate — 2026-08-16

## Classification

```text
FAIL_CLOSED_DEPLOYMENT_CANDIDATE_BUILT=true
CANONICAL_SOURCE_COMMIT=7d97a6330e4aff5a6e251ad19d717d7408cf3825
DEPLOYMENT_SCOPE_FILE_COUNT=18
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
NEXT_GATE=PREDEPLOYMENT_SERVER_BACKUP_AND_OPERATOR_DEPLOYMENT
```

## Authorization context

The operator authorized continuation of the Conta sandbox invoice-draft rollout until controlled invoice drafting is successful and complete. The authorization is recorded in `INVOICE_DRAFT_CONTINUATION_AUTHORIZATION_20260816.md` and does not authorize production provider mutations.

The existing verified sandbox invoice draft remains the first successful mutation evidence. No duplicate sandbox draft was created during this deployment-candidate work.

## Canonical candidate

Repository: `nanotech-solutions-norway/Conta-MCP`  
Branch at build: `main`  
Source commit: `7d97a6330e4aff5a6e251ad19d717d7408cf3825`

GitHub Actions workflow:

```text
Conta Fail-Closed Deployment Candidate
run_id=31915890577
conclusion=success
```

The builder validated:

- exact protected-runtime path set;
- all 18 PHP/runtime files present;
- PHP syntax;
- Controlled Write Foundation tests;
- invoice-draft readback-verifier regression tests;
- remaining control-path tests;
- fail-closed production defaults;
- blocked example kill switch;
- pending sandbox-authorization example;
- secret-bearing path exclusions;
- obvious credential-material scan;
- immutable protected-runtime artifact generation.

## Artifact evidence

GitHub artifact ID: `9254881563`  
GitHub artifact digest SHA-256:

```text
23ddd01823703f54381f3114d79dd18246656d148990a765d157ab76f06000e7
```

The downloaded GitHub artifact ZIP independently hashed to the same value:

```text
23ddd01823703f54381f3114d79dd18246656d148990a765d157ab76f06000e7
```

Intermediate candidate tar.gz SHA-256:

```text
ea89e8c38b25918674f4a94ec6f1c3294c92f3d9375e07cada90e6754678e2fc
```

Protected-runtime deployment ZIP SHA-256:

```text
2da8802035e4b9989c76f493359abe2587e84896e9e4c5c1911d52748702f6cc
```

Controlled-write Google Drive evidence copy:

```text
file_id=1s2XU0StVtnh7pUqZ0le0H0OM5o-LvdC9
folder_id=1e7bNnrEzTHsvSAjwPRx29fRRbpHG31U-
```

## Protected runtime scope

Exactly these 18 files are in the deployment payload:

```text
app/ApprovalEnvelopeVerifier.php
app/AuditLogger.php
app/bootstrap.php
app/Config.php
app/ContaClient.php
app/ContaTools.php
app/HttpClient.php
app/InvoiceDraftPreview.php
app/InvoiceDraftReadbackVerifier.php
app/McpServer.php
app/ReleaseManifestGuard.php
app/SandboxAuthorizationGate.php
app/Security.php
app/WriteDispatchPermit.php
app/WriteExecutionLedger.php
app/WriteKillSwitch.php
app/WritePolicy.php
config/tool_policy.php
```

Target protected runtime root:

```text
/Custom Models/conta-mcp
```

The public bridge/document root remains:

```text
www/cm
```

and must remain unchanged.

## Delta from last deployed fail-closed source

Last deployed source evidence: `54b0d02d6eafe423a1d4485dfc88bdb85a28f5d0`.

Within the 18-file protected-runtime scope, only two files changed between that source and this candidate:

```text
app/Config.php
app/InvoiceDraftReadbackVerifier.php
```

The complete 18-file payload is retained for deterministic deployment and hash verification rather than deploying an ad-hoc two-file patch.

## Required fail-closed state

```text
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_organization_ids=[]
allowed_write_actions=[]
kill_switch_global_blocked=true
production_write_authorized=false
```

Server-only `config/conta_config.local.php`, generated storage state and all sandbox credentials/approvals remain excluded from the payload.

## Required next gate

The next operation requires authenticated Domeneshop/server access and cannot be performed through the currently connected GitHub/Drive tools.

Before upload:

1. back up every existing protected-runtime target that will be overwritten;
2. record hashes of the current protected runtime and the `www/cm` public bridge;
3. verify that `config/conta_config.local.php` is preserved and not read into evidence;
4. independently verify the deployment ZIP SHA-256 is `2da8802035e4b9989c76f493359abe2587e84896e9e4c5c1911d52748702f6cc`;
5. upload/extract only the 18 protected-runtime files under `/Custom Models/conta-mcp`;
6. do not modify `www/cm`;
7. immediately validate HTTP health, unauthenticated rejection, authenticated initialize/tools-list, fail-closed write-tool visibility/refusal and deployed hashes;
8. only after fail-closed parity is confirmed may the separately authorized sandbox invoice-draft end-to-end validation proceed.

No production provider mutation is authorized by this deployment candidate.
