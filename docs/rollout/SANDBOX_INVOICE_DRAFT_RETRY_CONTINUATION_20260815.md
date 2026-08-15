# Conta sandbox invoice-draft retry continuation — 2026-08-15

## Authorization continuation

The operator previously authorized the Conta sandbox invoice-draft validation to retry until completed. This continuation remains sandbox-only and production writes remain unauthorized.

Three execution defects/evidence points have now been identified:

1. Numeric organization IDs were coerced while constructing the local allowlist, causing a pre-dispatch `write_organization_not_allowlisted` failure. This was corrected.
2. Run `31881756053` reached Conta but HTTP 422 was incorrectly classified as automatically retryable whenever GET post-state proved no draft existed. The completed job log confirms 58 completed 422 attempts before manual cancellation during backoff. This was corrected by making non-429 4xx terminal and bounding each workflow run to three attempts.
3. Bounded diagnostic run `31883320242` made exactly one POST, received HTTP 422, reconciled zero drafts and emitted `PROVIDER_ERROR_NAME=WrongVatCodeException`. The rejected payload used `vatCode=no.vat`.

## Authoritative payload correction

The protected GET-only preview was corrected to `vatCode=high`, matching the current Conta invoice-draft example, while preserving every other synthetic payload field.

Protected preview run `31884357398` completed successfully with:

```text
environment=sandbox
provider_methods=GET_ONLY
provider_mutation=false
synthetic_fixture_exact_match=true
synthetic_fixture_readback=true
customer_identifier_printed=false
vatCode=high
payload_sha256=61bb8961a82a45f0304909473c020f2f721d738aa4ea6c934722a258d2f346e0
```

The next execution is bound to that exact full canonical payload hash. The protected customer identifier remains masked and is not committed.

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

## Next execution: preview-bound corrected payload

The next protected sandbox execution is authorized to use exactly:

```text
vatCode=high
payload_sha256=61bb8961a82a45f0304909473c020f2f721d738aa4ea6c934722a258d2f346e0
```

All other synthetic payload fields remain unchanged from the previously approved invoice-draft validation payload.

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

No production write is authorized by this continuation.
