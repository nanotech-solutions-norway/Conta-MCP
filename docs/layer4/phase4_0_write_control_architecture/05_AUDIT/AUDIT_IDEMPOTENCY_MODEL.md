# Audit and Idempotency Model — Layer 4 / Phase 4.0

## Status

Planning-only. No audit database or runtime changes are implemented in Phase 4.0.

## Idempotency purpose

Idempotency prevents duplicate execution of the same approved write action.

A future execution layer must reject duplicate use of the same idempotency key.

## Required idempotency fields

| Field | Purpose |
|---|---|
| `idempotencyKey` | Unique execution guard |
| `payloadHash` | Confirms payload has not changed after dry-run |
| `actionType` | Binds key to action type |
| `dryRunId` | Binds execution to validated dry-run |
| `approvalId` | Binds execution to approval |
| `operatorReference` | Server-side operator reference |
| `createdAt` | Creation timestamp |
| `expiresAt` | Expiry timestamp |
| `executionStatus` | Prevents duplicate execution |

## Audit lifecycle

```text
INTENT_CREATED
DRY_RUN_STARTED
DRY_RUN_FAILED
DRY_RUN_PASSED
APPROVAL_REQUIRED
APPROVAL_GRANTED
APPROVAL_REJECTED
APPROVAL_EXPIRED
EXECUTION_QUEUED
EXECUTION_STARTED
EXECUTION_COMPLETED
EXECUTION_FAILED
POST_WRITE_VERIFIED
POST_WRITE_VERIFICATION_FAILED
CORRECTION_REQUIRED
CLOSED
```

## Audit record requirements

| Field | Required | Notes |
|---|---:|---|
| `auditId` | Yes | Unique immutable reference |
| `state` | Yes | Lifecycle state |
| `actionType` | Yes | Controlled enum |
| `riskClass` | Yes | R1–R5 |
| `dryRunId` | Yes | For write actions |
| `approvalId` | Conditional | Required before execution |
| `idempotencyKey` | Conditional | Required before execution |
| `payloadHash` | Yes | SHA-256 or stronger |
| `providerRequestHash` | Conditional | Required if provider called |
| `providerResponseHash` | Conditional | Required if provider called |
| `operatorReference` | Yes | Server-side reference only |
| `resultStatus` | Yes | Normalized outcome |
| `rollbackReference` | Conditional | Required if correction needed |
| `createdAt` | Yes | Timestamp |
| `updatedAt` | Yes | Timestamp |

## Data minimization

Audit logs must not store:

- API keys.
- Bearer tokens.
- Private config paths.
- Raw organization/company IDs unless explicitly approved for server-side audit.
- Bank credentials.
- Full customer confidential data where a reference/hash is sufficient.

## Provider evidence

Provider request and response evidence should be hash-archived. Full payload retention requires a separate data-retention decision.
