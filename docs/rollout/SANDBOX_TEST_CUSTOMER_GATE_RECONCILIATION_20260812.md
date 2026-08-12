# Conta MCP Sandbox Test-Customer Gate Reconciliation — 21:29, 12.08.2026

## Classification

```text
READ_ONLY_SANDBOX_TEST_CUSTOMER_GATE=BLOCKED
BLOCKER_CLASS=SELECTED_CUSTOMER_NOT_FOUND
PROVIDER_WRITE_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
WRITE_ENABLEMENT_CHANGED=false
PRODUCTION_WRITE_APPROVED=false
```

This record reconciles the latest read-only sandbox test-customer validation evidence. It does not authorize a provider mutation, payload materialization, write-tool enablement, production access, or one-call authorization.

## Canonical repository and workflow

```text
repository=nanotech-solutions-norway/Conta-MCP
branch=main
validated_main_head=60bffa5beb4bf92ee7d4ad56a940c9833d78a1a1
workflow=.github/workflows/conta-sandbox-test-customer-validation.yml
workflow_run=31488585367
workflow_result=FAILURE
```

The workflow boundary check passed. Protected values for the sandbox API key, organization identifier, and sandbox test-customer identifier were present and masked. The configured environment and gateway were also correct:

```text
CONTA_ENVIRONMENT=sandbox
CONTA_API_BASE_URL=https://api.gateway.conta-sandbox.no
PROTECTED_BOUNDARY_CHECK=PASSED
SECRET_VALUES_PRINTED=false
```

## Provider observation

The workflow issued exactly one customer-detail request using `GET` only. The transport completed successfully, but Conta returned HTTP `404`:

```text
HTTP_METHOD=GET
CURL_EXIT=0
CUSTOMER_DETAIL_HTTP=404
PROVIDER_READ_CALL_PERFORMED=true
PROVIDER_WRITE_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
```

Conta's current official API help continues to document the customer-detail route as:

```text
GET /invoice/organizations/{organizationId}/customers/{customerId}
```

Therefore the route shape is not treated as the primary fault. The controlling classification is that the currently protected test-customer selection was not found for the configured sandbox organization at validation time. No customer response body or identifier was exposed.

## Previously closed evidence retained

The following prior gates remain valid and are not reopened by this failure:

```text
FAIL_CLOSED_CANONICAL_DEPLOYMENT_COMPLETE=true
AUTHENTICATED_POST_DEPLOYMENT_MCP_CONTRACT_VALIDATED=true
CURRENT_OFFICIAL_SCHEMA_FRESHNESS=VERIFIED
SANDBOX_IDENTITY_ACCESS=VALIDATED
SANDBOX_ORGANIZATION_BINDING=VALIDATED
ORGANIZATION_SCOPED_READ_ACCESS=VALIDATED
REQUEST_SCHEMA_HASH=VERIFIED_OFFICIAL_DOCS_20260811
RESPONSE_SCHEMA_HASH=VERIFIED_OFFICIAL_DOCS_20260811
```

## Required operator action

The next action is configuration-only and must not create or modify a Conta object.

1. Sign in to the existing Conta sandbox organization through `https://app.conta-sandbox.no`.
2. Open the customer list for the already configured sandbox organization.
3. Select an **existing sandbox-only test customer**. Do not create, edit, delete, invoice, send, or otherwise mutate anything for this gate.
4. Determine that customer's numeric customer ID from the sandbox UI/API tooling available to the operator.
5. In GitHub repository `nanotech-solutions-norway/Conta-MCP`, update the protected environment secret `CONTA_SANDBOX_TEST_CUSTOMER_ID` under environment `conta-sandbox-secrets` with that numeric ID.
6. Do **not** paste the ID, API key, organization ID, or any customer information into ChatGPT, Git, Drive, issues, PR comments, logs, or documents.
7. Return only `Done` to the operator workflow after the protected secret has been updated.

After that configuration change, re-run the existing `Conta Sandbox Test Customer Validation` workflow. The rerun must remain GET-only and fail closed.

## Gates that remain closed

```text
TEST_PAYLOAD_MATERIALIZED=false
PAYLOAD_APPROVAL=NOT_GRANTED
ONE_CALL_OPERATOR_AUTHORIZATION=NOT_GRANTED
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
SANDBOX_EXECUTION_ALLOWED=false
PRODUCTION_EXECUTION_ALLOWED=false
```

No progress credit is granted for the failed customer validation. The next closable gate is `READ_ONLY_SANDBOX_TEST_CUSTOMER_VALIDATION` after the protected selection is corrected.