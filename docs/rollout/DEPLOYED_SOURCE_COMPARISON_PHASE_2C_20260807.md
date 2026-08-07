# Conta MCP Deployed-Source Comparison Phase 2C — 00:10, 07.08.2026

## Classification

```text
DEPLOYED_SOURCE_CANONICAL_COMPARISON_COMPLETE=true
RUNTIME_DRIFT_REQUIRES_REVIEW
RUNTIME_DRIFT_CLASSIFICATION=LEGACY_SOURCE_DRIFT_CONFIRMED
DEPLOYED_SOURCE_MATCHES_CANONICAL_MAIN=false
```

The active Domeneshop runtime does not match canonical `main`.

## Canonical source

```text
repository=nanotech-solutions-norway/Conta-MCP
branch=main
deployment_candidate_commit=7b0fee990cad291a5c01ee1468ba4329e0aeb543
local_worktree_clean=true
head_equals_origin_main=true
```

The comparison was executed against the operator-verified clean checkout at:

```text
C:\Users\Ruben A. Meyer\source\repos\Conta-MCP
```

## Runtime source inventory

```text
active_endpoint=https://mcp.atlas-ai.no
ftps_runtime_root=/Custom Models/conta-mcp
server_runtime_source_file_count=11
canonical_runtime_source_file_count=22
server_directory_absent=public
```

The comparison used normalized text content and SHA-256 hashes. It did not read `config/conta_config.local.php`, print secrets, upload files, change server configuration, authenticate to MCP, invoke a provider tool, perform a provider call or mutate a sandbox.

## File comparison matrix

| File | Server normalized SHA-256 | Canonical normalized SHA-256 | Result |
|---|---|---|---|
| `.htaccess` | `5a5ef6904976be4b8f0237e562f965510993b017ae167e48c5150342d5267f8b` | `8656e6a2be8fb87d4c10eff47581530d1f40d0eae1ae53e734a63230a58f7fd3` | `DIFFERENT` |
| `app/ApprovalEnvelopeVerifier.php` | — | `15be802a1fcdf5608d58ac187d0c783f36b70ba7ef764a69f23da8e5e1ebcd51` | `CANONICAL_ONLY` |
| `app/AuditLogger.php` | `94b3e128cd99f9f031f9692916403148aa1e1d8585cf3e246366110fac94d35d` | `7be47f4d66b9e0bea5a94a436dbbb343ca9bf394705db17a16750f40da4c12fb` | `DIFFERENT` |
| `app/bootstrap.php` | `65a353d0c74c2c9803eda3b9dfa72b530a57bcb4d4cfa43f99986cffbc9d3443` | `54d1b4aa442f3529a0b72885aa6f73d6e106c1bae33c01bf43b2b6f04a4297a9` | `DIFFERENT` |
| `app/Config.php` | `052441da15c78b4c82d289531220a8078c55de947a902a4a35514ca0f8d250b0` | `4781d0b85a44ecff396e53f8db5775f336eb51815c0a98f8d12de268c626c511` | `DIFFERENT` |
| `app/ContaClient.php` | `7a57dd4e59c880241b5fcea696c9ac516a13735fe9e4b8155b1d23e8e5a7c53d` | `c9b4633749409af2a6b76145593839cd72e9f6ffc6d15feef13e2c503fb1bbe1` | `DIFFERENT` |
| `app/ContaTools.php` | `263cc581fb2a9954f3ea6d6d9559a5921704a0b905989ffa232039ffad322404` | `5626cc5b4374e510cdc989df59b11c4fd3fe04d54fac3735a6e5464619f7cd18` | `DIFFERENT` |
| `app/HttpClient.php` | `e088dcb506d6b670a6103828f3cf2344dcbfb56b2b8f9dbf85e96509cdac0dd9` | `e088dcb506d6b670a6103828f3cf2344dcbfb56b2b8f9dbf85e96509cdac0dd9` | `MATCH` |
| `app/InvoiceDraftPreview.php` | — | `d976b54b1ddc55a5399367ce35b35144d687d462cbf1217a7d5d469572c54fc8` | `CANONICAL_ONLY` |
| `app/InvoiceDraftReadbackVerifier.php` | — | `43dbf466cfe0fc8f398a3cf42f739c3c5e71b8941ea03aca21c5d5e09d367fa3` | `CANONICAL_ONLY` |
| `app/McpServer.php` | `207c872f0d9b523ab9238f499a5922beb51c14c7332f7b894367ba7beaf13ae4` | `6b12f5fb849e4055aef8342117f5efdd3a59433c1a9e6541440033ef9e1ca54a` | `DIFFERENT` |
| `app/ReleaseManifestGuard.php` | — | `35989dd9678769b02c33c235a751c7dfea98139a5befb0568b27c37d5bc3a63f` | `CANONICAL_ONLY` |
| `app/SandboxAuthorizationGate.php` | — | `0c62c37e2fafb0a518be6b6dc4f7682cc9999a601dbad05d3cfbee4dfd469c71` | `CANONICAL_ONLY` |
| `app/Security.php` | `8ee34e4a335564d817e434da18bd1804ee3ea2087b273141a27e21efb00de0db` | `f7c3ba277387c01a0a41d15af5136c19eca59cb90cd966dae55c25a7d5b394d0` | `DIFFERENT` |
| `app/WriteDispatchPermit.php` | — | `1bbf1dee22de280f8902dd65359b218f5e2f7aee4eae8e1d41af7293b90e5fcf` | `CANONICAL_ONLY` |
| `app/WriteExecutionLedger.php` | — | `86639a2eb588b4c4d9402027f2f957da462f665b306408a8fed1b51e6332b008` | `CANONICAL_ONLY` |
| `app/WriteKillSwitch.php` | — | `27df547e9330a8342e41fc665f58caed3b19b53bae2ed2d3635ec5bf291bd373` | `CANONICAL_ONLY` |
| `app/WritePolicy.php` | — | `ba455e6c969541ab9ee96a7318288ce4a5b8addcb1c9f2bcc44849586361e472` | `CANONICAL_ONLY` |
| `config/conta_config.example.php` | `4d2e14af70e58ce1e867740844a8cd868b6c994c564d6eacf6e928c76d048eb5` | `3207b9fdadee4b0ab9dfa85a1eee6b54d606273bab45e84d658b3f1dc670644a` | `DIFFERENT` |
| `config/tool_policy.php` | `cf24a7ca4b28c13be19c3a9c3b1aee3e0764f62cd7fc29f761e33092a81a386e` | `1b2d27fb5c8d1098b73810d0036636284fde8f5b1a1388c3bfde8c2088cabe7d` | `DIFFERENT` |
| `public/health.php` | — | `1f4ddc0dba0fc9c22de67ed1927b0e5190341b2fe09f849c3bddda5ca8193415` | `CANONICAL_ONLY` |
| `public/index.php` | — | `d8fdcd0484c5c4c261bad6ce788c475d14bee6899644fa527d810409de7ec5c2` | `CANONICAL_ONLY` |

