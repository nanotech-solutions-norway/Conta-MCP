# Conta MCP Issue #98 — Repeatable CREATE Synthetic Governance Review — 13:18, 17.10.2026

## Authority and source baseline

- Governing work item: https://github.com/nanotech-solutions-norway/Conta-MCP/issues/98
- Canonical next action: `docs/rollout/NEXT_SAFE_WORK_UNIT_20261014.md`.
- Accepted operational V1: `docs/rollout/CONTA_MCP_OPERATIONAL_V1_ACCEPTANCE_20261013.md`, non-mutating protected acceptance run `38380188722`.
- Historic single-use CREATE: `docs/rollout/PRODUCTION_FIRST_INVOICE_DRAFT_EXECUTION_RESULT_20260825.md`; the prior authorization is **consumed**.
- Hosting credential rotation was independently recorded by PR #99; it conveys no provider-write permission.

**Current state:** OPEN / SYNTHETIC CONTRACT CANDIDATE / INDEPENDENT ACCEPTANCE PENDING.

## Change under review

- `tests/support/SyntheticBoundedCreate.php`: offline-only candidate coordinator; no HTTP client, provider credentials, environment mutations, service registration, production route exposure, deployment, or actual POST. It reuses the existing `InvoiceDraftPreview`, `InvoiceDraftReadbackVerifier`, `WriteDispatchPermit`, and `WriteExecutionLedger` semantics.
- `tests/repeatable-bounded-create.php`: deterministic positive and negative regression cases using synthetic-only references.
- `controlled-write-foundation.yml`: runs the synthetic suite in the existing non-provider PHP CI job.

### Contract boundary

Synthetic work units bind an exact work ID, operation `invoice_draft_create_v2`, POST method, organization/customer references, deterministic payload hash, current GET pre-read hash, controlled invoice fields, a signed fixture authorization, one-use identifiers, 900-second maximum authorization duration and a 300-second maximum pre-read age. For every simulated attempt, the existing ledger reserves the idempotency key, nonce and authorization ID first. At most one simulated POST attempt is recorded. Success requires GET-only exact organization, customer and controlled-field readback. Missing/ambiguous outcomes are terminal and never retried. Every exit reports simulated closed-state markers without claiming any production runtime change.

Only `synthetic-*` identifiers and one constrained synthetic NOK/one-line payload are admitted. The test HMAC key is a public fixture, **not** a production signing credential or an approved production authorization mechanism. No synthetic approval is portable to a live environment.

The pre-read and response are supplied as inert fixtures; they are **not** actual provider requests. Consequently the suite tests governance behavior, not provider contract equivalence, transport delivery, concurrent controller behavior or independent runtime closure.

## Review checklist

| Criterion | Local candidate evidence | Independent decision |
| --- | --- | --- |
| Exact action, organization, customer, controlled fields, canonical hash | Synthetic preview and changed-scope/payload refusal | PENDING_REVIEW |
| Current GET-only pre-read, duplicate/ambiguity denial | Signed pre-read digest; freshness, duplicate and ambiguous fixtures | PENDING_REVIEW |
| One-use nonce/authorization/idempotency | Existing persistent ledger; replay tests | PENDING_REVIEW |
| Expiring signed authorization, no implicit approval | Test fixture HMAC, negative tests; real protected authorization not evaluated | PENDING_REVIEW |
| Global and action kill gates | Default-deny switch inputs in synthetic runner; runtime not touched | PENDING_REVIEW |
| One attempt, no automatic retry, ambiguous outcome closure | Synthetic outcomes and terminal ledger markers | PENDING_REVIEW |
| Mandatory GET-only readback and controlled-field verification | Existing readback verifier reused; mismatches and unavailable fixtures | PENDING_REVIEW |
| Repository-safe output | No raw IDs, customer data, invoice payload or credentials returned by runner | PENDING_REVIEW |
| Source-controlled CI and security validation | Requires branch PR, CI readback and reviewer | PENDING_REVIEW |
| Live runtime/protected kill closure | Not attempted; only historical operational-V1 evidence exists | BLOCKED — SEPARATE GATE |

