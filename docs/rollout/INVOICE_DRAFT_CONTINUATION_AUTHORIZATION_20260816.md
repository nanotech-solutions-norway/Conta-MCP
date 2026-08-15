# Conta MCP Invoice-Draft Continuation Authorization — 2026-08-16

## Operator authorization

At 2026-08-16 01:50 Europe/Oslo, the operator explicitly authorized invoice drafting to continue until the rollout is successful and complete and instructed the project to proceed accordingly.

## Authorized objective

Continue the Conta **sandbox invoice-draft rollout** through the remaining deployment/readiness and end-to-end validation gates until controlled invoice drafting is demonstrably operational and independently verified.

The already-created sandbox draft remains valid evidence. This authorization does **not** require or justify creating a duplicate draft merely to exercise the authorization.

## Mutation scope

When a further sandbox invoice-draft mutation is genuinely required for end-to-end validation, it is authorized subject to all existing fail-closed controls:

- sandbox environment only;
- invoice-draft create only;
- fresh one-use approval, nonce and idempotency key per attempt;
- maximum one provider POST per attempt;
- mandatory GET readback verification;
- same-key replay rejection;
- kill-switch closure after the attempt;
- automatic retry only when no object exists and dispatch state is safely known;
- no retry after an indeterminate/maybe-accepted POST or after any object is observed;
- no retry after a deterministic non-429 provider 4xx until the request or entitlement defect is corrected;
- production-write authorization remains false.

## Explicit exclusions

This authorization does not authorize:

- any production Conta mutation;
- sending, posting, finalizing, converting, crediting or deleting an invoice;
- customer or accounting-master-data mutation;
- deletion/cleanup of the existing sandbox evidence draft;
- enabling production write tools;
- changing production provider/VAT/accounting settings;
- reuse of sandbox credentials in production.

## Current state at authorization

```text
SANDBOX_INVOICE_DRAFT_CREATE=VERIFIED
READBACK_VERIFIED=true
MISMATCH_COUNT=0
SAME_KEY_REPLAY_REJECTED=true
KILL_SWITCH_CLOSED=true
PRODUCTION_WRITE_AUTHORIZED=false
```

Canonical source at authorization:

```text
repository=nanotech-solutions-norway/Conta-MCP
branch=main
commit=cf0dd0e5d7a30fc94fe0ae71ec0d96c6b06c1fb7
```

## Immediate next action

The immediate work unit is fail-closed deployment readiness from the verified canonical source. Build an immutable protected-runtime deployment candidate, validate it, reconcile it with the deployed runtime, and preserve all production write gates closed.

A further sandbox provider mutation is permitted only when it is necessary to prove the deployed end-to-end invoice-draft path and the preconditions above are satisfied.
