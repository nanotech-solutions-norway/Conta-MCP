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

## Recommended values pending operator decision — 2026-08-18

The following non-confidential recommendations are configured but deliberately remain non-final:

```text
CONTA_PROD_AUDIT_METADATA_RETENTION=proposed:P5Y_AFTER_FISCAL_YEAR_END;purpose=accounting-control-evidence;metadata-only=true;legal-hold=extend;expiry=delete-or-anonymize
CONTA_PROD_EXECUTION_LEDGER_RETENTION=proposed:P13M_AFTER_EXECUTION;purpose=replay-and-incident-investigation;minimal-metadata=true;legal-hold=extend;expiry=delete-or-anonymize
CONTA_PROD_PROVIDER_CAPABILITY_DECISION=capability-gap-unresolved;production-write-blocked=true;required=dedicated-production-user-single-organization-minimum-permissions-or-explicit-security-risk-acceptance
```

The five-year audit recommendation aligns metadata used as accounting-control evidence with the general Norwegian five-year retention period for primary accounting material. This is a conservative policy alignment, not a claim that every audit field is statutory accounting material. The thirteen-month execution-ledger recommendation covers a complete annual control cycle plus one month while limiting retention of operational metadata. Legal hold extends either period, and expiry requires deletion or anonymization.

The repository evidence says Conta API keys inherit the creating user's access and the public API contract exposes no granular API-key scope list. Least privilege is therefore not verified. The recommended current capability value blocks production progress; it is not a risk acceptance.

Recommendation basis:

- Norwegian Tax Administration, accounting-material retention: `https://www.skatteetaten.no/en/rettskilder/type/uttalelser/prinsipputtalelser/oppbevaring-av-regnskapsmateriale-ved-avvikling-og-konkurs/`
- Datatilsynet, storage limitation and deletion/anonymization: `https://www.datatilsynet.no/rettigheter-og-plikter/personvernprinsippene/grunnleggende-personvernprinsipper/lagringsbegrensning/`
- NSM, logging strategy, integrity, retention and deletion: `https://nsm.no/hold-deg-oppdatert/meninger/logging-du-ma-vite-hva-som-skjer-og-hva-som-har-skjedd`
- Conta access-model evidence: `CURRENT_PROVIDER_SCHEMA_SANDBOX_EVIDENCE_REFRESH_20260810.md`

The attestation builder rejects retention values unless they start with `approved:`. It also rejects provider capability unless it starts with either `least_privilege_confirmed:` or `capability_gap_risk_accepted:`. This prevents a proposed value or unresolved capability gap from becoming a successful attestation merely because the operator review variable changes.

## Next stop

1. Merge and validate the single-human governance change.
2. Decide the two retention recommendations and replace `proposed:` with `approved:` only if accepted.
3. Obtain provider least-privilege evidence or explicitly accept the documented capability gap, then record the corresponding permitted prefix and a non-sensitive decision/evidence reference.
4. Set `CONTA_PROD_OPERATOR_REVIEW_ATTESTED=true` only after Ruben A. Meyer has reviewed and accepted the final configured governance decisions.
5. Dispatch the governance-only decision-attestation workflow from `main`.
6. A successful attestation may support a later implementation-authorization request, but does not itself authorize implementation or any production provider mutation.