## Summary counts

```text
MATCH_COUNT=1
DIFFERENT_COUNT=10
SERVER_ONLY_COUNT=0
CANONICAL_ONLY_COUNT=11
```

Only `app/HttpClient.php` matches canonical `main`. The deployed source is the confirmed legacy runtime rather than a partial copy of the current controlled-write implementation.

## Security and release implications

1. The active runtime lacks the canonical preview, release-manifest, sandbox-authorization, write-policy, kill-switch, dispatch-permit, execution-ledger and readback-verification controls.
2. The active runtime's existing `enable_write_tools=false` legacy gate remains the verified containment boundary until replacement.
3. The absent deployed `public` directory creates a deployment-topology mismatch that must be resolved explicitly before any upload.
4. The canonical deployment candidate must not be overlaid directly onto the active tree without a rollback package, server-only configuration preservation, path mapping and post-deployment fail-closed validation.
5. Release-manifest approval remains blocked until the deployed candidate and runtime topology are controlled.

## Preserved safety state

```text
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
kill_switch_global_blocked=true
config_local_file_read=false
secret_values_printed=false
local_worktree_changed=false
ftp_upload_performed=false
server_configuration_changed=false
mcp_authentication_performed=false
provider_tool_called=false
provider_call_performed=false
sandbox_mutation_performed=false
```

## Acceptance result

```text
PHASE_2C_ACCEPTED=true
RUNTIME_INVENTORY_ACCEPTANCE=RUNTIME_DRIFT_REQUIRES_REVIEW
DIRECT_CANONICAL_DEPLOYMENT_AUTHORIZED=false
WRITE_ENABLEMENT_AUTHORIZED=false
SANDBOX_ONE_CALL_AUTHORIZED=false
```

## Progress record

```text
stable_read_only_production_target_verified_gates=5/8
stable_read_only_production_target_progress=62.5%
controlled_invoice_draft_target_verified_gates=5/12
controlled_invoice_draft_target_progress=41.7%
current_process=PHASE_2C_COMPLETE
next_gate=FAIL_CLOSED_CANONICAL_DEPLOYMENT_PACKAGE_PREPARATION
```

These percentages are evidence tracking only and grant no execution authority.
