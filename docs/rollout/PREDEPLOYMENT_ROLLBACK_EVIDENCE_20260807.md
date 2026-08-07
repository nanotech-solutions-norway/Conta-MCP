# Conta MCP Fail-Closed Pre-Deployment Rollback Evidence — 07.08.2026

## Status

Pre-deployment rollback evidence capture completed successfully for the immutable Phase 2E protected-runtime-only deployment candidate.

No FTP upload, delete, rename, server configuration change, public bridge change, MCP authentication, provider call, sandbox mutation, or write enablement occurred.

## Immutable deployment candidate

```text
source_commit=54b0d02d6eafe423a1d4485dfc88bdb85a28f5d0
payload_sha256=a7dc6133080c4c0fa70101da10624e012ea1c070e1ad9ddef5330be9ec2399b2
manifest_sha256=f19caca506e84772b96b59ba65c2dd9782990290b18523a533a1d63e0e57ea7a
payload_file_count=18
```

The payload and manifest hashes were revalidated immediately before rollback evidence capture, and every ZIP file matched the manifest.

## Server-only configuration

```text
path=/Custom Models/conta-mcp/config/conta_config.local.php
present=true
content_read=false
content_printed=false
overwrite_allowed=false
```

The server-only configuration remains outside the deployment payload and must be preserved byte-for-byte.

## Protected-parent access-control evidence

```text
path=/Custom Models/conta-mcp/.htaccess
sha256=5a5ef6904976be4b8f0237e562f965510993b017ae167e48c5150342d5267f8b
hash_verified_against_phase2d_topology=true
```

## Protected runtime rollback inventory

### Existing deployed files backed up

```text
app/AuditLogger.php
app/bootstrap.php
app/Config.php
app/ContaClient.php
app/ContaTools.php
app/HttpClient.php
app/McpServer.php
app/Security.php
config/tool_policy.php
```

Their pre-deployment contents were downloaded into the local rollback evidence package and hashed.

### Candidate files absent before deployment

These paths do not currently exist on production and must therefore be deleted during rollback if a later deployment creates them and rollback is required:

```text
app/ApprovalEnvelopeVerifier.php
app/InvoiceDraftPreview.php
app/InvoiceDraftReadbackVerifier.php
app/ReleaseManifestGuard.php
app/SandboxAuthorizationGate.php
app/WriteDispatchPermit.php
app/WriteExecutionLedger.php
app/WriteKillSwitch.php
app/WritePolicy.php
```

Count:

```text
existing_overwrite_targets=9
new_candidate_paths=9
total_candidate_paths=18
```

## Public bridge rollback/hash evidence

The production public bridge remains outside the deployment payload and was re-hashed without modification:

```text
www/cm/.htaccess
sha256=6462faa6ce243f472509f7f2465719a995bde35d33cc25a7b80283fcdb842a1b

www/cm/index.php
sha256=80b779188debb156ce099a98ad724b575b77588e0cac44d0aed08f26522ae3eb

www/cm/health.php
sha256=fb3153021dd53e05c92fe76db5b3990e12e88225cda0118520d63948813dace5
```

All three match the previously validated Phase 2D topology evidence.

## Local rollback artifacts

```text
rollback_backup_sha256=e7e01f0f6de0b13c458516d8c012b624b952dceabc8bf48143407e59331f8d54
predeployment_evidence_sha256=0258d99d1cbc2ad378f5126b6ee39abb47cedbca5d533a4ad2e1aa17e983ab16
```

The local rollback ZIP contains the protected runtime files that currently exist, the protected-parent `.htaccess`, and the three public bridge files. The machine-readable evidence JSON records both existing and absent target paths.

## Safety state after evidence capture

```text
deployment_authorized=false
ftp_upload_performed=false
ftp_delete_performed=false
ftp_rename_performed=false
server_configuration_changed=false
public_bridge_changed=false
mcp_authentication_performed=false
provider_tool_called=false
provider_call_performed=false
sandbox_mutation_performed=false
write_tools_enabled=false
```

## Next gate

```text
EXPLICIT_OPERATOR_FAIL_CLOSED_DEPLOYMENT_AUTHORIZATION
```

The next action requires separate explicit operator authorization to deploy exactly the 18-file immutable protected-runtime candidate. Such authorization does not authorize provider execution, write enablement, sandbox mutation, public-bridge changes, server-only configuration changes, or any other production mutation outside the specified protected-runtime file set.
