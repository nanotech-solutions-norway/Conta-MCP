# Conta MCP Synthetic Sandbox Test-Customer Fixture Proposal — 11:34, 13.08.2026

## Status

```text
classification=PENDING_OPERATOR_APPROVAL
fixture_designed=true
fixture_created=false
customer_id_assigned=false
provider_call_authorized=false
sandbox_mutation_authorized=false
invoice_draft_authorized=false
```

This is an offline fixture design only. It does not authorize creation of any Conta object.

## Purpose

The configured Conta sandbox environment contains no test customers or test invoices according to the operator report of 13.08.2026. A synthetic sandbox-only customer is required before the existing GET-only customer-binding validation and later invoice-draft test can proceed.

Conta's official API help documents customer creation in the sandbox-capable API through:

```text
POST /invoice/organizations/{organizationId}/customers
```

The official examples support an `INDIVIDUAL` customer with name, active status, address, invoice-delivery method and email address. No production/customer data is required for the fixture.

## Proposed fixture

Use one deliberately synthetic individual customer:

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

`example.com` is used only as synthetic test data. No real customer, employee, supplier or production contact information may be substituted.

## Creation method recommendation

For this prerequisite fixture, prefer manual creation in the Conta sandbox web UI rather than introducing a new API write path solely to seed test data. This keeps fixture setup outside the MCP controlled invoice-draft execution path and makes the later MCP write test easier to attribute and audit.

Creation remains a sandbox mutation and therefore requires explicit operator authorization before it is performed.

## Post-creation handling

After creation:

1. Do not create an invoice or invoice draft manually.
2. Place the resulting numeric customer identifier only in the GitHub protected environment secret `CONTA_SANDBOX_TEST_CUSTOMER_ID` under `conta-sandbox-secrets`.
3. Do not put that identifier, customer response, API key or organization identifier in Git, Drive, chat, issues or PR comments.
4. Run the existing GET-only `Conta Sandbox Test Customer Validation` workflow.
5. If that workflow succeeds, keep the synthetic customer unchanged while the invoice-draft test package is prepared and reviewed.
6. Do not automatically delete the customer after testing. Any cleanup is a separate mutation requiring a separate operator decision.

## Approval boundary

An operator approval for this fixture must authorize only creation of this one synthetic sandbox customer. It must not authorize:

- invoice-draft creation;
- invoice creation or sending;
- customer update/delete;
- write-tool enablement;
- production access;
- reuse of the authorization for another payload or object.

Until explicit approval is granted:

```text
SANDBOX_TEST_CUSTOMER_FIXTURE_APPROVED=false
SANDBOX_TEST_CUSTOMER_CREATE_AUTHORIZED=false
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
SANDBOX_EXECUTION_ALLOWED=false
PRODUCTION_EXECUTION_ALLOWED=false
```
