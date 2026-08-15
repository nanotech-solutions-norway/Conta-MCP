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

The controlled sandbox create is now proven to work after enabling invoicing with VAT. The remaining blocker is **readback verification compatibility**, not provider write capability.

`InvoiceDraftReadbackVerifier` currently compares scalar values using strict PHP identity (`!==`). The current Conta OpenAPI models invoice-draft `price`, `quantity`, and `discount` as JSON `number` values. JSON representations such as `1` and `1.0` are semantically equivalent for the provider schema but decode to different PHP scalar types and can therefore fail the existing strict verifier.

That is a plausible cause, but no verifier relaxation is authorized from inference alone.

## GET-only reconciliation gate

The protected workflow `Conta Sandbox Created Draft Readback Reconciliation` performs only GET requests. It:

1. resolves the same protected synthetic customer;
2. lists the complete sandbox invoice-draft collection using the already-proven `hits`, `page`, and `sort` parameters;
3. requires the post-create collection to contain exactly one draft, consistent with the proven zero-draft pre-state and exactly one successful provider POST;
4. identifies that exact already-created draft using the committed SHA-256 of its draft ID, without printing the raw ID;
5. GETs that draft;
6. runs the existing verifier against the exact protected payload;
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

No additional invoice draft may be created while this existing object remains unverified.

## First GET-only reconciliation run — failed before draft readback

Workflow run `31913656966` passed the protected GET-only boundary and PHP syntax validation, then failed at the invoice-draft search step with:

```text
invoice_draft_search_failed
```

The run did not execute any provider mutation. Its boundary markers were:

```text
GET_ONLY_BOUNDARY_VERIFIED=true
PRODUCTION_WRITE_AUTHORIZED=false
```

The first diagnostic had added customer/type/currency filters to the invoice-draft search. Although the current OpenAPI exposes those query parameters, those filters are unnecessary for this reconciliation and introduced an avoidable failure surface.

### Corrective action

The diagnostic is narrowed to the invariant already established by controlled-write evidence:

- invoice-draft pre-state immediately before creation was exactly zero;
- exactly one provider POST was made after VAT was enabled;
- that POST returned a draft ID whose SHA-256 is committed above;
- therefore the post-create full collection must contain exactly one draft for this controlled reconciliation.

The revised diagnostic now:

- resolves the synthetic customer using the same recursive exact-name matcher as the successful controlled-write script;
- lists all invoice drafts using only `hits=100`, `page=0`, `sort=id`;
- requires `hitCount=1` and one returned hit object;
- validates the hit ID against the committed draft-ID hash;
- emits safe HTTP status/body-hash/top-level-key diagnostics on any GET failure;
- preserves the GET-only mutation guard and production-write prohibition.
