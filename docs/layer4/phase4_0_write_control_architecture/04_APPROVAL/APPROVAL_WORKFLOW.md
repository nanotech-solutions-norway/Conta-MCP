# Human Approval Workflow — Layer 4 / Phase 4.0

## Status

Planning-only. No approval workflow is implemented in Phase 4.0.

## Approval principles

A future write action must not execute unless approval is:

- Explicit.
- Tied to a specific dry-run result.
- Tied to a payload hash.
- Tied to an idempotency key.
- Time-bound.
- Operator-attributed.
- Audit-logged.

## Operator approval screen requirements

The operator must see:

| Area | Required display |
|---|---|
| Action | Exact action type |
| Entity | Customer, supplier, invoice, voucher, product, etc. |
| Amounts | Net, VAT, gross, currency |
| Accounting impact | Debit/credit/accounts/period where relevant |
| VAT treatment | VAT code, rate, basis |
| Duplicate risk | Existing similar records or references |
| Risk class | R1–R5 |
| Reversibility | Correction/rollback path |
| Idempotency | Idempotency key |
| Audit | Audit record reference |
| Confirmation text | Exact phrase or explicit confirmation control |

## Recommended approval states

```text
APPROVAL_NOT_REQUIRED
APPROVAL_REQUIRED
APPROVAL_PENDING
APPROVAL_GRANTED
APPROVAL_REJECTED
APPROVAL_EXPIRED
APPROVAL_REVOKED
```

## Approval expiry

Recommended default:

| Risk class | Approval expiry |
|---|---:|
| R1 | 24 hours |
| R2 | 2 hours |
| R3 | 30 minutes |
| R4 | Explicit future policy required |
| R5 | Blocked |

## Required confirmation phrase pattern

For higher-risk actions, the operator should confirm with an action-specific phrase such as:

```text
I APPROVE [ACTION_TYPE] FOR [ENTITY_REFERENCE] USING DRY-RUN [DRY_RUN_ID]
```

This phrase is a future workflow design only and is not active in Phase 4.0.
