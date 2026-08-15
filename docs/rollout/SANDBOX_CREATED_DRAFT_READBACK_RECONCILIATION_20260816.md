# Conta Sandbox Created Draft Readback Reconciliation — 2026-08-16

## Established execution state

Protected sandbox workflow run `31913305907` has two GitHub Actions attempts.

### Attempt 1 — provider mutation occurred

Run attempt 1 (`job 95081486064`) used the exact protected payload:

```text
vatCode=high
lineNo=1
payload_sha256=79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7
```

After the operator enabled invoice VAT in the Conta sandbox UI, the provider accepted the create request and returned an invoice-draft object. The mandatory readback also returned an invoice-draft object, but the local strict verifier did not accept the readback representation.

Safe result markers:

```text
EXECUTION_OUTCOME=SUCCEEDED_OBJECT_OBSERVED_UNVERIFIED
LEDGER_RESERVED=true
SAME_KEY_REPLAY_REJECTED=true
KILL_SWITCH_CLOSED=true
PRODUCTION_WRITE_AUTHORIZED=false
AUTOMATIC_RETRY_PERFORMED=false
PROVIDER_RESULT_STATUS=502
READBACK_VERIFIED=false
DRAFT_ID_SHA256=eab8ff114cc63fd8ab3d9f42249e20b8ce5ecce463e8368e98747f03c50eeabb
READBACK_PROJECTION_SHA256=5b70e2d16d54ba0246b6380dcdef36e88c2f516c478be194ce62dc8fc7ecc8f8
```

The `502` is generated locally by `ContaClient::createInvoiceDraft()` when the readback verifier reports mismatches; it is not evidence that Conta rejected the create. The logged response key paths show both `create` and `readback` invoice-draft objects.

Because an object exists, the workflow correctly stopped and no further provider mutation is authorized.

### Attempt 2 — no provider mutation

The user later re-ran the same GitHub Actions run. GitHub records this as `run_attempt=2`. The PHP guard rejected it before dispatch with:

```text
workflow_rerun_not_authorized
```

No ledger reservation, provider status, or provider execution outcome was produced in attempt 2. The rerun guard therefore operated as designed and must not be weakened.

## Current blocker

The controlled sandbox create is proven to work after enabling invoicing with VAT. The remaining work is readback-verifier compatibility, not provider write capability.

## GET-only reconciliation gate

The protected workflow `Conta Sandbox Created Draft Readback Reconciliation` performs only GET requests. It:

1. resolves the same protected synthetic customer;
2. lists the complete sandbox invoice-draft collection using the already-proven `hits`, `page`, and `sort` parameters;
3. requires the post-create collection to contain exactly one draft, consistent with the proven zero-draft pre-state and exactly one successful provider POST;
4. identifies that exact already-created draft using the committed SHA-256 of its draft ID, without printing the raw ID;
5. GETs that draft;
6. runs the verifier against the exact protected payload;
7. emits mismatch paths/reasons and safe scalar-type diagnostics for controlled fields;
8. performs no POST, PUT, PATCH, DELETE, send, finalize, credit, update, or cleanup action.

Expected draft ID SHA-256:

```text
eab8ff114cc63fd8ab3d9f42249e20b8ce5ecce463e8368e98747f03c50eeabb
```

Required markers include:

```text
PROVIDER_HTTP_METHODS_USED=GET_ONLY
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

No additional invoice draft may be created while this existing object remains under reconciliation.

## First GET-only reconciliation run — failed before draft readback

Workflow run `31913656966` passed the protected GET-only boundary and PHP syntax validation, then failed at the invoice-draft search step with:

```text
invoice_draft_search_failed
```

The run did not execute any provider mutation. The diagnostic was then narrowed to the established zero-before / exactly-one-after invariant and the full draft list.

## Second GET-only reconciliation run — mismatch isolated

Workflow run `31914316870`, job `95083868934`, completed successfully using GET only. It found exactly one invoice draft and matched the committed draft-ID hash.

Key markers:

```text
INVOICE_DRAFT_SEARCH_STATUS=200
INVOICE_DRAFT_SEARCH_HIT_COUNT=1
EXPECTED_PAYLOAD_SHA256=79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7
EXPECTED_PAYLOAD_HASH_MATCH=true
EXPECTED_DRAFT_FOUND=true
DRAFT_ID_SHA256=eab8ff114cc63fd8ab3d9f42249e20b8ce5ecce463e8368e98747f03c50eeabb
READBACK_VERIFIED=false
MISMATCH_COUNT=2
MISMATCH_1_PATH=invoiceDraftLines.0.price
MISMATCH_1_REASON=value_mismatch
MISMATCH_2_PATH=registrationSource
MISMATCH_2_REASON=missing_in_readback
PROVIDER_HTTP_METHODS_USED=GET_ONLY
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

The field diagnostics established:

- expected `price` type = float;
- actual `price` type = integer;
- strict equality = false;
- numeric equality = true;
- actual price value = `1`;
- expected `registrationSource` = string;
- actual `registrationSource` = null / omitted;
- all substantive controlled business fields match: type, customerId, invoiceLanguage, invoiceCurrency, line description, quantity, discount, vatCode=`high`, and lineNo=`1`.

### Verified compatibility rules

The current Conta OpenAPI models invoice-draft line `price`, `quantity`, and `discount` as JSON `number` values. Therefore integer and floating-point lexical representations of the same numeric value are semantically equivalent. The verifier may accept numeric equality when both decoded values are actual PHP numeric scalars (`int` or `float`), while continuing to reject numeric strings.

The OpenAPI response model exposes `registrationSource` as a property but does not mark it as required. The live sandbox readback omitted it. Therefore absence of `registrationSource` alone must not fail readback verification; if the provider returns the field, its value remains subject to strict verification.

The corrective implementation must remain fail-closed for substantive mismatches, including VAT code, customer, line content, currency, language, type, and line numbering.
