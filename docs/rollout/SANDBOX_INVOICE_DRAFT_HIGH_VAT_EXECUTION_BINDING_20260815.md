# Sandbox invoice-draft high-VAT execution binding — 15.08.2026

## Protected preview result

Protected GET-only preview workflow run `31884357398` completed successfully on commit `604c788a62fabe88fa24bbb4e2ac7d4e496fd74a`.

Observed safe facts:

```text
preview_status=success
environment=sandbox
provider_methods=GET_ONLY
provider_mutation=false
synthetic_fixture_exact_match=true
synthetic_fixture_readback=true
customer_identifier_printed=false
vatCode=high
payload_sha256=61bb8961a82a45f0304909473c020f2f721d738aa4ea6c934722a258d2f346e0
```

## Execution binding

The next protected sandbox invoice-draft attempt is bound to exactly the corrected preview payload above.

Only these payload-contract changes relative to the previously rejected attempt are authorized:

```text
vatCode: no.vat -> high
payload_sha256: dab571f2807745e1236a30dc93ae34ca8b8d2b15daaa26034f68a255e170b786 -> 61bb8961a82a45f0304909473c020f2f721d738aa4ea6c934722a258d2f346e0
```

All other synthetic payload fields remain unchanged.

The existing retry-until-complete authorization continues subject to the hardened retry policy:

```text
environment=sandbox
per_attempt_provider_mutations=1
max_attempts=3
non_429_4xx_terminal=true
retryable_provider_statuses=429_or_5xx_only
readback_required=true
same_key_replay_rejection_required=true
kill_switch_required=true
production_write_authorized=false
```

The previous HTTP 422 `WrongVatCodeException` attempt created no invoice draft according to GET post-state reconciliation. The corrected execution must still fail closed if pre-state is non-empty, dispatch becomes indeterminate, a created object cannot be verified, replay rejection fails, or any production boundary is encountered.

No production write is authorized by this record.
