# Sandbox invoice-draft 422 diagnostic plan — 15.08.2026

## Objective

Capture the provider's redacted HTTP 422 diagnostic from exactly one controlled sandbox invoice-draft attempt, then correct the request only from evidence.

## Current evidence

- Provider route reached successfully.
- Provider returned HTTP 422 repeatedly.
- GET post-state showed no invoice draft after every supplied attempt.
- Same-key replay rejection passed.
- Kill switch closed after every supplied attempt.
- Production write authorization remains false.

## Next execution rule

The next controlled sandbox run must use the corrected bounded workflow. For a provider HTTP 422 it must:

1. perform the authorized POST once;
2. reconcile GET post-state;
3. emit redacted provider diagnostics only;
4. classify 422 as terminal;
5. perform no second POST.

Expected diagnostic markers include:

```text
PROVIDER_RESULT_STATUS=422
PROVIDER_ERROR_BODY_SHA256=<sha256>
PROVIDER_ERROR_TOP_LEVEL_KEYS=<keys>
PROVIDER_ERROR_KEY_PATHS=<paths>
PROVIDER_ERROR_<SAFE_FIELD>=<redacted scalar diagnostic, when available>
RETRY_TERMINAL_PROVIDER_4XX=true
```

## Candidate hypotheses — not yet approved as fixes

The current request should not be changed from inference alone. Potential classes to distinguish from the provider error are:

- invoice-draft line numbering/business-rule validation;
- VAT-code validity for the sandbox organization;
- another request-content validation rule;
- feature/subscription entitlement or organization configuration.

The provider diagnostic, not these hypotheses, controls the next payload correction.
