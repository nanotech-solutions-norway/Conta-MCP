# Conta Sandbox Invoice Draft — lineNo attempt result — 2026-08-16

## Scope

Records protected sandbox execution run `31910909286` (`Conta Sandbox Invoice Draft Retry Until Complete`, run #7) after protected GET-only preview run `31903886829` bound the exact payload with `vatCode=high`, `lineNo=1`, and canonical SHA-256 `79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7`.

## Runtime binding

The execution log confirms both temporary runtime binding stages completed successfully before provider dispatch:

```text
RUNTIME_COMPATIBILITY_PATCH_APPLIED=true
NUMERIC_ORGANIZATION_ALLOWLIST_PATCH_APPLIED=true
PROVIDER_ERROR_DIAGNOSTICS_PATCH_APPLIED=true
CORRECTED_VAT_CODE_BOUND=high
LINE_NUMBER_BOUND=1
CORRECTED_PAYLOAD_SHA256_BOUND=79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7
PRODUCTION_WRITE_AUTHORIZED=false
```

## Execution result

Exactly one sandbox provider POST was attempted. Conta rejected the request with HTTP 422 and the same provider exception/body hash previously observed for the `high` VAT payload without `lineNo`:

```text
RETRY_ATTEMPT=1
EXECUTION_OUTCOME=FAILED_NO_OBJECT_OBSERVED_BY_POSTSTATE_GET
PAYLOAD_SHA256=79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7
LEDGER_RESERVED=true
SAME_KEY_REPLAY_REJECTED=true
KILL_SWITCH_CLOSED=true
PRODUCTION_WRITE_AUTHORIZED=false
AUTOMATIC_RETRY_PERFORMED=false
PROVIDER_RESULT_STATUS=422
READBACK_VERIFIED=false
PROVIDER_ERROR_BODY_SHA256=6bbeb7fe3983e79d101561ab4e6020d44ccb3a88f680b4cb67c3bb7073d5e7ad
PROVIDER_ERROR_TOP_LEVEL_KEYS=category,messages,name
PROVIDER_ERROR_KEY_PATHS=category,name,messages,messages.NO,messages.EN
PROVIDER_ERROR_NAME=WrongVatCodeException
RETRY_TERMINAL_PROVIDER_4XX=true
PROVIDER_4XX_STATUS=422
```

## Findings

1. `lineNo=1` is not the cause of the prior VAT rejection; adding the correctly numbered line produced the identical `WrongVatCodeException` and identical provider error-body SHA-256.
2. The request reached Conta with the exact protected preview hash. The temporary binder and payload hash gate worked as designed.
3. Mandatory GET post-state observed no invoice draft. No created object is known to exist from this attempt.
4. The retry classifier correctly treated the deterministic 422 as terminal. No automatic second POST occurred.
5. Same-key replay protection and kill-switch closure both passed. Production writes remained disabled.
6. Prior GET-only VAT-capability evidence showed active sales accounts containing `output.high`, so bookkeeping chart-of-account VAT capability alone does not explain the invoice-layer rejection.

## Current blocker

Conta's external API defines `WrongVatCodeException` as the condition where an organization attempts to use a VAT code not available for that organization type. The public external API does not expose a VAT-registration / invoice-VAT enablement property for the organization.

Conta's help documentation separately states that a VAT-registered company must enable invoicing with VAT in Conta settings. Therefore the next safe diagnostic gate is a provider-side sandbox settings check for the invoice VAT setting, not another API POST and not another guessed VAT token.

## Next gate

Operator checks the protected Conta sandbox organization in the Conta UI:

- open Settings / Innstillinger;
- locate the invoice VAT setting (described by Conta help as enabling invoicing with VAT / `fakturere med MVA`);
- if it is disabled and this sandbox organization is intended to model a VAT-registered Norwegian business, enable it;
- if already enabled, make no change and report that state.

No further provider POST is authorized by this evidence record until that state is resolved.

Production writes remain disabled.
