# Conta MCP Server

**Project:** Conta-MCP  
**Target runtime:** Domeneshop PHP hosting  
**Active endpoint:** `https://mcp.atlas-ai.no`  
**Superseded endpoint:** `https://www.nanoconcept.no/conta-mcp/mcp` (GitHub Pages 404; not the active PHP runtime)  
**Rollout mode:** Sandbox controlled-write validated; production write program not implemented  

This repository contains a dependency-free PHP MCP-style JSON-RPC server for connecting an AI orchestrator to Conta through Conta's official REST API.

## Current status

```text
CONTROLLED_WRITE_FOUNDATION_MERGED
POST_MERGE_CI_PASSED
LOCAL_DESKTOP_VALIDATION_VERIFIED
PUBLIC_RUNTIME_INVENTORY_PHASE_2A_VERIFIED
AUTHENTICATED_RUNTIME_INVENTORY_PHASE_2B_VERIFIED
LEGACY_PRODUCTION_RUNTIME_0_3_0_IDENTIFIED
LEGACY_WRITE_GATE_CONTAINMENT_VERIFIED
SANDBOX_INVOICE_DRAFT_CREATE_VERIFIED
SANDBOX_READBACK_VERIFICATION_VERIFIED
SANDBOX_REPLAY_PROTECTION_VERIFIED
SANDBOX_KILL_SWITCH_CLOSURE_VERIFIED
SANDBOX_RUNTIME_PATCHES_BAKED_INTO_SOURCE
RUNTIME_DEPLOYMENT_COMMIT_NOT_VERIFIED
PRODUCTION_WRITE_AUTHORIZED_FALSE
PRODUCTION_WRITE_PROGRAM_NOT_IMPLEMENTED
```

The first controlled sandbox invoice-draft write was created and independently verified on 2026-08-16. The exact validated payload uses `vatCode=high`, `lineNo=1`, and canonical payload SHA-256 `79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7`.

Definitive evidence is recorded in:

`docs/rollout/SANDBOX_INVOICE_DRAFT_VERIFIED_SUCCESS_20260816.md`

The active production endpoint was previously identified as legacy runtime version `0.3.0-mcp-atlas-ai`. That runtime remains deployment-drift evidence and must not be treated as equivalent to canonical `main` until a separately authorized fail-closed deployment and runtime-parity verification are completed.

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

Store runtime credentials and environment-specific control files only on the protected server or protected GitHub environment.

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

In canonical source, the execution tool is absent from `tools/list` unless every effective execution gate is valid and open. Direct invocation also fails closed.

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

The sandbox harness performs at most one provider mutation per invocation. The protected workflow may retry only under explicitly classified transient conditions and only when GET post-state proves that no object exists. GitHub workflow reruns with `run_attempt > 1` are rejected by the harness.

The successful first sandbox mutation additionally established that the tested sandbox organization required Conta's invoice-with-VAT setting to be enabled before `vatCode=high` was accepted.

## Readback compatibility rules

The verified Conta readback may:

- return JSON numeric `1.0` as PHP integer `1`; integer/float values are therefore compared numerically only when both values are actual numeric scalars;
- omit `registrationSource`; omission is tolerated, but if the field is returned its value is still verified.

Numeric strings are not treated as numeric-equivalent, and substantive field mismatches remain failures. Dedicated regression tests cover these boundaries.

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
├── .github/                 # CI, security and protected validation workflows
├── app/                     # MCP server, Conta client and runtime policy classes
├── bin/                     # Readiness, manifest, signing and CLI tools
├── config/                  # Fail-closed examples and tool policy
├── docs/                    # Deployment, security and rollout evidence
│   └── rollout/             # Controlled-write and post-merge records
├── public/                  # Health and MCP endpoint entrypoints
├── storage/                 # Server-only runtime state; committed content excluded
├── tests/                   # Smoke, readback and controlled-write validation
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
php tests/invoice-draft-readback-verifier.php
php tests/remaining-control-paths.php
php -l .github/scripts/conta-sandbox-invoice-draft-one-call.php
```

Expected markers include:

```text
CONTROLLED_WRITE_FOUNDATION_TESTS_PASSED
INVOICE_DRAFT_READBACK_VERIFIER_TESTS_PASSED
REMAINING_CONTROL_PATHS_TESTS_PASSED
```

A successful source test does not authorize deployment or a provider call.

## Rollout sequence from current state

1. Merge post-success stabilization only after CI/security validation passes.
2. Retain the verified sandbox draft as evidence; do not create another draft or perform cleanup without separate authorization.
3. Select the stabilized `main` commit as the next deployment candidate.
4. Reconcile the active Domeneshop runtime against that commit.
5. Prepare a fail-closed deployment preserving all production write gates closed.
6. Obtain separate operator authorization before deployment.
7. After deployment, verify health, authenticated initialization, `tools/list`, read-only tools, preview-only behavior, runtime version/hashes, and production write refusal.
8. Design and review a separate production-write program only after fail-closed runtime parity is established.

## Current rollout records

- `docs/rollout/SANDBOX_INVOICE_DRAFT_VERIFIED_SUCCESS_20260816.md`
- `docs/rollout/RECOMMENDED_NEXT_ACTION_20260816.md`
- `docs/rollout/SANDBOX_CREATED_DRAFT_READBACK_RECONCILIATION_20260816.md`
- `docs/rollout/POST_MERGE_BASELINE_20260805.md`
- `docs/rollout/LOCAL_DESKTOP_VALIDATION_20260805.md`
- `docs/rollout/PUBLIC_RUNTIME_INVENTORY_PHASE_2A_20260805.md`
- `docs/rollout/AUTHENTICATED_RUNTIME_INVENTORY_PHASE_2B_20260805.md`
- `docs/rollout/DOCUMENTATION_DRIFT_REGISTER_20260805.md`
- `docs/rollout/RUNTIME_INVENTORY_CHECKLIST_20260805.md`
- `docs/rollout/OPERATOR_GATE_RUNBOOK.md`
- `docs/rollout/PROVIDER_EVIDENCE_REGISTER.md`

Historical phase and draft-branch records remain evidence of their original state. The 2026-08-16 verified-success and recommended-next-action records supersede older next-action guidance for current rollout sequencing.

## Execution boundary

Do not deploy, enable production write tools, approve a production release manifest, open a production kill switch, or call a production write-capable Conta route without the applicable reviewed evidence and explicit operator authorization.
