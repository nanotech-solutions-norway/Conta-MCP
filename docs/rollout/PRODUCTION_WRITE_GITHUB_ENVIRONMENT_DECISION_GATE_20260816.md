# Conta MCP GitHub-Environment Production-Write Decision Gate — 2026-08-16

## Operator-selected remote method

PowerShell is unavailable to the operator. The governance-only decision packet is therefore assembled transiently in the reviewer-protected GitHub Actions environment named `conta-production-write-decisions`.

The operator subsequently clarified that GitHub Secrets are reserved for credentials and genuinely confidential values. This workflow needs no API key, token, username, password or provider credential and therefore consumes no GitHub Secrets. Its non-confidential governance values are GitHub Environment variables.

Do not place credentials, raw customer data, invoice payloads or confidential identifiers in these variables. Use only non-confidential rules and opaque governance references. Any confidential underlying record remains in its separately approved protected system.

```text
METHOD=GITHUB_PROTECTED_ENVIRONMENT_VARIABLES
ENVIRONMENT=conta-production-write-decisions
GITHUB_SECRETS_REQUIRED=0
PROVIDER_CREDENTIALS_REQUIRED=0
DECISION_GATE_AUTHORIZED=true
VARIABLES_CONFIGURED=false
PROTECTED_DECISIONS_COMPLETE=false
IMPLEMENTATION_AUTHORIZED=false
DEPLOYMENT_AUTHORIZED=false
RELEASE_APPROVED=false
LIVE=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Required environment variable names

```text
CONTA_PROD_ORGANIZATION_REFERENCE
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
- receives no GitHub Secrets or provider credentials;
- performs no network or provider call;
- constructs the canonical packet only in runner memory;
- writes and uploads only the packet SHA-256, expiry and safe boolean attestations;
- grants no implementation, deployment, release or production-write authority.

The attestation expires 24 hours after creation. An expired or incomplete attestation cannot open the next gate.

## Next stop

After this correction is merged, the operator must enter only non-confidential values or opaque references as environment variables. The workflow must not be dispatched until all required reviews are complete. A successful attestation only makes a later implementation-authorization request ready for review.
