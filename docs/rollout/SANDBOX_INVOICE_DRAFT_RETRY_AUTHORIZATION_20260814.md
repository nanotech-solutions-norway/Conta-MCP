# Conta sandbox invoice-draft retry-until-complete authorization — 2026-08-14

## Status

```text
authorization_status=OPERATOR_APPROVED_PENDING_EXECUTION
candidate_id=invoice_draft_create_v2
environment=sandbox
retry_until_completed=true
automatic_retry=true
per_attempt_provider_mutations=1
total_retry_series_provider_mutations=UNTIL_COMPLETED_OR_SAFETY_STOP
readback_required=true
production_write_authorized=false
```

The operator explicitly superseded the previous one-mutation-maximum restriction on 2026-08-14 and instructed the sandbox invoice-draft validation to retry until completed.

This authorization remains limited to the previously approved synthetic sandbox invoice-draft validation payload and sandbox organization. It does not authorize production writes, sending/posting an invoice, payment actions, ledger posting, VAT/statutory actions, cleanup, or any other Conta mutation.

## Retry policy

1. Each individual attempt may dispatch at most one provider POST and must use a fresh one-use approval envelope, nonce and idempotency key.
2. A failed attempt may be retried automatically when no provider mutation occurred, or when an authorized GET-only post-state proves that no invoice draft exists after the failed attempt.
3. A retry series continues until the invoice draft is created and the configured GET readback verifies the created object, subject to the workflow execution window.
4. If a provider POST may have been accepted but the result is indeterminate, or GET evidence indicates an object exists without verifiable identity/readback, the retry series must stop before another POST to prevent duplicate drafts.
5. If pre-state indicates an existing invoice draft, the retry series must stop before another POST.
6. Same-key replay rejection remains mandatory for each dispatched attempt.
7. The write kill switch must close after every attempt.
8. Production write authorization remains false.

## Runtime compatibility evidence incorporated

The GET-only sandbox diagnostic on 2026-08-13 observed the invoice-draft list endpoint returning an aggregate object with top-level keys `hitCount`, `pageCount`, and `sum`, without a `hits` array. The retry workflow is authorized to recognize `hitCount` as the authoritative zero/non-zero pre-state indicator while remaining fail-closed for malformed or inconsistent responses.

## Supersession

This document supersedes only these fields in `SANDBOX_INVOICE_DRAFT_ONE_CALL_AUTHORIZATION_20260813.md`:

```text
max_provider_mutations=1
automatic_retry=false
```

All other sandbox-only boundaries, payload identity constraints, readback requirements, replay protection, kill-switch requirements and production-write exclusions remain in force.
