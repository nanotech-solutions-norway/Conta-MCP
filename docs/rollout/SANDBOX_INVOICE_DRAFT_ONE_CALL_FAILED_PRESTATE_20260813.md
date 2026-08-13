# Conta Sandbox Invoice Draft One-Call — Failed Pre-State Gate, 2026-08-13

## Classification

```text
ONE_CALL_WORKFLOW_RUN=31734197946
FAILURE_STAGE=GET_ONLY_PRESTATE_VALIDATION
FAILURE_ERROR=invoice_draft_prestate_unrecognized
PROVIDER_MUTATION_PERFORMED=false
INVOICE_DRAFT_POST_PERFORMED=false
ONE_CALL_PROVIDER_MUTATION_AUTHORIZATION_CONSUMED=false
AUTOMATIC_RETRY_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

The protected one-call workflow failed before customer resolution, payload materialization, authorization-envelope creation, ledger reservation, or provider POST. The attached GitHub Actions log shows the exception occurred inside `assertZeroDraftPrestate()` immediately after the successful GET-only invoice-draft list request.

The current official Conta OpenAPI defines `GET /invoice/organizations/{opContextOrgId}/invoice-drafts` (`v1SearchInvoiceDrafts`) as returning `RouteV1QueryResultInvoiceListExtendedInfoModel`. That response model uses `hits` (array) and `hitCount` (integer), with `pageCount` and `sum` metadata. The first one-call implementation did not recognize `hits`/`hitCount`, so it failed closed even though no provider mutation had been attempted.

Remediation is limited to schema-aligned GET list-envelope recognition. The exact approved payload hash and maximum provider-mutation count remain unchanged. A fresh protected workflow run is required; rerunning run 31734197946 remains prohibited by the workflow's `GITHUB_RUN_ATTEMPT == 1` control.
