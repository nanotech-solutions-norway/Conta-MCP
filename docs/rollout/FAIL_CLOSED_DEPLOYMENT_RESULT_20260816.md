# Conta MCP Fail-Closed Deployment Result — 2026-08-16

## Classification

`FAIL_CLOSED_CANONICAL_DEPLOYMENT_COMPLETE`

The operator authorized deployment of the exact immutable protected-runtime candidate recorded in `FAIL_CLOSED_DEPLOYMENT_AUTHORIZATION_20260816.md`. No public bridge, server-only configuration, provider data or production write gate was authorized for change.

## Deployed candidate

```text
candidate_source_commit=7d97a6330e4aff5a6e251ad19d717d7408cf3825
protected_runtime_zip_sha256=2da8802035e4b9989c76f493359abe2587e84896e9e4c5c1911d52748702f6cc
target_runtime_root=/Custom Models/conta-mcp
payload_runtime_file_count=18
```

Deployment control:

```text
repository=nanotech-solutions-norway/Domeneshop---MCP-
control_merge_commit=a240421d7f9919cf49b37012e51fde0040936491
workflow_run=31944830604
workflow_conclusion=success
```

## Backup and deployment evidence

The protected workflow completed the mandatory server-side backup before uploading any candidate file. It then uploaded only the 18 authorized protected-runtime paths and verified every remote SHA-256 against the immutable candidate.

```text
PREDEPLOYMENT_SERVER_BACKUP_COMPLETE=true
BACKUP_TARGET_COUNT=18
REMOTE_PAYLOAD_HASHES_VERIFIED=true
PUBLIC_BRIDGE_PRESERVED=true
SERVER_ONLY_CONFIG_PRESERVED=true
ROLLBACK_INVOKED=false
```

The rollback package remains server-side under the protected backup root created by the workflow. Its path was intentionally not copied into public repository evidence.

## Immediate fail-closed validation

```text
IMMUTABLE_CANDIDATE_AND_PUBLIC_CONTRACT_VALIDATED=true
IMMEDIATE_FAIL_CLOSED_HTTP_VALIDATION_PASSED=true
HEALTH_STATUS=ok
HEALTH_SERVICE=conta-mcp-server
WRITE_PREVIEW_ENABLED=true
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
EXECUTION_ALLOWED=false
PRODUCTION_WRITE_APPROVED=false
ALLOWED_WRITE_ACTION_COUNT=0
ALLOWED_WRITE_ORGANIZATION_COUNT=0
```

`/`, `/health` and `/mcp` returned HTTP 200. Protected `/app/`, `/config/` and `/storage/` paths remained non-public. Unauthenticated MCP initialization remained rejected inside the deployment workflow.

## Preserved authority boundary

```text
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
PUBLIC_BRIDGE_CHANGED=false
SERVER_ONLY_CONFIG_CHANGED=false
SANDBOX_DUPLICATE_DRAFT_CREATED=false
```

## Next gate

Authenticated MCP `initialize` and `tools/list` validation was required immediately after deployment and is recorded separately in `AUTHENTICATED_POST_DEPLOYMENT_CONTRACT_20260816.md`.
