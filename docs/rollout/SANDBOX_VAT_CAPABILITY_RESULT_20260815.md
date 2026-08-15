# Conta Sandbox VAT Capability Result — 15.08.2026

## Classification

```text
GET_ONLY_DIAGNOSTIC_PARTIAL=true
SANDBOX_ORG_ACTIVE=true
SANDBOX_ORG_BLOCKED=false
BOOKKEEPING_ENABLED=true
PRODUCT_VAT_HISTORY_PRESENT=false
INVOICE_VAT_HISTORY_PRESENT=false
UNSCOPED_BOOKKEEPING_PAGE_VAT_CODE_OCCURRENCES=20
UNSCOPED_BOOKKEEPING_PAGE_VAT_CODES_RAW=input.no.vat
BOOKKEEPING_SCOPE_COMPLETE=false
OUTPUT_VAT_CODE_ABSENCE_PROVEN=false
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
INVOICE_DRAFT_POST_AUTHORIZED=false
CURRENT_BLOCKER=SALES_ACCOUNT_VAT_CAPABILITY_NOT_YET_OBSERVED
```

## Evidence from protected run #2

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

## Corrected interpretation

The sandbox organization is accessible, active, not blocked, and has bookkeeping capability. It contains no product or invoice VAT history in the queried first pages.

Run #2 also returned exactly 20 VAT-code occurrences from the bookkeeping-account endpoint, all `input.no.vat`. However, that request did not explicitly set paging parameters and did not restrict the bookkeeping account range to sales/revenue accounts. Therefore those 20 observations are an unscoped/default-page sample and do **not** prove that the organization lacks output VAT codes.

The earlier interpretation that the sandbox organization's VAT configuration itself was conclusively the blocker is superseded by this correction. No sandbox business setting change should be made on the basis of run #2 alone.

Previous bounded invoice-draft attempts with route-level VAT codes `no.vat` and `high` both terminated with HTTP 422 `WrongVatCodeException`, with mandatory GET reconciliation proving that no invoice draft existed after each failed attempt. Those failures remain valid evidence, but they do not identify which output VAT code—if any—is configured for the organization's sales accounts.

Conta's current external API contract defines invoice-route VAT tokens and exposes paged bookkeeping-account reads with account-number range filters. The next diagnostic therefore scopes the provider read to active bookkeeping accounts in the 3000–3999 sales/revenue range and uses explicit paging.

## Required next gate

Run the corrected protected GET-only VAT capability diagnostic and inspect:

```text
SALES_BOOKKEEPING_ACCOUNTS_GET_STATUS
ACTIVE_SALES_BOOKKEEPING_VAT_CODE_OCCURRENCES
ACTIVE_SALES_BOOKKEEPING_OUTPUT_VAT_CODE_OCCURRENCES
ACTIVE_SALES_BOOKKEEPING_VAT_CODES_RAW
ACTIVE_SALES_BOOKKEEPING_VAT_CODES_INVOICE_FORM
OBSERVED_ORG_INVOICE_VAT_CODES
```

Only `output.*` bookkeeping VAT codes from the scoped sales-account result are converted into invoice-route candidates. No invoice-draft POST, sandbox configuration change, global write enablement, or production write is authorized until this read-only gate completes.
