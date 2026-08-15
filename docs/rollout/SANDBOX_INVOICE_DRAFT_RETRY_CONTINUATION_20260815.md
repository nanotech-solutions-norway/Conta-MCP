# Conta sandbox invoice-draft retry continuation — 2026-08-15

## Authorization continuation

The operator previously authorized the Conta sandbox invoice-draft validation to retry until completed. The first retry-series run on 2026-08-15 failed before provider dispatch with:

```text
PRIMARY_ERROR_CLASS=write_organization_not_allowlisted
LEDGER_RESERVED=false
PROVIDER_RESULT_STATUS=0
```

No provider mutation occurred. This continuation record authorizes the existing retry-until-complete sandbox series to resume after the local numeric organization allowlist normalization defect is corrected.

Boundaries remain unchanged:

```text
environment=sandbox
retry_until_completed=true
automatic_retry=true
per_attempt_provider_mutations=1
readback_required=true
production_write_authorized=false
```

A retry must stop before another POST whenever a previous attempt may have created an object, an invoice draft is observed in pre/post state without verified identity, or dispatch state cannot be proven safe. Production writes remain unauthorized.
