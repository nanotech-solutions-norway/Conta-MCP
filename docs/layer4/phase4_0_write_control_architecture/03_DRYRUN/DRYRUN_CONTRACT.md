# Dry-Run Contract Design — Layer 4 / Phase 4.0

## Status

Planning-only. Non-deployable. Not an active OpenAPI schema.

## Purpose

Dry-run is the mandatory safety layer between intent creation and any future write execution.

The dry-run layer must answer:

- Is the proposed action structurally valid?
- What accounting effect would it have?
- What risk class applies?
- What operator must approve it?
- What duplicate risks exist?
- What irreversible effects could occur?
- What idempotency key will bind a future execution?
- What audit evidence will be stored?

## Required invariant

```text
writeExecuted=false
```

This invariant must be returned on every dry-run response.

## Request concept

A future dry-run request should include:

| Field | Required | Notes |
|---|---:|---|
| `actionType` | Yes | Controlled enum |
| `mode` | Yes | Must equal `dry_run` |
| `payload` | Yes | Proposed write payload |
| `operatorContext` | Yes | Server-side identity reference, not raw secrets |
| `idempotencyKey` | Conditional | Required before execution; may be generated during dry-run |
| `reason` | Yes | Business reason / operator note |
| `expectedAccountingPeriod` | Conditional | Required for accounting mutations |

## Response concept

The dry-run response must include:

| Field | Required | Notes |
|---|---:|---|
| `success` | Yes | Whether dry-run validation completed |
| `mode` | Yes | `dry_run` |
| `writeExecuted` | Yes | Always `false` |
| `riskClass` | Yes | R1–R5 |
| `requiresHumanApproval` | Yes | Usually true for R1+ |
| `idempotencyKey` | Yes | Future execution binding |
| `payloadHash` | Yes | Hash of proposed payload |
| `preview` | Yes | Human-readable and structured preview |
| `accountingImpact` | Conditional | Required for R2+ |
| `warnings` | Yes | Non-blocking warnings |
| `blockedReasons` | Yes | Blocking issues |
| `approvalText` | Conditional | Required if executable in a later phase |
| `auditPreview` | Yes | Audit fields to be stored |

## Validation checks

Minimum dry-run checks:

1. Schema validation.
2. Required field validation.
3. Date and accounting period validation.
4. Currency validation.
5. Account/VAT mapping validation.
6. Customer/vendor existence or creation strategy.
7. Duplicate detection.
8. Permission/scope validation.
9. Risk classification.
10. Operator preview generation.
11. Idempotency key generation/validation.
12. Audit preview generation.
13. Reversal/correction assessment.

## Hard block examples

Dry-run must block if:

- The action is R4 or R5 and no explicit future statutory/payment approval exists.
- Required account or VAT mapping is missing.
- The accounting period is locked or ambiguous.
- Duplicate risk is high and unresolved.
- Approval identity cannot be resolved.
- The provider endpoint semantics are unknown.
- No rollback/correction model exists for the action.
