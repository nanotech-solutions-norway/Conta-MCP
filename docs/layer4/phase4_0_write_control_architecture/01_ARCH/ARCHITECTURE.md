# Write-Control Architecture Specification — Layer 4 / Phase 4.0 — 14:04, 01.07.2026

## 1. Scope

This architecture defines the control model for possible future Conta Bridge write capability. It is not an implementation plan for immediate deployment.

The existing Layer 3 read-only release is the protected production baseline.

## 2. Non-negotiable invariants

| Invariant | Required state in Phase 4.0 |
|---|---|
| Production runtime | Unchanged |
| Active Custom GPT schema | GET-only |
| Live accounting writes | Disabled |
| Write routes | Not deployed |
| Formal report comparison | Gated |
| Official statutory reporting | Not claimed |
| Secrets/config values | Not exposed |
| Layer 3 RC1 fallback | Preserved |

## 3. Architecture principle

All future write capability must be separated from read capability at runtime, schema, approval, validation, and audit layers.

```text
READ FOUNDATION
  -> WRITE INTENT
  -> DRY-RUN VALIDATION
  -> RISK CLASSIFICATION
  -> HUMAN APPROVAL
  -> IDEMPOTENCY LOCK
  -> AUDIT RECORD
  -> SANDBOX EXECUTION PROOF
  -> FUTURE LIVE EXECUTION GATE
```

## 4. Proposed future components

### 4.1 Read-only foundation

Layer 3 RC1 remains the default production surface. It must remain available as fallback even if later write-capable phases are approved.

### 4.2 Write-intent service

A future write-intent service should create a non-mutating intent record. Intent creation must not mutate Conta accounting data.

Required attributes:

- `intentId`
- `actionType`
- `riskClass`
- `mode`
- `payloadHash`
- `requiresHumanApproval`
- `writeExecuted=false`
- `createdAt`
- `expiresAt`
- `operatorReference`

### 4.3 Dry-run validator

Dry-run validation must validate the proposed write and return deterministic preview data.

It must not execute provider mutations.

Required validation domains:

- Required payload fields.
- Customer/vendor identity.
- Product/service mapping.
- Account mapping.
- VAT handling.
- Currency.
- Fiscal year/date/period locking.
- Duplicate detection.
- Permission scope.
- Accounting effect.
- Reversal/correction feasibility.
- Risk class.
- Human-readable operator preview.

### 4.4 Approval gateway

No future execution may occur unless the approval gateway has a valid approval linked to the exact dry-run result.

Approval must be explicit, time-bound, operator-attributed, and hash-bound.

### 4.5 Idempotency lock

Each future write execution must use an idempotency key derived from stable payload/action/operator context or generated and stored before execution.

The same idempotency key must not execute twice.

### 4.6 Audit log

The audit log must record the full state transition without storing secrets.

Minimum lifecycle:

```text
INTENT_CREATED
DRY_RUN_STARTED
DRY_RUN_PASSED / DRY_RUN_FAILED
OPERATOR_APPROVED / OPERATOR_REJECTED
EXECUTION_QUEUED
EXECUTION_STARTED
EXECUTION_COMPLETED / EXECUTION_FAILED
POST_WRITE_VERIFIED / POST_WRITE_VERIFICATION_FAILED
CORRECTION_REQUIRED / CLOSED
```

### 4.7 Execution layer

Execution is out of scope for Phase 4.0.

A future execution layer must remain unavailable until:

1. Source documents are collected.
2. Write inventory is approved.
3. Dry-run contract is approved.
4. Audit/idempotency design is approved.
5. Sandbox validation passes.
6. Operator workflow is approved.
7. A separate implementation phase is explicitly authorized.

## 5. Recommended future deployment separation

| Surface | Purpose | Allowed in Phase 4.0 |
|---|---|---:|
| Layer 3 active read-only schema | Current Custom GPT action surface | Yes, unchanged |
| Layer 4 planning documents | Architecture and governance | Yes |
| Future dry-run schema draft | Later review-only file | Not active |
| Future execution schema | Later controlled deployment | No |
| Production runtime write routes | Live mutation | No |
| Sandbox/test-company routes | Later validation | No implementation in 4.0 |

## 6. Future write-control decision rule

Any future write action must answer all of the following before implementation:

1. What exact accounting object may change?
2. Is the action reversible?
3. What legal/statutory impact can result?
4. What operator must approve it?
5. What duplicate risk exists?
6. What idempotency key prevents double execution?
7. What audit evidence proves the action?
8. What post-write verification confirms outcome?
9. What correction process exists if the action is wrong?
10. What sandbox evidence proves safe behavior?

If any answer is missing, the write action remains blocked.
