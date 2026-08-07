# Conta MCP Phase 2E Topology-Compatible Fail-Closed Deployment Plan — 07.08.2026

## Objective

Prepare a new immutable deployment candidate/package that fits the verified Domeneshop topology without modifying the public `www/cm` bridge during the first canonical fail-closed deployment.

This phase is preparation only. No FTP upload, virtual-host change, provider call, write enablement or sandbox mutation is authorized.

## Verified topology

```text
mcp.atlas-ai.no
  -> document root: www/cm
       -> existing .htaccess
       -> existing index.php
       -> existing health.php
            -> protected runtime: /Custom Models/conta-mcp
```

The public bridge is already live and working for `/`, `/mcp`, `/health`, legacy read/API routes and OpenAPI/privacy resources. The bridge must therefore be treated as server-owned compatibility infrastructure for the first deployment.

## Phase 2E deployment strategy

Use a protected-runtime-only first deployment.

### Public bridge: preserve in place

Do not upload or replace any `www/cm` files in the first fail-closed canonical deployment.

Preserve at minimum:

```text
www/cm/.htaccess
www/cm/index.php
www/cm/health.php
www/cm/api.php
www/cm/api_v2.php
www/cm/openapi.json
www/cm/openapi-v2-layer1-readonly.json
www/cm/privacy.html
```

Any additional existing `www/cm` files are server-owned unless separately inventoried and approved.

### Protected runtime: canonical replacement scope

Target:

```text
/Custom Models/conta-mcp
```

Candidate source scope:

```text
app/*.php
config/tool_policy.php
```

Candidate support/example files may be retained in local evidence but are not required for production upload.

### Explicit production-upload exclusions

Do not upload from the repository into the protected runtime during the first deployment:

```text
.htaccess
public/
bin/
tests/
docs/
.github/
AGENTS.md
README.md
SECURITY.md
mcp-client-config.example.json
config/conta_config.example.php
config/conta_config.local.php
config/*.example.json
storage/audit.log
storage/write-execution-ledger.jsonl
storage/*.json containing approvals, manifests, authorizations or runtime state
```

Rationale:

- root repository `.htaccess` represents a different documented public layout and must not overwrite the active protected-parent deny rule;
- `public/` is superseded by the existing `www/cm` bridge in this topology;
- `bin/` contains operational scripts and must not be web-deployed;
- server-only production configuration must remain untouched;
- tests/docs/repository metadata are not runtime dependencies;
- generated control/audit/ledger state must not be replaced by source packaging.

## Server-owned configuration

The existing file:

```text
/Custom Models/conta-mcp/config/conta_config.local.php
```

must be preserved byte-for-byte by deployment. It must not be read for secret values, included in a package, replaced, renamed or deleted.

## Required fail-closed state

The new runtime must continue to require:

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

If the existing production config does not supply a new control value, application defaults must fail closed.

## Compatibility assumptions requiring local validation

The current `www/cm/index.php` references the protected runtime bootstrap and MCP server. The new protected runtime package must therefore verify locally that:

1. `app/bootstrap.php` loads all required canonical control classes;
2. `McpServer` initializes without requiring repository `public/` files;
3. the existing bridge contract remains compatible with the canonical bootstrap/MCP server;
4. `health.php` can continue to use the protected bootstrap without provider execution;
5. no operational `bin/` script is required for normal HTTP runtime operation.

## New deployment candidate requirement

The Phase 2D evidence package:

```text
commit=7b0fee990cad291a5c01ee1468ba4329e0aeb543
sha256=3e90e9a4f2e57625d2b1aa11fcb05cc181c4c2476510dff3213ce7caae8a1983
```

must not be deployed. Phase 2E must generate a new package and manifest from the post-Phase-2D canonical source state.

Phase 2E branch base:

```text
main_merge_commit=7538bcd371b1217b77e3a6788b55d70c9b047888
branch=phase2e/topology-compatible-deployment-20260807
```

## Required local package structure

The production payload should contain only paths relative to the protected runtime root, for example:

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

The package builder must derive the final `app/*.php` set from the locked candidate rather than relying on this prose list alone.

## Pre-deployment backup scope

Before any later authorized upload, a separate deployment procedure must back up:

1. every existing deployed runtime source file that will be overwritten;
2. the active protected-parent `.htaccess` as rollback evidence, even though it will not be overwritten;
3. sanitized metadata for `config/conta_config.local.php` without secret values;
4. current public bridge hashes for `www/cm/.htaccess`, `www/cm/index.php`, and `www/cm/health.php` without modifying them.

## Post-deployment validation sequence

A later authorized deployment must stop immediately if any of these fail:

1. protected paths remain non-public;
2. `GET /health` returns expected service identity and fail-closed configuration state;
3. unauthenticated initialize remains rejected;
4. authenticated initialize succeeds;
5. `tools/list` exposes the expected canonical read/preview contract;
6. write execution remains disabled;
7. no provider call occurs;
8. `/`, `/mcp`, legacy required read/API routes and existing OpenAPI/privacy resources remain available as expected.

## Phase 2E acceptance markers

```text
TOPOLOGY_COMPATIBLE_DEPLOYMENT_SCOPE_DEFINED=true
PUBLIC_BRIDGE_UPLOAD_REQUIRED=false
PUBLIC_BRIDGE_PRESERVE_IN_PLACE=true
PROTECTED_RUNTIME_UPLOAD_SCOPE_DEFINED=true
ROOT_HTACCESS_UPLOAD_ALLOWED=false
PUBLIC_DIRECTORY_UPLOAD_ALLOWED=false
BIN_DIRECTORY_UPLOAD_ALLOWED=false
SERVER_ONLY_CONFIG_PRESERVE_REQUIRED=true
NEW_DEPLOYMENT_CANDIDATE_REQUIRED=true
FTP_UPLOAD_PERFORMED=false
SERVER_CONFIGURATION_CHANGED=false
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
```

The next execution step is a local Phase 2E package build/validation from a clean checkout of current `main` or an explicitly locked candidate derived from this branch. Deployment remains separately gated.