## Acceptance still required

1. Independent reviewer approves the *exact synthetic repeatable-work-unit contract*, limits, evidence projections, concurrency/recovery strategy and separation from deployed provider transport. Pay particular attention to the synthetic HMAC fixture (not production authorization) and ledger terminal-error handling.
2. The PR's Controlled Write Foundation, security, dependency and applicable code analysis checks must complete successfully, with SHA-level GitHub readback. Do not equate a local suite pass with CI acceptance.
3. Before any implementation is connected to production execution, approve a new **specific** design integrating the already-deployed signed production envelope, protected one-use gate, manifest and operator/accounting/security approvals. Verify actual runtime kill-switch closure and persistent ledger integrity independently.
4. Any future real CREATE requires a **new** approved work ID, current protected GET pre-read, exact payload preview/hash, authorized scope and customer, fresh signed one-use record with nonce/expiry, one approved execution attempt, mandatory GET-only readback, immediate verified closeout and separate acceptance. The August authorization cannot be reused.

## STOP and evidence handling

- No real Conta CREATE, DELETE, send, finalize, correction, customer/product mutation or financial mutation.
- No standing production-write gate, undocumented UPDATE, provider POST or automatic retry.
- No provider identifiers, secrets or raw invoice/customer/accounting records in source, issues, logs, or Drive.
- Mark Issue #98 COMPLETE only after source-controlled implementation, passing positive/negative tests, explicitly approved exact contract, replay/idempotency evidence, fail-closed verification and independent acceptance. Local test coverage has **no approved completion denominator** (progress N/A).


## Owner review and current boundary — 09:19, 11.11.2026 (Europe/Madrid)

### Owner review disposition

The operator stated in the 11.11.2026 ChatGPT session that the security review was performed by the owner and that owner review is sufficient. For this internal, synthetic-only contract, record the review gate as **OWNER REVIEW ACCEPTED**. This is an operator-attested decision; the GitHub connector does not independently establish the operator's GitHub identity or the contents of a separate review artifact.

### Evidence and remaining blockers

- Synthetic bounded-CREATE simulator and its CI suite were merged through PR #100. Subsequent ledger hardening PRs #103–#106 were merged. The latest readback for PR #106 records successful offline CI, ten malformed/corrupt/symlink denials, and no provider side effects. The merged code remains an offline/synthetic validation path.
- The main-branch baseline inspected before PR #108 was `449e1082c9ae61e2bdf8d449f56f212d53c25a5e`, descending from ledger-hardening merge `a049745dc695a9feb14d5f3c55f1060975b943cc`.
- **Not cleared:** production/runtime transition. All writers using the old inode-lock scheme must be stopped or upgraded together before the stable-sidecar-lock implementation is used. No current runtime writer inventory, provider identity, write grant, rollback envelope, or exact protected deployment authority was verified in this review. No runtime transition or provider operation is authorized here.
- **Not qualified:** power-loss durability and network filesystems. The current implementation states its boundary as cooperating writers on a local same-filesystem rename. Do not claim guarantees beyond that boundary without a separately scoped environment qualification.

### Status

`OWNER_REVIEW_ACCEPTED / SYNTHETIC_CI_EVIDENCE_PRESENT / RUNTIME_TRANSITION_BLOCKED / DURABILITY_SCOPE_LIMITED`

Issue #98 remains **OPEN / PARTIAL**. The synthetic governance and ledger-hardening work is review-accepted within its stated scope. Production integration, runtime rollout, and any real Conta CREATE remain separate protected work. Progress remains **N/A%** because no approved completion denominator exists.

### Next safe actions

1. Keep live execution fail-closed; do not mix old and new writer implementations.
2. For any future runtime rollout, first prepare a fresh exact inventory of every active writer, runtime identity/grant, storage type, rollback path, and acceptance readback. Require the applicable exact protected authority and provider permission before deployment.
3. If a future deployment needs network-filesystem or power-loss guarantees, qualify that exact storage/runtime with fault-injection and durable readback before enabling writes. Otherwise retain the documented local-filesystem-only boundary and deny unsupported configurations.
