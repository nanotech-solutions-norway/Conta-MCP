# Conta MCP Server

**Project:** Conta-MCP  
**Target runtime:** Domeneshop PHP hosting  
**Active endpoint:** `https://mcp.atlas-ai.no`  
**Superseded endpoint:** `https://www.nanoconcept.no/conta-mcp/mcp` (GitHub Pages 404; not the active PHP runtime)  
**Rollout mode:** Operational V1 accepted in production; fail-closed by default; no standing production-write authority  

This repository contains a dependency-free PHP MCP-style JSON-RPC server for connecting an AI orchestrator to Conta through Conta's official REST API.

## Current status

The canonical operational status is `docs/rollout/CONTA_MCP_OPERATIONAL_V1_ACCEPTANCE_20261013.md`, with the bounded production-write evidence in `docs/rollout/PRODUCTION_FIRST_INVOICE_DRAFT_EXECUTION_RESULT_20260825.md`. These supersede older README/status text that described the production-write program as not implemented.

```text
CONTROLLED_WRITE_FOUNDATION_MERGED
POST_MERGE_CI_PASSED
LOCAL_DESKTOP_VALIDATION_VERIFIED
FAIL_CLOSED_PRODUCTION_RUNTIME_LIVE
PRODUCTION_WRITE_IMPLEMENTATION_IMPLEMENTED
FIRST_BOUNDED_PRODUCTION_INVOICE_DRAFT_VALIDATED
MANDATORY_GET_ONLY_READBACK_VERIFIED
OPERATIONAL_V1_ACCEPTANCE_VERIFIED
CONTA_MCP_V1_OPERATIONAL=true
ONE_USE_AUTHORIZATION_CONSUMED
PRODUCTION_WRITE_PATH_CURRENTLY_OPEN=false
WRITE_TOOLS_CURRENTLY_LIVE=false
GENERAL_PRODUCTION_WRITE_RELEASE=NOT_APPROVED
```

The first bounded production execution created exactly one **unsent NOK 1.00 validation invoice draft** and independently verified the controlled fields by GET-only reconciliation. Exactly one provider mutation occurred, no automatic retry was performed, and no send, posting, finalization, correction, deletion, customer mutation or other provider mutation was executed.

After the execution cycle, the runtime was returned to fail-closed state:

```text
kill_switch_global_blocked=true
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_actions=[]
allowed_write_organization_ids=[]
```

The one-use production authorization is consumed. No further production mutation is authorized without a new operator-defined work unit, current governance evidence and a fresh one-use authorization.

### Operational v1 boundary

The practical v1 service boundary is:

- authenticated health / MCP initialization;
- read-only organization, customer and invoice tools;
- deterministic non-mutating invoice-draft preview;
- controlled unsent invoice-draft creation only through the existing one-use approval, allowlist, amount-limit, idempotency, kill-switch and mandatory-readback controls;
- write gates closed by default between authorized work units.

Invoice send/finalize, corrections, deletion, customer/product mutation, ledger, bank, payment, payroll, VAT/statutory and other broader write classes are outside the v1 standing capability and require separate action-specific rollout evidence and authorization.

Definitive evidence:

- `docs/rollout/PRODUCTION_FIRST_INVOICE_DRAFT_EXECUTION_RESULT_20260825.md`
- PR #93 — record first production invoice-draft execution
- PR #94 — record consumed production execution gate

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

Production execution remains explicitly refused while the fail-closed gates are closed. The bounded production create path is implemented and validated, but every future production mutation requires a new explicit one-use work unit and authorization.

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

1. Treat the completed first bounded production invoice-draft execution as the baseline for operational v1.
2. Keep the production runtime fail-closed between work units; do not reuse the consumed authorization.
3. Treat the 2026-10-13 non-mutating operational acceptance as the production V1 baseline.
4. Operate invoice-draft creation only through a fresh one-use, payload-bound authorization for each explicitly approved work unit.
5. Rotate the legacy Domeneshop webhotel credential identified in historical Drive evidence and remove the plaintext historical copy after successful rotation and connectivity validation.
6. Use `docs/rollout/NEXT_SAFE_WORK_UNIT_20261014.md` as the current next-action record: operationalize repeatable bounded CREATE work units without standing write authority. Current official Conta OpenAPI does not document invoice-draft PUT/PATCH UPDATE, so no UPDATE capability is to be invented.
7. Verify the current official provider contract before designing any new write class, and keep delete, send/finalize, corrections, ledger, bank, payment, payroll and VAT/statutory capabilities in separate higher-risk rollout programs.

## Current rollout records

- `docs/rollout/CONTA_MCP_OPERATIONAL_V1_ACCEPTANCE_20261013.md`
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
