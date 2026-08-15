# Conta MCP Recommended Next Action — 2026-08-16

## Current state

The first controlled Conta sandbox invoice-draft mutation is complete and independently verified.

```text
SANDBOX_INVOICE_DRAFT_CREATE=VERIFIED
READBACK_VERIFICATION=VERIFIED
SAME_KEY_REPLAY_REJECTION=VERIFIED
KILL_SWITCH_CLOSURE=VERIFIED
PRODUCTION_WRITE_AUTHORIZED=false
PRODUCTION_WRITE_PROGRAM=NOT_IMPLEMENTED
```

Definitive evidence: `SANDBOX_INVOICE_DRAFT_VERIFIED_SUCCESS_20260816.md`.

## Next authorized work unit

The next work unit is **post-success stabilization and fail-closed deployment readiness**, not another provider mutation.

Sequence:

1. Merge the permanent stabilization changes after CI/security validation.
2. Treat the verified sandbox draft as retained evidence; do not create another draft or perform cleanup without separate authorization.
3. Select the stabilized `main` commit as the next deployment candidate.
4. Reconcile the active Domeneshop runtime against canonical `main`.
5. Prepare a fail-closed deployment plan that preserves all production write gates closed.
6. Deploy canonical runtime only under a separate deployment authorization.
7. After deployment, verify health, authenticated MCP initialization, `tools/list`, read-only Conta calls, preview-only behavior, runtime hashes/version, and production write refusal.
8. Only after fail-closed runtime parity is verified, design and review a separate production-write program.

## Required fail-closed deployment state

Any deployment candidate must preserve:

```text
enable_write_preview=true
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_organization_ids=[]
allowed_write_actions=[]
kill_switch_global_blocked=true
sandbox_authorization=missing_or_pending
```

The production runtime must not inherit sandbox credentials, sandbox authorization packets, temporary ledgers, one-use approvals, or the sandbox-only execution state.

## Production-write program remains a separate gate

Sandbox success proves that the invoice-draft create control path works against Conta under the tested sandbox conditions. It does not prove or authorize production execution.

Before any production mutation, separately define and approve at minimum:

- production organization allowlist and identity validation;
- production provider/VAT prerequisites;
- production release manifest and deployment hash authority;
- production approval authority and credential custody;
- production-specific rate/amount/customer/operation limits;
- production audit retention and incident response;
- rollback/containment procedures;
- production readback semantics;
- explicit operator approval for the first production mutation.

Until that program is implemented and approved, `ContaClient`/`WritePolicy` must continue to refuse production writes.

## Stop conditions

Stop before deployment or mutation if any of the following is true:

- stabilization CI/security gates are not green;
- runtime commit/hash cannot be established;
- active production endpoint differs unexpectedly from the selected candidate;
- any write-capable tool becomes executable in production;
- production config opens a write gate;
- sandbox secrets or sandbox authorization material appear in deployment artifacts;
- provider schema/route evidence is stale or conflicting;
- any provider mutation would be required merely to validate deployment.

## Manual operator gate

The next manual operator decision should occur only when a concrete fail-closed deployment candidate and deployment procedure are ready for review. No further sandbox mutation approval is required for the current post-success stabilization work.
