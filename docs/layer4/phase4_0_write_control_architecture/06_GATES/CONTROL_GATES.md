# Control Gates — Layer 4 / Phase 4.0

## Gate 0 — Read-only baseline gate

Required before any Layer 4 work continues:

```text
writePaused=true
runtimeWriteBlocked=true
openApiGetOnly=true
blockedMethods includes POST, PUT, PATCH, DELETE
writeActionsExposed=false
statutorySubmissionExposed=false
```

Failure result: stop.

## Gate 1 — Architecture approval gate

Phase 4.0 architecture must be approved before Phase 4.1 starts.

Approval does not approve implementation.

## Gate 2 — Write inventory gate

All future write actions must be classified R1–R5.

Unclassified actions remain blocked.

## Gate 3 — Dry-run contract gate

Dry-run contract must be approved before any write-capable schema is drafted.

## Gate 4 — Source documentation gate

Current Conta API write documentation, payload contracts, sandbox/test-company rules, and accounting business rules must be available.

Missing source material blocks implementation.

## Gate 5 — Audit/idempotency gate

No execution route may exist without approved idempotency and audit design.

## Gate 6 — Sandbox gate

No live company writes until sandbox/test-company validation has passed.

## Gate 7 — Active schema gate

No active Custom GPT schema may expose write methods unless a future implementation phase is explicitly approved.

The current active schema remains GET-only.

## Gate 8 — Live execution gate

Live execution requires separate approval after all prior gates are satisfied.

Phase 4.0 may define this gate. It cannot pass this gate.
