# Conta MCP GitHub-Environment Production-Write Decision Gate — 2026-08-16

## Operator-selected remote method

PowerShell is unavailable to the operator. The governance-only decision packet is therefore assembled transiently in the reviewer-protected GitHub Actions environment named `conta-production-write-decisions`.

The operator subsequently clarified that GitHub Secrets are reserved for credentials and genuinely confidential values. This workflow needs no API key, token, username, password or provider credential. The operator classified the production organization reference as confidential, so that one value is consumed as a GitHub Environment secret. All non-confidential governance values are GitHub Environment variables.

Do not place credentials, raw customer data, invoice payloads or confidential identifiers in these variables. Use only non-confidential rules and opaque governance references. Any confidential underlying record remains in its separately approved protected system.

```text
METHOD=GITHUB_PROTECTED_ENVIRONMENT_VARIABLES_PLUS_ONE_SECRET
ENVIRONMENT=conta-production-write-decisions
GITHUB_SECRETS_REQUIRED=1
REQUIRED_SECRET=CONTA_PROD_ORGANIZATION_REFERENCE
PROVIDER_CREDENTIALS_REQUIRED=0
DECISION_GATE_AUTHORIZED=true
NON_CONFIDENTIAL_VARIABLES_CONFIGURED=true
HUMAN_REVIEW_ATTESTATIONS=false
TARGET_ENVIRONMENT_ORGANIZATION_SECRET_PRESENT=false
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

This must be the approved production Conta organization reference. It must exist in `conta-production-write-decisions`; a same-named secret in the sandbox environment is not accessible and must not be copied or inferred by the workflow.

## Required environment variable names

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
CONTA_PROD_ACCOUNTING_REVIEWER_REFERENCE
CONTA_PROD_SECURITY_RELEASE_REVIEWER_REFERENCE
CONTA_PROD_CREDENTIAL_CUSTODIAN_REFERENCE
CONTA_PROD_EXECUTION_APPROVER_REFERENCE
CONTA_PROD_INCIDENT_OWNER_REFERENCE
CONTA_PROD_PROVIDER_CAPABILITY_DECISION
CONTA_PROD_ACCOUNTING_REVIEW_ATTESTED
CONTA_PROD_SECURITY_REVIEW_ATTESTED
CONTA_PROD_CREDENTIAL_CUSTODY_ATTESTED
CONTA_PROD_INCIDENT_REVIEW_ATTESTED
```

The four review variables must be exactly `true`, set only after their respective reviews. The accounting reviewer reference must differ from the security/release reviewer reference, and the credential custodian reference must differ from the execution approver reference.

## Remote execution boundary

The manual workflow:

- runs only from `main`;
- requires approval through the protected GitHub environment;
- receives only `CONTA_PROD_ORGANIZATION_REFERENCE` from GitHub Secrets and receives no provider credentials;
- performs no network or provider call;
- constructs the canonical packet only in runner memory;
- writes and uploads only the packet SHA-256, expiry and safe boolean attestations;
- grants no implementation, deployment, release or production-write authority.

The attestation expires 24 hours after creation. An expired or incomplete attestation cannot open the next gate.

## Next stop

The non-confidential variables have been created with conservative fail-closed rules, opaque role references, pending retention/provider decisions and all four human-review attestations set to `false`. The operator must add the organization-reference secret to the correct production-decision environment and complete the required human reviews. The workflow must not be dispatched until those reviews are complete. A successful attestation only makes a later implementation-authorization request ready for review.
