# Conta MCP Sandbox Fixture Gate Reconciliation — 11:51, 13.08.2026

## Classification

```text
READ_ONLY_SANDBOX_TEST_CUSTOMER_GATE=BLOCKED_PENDING_FIXTURE_EXECUTION
BLOCKER_CLASS=SANDBOX_HAS_NO_TEST_CUSTOMER
SANDBOX_TEST_INVOICES_PRESENT=false
SOURCE_OF_EMPTY_SANDBOX_OBSERVATION=OPERATOR_REPORT_20260813
SANDBOX_TEST_CUSTOMER_FIXTURE_DESIGNED=true
SANDBOX_TEST_CUSTOMER_FIXTURE_APPROVED=true
SANDBOX_TEST_CUSTOMER_CREATE_AUTHORIZED=true
FIXTURE_CREATED=false
INVOICE_DRAFT_AUTHORIZED=false
WRITE_ENABLEMENT_CHANGED=false
PRODUCTION_WRITE_APPROVED=false
```

The operator reported on 13.08.2026 that the configured Conta sandbox environment contains no test customers and no test invoices. The earlier HTTP `404` from the GET-only test-customer workflow is reconciled as a missing fixture prerequisite.

At 11:44, 13.08.2026 Europe/Oslo, the operator explicitly authorized:

`Authorize creation of the proposed synthetic Conta sandbox test customer only.`

This authorization is bound to the exact fixture and control conditions in `SANDBOX_TEST_CUSTOMER_FIXTURE_PROPOSAL_20260813.md`. It authorizes at most one customer-create POST in the Conta sandbox after an exact-name duplicate preflight. It does not authorize an invoice, invoice draft, customer update/delete, production access or general write enablement.

## Execution control

The execution workflow is:

```text
.github/workflows/conta-sandbox-synthetic-customer-create-once.yml
```

It is designed to run only when the workflow file is pushed to protected `main`, and it uses protected environment `conta-sandbox-secrets`. The environment requires a reviewer approval before protected variables/secrets are released to the job.

Mandatory runtime controls:

```text
CONTA_ENVIRONMENT=sandbox
CONTA_API_BASE_URL=https://api.gateway.conta-sandbox.no
EXACT_NAME_PREFLIGHT_REQUIRED=true
MAXIMUM_CUSTOMER_CREATE_POSTS=1
AUTOMATIC_POST_RETRY=false
GET_READBACK_REQUIRED=true
CUSTOMER_ID_PRINTED=false
PROVIDER_RESPONSE_BODY_PRINTED=false
INVOICE_OPERATION_ALLOWED=false
INVOICE_DRAFT_OPERATION_ALLOWED=false
CUSTOMER_UPDATE_DELETE_ALLOWED=false
PRODUCTION_ACCESS_ALLOWED=false
```

## Validation state before merge

All required pull-request checks passed against the authorized execution package:

```text
DEPENDENCY_REVIEW=PASSED
SECURITY_BASELINE=PASSED
REPOSITORY_SECURITY_BASELINE=PASSED
CONTROLLED_WRITE_FOUNDATION_VALIDATION=PASSED
CODEQL=PASSED
```

## Previously closed evidence retained

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

## Next gate

Merge the reviewed fixture authorization/execution package to `main`. The protected-environment deployment must then receive operator reviewer approval. If the one-shot job succeeds, record creation/readback evidence and continue only to the GET-only fixture binding/reconciliation gate.

All invoice-draft and production execution gates remain closed.