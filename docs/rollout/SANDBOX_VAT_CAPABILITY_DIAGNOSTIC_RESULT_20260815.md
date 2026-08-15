# Sandbox VAT Capability Diagnostic Result — 15 August 2026

## Run examined

- Workflow: `Conta Sandbox VAT Capability Diagnostic`
- Run ID: `31889269761`
- Run number: `1`
- Head SHA: `c40f50d7b12da01955998e66a9dc3a8ad387e5d4`
- Conclusion: `success`

## Read-only evidence

The protected sandbox diagnostic used provider HTTP `GET` only. No provider mutation was performed and production write authorization remained false.

Observed organization state:

- `ORG_ACCESS_ACTIVE=true`
- `ORG_BLOCKED_STATUS=NOT_BLOCKED`

Observed VAT history/configuration from current invoice-domain records:

- `PRODUCT_VAT_CODE_OCCURRENCES=0`
- `PRODUCT_VAT_CODES_RAW=none`
- `PRODUCT_VAT_CODES_INVOICE_FORM=none`
- `INVOICE_VAT_CODE_OCCURRENCES=0`
- `INVOICE_VAT_CODES_OBSERVED=none`
- `OBSERVED_ORG_INVOICE_VAT_CODES=none`

## Interpretation

The organization is active and not blocked, but it has no existing product or invoice VAT-code history that can identify an accepted invoice-draft VAT code. This result is therefore insufficient to choose another write payload safely.

The next diagnostic must remain GET-only and inspect organization-specific configuration evidence rather than test another VAT code by POST. The preferred provider-supported read path is the organization bookkeeping-account list, whose response model includes `vatCode` and `isActive`, supplemented by the organization subscription-plan read to establish whether bookkeeping capability is available.

No additional sandbox invoice-draft POST is authorized by this record.
