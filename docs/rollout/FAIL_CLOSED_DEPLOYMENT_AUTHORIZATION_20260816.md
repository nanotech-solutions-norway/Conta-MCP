# Conta MCP Fail-Closed Deployment Authorization — 16.08.2026

## Operator authorization

At 11:31 Europe/Oslo on 16.08.2026, the operator explicitly authorized:

```text
AUTHORIZE FAIL-CLOSED DEPLOYMENT
```

This authorization applies only to the exact validated fail-closed deployment candidate described below.

## Authorized immutable candidate

```text
repository=nanotech-solutions-norway/Conta-MCP
candidate_source_commit=7d97a6330e4aff5a6e251ad19d717d7408cf3825
protected_runtime_zip=Conta_MCP_Protected_Runtime_7d97a6330e4a.zip
protected_runtime_zip_sha256=2da8802035e4b9989c76f493359abe2587e84896e9e4c5c1911d52748702f6cc
target_runtime_root=/Custom Models/conta-mcp
payload_runtime_file_count=18
```

The candidate was built by GitHub Actions run `31915890577`, which completed successfully, and is preserved in the controlled-write Google Drive evidence folder as file ID `1s2XU0StVtnh7pUqZ0le0H0OM5o-LvdC9`.

## Authorized deployment scope

Deployment may replace only the 18 protected-runtime files in the validated package:

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

## Mandatory preservation

The deployment must preserve unchanged:

```text
www/cm
/Custom Models/conta-mcp/.htaccess
/Custom Models/conta-mcp/config/conta_config.local.php
generated storage/audit/ledger/authorization state
Domeneshop virtual-host/document-root mapping
```

Before overwrite, the current versions of all existing target files must be backed up and the rollback package/evidence recorded.

## Required fail-closed state after deployment

```text
enable_write_preview=true
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_organization_ids=[]
allowed_write_actions=[]
kill_switch_global_blocked=true
PRODUCTION_WRITE_AUTHORIZED=false
```

The deployed production runtime must not receive sandbox API credentials, sandbox authorization packets, temporary one-use approvals/nonces, sandbox ledgers, or any sandbox-only execution state.

## Explicitly not authorized by this deployment authorization

```text
PRODUCTION_PROVIDER_MUTATION=false
PRODUCTION_INVOICE_DRAFT_CREATE=false
PRODUCTION_INVOICE_SEND=false
PRODUCTION_WRITE_ENABLEMENT=false
VIRTUAL_HOST_CHANGE=false
PUBLIC_BRIDGE_CHANGE=false
SERVER_ONLY_CONFIG_REPLACEMENT=false
SANDBOX_DUPLICATE_DRAFT_CREATE=false
```

The user's standing authorization to continue sandbox invoice-draft validation until successfully complete remains separate from production provider mutation authority.

## Immediate post-deployment validation

After upload, validate in order:

1. remote hashes for all 18 protected-runtime files against the candidate;
2. `/`, `/health`, and `/mcp` return the expected service responses;
3. unauthenticated MCP initialize remains rejected;
4. protected internal paths remain non-public;
5. authenticated initialize succeeds;
6. authenticated `tools/list` contains the six read-only tools plus preview only;
7. execution/write tool is not exposed;
8. health reports write tools disabled, runtime writes blocked, execution disallowed, production write approval false;
9. read-only Conta calls and invoice-draft preview operate without mutation;
10. production mutation paths refuse execution.

Rollback the 18 target paths from the pre-deployment backup if runtime/hash/contract verification fails.

## Current execution state

```text
DEPLOYMENT_AUTHORIZED=true
DEPLOYMENT_PERFORMED=false
PROVIDER_MUTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
NEXT_REQUIRED_ACTION=BACKUP_CURRENT_RUNTIME_AND_DEPLOY_EXACT_CANDIDATE
```
