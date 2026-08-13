# Conta sandbox invoice-draft one-call authorization — 2026-08-13

## Status

```text
authorization_status=OPERATOR_APPROVED_PENDING_EXECUTION
candidate_id=invoice_draft_create_v2
environment=sandbox
max_provider_mutations=1
automatic_retry=false
readback_required=true
production_write_authorized=false
```

The operator explicitly approved `APPROVE_CONTA_SANDBOX_INVOICE_DRAFT_ONE_CALL` on 2026-08-13. This authorization is limited to exactly one sandbox invoice-draft creation attempt bound to the validated preview payload hash below.

```text
payload_sha256=dab571f2807745e1236a30dc93ae34ca8b8d2b15daaa26034f68a255e170b786
registrationSource=CONTA
type=NORMAL
invoiceLanguage=NO
invoiceCurrency=NOK
line_count=1
price_NOK=1.00
quantity=1
discount=0
vatCode=no.vat
```

Customer identity remains protected and must be re-resolved/validated at execution time from the separately authorized synthetic sandbox fixture. The customer identifier must not be committed or printed.

## Execution constraints

1. Sandbox base URL and protected organization binding must be enforced.
2. Workflow reruns are not authorized and must fail before provider mutation.
3. Expected sandbox pre-state is zero invoice drafts. Any observed existing draft blocks the POST.
4. The exact synthetic customer fixture must be resolved and GET-read back successfully.
5. The canonical payload hash must equal the approved SHA-256 above.
6. A one-use signed approval envelope and signed sandbox authorization packet must be generated only in protected runner-temporary storage.
7. The controlled-write execution ledger must reserve the idempotency key, nonce and authorization ID before dispatch.
8. Maximum provider mutations is one POST to the verified sandbox invoice-draft create route.
9. No automatic retry is permitted for any failure or indeterminate response.
10. A successful create must be followed by the configured GET readback and proposal comparison.
11. Same-key replay must be rejected before a second provider mutation.
12. The kill switch must be closed and ephemeral authorization material removed immediately after the attempt.
13. The resulting unsent sandbox draft is retained as test evidence. No send, post, update, convert, credit or delete operation is authorized.
14. Any later cleanup requires separate operator authorization.

This document does not authorize production access or production writes.
