# Conta MCP Sandbox Fixture Gate Reconciliation — 11:34, 13.08.2026

## Classification

```text
READ_ONLY_SANDBOX_TEST_CUSTOMER_GATE=BLOCKED
BLOCKER_CLASS=SANDBOX_HAS_NO_TEST_CUSTOMER
SANDBOX_TEST_INVOICES_PRESENT=false
SOURCE_OF_EMPTY_SANDBOX_OBSERVATION=OPERATOR_REPORT_20260813
PROVIDER_WRITE_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
WRITE_ENABLEMENT_CHANGED=false
PRODUCTION_WRITE_APPROVED=false
```

This record supersedes the earlier assumption that the protected sandbox test-customer identifier was merely stale. The operator reported on 13.08.2026 that the configured Conta sandbox environment contains no test customers and no test invoices. That operator observation is accepted as the current operational context but is not represented here as an independently verified provider inventory.

This record does not authorize creation of a customer, invoice draft, invoice or any other Conta object. It does not authorize payload materialization, write-tool enablement, production access, kill-switch opening or one-call authorization.

## Canonical repository and failed validation evidence

```text
repository=nanotech-solutions-norway/Conta-MCP
validated_main_head=60bffa5beb4bf92ee7d4ad56a940c9833d78a1a1
workflow=.github/workflows/conta-sandbox-test-customer-validation.yml
workflow_run=31488585367
workflow_result=FAILURE
```

The workflow boundary check passed. Protected values for the sandbox API key, organization identifier, and configured test-customer identifier were present and masked. The configured environment and gateway were correct:

```text
CONTA_ENVIRONMENT=sandbox
CONTA_API_BASE_URL=https://api.gateway.conta-sandbox.no
PROTECTED_BOUNDARY_CHECK=PASSED
SECRET_VALUES_PRINTED=false
```

The workflow issued one customer-detail request using `GET` only. Transport completed successfully and Conta returned HTTP `404`:

```text
HTTP_METHOD=GET
CURL_EXIT=0
CUSTOMER_DETAIL_HTTP=404
PROVIDER_READ_CALL_PERFORMED=true
PROVIDER_WRITE_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
```

Conta's current official API help documents HTTP `404` as object/URL not found and documents the customer-detail route as:

```text
GET /invoice/organizations/{organizationId}/customers/{customerId}
```

Given the operator-confirmed empty sandbox, the failed validation is therefore consistent with a missing prerequisite test fixture. It is no longer classified as evidence that an existing test-customer selection merely needs replacement.

## Official sandbox capability relevant to the next gate

Conta's official API help documents that the sandbox environment is intended for testing and supports customer creation. It documents customer creation through:

```text
POST /invoice/organizations/{organizationId}/customers
```

Conta's web application also supports adding customers through the customer list. A customer is a prerequisite for a meaningful invoice-draft creation test because invoice-draft requests reference a customer identifier.

The existence of this capability is evidence only. It does not authorize use of the POST route or a UI mutation.

## Previously closed evidence retained

The following prior gates remain valid and are not reopened by the empty sandbox condition:

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

## Revised next-gate sequence

The rollout now requires a sandbox fixture prerequisite before test-customer validation can close.

### Gate A — approve a synthetic sandbox test-customer fixture

Operator approval is required for creation of exactly one sandbox-only synthetic customer. The fixture must contain no production customer data, no private customer data and no real financial information. The proposed fixture is defined in `SANDBOX_TEST_CUSTOMER_FIXTURE_PROPOSAL_20260813.md`.

```text
SANDBOX_TEST_CUSTOMER_FIXTURE_DESIGNED=true
SANDBOX_TEST_CUSTOMER_FIXTURE_APPROVED=false
SANDBOX_TEST_CUSTOMER_CREATE_AUTHORIZED=false
```

### Gate B — create exactly one sandbox test customer

Creation must occur only after explicit operator authorization. The approved method may be the Conta sandbox UI or a separately controlled API workflow, but it must not be combined with invoice-draft authorization. The resulting numeric customer identifier must remain in the protected configuration plane and must not be committed to Git or written to Drive/chat.

### Gate C — rerun GET-only customer validation

After creation and protected configuration of the customer identifier, rerun `Conta Sandbox Test Customer Validation`. It must remain GET-only and fail closed. A successful result closes only the sandbox test-customer existence/binding prerequisite.

### Gate D — prepare invoice-draft test payload and authorization

Only after Gate C closes may the invoice-draft payload be materialized in the protected runtime, hashed, previewed, reviewed and bound to a separate one-call sandbox mutation authorization.

## Gates that remain closed

```text
TEST_PAYLOAD_MATERIALIZED=false
PAYLOAD_APPROVAL=NOT_GRANTED
ONE_CALL_OPERATOR_AUTHORIZATION=NOT_GRANTED
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
SANDBOX_EXECUTION_ALLOWED=false
PRODUCTION_EXECUTION_ALLOWED=false
SANDBOX_TEST_CUSTOMER_CREATE_AUTHORIZED=false
```

No progress credit is granted for the failed customer validation. The next closable process is operator approval of the already-designed synthetic sandbox test-customer fixture. No Conta mutation is authorized by this reconciliation.