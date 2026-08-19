# Conta MCP Production-Write Implementation Authorization Request — 2026-08-19

## Status

```text
REQUEST_STATUS=READY_FOR_OPERATOR_AUTHORIZATION
GOVERNANCE_ATTESTATION=VERIFIED_SUCCESS
IMPLEMENTATION_AUTHORIZED=false
CONFIGURATION_AUTHORIZED=false
CREDENTIAL_PROVISIONING_AUTHORIZED=false
DEPLOYMENT_AUTHORIZED=false
RELEASE_APPROVED=false
PROVIDER_MUTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Governing evidence

This request is bound to the approved production-write control framework and the completed single-human governance decision gate.

```text
APPROVED_DESIGN_REVIEW_COMMIT=14f60d04b10dc87a68d55d85704fa5ebdd7880f2
DESIGN_DECISION_RECORD=docs/rollout/PRODUCTION_WRITE_PROGRAM_DESIGN_DECISION_20260816.md
DECISION_GATE_COMMIT=a0d86f2c0898abf39fa05af3dd8002b9a525977b
GOVERNANCE_MODEL=single_human_operator
SOLE_HUMAN_OPERATOR=RUBEN_A_MEYER
ATTESTATION_RUN_ID=32230395040
ATTESTATION_JOB_ID=96004835112
ATTESTATION_ARTIFACT_ID=9357710383
ATTESTATION_ARTIFACT_DIGEST=sha256:f9740d7a422246b4e2d2b44b5593a0a3ffc342feb3113abfd29d92405c9eb535
DECISION_PACKET_VERSION=2
DECISION_PACKET_SHA256=cfc5fc9ab38b8fa23fe813b191b1dd401ffa4b217cf14af8d8f8fda7c555117f
DECISION_PACKET_EXPIRES_AT=2026-08-20T08:23:54Z
```

The successful attestation recorded all of the following as true:

- organization-reference hash bound;
- customer-selection rule bound;
- accounting limits bound;
- VAT treatment reviewed;
- fiscal-period rule bound;
- duplicate-detection rule bound;
- retention decisions bound;
- single-human operator review complete;
- incident ownership reviewed;
- provider capability decision recorded.

It also recorded:

```text
PROTECTED_VALUE_PRINTED=false
PROVIDER_CALL_PERFORMED=false
IMPLEMENTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Requested implementation authority

If explicitly authorized by the operator for the exact reviewed request commit, the implementation phase may perform **repository code changes and offline tests only** to implement the approved fail-closed production-write control framework for `invoice_draft_create_v2`.

Permitted implementation objectives:

1. Implement production-specific policy enforcement for exactly one allowed action: `invoice_draft_create_v2`.
2. Implement exact protected organization allowlist/hash binding interfaces while keeping the runtime allowlist empty by default.
3. Implement deterministic preview/payload-hash binding and the first-run numeric limits already fixed by the approved packet:
   - maximum lines: 1;
   - maximum line amount: NOK 1.00;
   - maximum draft total: NOK 1.00;
   - maximum provider mutations: 1;
   - automatic retry: false;
   - approval TTL: at most 900 seconds.
4. Implement one-use approval validation, nonce/replay/idempotency-ledger controls and mandatory GET readback verification.
5. Implement fail-closed kill-switch and execution-gate handling such that all production-write execution remains blocked by default.
6. Implement metadata-only audit/ledger structures consistent with the approved retention decisions.
7. Implement offline/unit/regression tests proving closed-default behavior, replay rejection, exact binding, no automatic retry and fail-closed rejection of incomplete/expired/mismatched approvals.
8. Update repository documentation necessary to describe the implemented controls and test evidence.

## Permitted repository scope

Implementation may modify only files required for the production-write control path and its tests/documentation, principally:

```text
app/Config.php
app/WritePolicy.php
app/ContaClient.php
app/*Approval*.php
app/*Ledger*.php
app/*Readback*.php
app/*InvoiceDraft*.php
config/conta_config.example.php
tests/*
docs/rollout/*
.github/scripts/*production-write*
.github/workflows/*production-write*
```

New files under these same functional scopes are permitted where necessary. Any material expansion outside this scope requires a renewed authorization request.

## Explicit exclusions

This authorization request does **not** authorize any of the following:

- changing live/server production configuration;
- populating `allowed_write_organization_ids` or `allowed_write_actions` in production;
- installing, rotating, exposing or using the production Conta API key;
- changing the protected runtime or public bridge;
- deploying any implementation to `/Custom Models/conta-mcp` or another target;
- making any authenticated production Conta provider call;
- making any sandbox or production provider mutation;
- making the execution tool visible in production;
- opening `enable_write_tools`, `execution_allowed`, `production_write_approved` or any kill switch;
- preparing or approving a real invoice payload/customer selection;
- sending, posting, finalizing, updating, deleting, crediting or cleaning up any invoice;
- creating/updating customers or products;
- ledger, bank, payment, payroll or statutory operations;
- release approval or first-production execution approval.

## Required fail-closed state throughout implementation

Offline implementation and tests must preserve this intended live state:

```text
enable_write_preview=true
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_organization_ids=[]
allowed_write_actions=[]
kill_switch_global_blocked=true
```

No test may require a production provider mutation. Provider-facing behavior must be exercised through mocks/fixtures or already approved sandbox evidence unless a later separate authorization is granted.

## Acceptance criteria for implementation phase

The implementation phase is complete only when repository evidence demonstrates all of the following without opening a production write gate:

- all relevant unit/regression/security tests pass;
- exact organization/action/payload/approval binding fails closed on mismatch;
- expired or reused approval fails closed;
- duplicate/replay attempt fails closed;
- no automatic retry occurs after ambiguous provider outcome;
- mandatory readback verification is enforced before success closure;
- audit output excludes credentials/raw organization IDs/raw customer IDs/full payloads/full provider responses;
- production execution tool remains hidden/blocked by default;
- no credential or server-local configuration is committed;
- no provider call was made during implementation validation;
- implementation evidence is bound to an exact source commit.

## Post-implementation gates

Successful implementation does not authorize configuration, deployment, release or execution. After implementation and offline validation, separate explicit gates remain required for:

1. production credential provisioning and protected GET-only organization-identity validation;
2. fail-closed deployment authorization for an exact implementation commit/package;
3. post-deployment authenticated non-mutating validation;
4. release approval for the exact deployed runtime;
5. exact first-production payload/preview approval;
6. time-bounded one-use execution authorization;
7. mandatory readback and immediate gate closure.

## Required operator authorization syntax

Only the following instruction, referencing the exact reviewed request commit, authorizes this implementation phase:

```text
AUTHORIZE_CONTA_PRODUCTION_WRITE_IMPLEMENTATION for commit <exact-reviewed-request-commit>
```

That instruction authorizes repository implementation and offline tests only within this record. It does not authorize credential provisioning, live configuration, deployment, release, tool visibility or any provider mutation.
