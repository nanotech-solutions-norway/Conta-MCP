# Conta MCP fail-closed canonical deployment result — 2026-08-07

## Classification

`FAIL_CLOSED_CANONICAL_DEPLOYMENT_COMPLETE`

The operator explicitly authorized deployment of the immutable Phase 2E protected-runtime candidate only. The public bridge and server-only configuration were outside authorization scope.

## Authorized candidate

```text
source_commit=54b0d02d6eafe423a1d4485dfc88bdb85a28f5d0
payload_sha256=a7dc6133080c4c0fa70101da10624e012ea1c070e1ad9ddef5330be9ec2399b2
manifest_sha256=f19caca506e84772b96b59ba65c2dd9782990290b18523a533a1d63e0e57ea7a
deployment_target=/Custom Models/conta-mcp
payload_file_count=18
```

## Deployment result

All 18 authorized files were uploaded to the protected runtime and immediately downloaded again for SHA-256 verification. Every remote hash matched the immutable manifest.

```text
FAIL_CLOSED_CANONICAL_DEPLOYMENT_COMPLETE=true
DEPLOYED_FILE_COUNT=18
FTP_UPLOAD_PERFORMED=true
REMOTE_PAYLOAD_HASHES_VERIFIED=true
SERVER_RUNTIME_SOURCE_CHANGED=true
ROLLBACK_ATTEMPTED=false
```

Local deployment evidence:

```text
deployment_evidence_sha256=af660c9e36c566270867b1aa21e4ffea5a6c7be4db96fb021a9623f127543ad6
```

## Preserved topology and configuration

The deployment did not target or modify the public bridge or server-only configuration.

```text
PROTECTED_PARENT_HTACCESS_PRESERVED=true
PUBLIC_BRIDGE_CHANGED=false
SERVER_ONLY_CONFIG_PRESERVED=true
SERVER_ONLY_CONFIG_CONTENT_READ=false
SERVER_CONFIGURATION_CHANGED=false
```

The active topology remains:

```text
mcp.atlas-ai.no
  -> www/cm
     -> public bridge/front controller
        -> /Custom Models/conta-mcp protected runtime
```

## Immediate unauthenticated validation

```text
GET / = 200 JSON
GET /health = 200 JSON
GET /mcp = 200 JSON
UNAUTH_INITIALIZE_ROOT_HTTP=401
UNAUTH_INITIALIZE_MCP_HTTP=401
UNAUTHENTICATED_ACCESS_REJECTED=true
PROTECTED_PATH_HTTP_200_DETECTED=false
IMMEDIATE_FAIL_CLOSED_HTTP_VALIDATION_PASSED=true
```

The following paths all remained non-200:

```text
/app/
/config/
/storage/
/docs/
/tests/
/bin/
/admin/
/backups/
/public/
```

## Preserved fail-closed authority boundary

```text
PROVIDER_TOOL_CALLED=false
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
WRITE_TOOLS_ENABLED=false
MCP_AUTHENTICATION_PERFORMED=false
```

No provider call, sandbox mutation, write enablement, public-bridge change, or server-only configuration change was authorized or performed.

## Next required gate

```text
AUTHENTICATED_POST_DEPLOYMENT_MCP_CONTRACT_VALIDATION
```

The next validation is limited to authenticated MCP `initialize` and `tools/list`. It must not call `tools/call`, must not invoke any Conta read or write tool, and must not perform any provider operation. Expected canonical contract at the deployed source commit is MCP protocol `2025-06-18`, server name `conta-mcp-server`, server version `0.1.0`, six base read tools, preview tool visible when write preview is enabled, and execution tool absent while the effective execution gate is closed.