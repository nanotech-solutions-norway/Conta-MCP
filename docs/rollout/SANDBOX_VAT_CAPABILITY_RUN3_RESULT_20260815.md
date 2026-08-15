# Conta Sandbox VAT Capability Run #3 Result — 15.08.2026

## Classification

```text
GET_ONLY_DIAGNOSTIC_COMPLETE=true
SANDBOX_ORG_ACTIVE=true
SANDBOX_ORG_BLOCKED=false
BOOKKEEPING_ENABLED=true
SALES_ACCOUNT_SCOPE=3000-3999
ACTIVE_SALES_BOOKKEEPING_VAT_CODE_OCCURRENCES=15
ACTIVE_SALES_BOOKKEEPING_OUTPUT_VAT_CODE_OCCURRENCES=12
ACTIVE_SALES_BOOKKEEPING_VAT_CODES_INVOICE_FORM=exempted,export,high,low,medium,zero.rate
HIGH_OUTPUT_VAT_OBSERVED=true
NO_VAT_OUTPUT_OBSERVED=false
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
INVOICE_DRAFT_POST_AUTHORIZED=false
CURRENT_BLOCKER=INVOICE_DRAFT_REQUEST_SEMANTICS_OR_INVOICE_LAYER_ELIGIBILITY
```

## Evidence

Protected workflow:
- `Conta Sandbox VAT Capability Diagnostic`
- run number: `3`
- run id: `31903404156`
- head SHA: `8f6b7c020ed61087c474edbbb25c15c29bbf76e6`
- conclusion: `success`

Observed safe markers:

```text
ORG_ACCESS_ACTIVE=true
ORG_BLOCKED_STATUS=NOT_BLOCKED
PRODUCT_VAT_CODE_OCCURRENCES=0
PRODUCT_VAT_CODES_RAW=none
PRODUCT_VAT_CODES_INVOICE_FORM=none
INVOICE_VAT_CODE_OCCURRENCES=0
INVOICE_VAT_CODES_OBSERVED=none
SALES_BOOKKEEPING_ACCOUNTS_GET_STATUS=200
ACTIVE_SALES_BOOKKEEPING_VAT_CODE_OCCURRENCES=15
ACTIVE_SALES_BOOKKEEPING_OUTPUT_VAT_CODE_OCCURRENCES=12
ACTIVE_SALES_BOOKKEEPING_VAT_CODES_RAW=input.no.vat,output.exempted,output.export,output.high,output.low,output.medium,output.zero.rate
ACTIVE_SALES_BOOKKEEPING_VAT_CODES_INVOICE_FORM=exempted,export,high,low,medium,zero.rate
SUBSCRIPTION_PLAN_GET_STATUS=200
SUBSCRIPTION_ALLOW_BOOKKEEPING=true
OBSERVED_ORG_INVOICE_VAT_CODES=exempted,export,high,low,medium,zero.rate
PROVIDER_HTTP_METHODS_USED=GET_ONLY
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Interpretation

Run #3 supersedes the incomplete bookkeeping interpretation from run #2. The sandbox organization has active output VAT configuration on sales/revenue accounts, including `output.high`. Therefore a missing output VAT configuration is not a valid explanation for the previous `WrongVatCodeException` returned by the invoice-draft route.

Conta's current external API contract uses short invoice-route VAT tokens (`high`, `medium`, `low`, `zero.rate`, `exempted`, `export`, `no.vat`) and states that the invoice-draft route converts those values to internal VAT codes. The bookkeeping-account result uses internal-style codes such as `output.high`. Accordingly, the invoice-draft request must continue using `high`; changing it to `output.high` would not follow the invoice-route contract.

The runtime dispatch path was also inspected and does not remap the invoice payload after authorization. The current retry preparer binds exactly one source payload VAT anchor from `no.vat` to `high` before execution.

## Next read-only gate

The next controlled test will preserve `vatCode=high` and add only `lineNo=1` to the single synthetic invoice-draft line, matching Conta's published invoice-draft example. Because this changes the canonical payload, a protected GET-only preview must first resolve the synthetic customer and calculate a new exact payload SHA-256. No provider mutation is authorized by this record.

If a later bounded POST still returns `WrongVatCodeException`, diagnostic output should include safely redacted nested localized provider message values so the provider's detailed explanation can be evaluated without exposing identifiers or business data.

Production writes remain disabled.
