# Sandbox invoice-draft test-payload contract — 2026-08-11

## Status after validated fixture creation — 13.08.2026

```text
payload_status=PROPOSED_PENDING_PROTECTED_RUNTIME_PREVIEW
payload_materialized=false
payload_hash_created=false
sandbox_test_customer_fixture_present=true
sandbox_test_customer_fixture_validated=true
customer_identifier_storage=PROTECTED_RUNTIME_ONLY
provider_call_authorized=false
sandbox_mutation_authorized=false
```

The synthetic sandbox customer prerequisite has been closed. Exactly one approved fixture was created and immediately verified by GET readback in protected workflow run `31688667208`. The customer identifier was masked and was not committed or printed.

## Current documented contract

Current official Conta OpenAPI documents `registrationSource` as required for the invoice-draft create request. Permitted values are `CONTA` and `TIMERABBIT`; `CONTA` is the documented default. Each supplied `invoiceDraftLines` item requires `description`, `vatCode`, `discount`, `price` and `quantity`. Documented external VAT codes are `no.vat`, `high`, `medium`, `low`, `zero.rate`, `exempted` and `export`.

## Proposed protected test payload

| Field | Proposed value / policy |
| --- | --- |
| `registrationSource` | `CONTA` |
| `customerId` | Resolve at runtime from the exact approved synthetic fixture; never commit or print |
| `invoiceDraftLines` | Exactly one minimal synthetic line |
| `description` | `Atlas MCP Sandbox Invoice Draft Validation` |
| `vatCode` | `no.vat` |
| `discount` | `0` |
| `price` | `1.00` NOK ex VAT |
| `quantity` | `1` |
| `type` | `NORMAL` |
| `invoiceLanguage` | `NO` |
| `invoiceCurrency` | `NOK` |
| Optional references and attachments | Omit |

This is synthetic test data only and does not represent a real sale or accounting obligation.

## Protected preview gate

Workflow:

```text
.github/workflows/conta-sandbox-invoice-draft-preview-packet.yml
```

The workflow must:

1. enforce the sandbox boundary;
2. resolve exactly one exact-name synthetic fixture using GET only;
3. GET-read and verify the fixture identity;
4. keep the customer identifier masked;
5. materialize the proposal only in runner-temporary storage;
6. calculate the exact canonical SHA-256 through `InvoiceDraftPreview::payloadHash`;
7. print only sanitized proposal facts and the payload hash;
8. delete temporary provider responses and payload files;
9. perform no invoice-draft provider mutation;
10. change no write gate.

## Idempotency boundary

No usable provider-native idempotency header is documented for the invoice-draft create operation. A generic provider `RestRequestIdempotencyException` is documented, but that does not establish a client contract. Provider-native idempotency therefore remains unverified.

Any later one-call sandbox execution must rely on the repository-controlled one-use approval envelope, execution ledger, kill switch, no-auto-retry behavior and readback reconciliation.

## Materialization and authorization sequence

```text
fixture created and validated = COMPLETE
protected fixture resolution/readback = PENDING_PREVIEW_RUN
local payload materialization = PENDING_PREVIEW_RUN
payload SHA-256 = PENDING_PREVIEW_RUN
rectification procedure approval = PENDING_OPERATOR_APPROVAL
maximum one-call window = PENDING_OPERATOR_APPROVAL
payload-bound approval = NOT_GRANTED
separate invoice-draft mutation authorization = NOT_GRANTED
```

Until explicit payload-bound and one-call authorization is granted, `provider_call_allowed=false` and `sandbox_execution_allowed=false` remain controlling.
