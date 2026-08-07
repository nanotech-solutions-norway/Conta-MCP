# Conta MCP Recommended Next Action — updated 11:46, 07.08.2026

## Decision

Phase 2C is complete as:

```text
RUNTIME_DRIFT_REQUIRES_REVIEW
LEGACY_SOURCE_DRIFT_CONFIRMED
```

Phase 2D is now complete as a local package-preparation and deployment-topology review phase:

```text
DEPLOYMENT_PACKAGE_SOURCE_COMMIT_LOCKED=true
DEPLOYMENT_PACKAGE_SECRET_SCAN_PASSED=true
DEPLOYMENT_PACKAGE_PHP_LINT_PASSED=true
DEPLOYMENT_PACKAGE_CONTROL_TESTS_PASSED=true
DEPLOYMENT_PACKAGE_MANIFEST_GENERATED=true
DEPLOYMENT_PACKAGE_MANIFEST_VERIFIED=true
DEPLOYMENT_TOPOLOGY_MAPPING_REVIEWED=true
ROLLBACK_PLAN_PREPARED=true
FTP_UPLOAD_PERFORMED=false
SERVER_CONFIGURATION_CHANGED=false
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
PHASE_2D_COMPLETE=true
```

## Resolved deployment topology

Domeneshop operator readback:

```text
hostname=mcp.atlas-ai.no
document_root=www/cm
```

Observed bridge:

```text
mcp.atlas-ai.no
  -> www/cm
       -> .htaccess
       -> index.php
       -> health.php
            -> protected source/runtime tree: /Custom Models/conta-mcp
```

The public `www/cm` bridge routes `/mcp` to `index.php` and `/health` to `health.php`; both PHP entrypoints reference the protected Conta MCP runtime. The protected runtime tree is not itself the public document root.

## Governing source evidence

The Phase 2D local package was built from:

```text
commit=7b0fee990cad291a5c01ee1468ba4329e0aeb543
payload_sha256=3e90e9a4f2e57625d2b1aa11fcb05cc181c4c2476510dff3213ce7caae8a1983
```

That payload remains valid evidence for the exact Phase 2D build only. It is not deployment authority and must not be used as-is after topology compatibility changes.

## Next authorized work unit

```text
PHASE_2E
TOPOLOGY_COMPATIBLE_FAIL_CLOSED_DEPLOYMENT_CORRECTION
```

Phase 2E is source-control/local preparation only. It does not authorize FTP upload, virtual-host change, server deployment, provider call, write enablement or sandbox mutation.

## Phase 2E objectives

1. Preserve the two-layer topology: public `www/cm` bridge plus protected runtime tree.
2. Define the exact production upload scope separately for the protected runtime and public bridge.
3. Preserve the current `/`, `/mcp`, and `/health` behavior.
4. Preserve existing legacy read/API routes in `www/cm` unless explicitly reviewed and approved for retirement.
5. Prevent direct web execution of operational `bin/` scripts.
6. Preserve server-only `config/conta_config.local.php`; never package or overwrite it.
7. Preserve runtime/audit/ledger state outside immutable source deployment scope.
8. Keep all execution gates fail-closed:

```text
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_actions=[]
allowed_write_organizations=[]
kill_switch_global_blocked=true
release_manifest_status=PENDING_REVIEW
sandbox_authorization_status=ABSENT_OR_PENDING
provider_call_performed=false
```

9. Prepare an atomic backup/cutover/rollback sequence for both layers.
10. Generate a new immutable deployment candidate and package after any source or bridge compatibility correction.
11. Re-run PHP lint, controlled-write tests, secret scan and manifest verification on the new candidate.

## Phase 2E acceptance target

Phase 2E should produce evidence that:

```text
TOPOLOGY_COMPATIBLE_SOURCE_PREPARED=true
PUBLIC_BRIDGE_COMPATIBILITY_VALIDATED=true
PROTECTED_RUNTIME_DEPLOYMENT_SCOPE_VALIDATED=true
SERVER_ONLY_CONFIG_PRESERVED=true
LEGACY_REQUIRED_READ_ROUTES_PRESERVED=true
BIN_WEB_EXECUTION_BLOCKED=true
NEW_DEPLOYMENT_CANDIDATE_LOCKED=true
NEW_DEPLOYMENT_MANIFEST_VERIFIED=true
ROLLBACK_SEQUENCE_VALIDATED=true
FTP_UPLOAD_PERFORMED=false
SERVER_CONFIGURATION_CHANGED=false
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
```

## Separate future authorization gates

Only after Phase 2E passes may the operator be presented with:

```text
FAIL_CLOSED_CANONICAL_DEPLOYMENT_AUTHORIZATION
```

That authorization remains separate from:

```text
SANDBOX_ONE_CALL_AUTHORIZATION
PRODUCTION_WRITE_AUTHORIZATION
```

Neither packaging nor deployment preparation authorizes provider execution or write enablement.
