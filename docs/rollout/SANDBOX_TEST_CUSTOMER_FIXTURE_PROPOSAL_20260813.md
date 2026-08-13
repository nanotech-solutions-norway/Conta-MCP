# Conta MCP Synthetic Sandbox Test-Customer Fixture Proposal — 11:34, 13.08.2026

## Status

```text
classification=APPROVED_BY_OPERATOR_20260813
fixture_designed=true
fixture_created=false
customer_id_assigned=false
provider_call_authorized=true
sandbox_mutation_authorized=true
invoice_draft_authorized=false
```

Operator authorization received at 11:44, 13.08.2026 Europe/Oslo:

`Authorize creation of the proposed synthetic Conta sandbox test customer only.`

This authorization is limited to creation of exactly one instance of the synthetic sandbox-only customer defined below. It does not authorize any invoice or invoice-draft creation, customer modification/deletion, production access, general write-tool enablement, or reuse of this authorization for another mutation.

## Purpose

The configured Conta sandbox environment contains no test customers or test invoices according to the operator report of 13.08.2026. A synthetic sandbox-only customer is required before the existing GET-only customer-binding validation and later invoice-draft test can proceed.

Conta's official API help documents customer creation in the sandbox-capable API through:

```text
POST /invoice/organizations/{organizationId}/customers
```

## Authorized fixture

```text
customerType=INDIVIDUAL
isActive=true
name=Atlas MCP Sandbox Test Customer
customerAddressLine1=Testveien 1
customerAddressPostcode=0150
customerAddressCity=Oslo
invoiceDeliveryMethod=EMAIL
emailAddress=atlas-mcp-sandbox@example.com
```

`example.com` is synthetic test data. No production/private customer, employee, supplier or real financial information may be substituted.

## Authorized execution method

Use the existing protected GitHub environment `conta-sandbox-secrets`, with:

- exact sandbox environment enforcement;
- exact sandbox gateway enforcement;
- duplicate-safe GET preflight by exact fixture name;
- at most one customer-create POST;
- immediate GET readback of the returned customer identifier;
- no response body, secret, organization identifier or customer identifier printed;
- no automatic retry of the POST;
- no customer update/delete;
- no invoice or invoice-draft operation.

The protected environment requires operator review before secrets are released to the job. That review is an additional control and does not expand this authorization.

## Post-creation handling

After successful creation/readback:

1. Do not create an invoice or invoice draft.
2. Keep the synthetic customer unchanged while the next read-only binding gate is reconciled.
3. Do not print or commit the customer identifier.
4. Do not automatically delete the customer. Cleanup is a separate mutation requiring a separate operator decision.

## Controlling boundary

```text
SANDBOX_TEST_CUSTOMER_FIXTURE_APPROVED=true
SANDBOX_TEST_CUSTOMER_CREATE_AUTHORIZED=true
INVOICE_DRAFT_AUTHORIZED=false
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
PRODUCTION_EXECUTION_ALLOWED=false
```
