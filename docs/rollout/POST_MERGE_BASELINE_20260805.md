# Conta MCP Post-Merge Baseline — 11:11, 05.08.2026

## Classification

```text
CONTROLLED_WRITE_FOUNDATION_MERGED
POST_MERGE_CI_PASSED
SOURCE_IMPLEMENTATION_COMPLETE
LOCAL_DESKTOP_VALIDATION_PENDING
RUNTIME_DEPLOYMENT_NOT_VERIFIED
PROVIDER_EVIDENCE_INCOMPLETE
SANDBOX_ONE_CALL_NOT_AUTHORIZED
PRODUCTION_WRITE_PROGRAM_NOT_IMPLEMENTED
```

## Canonical source state

- Repository: `nanotech-solutions-norway/Conta-MCP`
- Canonical branch: `main`
- Merged pull request: `#1`
- Merge commit: `689cf28d943b761e26d9d1a7ef2eaddf5b78cc07`
- Merge timestamp: `2026-08-05T08:59:44Z`
- Post-merge controlled-write validation: `PASSED`
- Post-merge CodeQL Actions analysis: `PASSED`

This record supersedes any statement that the controlled-write foundation remains only on an unmerged draft branch. Historical records remain valid as evidence of the state that existed when they were created.

## Implemented source controls

The merged source includes:

1. policy-derived tool discovery;
2. non-executing invoice-draft preview;
3. deterministic canonical payload hashing;
4. independent write, runtime-block, execution and production gates;
5. explicit action and organization allowlists;
6. approved release-manifest verification;
7. global and action-specific kill switch;
8. HMAC-SHA256 signed one-use approval envelopes;
9. signed, payload-bound sandbox authorization;
10. authorization-ID, nonce and idempotency replay prevention;
11. dispatch-level method, route, organization, policy and manifest checks;
12. mandatory post-create readback and controlled-field comparison;
13. one-call sandbox harness with no retry loop;
14. explicit production-program refusal.

## Preserved effective safety state

```text
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
provider_call_performed=false
sandbox_execution_performed=false
production_deployment_performed=false
live_write_approved=false
```

No source merge, documentation update, manifest generation or local test grants provider-execution authority.

## Validation status

### Verified remotely

- Pull request `#1` merged into `main`.
- Merge commit recorded.
- Post-merge PHP validation workflow passed.
- Post-merge CodeQL Actions analysis passed.

### Pending Desktop validation

The operator must run the following from a clean local checkout:

```powershell
git fetch origin --prune
git checkout main
git pull --ff-only origin main
git rev-parse HEAD
git status --short

Get-ChildItem app,bin,config,public,tests -Recurse -Filter *.php |
    ForEach-Object {
        php -l $_.FullName
        if ($LASTEXITCODE -ne 0) { throw "PHP syntax validation failed: $($_.FullName)" }
    }

php tests/controlled-write-foundation.php
if ($LASTEXITCODE -ne 0) { throw "Controlled-write foundation tests failed" }

php tests/remaining-control-paths.php
if ($LASTEXITCODE -ne 0) { throw "Remaining control-path tests failed" }

git diff --exit-code
```

Expected commit:

```text
689cf28d943b761e26d9d1a7ef2eaddf5b78cc07
```

Until the local result is recorded, classify local validation as `PENDING_OPERATOR_VALIDATION`.

## Remaining gates before deployment

1. Complete local Desktop validation.
2. Inspect the active Domeneshop runtime read-only.
3. Compare deployed files and configuration state with canonical `main`.
4. Prepare a sanitized runtime drift report.
5. Deploy only in a fail-closed configuration.
6. Validate health, MCP initialization, authenticated `tools/list`, read tools and preview-only behavior.
7. Confirm the execution tool remains absent.
8. Refresh provider schema and route evidence.
9. Validate readback route, provider scopes, sandbox/test-company identity and rectification procedure.
10. Generate an observed release manifest and leave it `PENDING_OPERATOR_VALIDATION` until reviewed.
11. Require a separate explicit authorization before exactly one sandbox mutation.

## Provider evidence state

The existing offline evidence supports:

```text
POST /invoice/organizations/{opContextOrgId}/invoice-drafts
operationId=v1MakeInvoiceDraft
```

The following remain unresolved:

- current official schema freshness;
- required provider scopes;
- provider-native idempotency behavior;
- sandbox/test-company identity;
- exact organization allowlist;
- supported readback route;
- create-response draft-ID location;
- rectification procedure;
- one-call operator authorization.

No unresolved item may be interpreted as approval.

## Current authorized next action

```text
POST_MERGE_BASELINE_CLOSURE
READ_ONLY_RUNTIME_INVENTORY
NO_DEPLOYMENT
NO_PROVIDER_CALL
```
