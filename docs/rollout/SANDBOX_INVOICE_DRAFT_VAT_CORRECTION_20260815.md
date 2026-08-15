# Sandbox invoice-draft VAT correction — 15.08.2026

## Run #5 diagnostic result

Protected sandbox workflow run `31883320242` executed exactly one provider POST and terminated on the first non-retryable HTTP 422.

Observed safe markers:

```text
RETRY_ATTEMPT=1
EXECUTION_OUTCOME=FAILED_NO_OBJECT_OBSERVED_BY_POSTSTATE_GET
LEDGER_RESERVED=true
SAME_KEY_REPLAY_REJECTED=true
KILL_SWITCH_CLOSED=true
PRODUCTION_WRITE_AUTHORIZED=false
AUTOMATIC_RETRY_PERFORMED=false
PROVIDER_RESULT_STATUS=422
READBACK_VERIFIED=false
PROVIDER_ERROR_NAME=WrongVatCodeException
RETRY_TERMINAL_PROVIDER_4XX=true
```

The provider error body was not printed. The diagnostic exposed only the error-body hash, key names/paths and exception name. GET post-state reconciliation observed no invoice draft.

## Diagnosis

The rejected synthetic payload used:

```text
vatCode=no.vat
```

The current official Conta API help shows `vatCode: "high"` in the documented `Create an Invoice draft` example for ordinary invoice-draft lines:

- https://hjelp.conta.no/api/

The earlier repository payload contract had selected `no.vat` from the documented schema enum without live business-rule validation. Run #5 proves that value is not accepted for this protected sandbox draft context.

## Correction sequence

Do not weaken the existing full-payload SHA-256 gate. The customer identifier is protected and participates in the canonical full payload hash, so the corrected full hash cannot be safely precomputed outside the protected environment.

Required sequence:

1. change only the synthetic line VAT code from `no.vat` to `high` in the protected preview workflow;
2. run the preview using GET only and retrieve the new canonical full payload SHA-256;
3. bind that exact hash into the one-call execution script/authorization;
4. execute the next protected sandbox attempt under the existing bounded retry controls;
5. require provider create success plus GET readback verification before declaring completion.

## Safety boundary

```text
environment=sandbox
preview_provider_methods=GET_ONLY
preview_provider_mutation=false
next_execution_max_provider_mutations_per_attempt=1
non_429_4xx_terminal=true
max_attempts=3
production_write_authorized=false
```

No production write is authorized by this correction record.
