# Conta Sandbox VAT Capability Result — 15.08.2026

## Classification

```text
GET_ONLY_DIAGNOSTIC_COMPLETE=true
SANDBOX_ORG_ACTIVE=true
SANDBOX_ORG_BLOCKED=false
BOOKKEEPING_ENABLED=true
PRODUCT_VAT_HISTORY_PRESENT=false
INVOICE_VAT_HISTORY_PRESENT=false
ACTIVE_BOOKKEEPING_VAT_CODE_OCCURRENCES=20
ACTIVE_BOOKKEEPING_VAT_CODES_RAW=input.no.vat
OBSERVED_ORG_INVOICE_VAT_CODES=no.vat
OUTPUT_VAT_CODE_OBSERVED=false
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
INVOICE_DRAFT_POST_AUTHORIZED=false
CURRENT_BLOCKER=SANDBOX_ORGANIZATION_VAT_CONFIGURATION
```

## Evidence

Protected workflow:
- `Conta Sandbox VAT Capability Diagnostic`
- run number: `2`
- run id: `31892575746`
- head SHA: `886cf95ce507b8788fe01bc432d90d03555da04a`
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
BOOKKEEPING_ACCOUNTS_GET_STATUS=200
ACTIVE_BOOKKEEPING_VAT_CODE_OCCURRENCES=20
ACTIVE_BOOKKEEPING_VAT_CODES_RAW=input.no.vat
ACTIVE_BOOKKEEPING_VAT_CODES_INVOICE_FORM=no.vat
SUBSCRIPTION_PLAN_GET_STATUS=200
SUBSCRIPTION_ALLOW_BOOKKEEPING=true
OBSERVED_ORG_INVOICE_VAT_CODES=no.vat
PROVIDER_HTTP_METHODS_USED=GET_ONLY
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Interpretation

The sandbox organization is accessible, active, not blocked, and has bookkeeping capability. It contains no product or invoice VAT history. Its active bookkeeping configuration exposes only `input.no.vat`; no output VAT code was observed.

Previous bounded invoice-draft attempts with route-level VAT codes `no.vat` and `high` both terminated with HTTP 422 `WrongVatCodeException`, with mandatory GET reconciliation proving that no invoice draft existed after each failed attempt.

Conta's current external API contract defines the invoice-route VAT tokens `no.vat`, `high`, `medium`, `low`, `zero.rate`, `exempted`, and `export`. The same contract defines `WrongVatCodeException` as the condition where an organization attempts to use a VAT code not available for that organization type.

Therefore no further invoice-draft POST is authorized until the sandbox organization's invoice/VAT configuration is explicitly corrected or Conta confirms which invoice-route VAT token is valid for this organization state.

## Required next gate

Perform a sandbox-only business/invoice VAT configuration review in the Conta web application. Do not alter production configuration. Determine whether the sandbox business is intended to issue regular/non-tax invoices or VAT invoices and configure the sandbox business consistently. After that change, repeat the protected GET-only VAT capability diagnostic before generating a new payload hash or performing another invoice-draft POST.

No global write enablement and no production write are authorized by this record.
