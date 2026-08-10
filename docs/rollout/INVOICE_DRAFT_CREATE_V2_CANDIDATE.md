# Invoice Draft Create v2 Candidate — 16.07.2026

## Candidate identity

```text
candidate_id=invoice_draft_create_v2
classification=CONTROLLED_WRITE_PENDING_APPROVAL
execution_status=NOT_AUTHORIZED
provider_call_allowed=false
sandbox_execution_allowed=false
production_execution_allowed=false
```

## Verified static route evidence

```text
operationId=v1MakeInvoiceDraft
method=POST
path=/invoice/organizations/{opContextOrgId}/invoice-drafts
```

The route and operation were independently validated against the current official production and sandbox OpenAPI documents on 2026-08-10. Runtime create entitlement and behavior remain unvalidated.

## Required authorization packet

- Test-company identity binding: `VERIFIED_SANDBOX_RUNTIME_20260810`; the real identifier remains server-side only.
- Allowed organization ID binding: `VERIFIED_SERVER_SIDE_RUNTIME_20260810`; the real ID must never be committed.
- Exact provider route and operation ID: `VERIFIED_OFFICIAL_DOCS_20260810`.
- Request schema hash: `PENDING_OPERATOR_VALIDATION`.
- Response schema hash: `PENDING_OPERATOR_VALIDATION`.
- Required provider scopes: `PARTIAL_CONTEXT`; public documentation describes user-inherited API-key access, and effective sandbox read access is verified, but create entitlement is not.
- Idempotency behavior: `PENDING_REVIEW`; no create-operation idempotency contract is documented.
- Readback method: `VERIFIED_OFFICIAL_DOCS_20260810`; runtime readback remains unobserved.
- Expected pre-state: `PENDING_OPERATOR_VALIDATION`.
- Expected post-state: `PENDING_OPERATOR_VALIDATION`.
- Approved rectification procedure: `DRAFTED_PENDING_OPERATOR_APPROVAL`; see `SANDBOX_INVOICE_DRAFT_RECTIFICATION_DRAFT_20260811.md`.
- Maximum one-call execution window: `PENDING_OPERATOR_VALIDATION`.
- Operator approval: `NOT_GRANTED`.

## Mandatory sequence for the later sandbox gate

```text
pre-read
preview
approval-envelope creation
one provider mutation
post-read
approved-proposal comparison
same-key replay rejection
kill-switch verification
authorization revocation
evidence closure
```
