# Conta MCP GitHub-Secrets Production-Write Decision Gate — 2026-08-16

## Operator-selected remote method

The operator reported that PowerShell is unavailable and instructed the gate to use GitHub Secrets for confidential information. The protected decision packet will therefore be assembled transiently in a reviewer-protected GitHub Actions environment named `conta-production-write-decisions`.

No protected value belongs in Git, chat, workflow inputs, logs, summaries or artifacts.

```text
METHOD=GITHUB_ENVIRONMENT_SECRETS
ENVIRONMENT=conta-production-write-decisions
DECISION_GATE_AUTHORIZED=true
SECRETS_CONFIGURED=false
PROTECTED_DECISIONS_COMPLETE=false
IMPLEMENTATION_AUTHORIZED=false
DEPLOYMENT_AUTHORIZED=false
RELEASE_APPROVED=false
LIVE=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Required environment secret names

Enter each value directly through the GitHub environment settings page. Do not paste a value into an issue, PR, Actions input, repository file or model conversation.

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

Each review-attestation secret must be exactly `true`. The accounting reviewer must differ from the security/release reviewer, and the credential custodian must differ from the execution approver.

## Remote execution boundary

The manual workflow:

- runs only from `main`;
- requires approval through the protected GitHub environment;
- performs no network or provider call;
- constructs the canonical packet only in runner memory;
- writes and uploads only the packet SHA-256, expiry and safe boolean attestations;
- grants no implementation, deployment, release or production-write authority.

The attestation expires 24 hours after creation. An expired or incomplete attestation cannot open the next gate.

## Next stop

After this workflow is merged, the operator must enter the protected values directly in GitHub. The workflow must not be dispatched until that manual entry is complete. A successful attestation only makes a later implementation-authorization request ready for review.
