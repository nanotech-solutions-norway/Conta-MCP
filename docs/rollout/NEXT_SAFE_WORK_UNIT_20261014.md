# Conta MCP — Next Safe Work Unit — 2026-10-14

## Decision

The next rollout unit is **not invoice-draft UPDATE**.

A current verification of Conta's official production OpenAPI on 2026-10-14 shows:

- `GET /invoice/organizations/{opContextOrgId}/invoice-drafts` — list/search;
- `POST /invoice/organizations/{opContextOrgId}/invoice-drafts` — create;
- `GET /invoice/organizations/{opContextOrgId}/invoice-drafts/{id}` — read;
- `DELETE /invoice/organizations/{opContextOrgId}/invoice-drafts/{id}` — delete;
- no documented `PUT` or `PATCH` operation exists for invoice drafts.

Therefore an UPDATE implementation would invent an unsupported provider contract and is rejected.

## Current accepted state

```text
CONTA_MCP_V1_OPERATIONAL=true
CONTROLLED_INVOICE_DRAFT_CREATE=VALIDATED
DEFAULT_PRODUCTION_STATE=FAIL_CLOSED
STANDING_PRODUCTION_WRITE_AUTHORITY=false
INVOICE_DRAFT_UPDATE_PROVIDER_CONTRACT=NOT_DOCUMENTED
INVOICE_DRAFT_DELETE=OUTSIDE_V1_STANDING_SCOPE
SEND_FINALIZE=OUTSIDE_V1_STANDING_SCOPE
```

## Next safe work unit

Operationalize the already validated invoice-draft CREATE capability as a repeatable, bounded work-unit process without creating standing write authority.

Each future CREATE work unit must require:

1. a current non-mutating pre-read and deterministic preview;
2. exact organization/customer/action allowlisting;
3. a payload hash bound to the approved request;
4. a fresh one-use authorization with expiry and nonce;
5. a closed-by-default global/action kill switch;
6. exactly one provider mutation attempt;
7. mandatory GET-only readback and controlled-field verification;
8. replay rejection and idempotency-ledger closure;
9. immediate return to fail-closed state;
10. an evidence record for the completed work unit.

## Explicitly blocked

No work in this unit authorizes:

- invoice-draft DELETE;
- invoice send/finalize/posting;
- credit note/correction;
- customer/product mutation;
- ledger, bank, payment, payroll or VAT/statutory mutation;
- arbitrary provider routes;
- reusable or standing production authorization.

## Future capability expansion

Before any new write capability is designed, the provider's current official OpenAPI must first prove that the operation exists and define its method, route, request/response contract and readback semantics.

## Status

```text
NEXT_SAFE_WORK_UNIT=REPEATABLE_BOUNDED_CREATE_GOVERNANCE
PROVIDER_MUTATION_AUTHORIZED=false
WRITE_GATE_OPEN=false
IMPLEMENT_UNDOCUMENTED_UPDATE=false
```
