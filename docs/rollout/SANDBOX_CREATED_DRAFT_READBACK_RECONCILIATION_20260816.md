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

The new protected workflow `Conta Sandbox Created Draft Readback Reconciliation` performs only GET requests. It:

1. resolves the same protected synthetic customer;
2. searches invoice drafts for that customer;
3. identifies the exact already-created draft using the committed SHA-256 of its draft ID, without printing the raw ID;
4. GETs that draft;
5. runs the existing verifier against the exact protected payload;
6. emits mismatch paths/reasons and safe scalar-type diagnostics for controlled fields;
7. performs no POST, PUT, PATCH, DELETE, send, finalize, credit, update, or cleanup action.

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
