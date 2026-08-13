# Conta MCP Sandbox Invoice-Draft Preview Packet — 13.08.2026

## Classification

```text
PREVIEW_PACKET_STATUS=DESIGNED_PENDING_PROTECTED_RUNTIME_PREVIEW
PROVIDER_CALL_AUTHORIZED=false
SANDBOX_INVOICE_DRAFT_MUTATION_AUTHORIZED=false
PRODUCTION_EXECUTION_ALLOWED=false
```

This packet defines a non-executing sandbox invoice-draft proposal. It does not authorize or perform an invoice-draft provider mutation.

## Synthetic payload proposal

The protected runtime resolves the already-created exact synthetic sandbox customer and keeps its numeric identifier out of Git, Drive and chat.

```text
registrationSource=CONTA
type=NORMAL
invoiceLanguage=NO
invoiceCurrency=NOK
invoiceDraftLines.count=1
invoiceDraftLines[0].description=Atlas MCP Sandbox Invoice Draft Validation
invoiceDraftLines[0].price=1.00
invoiceDraftLines[0].quantity=1
invoiceDraftLines[0].discount=0
invoiceDraftLines[0].vatCode=no.vat
```

`customerId` is resolved only inside the protected runtime from the exact approved fixture `Atlas MCP Sandbox Test Customer` and must not be printed or committed.

## Current schema evidence

Current official Conta OpenAPI documents `registrationSource` as required for invoice-draft creation, with permitted values `CONTA` and `TIMERABBIT` and default `CONTA`. The documented external VAT codes include `no.vat`, `high`, `medium`, `low`, `zero.rate`, `exempted` and `export`. Each supplied invoice-draft line requires `description`, `vatCode`, `discount`, `price` and `quantity`.

The proposal selects `CONTA` and `no.vat` for a minimal synthetic validation draft. It does not represent a real commercial transaction.

## Protected preview workflow

```text
.github/workflows/conta-sandbox-invoice-draft-preview-packet.yml
```

The workflow is limited to:

1. sandbox-boundary validation;
2. GET-only exact-name customer search;
3. GET-only customer readback;
4. local temporary payload construction;
5. canonical SHA-256 calculation using `InvoiceDraftPreview::payloadHash`;
6. sanitized summary output;
7. temporary-file deletion.

No invoice-draft provider mutation is permitted by this workflow.

## Idempotency position

The current public OpenAPI does not document a provider request idempotency header for the invoice-draft create route. A generic `RestRequestIdempotencyException` exists, but this does not establish a usable provider-native idempotency contract. Provider-native idempotency therefore remains unverified.

A later separately authorized one-call test must rely on repository-controlled one-use approval, execution ledger, kill switch, no-auto-retry behavior and readback reconciliation. It must not assume provider deduplication.

## Rectification recommendation

Conservative default: if a future separately authorized sandbox draft is created successfully, retain it unchanged as test evidence. Do not send, post, update or delete it automatically. Any cleanup requires separate operator authorization.

## Remaining authorization boundary

```text
PAYLOAD_HASH=PENDING_PROTECTED_RUNTIME_PREVIEW
RECTIFICATION_PROCEDURE=PENDING_OPERATOR_APPROVAL
ONE_CALL_EXECUTION_WINDOW=PENDING_OPERATOR_APPROVAL
PAYLOAD_BOUND_APPROVAL=NOT_GRANTED
INVOICE_DRAFT_PROVIDER_CALL=NOT_AUTHORIZED
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
PRODUCTION_WRITE_APPROVED=false
```
