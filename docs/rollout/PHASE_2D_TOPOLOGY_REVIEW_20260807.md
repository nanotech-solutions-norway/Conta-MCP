# Conta MCP Phase 2D Deployment Topology Review — 11:46, 07.08.2026

## Classification

```text
DEPLOYMENT_TOPOLOGY_MAPPING_REVIEWED=true
PHASE_2D_TOPOLOGY_REVIEW_COMPLETE=true
PHASE_2D_COMPLETE=true
NEXT_REQUIRED_GATE=PHASE_2E_TOPOLOGY_COMPATIBLE_FAIL_CLOSED_DEPLOYMENT_CORRECTION
```

The active Domeneshop deployment topology has now been mapped sufficiently to close the Phase 2D topology-review gate. No upload, virtual-host change, MCP authentication, provider call, sandbox mutation or write-tool activation was performed.

## Authoritative hostname mapping

Operator readback from Domeneshop hosting configuration:

```text
hostname=mcp.atlas-ai.no
document_root=www/cm
```

The protected source/runtime tree at `/Custom Models/conta-mcp` is therefore not itself the hostname document root.

## Active public document root

Read-only FTPS inventory of `www/cm` established:

```text
WWW_CM_ENTRY_COUNT=22
WWW_CM__HTACCESS_PRESENT=true
WWW_CM_INDEX_PHP_PRESENT=true
WWW_CM_HEALTH_PHP_PRESENT=true
WWW_CM_MCP_PHP_PRESENT=false
WWW_CM_ROUTER_PHP_PRESENT=false
WWW_CM_STATUS_PHP_PRESENT=false
```

The public document root is a bridge/front-controller layer rather than a copy of the protected runtime source tree.

## Public routing

Observed `www/cm/.htaccess`:

```text
sha256=6462faa6ce243f472509f7f2465719a995bde35d33cc25a7b80283fcdb842a1b
```

Reviewed routing directives include:

```text
Options -Indexes -MultiViews
DirectoryIndex index.php
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [E=HTTP_AUTHORIZATION:%1]
RewriteRule ^health/?$ health.php [L,QSA]
RewriteRule ^mcp/?$ index.php [L,QSA]
```

The same public `.htaccess` also exposes existing legacy read/API routes for `api/v2`, `api/health`, customer and invoice reads, OpenAPI documents and privacy content. Eight non-comment directives were intentionally not printed during the safe review. Their contents were not required to establish the document-root bridge and were not used as deployment authority.

## Bridge relationship to protected runtime

`www/cm/index.php`:

```text
sha256=80b779188debb156ce099a98ad724b575b77588e0cac44d0aed08f26522ae3eb
references_conta_mcp=true
references_custom_models=true
references_bootstrap=true
references_mcp_server=true
```

`www/cm/health.php`:

```text
sha256=fb3153021dd53e05c92fe76db5b3990e12e88225cda0118520d63948813dace5
references_conta_mcp=true
references_custom_models=true
references_bootstrap=true
references_mcp_server=false
```

This establishes the active deployment topology:

```text
mcp.atlas-ai.no
  -> Domeneshop document root: www/cm
       -> index.php / health.php bridge
            -> protected runtime: /Custom Models/conta-mcp
```

## HTTP containment evidence

Observed public behavior:

```text
GET / = 200 JSON
GET /health = 200 JSON
GET /mcp = 200 JSON
GET /public/ = 404
UNAUTH_INITIALIZE_ROOT_HTTP=401
UNAUTH_INITIALIZE_MCP_HTTP=401
ACTIVE_ROUTE_CLASSIFICATION=DUAL_PROTECTED_MCP_ROUTES
```

Protected paths did not return HTTP 200:

```text
/app/
/config/
/storage/
/docs/
/tests/
/bin/
/admin/
/backups/
```

## Deployment implication

The previously generated Phase 2D package from commit `7b0fee990cad291a5c01ee1468ba4329e0aeb543` remains valid local build evidence, but it must not be uploaded as a flat replacement of `www/cm` or `/Custom Models/conta-mcp`.

The production-safe deployment model must preserve the two-layer topology:

1. protected canonical runtime source under `/Custom Models/conta-mcp` (or an explicitly versioned protected sibling used for atomic cutover);
2. a minimal `www/cm` bridge/front-controller layer that continues to expose `/`, `/mcp` and `/health` while keeping protected source paths non-public;
3. server-only `config/conta_config.local.php` preserved outside the deployment package and never overwritten;
4. all write/execution gates fail-closed.

The existing `www/cm` bridge also contains legacy routes outside the canonical MCP package. Those routes must be preserved or separately retired through reviewed compatibility work; they must not be silently deleted by deployment.

## Required Phase 2E correction

Phase 2E must prepare, locally and in source control, a topology-compatible deployment plan/package that:

- preserves the `www/cm` public bridge contract;
- does not flatten protected runtime source into the public document root;
- preserves existing required legacy read/API endpoints unless explicitly approved for retirement;
- explicitly prevents browser execution of operational `bin/` scripts;
- preserves server-only configuration and generated runtime state;
- provides an atomic rollback/cutover sequence;
- retains `enable_write_tools=false`, `runtime_write_blocked=true`, `execution_allowed=false`, `production_write_approved=false`, and `kill_switch_global_blocked=true`;
- performs no provider mutation.

A new immutable deployment candidate/package is required after any source or bridge compatibility correction. The Phase 2D payload hash must not be reused as authority for a modified deployment.

## Preserved safety state

```text
FTP_UPLOAD_PERFORMED=false
SERVER_CONFIGURATION_CHANGED=false
MCP_AUTHENTICATION_PERFORMED=false
PROVIDER_TOOL_CALLED=false
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
WRITE_TOOLS_ENABLED=false
```

## Phase 2D acceptance

All Phase 2D acceptance gates are now satisfied as planning/evidence gates:

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

No deployment authorization is implied by this closure.
