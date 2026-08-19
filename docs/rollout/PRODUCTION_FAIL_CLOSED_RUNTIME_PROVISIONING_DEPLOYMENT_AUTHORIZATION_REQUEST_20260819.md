# Conta MCP Fail-Closed Production Runtime Provisioning & Deployment Authorization Request — 2026-08-19

## Status

```text
REQUEST_STATUS=READY_FOR_OPERATOR_AUTHORIZATION
IMPLEMENTATION_MERGE_COMMIT=19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac
VALIDATION_WORKFLOW_MERGE_COMMIT=966a49d09f657794d27b3c093f8dc15d2e207bf1
IDENTITY_ACCESS_VALIDATION_RUN_ID=32235276641
ORGANIZATION_REFERENCE_SHA256=9ee050155b0c35066a2ea426c72a65e5cdd2806f18a3cf9829fb132bd66634ab
LIVE_CREDENTIAL_PROVISIONING_AUTHORIZED=false
FAIL_CLOSED_DEPLOYMENT_AUTHORIZED=false
RELEASE_APPROVED=false
PROVIDER_MUTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Requested work unit

If explicitly authorized for the exact reviewed request commit, this gate may:

1. securely provision the already validated dedicated-user production Conta API key into the live server-only configuration boundary;
2. provision the intended production organization identifier into the live server-only configuration boundary;
3. bind the configured organization to the validated hash `9ee050155b0c35066a2ea426c72a65e5cdd2806f18a3cf9829fb132bd66634ab`;
4. deploy production-write implementation merge commit `19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac` to `/Custom Models/conta-mcp`;
5. preserve the public bridge and unrelated server configuration;
6. perform non-mutating post-deployment health/MCP contract validation;
7. perform GET-only provider read validation if needed to confirm credential/config integrity;
8. record only repository-safe deployment evidence.

## Required fail-closed live state after deployment

The live runtime must remain non-executable after this gate:

```text
enable_write_preview=true
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_actions=[]
allowed_write_organization_ids=[]
kill_switch_global_blocked=true
```

Additionally:

- the production authorization packet must remain absent/unapproved;
- the production execution tool must remain invisible;
- no production write approval envelope may be created;
- no real invoice payload may be generated or approved;
- provider mutation count must remain zero.

## Permitted provider operations

Only non-mutating validation is permitted:

```text
GET /invoice/organizations
GET /invoice/organizations/{opContextOrgId}/subscription-plan
```

No POST, PUT, PATCH or DELETE is authorized.

## Explicit exclusions

This request does **not** authorize:

- `enable_write_tools=true`;
- `runtime_write_blocked=false`;
- `execution_allowed=true`;
- `production_write_approved=true`;
- populating live `allowed_write_actions` or `allowed_write_organization_ids` for execution;
- opening the production write kill switch;
- creating a production authorization packet;
- approving a release for production mutation;
- making the production execution tool visible;
- constructing or approving a real invoice draft payload;
- any POST, PUT, PATCH or DELETE provider call;
- any invoice, customer, product, ledger, payment, bank, payroll, statutory or other production mutation.

## Required deployment evidence

A successful gate must prove, without printing secrets or raw organization identifiers:

```text
IMPLEMENTATION_DEPLOYED=true
DEPLOYED_IMPLEMENTATION_COMMIT=19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac
PRODUCTION_CREDENTIAL_PRESENT=true
PRODUCTION_ORGANIZATION_CONFIG_PRESENT=true
PRODUCTION_ORGANIZATION_REFERENCE_SHA256=9ee050155b0c35066a2ea426c72a65e5cdd2806f18a3cf9829fb132bd66634ab
WRITE_PREVIEW_ENABLED=true
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
EXECUTION_ALLOWED=false
PRODUCTION_WRITE_APPROVED=false
ALLOWED_WRITE_ACTION_COUNT=0
ALLOWED_WRITE_ORGANIZATION_COUNT=0
KILL_SWITCH_GLOBAL_BLOCKED=true
EXECUTION_TOOL_VISIBLE=false
PROVIDER_READ_CALL_PERFORMED=<true-or-false>
PROVIDER_WRITE_CALL_PERFORMED=false
PRODUCTION_MUTATION_PERFORMED=false
SECRET_VALUE_PRINTED=false
RAW_ORGANIZATION_ID_PRINTED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

Any mismatch must fail closed and must not attempt a provider mutation.

## Post-deployment boundary

Successful completion of this gate means only that the production-write implementation and validated credential are present in the live runtime while all execution controls remain closed. A later separate release/execution authorization is required before any production write can become possible.

## Required operator authorization syntax

Only this exact instruction, referencing the exact reviewed request commit, authorizes this gate:

```text
AUTHORIZE_CONTA_FAIL_CLOSED_PRODUCTION_RUNTIME_PROVISIONING_AND_DEPLOYMENT for commit <exact-reviewed-request-commit>
```

That authorization permits credential/runtime provisioning, fail-closed deployment, and non-mutating validation only. It does not authorize any production mutation.
