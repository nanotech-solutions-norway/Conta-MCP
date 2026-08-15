# Conta sandbox invoice-draft retry continuation — 2026-08-15

## Authorization continuation

The operator previously authorized the Conta sandbox invoice-draft validation to retry until completed. This continuation remains sandbox-only and production writes remain unauthorized.

Two execution defects have now been identified and corrected:

1. Numeric organization IDs were coerced while constructing the local allowlist, causing a pre-dispatch `write_organization_not_allowlisted` failure.
2. After that correction, run `31881756053` reached Conta but HTTP 422 was incorrectly classified as automatically retryable whenever GET post-state proved no draft existed. The completed job log confirms 58 completed 422 attempts before manual cancellation during backoff.

Run #4 evidence after each completed attempt was materially consistent:

```text
EXECUTION_OUTCOME=FAILED_NO_OBJECT_OBSERVED_BY_POSTSTATE_GET
LEDGER_RESERVED=true
SAME_KEY_REPLAY_REJECTED=true
KILL_SWITCH_CLOSED=true
PRODUCTION_WRITE_AUTHORIZED=false
PROVIDER_RESULT_STATUS=422
READBACK_VERIFIED=false
```

The cancellation occurred during the post-attempt sleep after attempt 58 had fully completed; it did not interrupt a provider POST or readback.

## Corrected retry boundary

The retry-until-complete authorization now operates under these stricter implementation controls:

```text
environment=sandbox
retry_until_completed=true
per_attempt_provider_mutations=1
max_attempts_per_workflow_run=3
provider_4xx_retry_allowed=false
provider_429_retry_allowed=true
provider_5xx_retry_allowed=true
readback_required=true
production_write_authorized=false
```

A provider retry after a dispatched POST is permitted only for HTTP 429 or 5xx and only when mandatory GET post-state proves that no invoice draft exists. Any other 4xx, including HTTP 422, is terminal for that workflow run.

## Next execution: 422 diagnostic continuation

The next protected sandbox execution is authorized solely to capture the provider's redacted validation diagnostic while preserving the approved payload unchanged.

If the provider again returns HTTP 422, the run must:

1. complete the one authorized POST;
2. perform mandatory GET post-state reconciliation;
3. emit only redacted diagnostic markers such as provider error body hash, JSON key paths and masked selected scalar error fields;
4. close replay and kill-switch controls;
5. stop immediately with no second POST.

No payload field is authorized to change from inference alone. Any subsequent payload correction must be based on the captured provider diagnostic or other authoritative evidence.
