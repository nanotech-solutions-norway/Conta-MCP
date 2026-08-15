# Conta sandbox invoice-draft retry continuation — 2026-08-15

## Authorization continuation

The operator previously authorized the Conta sandbox invoice-draft validation to retry until completed. This continuation remains sandbox-only and production writes remain unauthorized.

## Prior execution evidence

The following defects and provider findings are established:

1. Numeric organization IDs were coerced while constructing the local allowlist, causing a pre-dispatch `write_organization_not_allowlisted` failure. This was corrected.
2. Run `31881756053` reached Conta but HTTP 422 was incorrectly classified as automatically retryable whenever GET post-state proved no draft existed. The completed job log confirms 58 completed 422 attempts before manual cancellation during backoff. This was corrected by making non-429 4xx terminal and bounding each workflow run to three attempts.
3. Bounded diagnostic run `31883320242` made exactly one POST, received HTTP 422, reconciled zero drafts and emitted `PROVIDER_ERROR_NAME=WrongVatCodeException`. The rejected payload used `vatCode=no.vat`.
4. Protected preview run `31884357398` corrected the invoice-route token to `vatCode=high` and produced payload SHA-256 `61bb8961a82a45f0304909473c020f2f721d738aa4ea6c934722a258d2f346e0`.
5. Bounded execution run `31886632151` used that exact `high` payload, made exactly one POST, received HTTP 422 `WrongVatCodeException`, reconciled zero drafts, rejected same-key replay, closed the kill switch, performed no automatic retry, and kept production writes false.

## Organization-specific VAT capability evidence

Protected GET-only VAT diagnostic run `31903404156` completed successfully and scoped the bookkeeping read to active sales/revenue accounts `3000–3999` with explicit paging.

Observed safe markers included:

```text
ORG_ACCESS_ACTIVE=true
ORG_BLOCKED_STATUS=NOT_BLOCKED
PRODUCT_VAT_CODE_OCCURRENCES=0
INVOICE_VAT_CODE_OCCURRENCES=0
SALES_BOOKKEEPING_ACCOUNTS_GET_STATUS=200
ACTIVE_SALES_BOOKKEEPING_VAT_CODE_OCCURRENCES=15
ACTIVE_SALES_BOOKKEEPING_OUTPUT_VAT_CODE_OCCURRENCES=12
ACTIVE_SALES_BOOKKEEPING_VAT_CODES_RAW=input.no.vat,output.exempted,output.export,output.high,output.low,output.medium,output.zero.rate
ACTIVE_SALES_BOOKKEEPING_VAT_CODES_INVOICE_FORM=exempted,export,high,low,medium,zero.rate
SUBSCRIPTION_ALLOW_BOOKKEEPING=true
OBSERVED_ORG_INVOICE_VAT_CODES=exempted,export,high,low,medium,zero.rate
PROVIDER_HTTP_METHODS_USED=GET_ONLY
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

This proves `output.high` exists on active sales accounts. Therefore the previous `high` rejection is not evidence that the sandbox lacks output VAT configuration, and the invoice payload must continue using the short invoice-route token `high`, not `output.high`.

## Protected line-number preview

Conta's current invoice-draft example supplies sequential line numbers. The previously rejected synthetic payload omitted `lineNo`.

Protected GET-only preview run `31903886829` completed successfully with the same synthetic customer and business fields, changing only the single invoice line by adding `lineNo=1`.

```text
environment=sandbox
provider_methods=GET_ONLY
provider_mutation=false
synthetic_fixture_exact_match=true
synthetic_fixture_readback=true
customer_identifier_printed=false
vatCode=high
lineNo=1
payload_sha256=79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7
execution_authorization_granted=false
production_write_authorized=false
```

The protected customer identifier remains masked and is not committed.

## Corrected retry boundary

```text
environment=sandbox
retry_until_completed=true
per_attempt_provider_mutations=1
max_attempts_per_workflow_run=3
provider_4xx_retry_allowed=false
provider_429_retry_allowed=true
provider_5xx_retry_allowed=true
readback_required=true
same_key_replay_rejection_required=true
kill_switch_required=true
production_write_authorized=false
```

A provider retry after a dispatched POST is permitted only for HTTP 429 or 5xx and only when mandatory GET post-state proves that no invoice draft exists. Any other 4xx is terminal for that workflow run.

## Next execution: exact preview-bound payload

The next protected sandbox execution is authorized to use exactly:

```text
vatCode=high
lineNo=1
payload_sha256=79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7
```

All other synthetic payload fields remain unchanged from the previously approved invoice-draft validation payload.

The runtime binding is deliberately two-stage and temporary:

1. `prepare-conta-sandbox-invoice-draft-retry.py` applies the already-reviewed sandbox compatibility, retry, diagnostics, and `high` VAT binding.
2. `bind-conta-sandbox-invoice-draft-lineno.py` changes only the already-previewed payload hash from `61bb...` to `79ae...` and inserts `lineNo => 1` immediately after `vatCode => high`.

The controlled-write CI reproduces both stages and verifies the final runtime script contains the exact protected hash, `vatCode=high`, and `lineNo=1` before merge.

Success requires all of the following:

1. sandbox boundary verified;
2. pre-state explicitly empty;
3. exact synthetic customer resolved and GET-verified;
4. runtime payload hash equals the preview-bound SHA-256 above;
5. at most one provider POST per attempt;
6. create response succeeds and a draft identifier is observed;
7. GET readback verifies the created draft identity/content;
8. same-key replay rejection passes;
9. kill switch closes;
10. production authorization remains false.

Stop without another POST if an object exists unverified, dispatch state becomes indeterminate, pre-state is non-empty, replay protection fails, or any non-retryable provider response occurs.

No global write enablement and no production write are authorized by this continuation.
