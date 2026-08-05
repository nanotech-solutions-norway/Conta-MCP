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

The server-side route value must still be independently validated against the current official provider schema before execution.

## Required authorization packet

- Test-company identifier: `PENDING_OPERATOR_VALIDATION`.
- Allowed organization ID: `PENDING_OPERATOR_VALIDATION`.
- Exact provider route and operation ID: `PENDING_OPERATOR_VALIDATION`.
- Request schema hash: `PENDING_OPERATOR_VALIDATION`.
- Response schema hash: `PENDING_OPERATOR_VALIDATION`.
- Required provider scopes: `PENDING_OPERATOR_VALIDATION`.
- Idempotency behavior: `PENDING_OPERATOR_VALIDATION`.
- Readback method: `PENDING_OPERATOR_VALIDATION`.
- Expected pre-state: `PENDING_OPERATOR_VALIDATION`.
- Expected post-state: `PENDING_OPERATOR_VALIDATION`.
- Approved rectification procedure: `PENDING_OPERATOR_VALIDATION`.
- Maximum one-call execution window: `PENDING_OPERATOR_VALIDATION`.
- Operator approval: `PENDING_OPERATOR_VALIDATION`.

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
