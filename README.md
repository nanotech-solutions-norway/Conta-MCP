# Conta MCP Server

**Project:** Conta-MCP  
**Target runtime:** Domeneshop PHP hosting  
**Default endpoint:** `https://www.nanoconcept.no/conta-mcp/mcp`  
**Rollout mode:** Sandbox first; production write program not implemented  

This repository contains a dependency-free PHP MCP-style JSON-RPC server for connecting an AI orchestrator to Conta through Conta's official REST API.

## Current status

```text
CONTROLLED_WRITE_FOUNDATION_MERGED
POST_MERGE_CI_PASSED
RUNTIME_DEPLOYMENT_NOT_VERIFIED
SANDBOX_ONE_CALL_NOT_AUTHORIZED
PRODUCTION_WRITE_PROGRAM_NOT_IMPLEMENTED
```

Canonical merged foundation commit:

```text
689cf28d943b761e26d9d1a7ef2eaddf5b78cc07
```

The merged source includes read-only tools, a non-executing invoice-draft preview, and a fail-closed controlled-write path for exactly one authorized sandbox invoice-draft creation. Source availability does not grant execution authority.

## Security position

This repository is public. Never commit:

- Conta API keys;
- MCP bearer tokens;
- approval signing keys;
- `.env` or `conta_config.local.php`;
- organization identifiers used for live authorization;
- customer, invoice, voucher, bank or accounting data;
- approval envelopes, sandbox authorization packets or runtime ledgers containing environment-specific values;
- raw payload or response logs.

Store runtime credentials and environment-specific control files only on the protected server.

## Architecture

```text
ChatGPT / Atlas AI orchestrator
        ↓
Authenticated MCP-style JSON-RPC endpoint
        ↓
Domeneshop PHP runtime
        ↓
Runtime policy and controlled-write gates
        ↓
Conta REST API client
        ↓
Conta API Gateway
```

## Tool model

### Read-only tools

- `conta_health_check`
- `conta_list_organizations`
- `conta_list_customers`
- `conta_get_customer`
- `conta_list_invoices`
- `conta_get_invoice`

### Non-executing preview

- `conta_preview_invoice_draft`

The preview normalizes the proposed payload and returns a deterministic SHA-256 payload hash. It does not call Conta or mutate provider state.

### Controlled sandbox execution

- `conta_create_invoice_draft`

The execution tool is absent from `tools/list` unless every effective execution gate is valid and open. Direct invocation also fails closed.

Required controls include:

- sandbox environment;
- write tools enabled;
- runtime write block removed;
- execution explicitly allowed;
- action and organization allowlists;
- current approved release manifest;
- matching repository, provider-schema, route-map and runtime-file hashes;
- open global/action kill switch;
- signed payload-bound sandbox authorization;
- signed one-use approval envelope;
- exact method and route match;
- approval expiry and maximum TTL;
- authorization-ID, nonce and idempotency replay prevention;
- mandatory post-create readback verification.

The one-call harness has no retry loop and refuses non-sandbox execution.

## Default fail-closed state

```text
enable_write_preview=true
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_organization_ids=[]
allowed_write_actions=[]
approved_release_manifest=missing_or_pending
write_kill_switch=blocked_or_missing
sandbox_authorization=missing_or_pending
```

Production execution is explicitly refused because a production write program has not been implemented.

## Repository structure

```text
Conta-MCP/
├── .github/                 # CI, security and repository controls
├── app/                     # MCP server, Conta client and runtime policy classes
├── bin/                     # Readiness, manifest, signing and one-call CLI tools
├── config/                  # Fail-closed examples and tool policy
├── docs/                    # Deployment, security and rollout evidence
│   └── rollout/             # Controlled-write and post-merge records
├── public/                  # Health and MCP endpoint entrypoints
├── storage/                 # Server-only runtime state; committed content excluded
├── tests/                   # Smoke and controlled-write validation
├── .htaccess
├── .gitignore
├── mcp-client-config.example.json
├── README.md
└── SECURITY.md
```

## Validation

Run from the repository root with PHP 8.2 or a compatible validated runtime:

```bash
find app bin config public tests -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/controlled-write-foundation.php
php tests/remaining-control-paths.php
```

Expected markers:

```text
CONTROLLED_WRITE_FOUNDATION_TESTS_PASSED
REMAINING_CONTROL_PATHS_TESTS_PASSED
```

A successful source test does not authorize deployment or a provider call.

## Rollout sequence

1. Synchronize and validate canonical `main` locally.
2. Complete a read-only inventory of the active Domeneshop runtime.
3. Compare deployed runtime hashes against the canonical repository.
4. Deploy only with all execution gates closed.
5. Validate health, MCP initialization, authenticated `tools/list`, read-only tools and preview-only behavior.
6. Refresh current official provider schema, routes, scopes and sandbox/test-company evidence.
7. Generate and review an observed release manifest.
8. Prepare signed sandbox authorization and one-use approval only for an approved payload hash.
9. Obtain a separate explicit operator authorization for exactly one sandbox mutation.
10. Execute once, verify readback, consume replay controls and immediately restore the blocked state.

## Current rollout records

- `docs/rollout/POST_MERGE_BASELINE_20260805.md`
- `docs/rollout/DOCUMENTATION_DRIFT_REGISTER_20260805.md`
- `docs/rollout/RUNTIME_INVENTORY_CHECKLIST_20260805.md`
- `docs/rollout/RECOMMENDED_NEXT_ACTION_20260805.md`
- `docs/rollout/OPERATOR_GATE_RUNBOOK.md`
- `docs/rollout/PROVIDER_EVIDENCE_REGISTER.md`

Historical phase and draft-branch records remain evidence of their original state. Use the post-merge baseline for current status.

## Execution boundary

Do not deploy, enable write tools, approve a release manifest, open the kill switch or call a write-capable Conta route without the applicable reviewed evidence and explicit operator authorization.
