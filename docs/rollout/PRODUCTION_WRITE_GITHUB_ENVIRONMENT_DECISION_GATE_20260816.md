# Conta MCP GitHub-Environment Production-Write Decision Gate — 2026-08-16

## Current governance model

The production-write governance gate uses a **single-human operator model**. Ruben A. Meyer is the sole required human reviewer/approver for this program. The former four-human review model and separation-of-duties identity requirements are superseded for this project.

This change affects human governance only. It does not remove technical controls: exact decision-packet hashing, 24-hour attestation expiry, one-use execution approval, short approval TTL, idempotency/replay protection, one provider POST maximum, no automatic retry, mandatory GET readback, kill-switch closure, runtime/manifest binding and separate later execution authorization remain required.

The governance-only decision packet is assembled transiently in the reviewer-protected GitHub Actions environment named `conta-production-write-decisions`.

The production organization reference is confidential and is consumed as the GitHub Environment secret `CONTA_PROD_ORGANIZATION_REFERENCE`. All other governance values used by this workflow are non-confidential GitHub Environment variables. No provider credential is required by this governance workflow.

```text
METHOD=GITHUB_PROTECTED_ENVIRONMENT_VARIABLES_PLUS_ONE_SECRET
ENVIRONMENT=conta-production-write-decisions
GITHUB_SECRETS_REQUIRED=1
REQUIRED_SECRET=CONTA_PROD_ORGANIZATION_REFERENCE
PROVIDER_CREDENTIALS_REQUIRED=0
GOVERNANCE_MODEL=SINGLE_HUMAN_OPERATOR
SOLE_HUMAN_OPERATOR=RUBEN_A_MEYER
DECISION_GATE_AUTHORIZED=true
NON_CONFIDENTIAL_VARIABLES_CONFIGURED=true
TARGET_ENVIRONMENT_ORGANIZATION_SECRET_PRESENT=true
OPERATOR_REVIEW_ATTESTED=false
PROTECTED_DECISIONS_COMPLETE=false
IMPLEMENTATION_AUTHORIZED=false
DEPLOYMENT_AUTHORIZED=false
RELEASE_APPROVED=false
LIVE=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Required environment secret

```text
CONTA_PROD_ORGANIZATION_REFERENCE
```

This is the approved production Conta organization reference. The operator reported completion of this secret configuration on 2026-08-17.

## Required environment variables consumed by the workflow

```text
CONTA_PROD_CUSTOMER_SELECTION_RULE
CONTA_PROD_VAT_TREATMENT_RULE
CONTA_PROD_FISCAL_PERIOD_RULE
CONTA_PROD_DUPLICATE_DETECTION_RULE
CONTA_PROD_AUDIT_METADATA_RETENTION
CONTA_PROD_EXECUTION_LEDGER_RETENTION
CONTA_PROD_STORAGE_AUTHORITY_REFERENCE
CONTA_PROD_TAMPER_EVIDENCE_REFERENCE
CONTA_PROD_PROGRAM_OWNER_REFERENCE
CONTA_PROD_PROVIDER_CAPABILITY_DECISION
CONTA_PROD_OPERATOR_REVIEW_ATTESTED
```

`CONTA_PROD_PROGRAM_OWNER_REFERENCE` is the opaque reference for the sole human operator. `CONTA_PROD_OPERATOR_REVIEW_ATTESTED` must be exactly `true` only after that operator has reviewed and accepted the production-write governance decisions represented by the configured values.

The former variables below are no longer consumed by the attestation workflow and no longer gate progress:

```text
CONTA_PROD_ACCOUNTING_REVIEWER_REFERENCE
CONTA_PROD_SECURITY_RELEASE_REVIEWER_REFERENCE
CONTA_PROD_CREDENTIAL_CUSTODIAN_REFERENCE
CONTA_PROD_EXECUTION_APPROVER_REFERENCE
CONTA_PROD_INCIDENT_OWNER_REFERENCE
CONTA_PROD_ACCOUNTING_REVIEW_ATTESTED
CONTA_PROD_SECURITY_REVIEW_ATTESTED
CONTA_PROD_CREDENTIAL_CUSTODY_ATTESTED
CONTA_PROD_INCIDENT_REVIEW_ATTESTED
```

They may remain temporarily in the environment for historical continuity but have no authority and should be removed during later housekeeping.

## Remote execution boundary

The manual workflow:

- runs only from `main`;
- requires the protected `conta-production-write-decisions` GitHub environment;
- receives only `CONTA_PROD_ORGANIZATION_REFERENCE` from GitHub Secrets and receives no provider credentials;
- requires one operator review variable exactly equal to `true`;
- performs no Conta network or provider call;
- constructs the canonical packet only in runner memory;
- writes and uploads only the packet SHA-256, expiry and safe boolean attestations;
- grants no implementation, deployment, release or production-write authority.

The attestation expires 24 hours after creation. An expired or incomplete attestation cannot open the next gate.

## Next stop

1. Merge and validate the single-human governance change.
2. Set `CONTA_PROD_OPERATOR_REVIEW_ATTESTED=true` only after Ruben A. Meyer has reviewed the configured governance decisions.
3. Dispatch the governance-only decision-attestation workflow from `main`.
4. A successful attestation may support a later implementation-authorization request, but does not itself authorize implementation or any production provider mutation.