# Conta MCP First-Production Release & Execution Authorization Request — 2026-08-24

## Status

```text
REQUEST_STATUS=READY_FOR_OPERATOR_AUTHORIZATION
DEPLOYMENT_RUN_ID=32713676779
DEPLOYED_IMPLEMENTATION_COMMIT=19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac
ORGANIZATION_REFERENCE_SHA256=9ee050155b0c35066a2ea426c72a65e5cdd2806f18a3cf9829fb132bd66634ab
CURRENT_WRITE_TOOLS_ENABLED=false
CURRENT_RUNTIME_WRITE_BLOCKED=true
CURRENT_EXECUTION_ALLOWED=false
CURRENT_PRODUCTION_WRITE_APPROVED=false
CURRENT_KILL_SWITCH_GLOBAL_BLOCKED=true
RELEASE_APPROVED=false
FIRST_PRODUCTION_MUTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Requested work unit

If explicitly authorized for the exact reviewed request commit, the next gate may prepare and execute exactly one bounded first-production operation for action `invoice_draft_create_v2`, subject to every implemented runtime gate succeeding.

The authorized operation is limited to:

- one protected production organization matching SHA-256 `9ee050155b0c35066a2ea426c72a65e5cdd2806f18a3cf9829fb132bd66634ab`;
- one unsent invoice draft only;
- NOK currency only;
- maximum one invoice line;
- maximum line amount NOK 1.00;
- maximum draft total NOK 1.00;
- exactly one provider POST maximum;
- no automatic retry;
- deterministic preview, payload hash, method and route binding;
- signed one-use approval with TTL no greater than 900 seconds;
- nonce/idempotency replay rejection;
- mandatory GET readback after dispatch;
- immediate execution-gate and kill-switch closure after the attempt;
- metadata-only safe evidence with no credentials, raw organization identifier or full business payload in public records.

## Required pre-dispatch gates

Before any provider POST, the execution path must verify all of the following:

1. deployed source is exactly `19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac`;
2. organization hash matches the validated production organization reference;
3. governance decision-packet hash matches the approved production decision packet;
4. exact action is `invoice_draft_create_v2`;
5. exact method and route are bound into the signed approval;
6. exact preview and payload hash are bound into the signed approval;
7. invoice line count, line amount, draft total and NOK currency satisfy the hard caps;
8. release manifest and runtime hashes match the approved deployment;
9. authorization packet is fresh, one-use and within the configured TTL;
10. provider mutation count is zero before dispatch;
11. no replay or prior-used nonce/idempotency key is detected;
12. global/action kill-switch state is deliberately opened only for the bounded execution window;
13. any mismatch fails closed before provider dispatch.

## Post-dispatch requirements

If and only if the POST is dispatched:

- no automatic retry is permitted;
- a provider response or ambiguous outcome must be followed by GET-only reconciliation;
- controlled-field readback must be compared with the approved payload intent;
- kill switches and execution gates must close immediately after the attempt regardless of outcome;
- a second mutation requires an entirely new authorization cycle;
- correction, deletion, sending, posting, finalizing or cleanup is not implied or authorized.

## Explicit exclusions

This request does **not** authorize:

- sending, posting or finalizing an invoice;
- modifying or deleting an invoice draft;
- credit-note or corrective mutation;
- customer or product mutation;
- voucher, ledger, payment, bank, payroll or statutory mutation;
- more than one provider POST;
- automatic retry after any result;
- changing the permanent production limits beyond the approved first-run caps;
- exposing credentials or raw organization/customer identifiers;
- treating deployment authorization as execution authorization.

## Required execution evidence

A successful or safely-contained first attempt must record repository-safe markers including:

```text
RELEASE_APPROVED=true
FIRST_PRODUCTION_MUTATION_AUTHORIZED=true
ACTION=invoice_draft_create_v2
DEPLOYED_IMPLEMENTATION_COMMIT=19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac
PRODUCTION_ORGANIZATION_REFERENCE_SHA256=9ee050155b0c35066a2ea426c72a65e5cdd2806f18a3cf9829fb132bd66634ab
MAX_PROVIDER_MUTATIONS=1
AUTOMATIC_RETRY_ALLOWED=false
PROVIDER_MUTATION_COUNT=<0-or-1>
MANDATORY_READBACK_REQUIRED=true
READBACK_PERFORMED=<true-or-false>
READBACK_VERIFIED=<true-or-false>
KILL_SWITCH_RE-CLOSED=true
EXECUTION_GATE_RE_CLOSED=true
SECRET_VALUE_PRINTED=false
RAW_ORGANIZATION_ID_PRINTED=false
```

The evidence must also state the final provider-call and mutation outcome without exposing the invoice draft identifier in raw form; any provider object identifier should be represented only by a one-way hash in repository evidence.

## Current boundary

Creating this request does not itself authorize release or execution. Until an exact operator authorization is issued for the exact reviewed request commit:

```text
RELEASE_APPROVED=false
FIRST_PRODUCTION_MUTATION_AUTHORIZED=false
PROVIDER_MUTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Required operator authorization syntax

Only this exact instruction, referencing the exact reviewed request commit, authorizes the bounded first-production release/execution gate:

```text
AUTHORIZE_CONTA_FIRST_PRODUCTION_INVOICE_DRAFT_RELEASE_AND_EXECUTION for commit <exact-reviewed-request-commit>
```

That authorization is limited to the one bounded unsent NOK 1.00 invoice-draft creation attempt described above. It does not authorize sending, posting, finalizing, correction, deletion or any other production mutation.
