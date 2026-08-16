# Conta MCP Production-Write Protected Decision Gate — 2026-08-16

## Purpose

This gate converts the approved production-write control framework into a complete, reviewable set of non-sensitive decisions and protected attestations. It does not authorize implementation.

```text
DESIGN_APPROVED_ONLY=true
DECISION_GATE=PROPOSED_FOR_AUTHORIZATION
PROTECTED_DECISIONS_COMPLETE=false
IMPLEMENTATION_AUTHORIZED=false
CONFIGURED=false
DEPLOYMENT_AUTHORIZED=false
RELEASE_APPROVED=false
LIVE=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Public decision envelope

The following conservative first-run limits are proposed for explicit approval:

| Decision | Proposed first-run value |
| --- | --- |
| Action | `invoice_draft_create_v2` only |
| Objects | Exactly one unsent invoice draft |
| Provider mutations | Exactly one POST maximum |
| Automatic retry | Disabled |
| Maximum invoice lines | `1` |
| Maximum line amount | `1.00 NOK` |
| Maximum draft total | `1.00 NOK` |
| Currency | `NOK` |
| Customer scope | One exact protected existing-customer reference |
| VAT scope | One accounting-approved treatment bound to the exact payload hash |
| Fiscal scope | Invoice date must be inside an accounting-approved open period |
| Duplicate rule | Any matching protected pre-read candidate stops execution |
| Approval lifetime | Maximum `900` seconds |
| Readback | Mandatory exact controlled-projection verification |
| Cleanup/correction | None; separate authorization required |

These limits are deliberately narrower than a normal invoice. Approval of this envelope does not select a real organization, customer, VAT treatment, date or payload.

## Protected decision packet

The exact protected decisions must be prepared outside Git, chat, model context and public Actions inputs. The packet must use canonical UTF-8 JSON with sorted keys and must contain:

```text
packetVersion
environment
action
organizationReference
customerSelectionRule
vatTreatmentRule
currency
maximumLines
maximumLineAmount
maximumDraftTotal
fiscalPeriodRule
duplicateDetectionRule
auditMetadataRetention
executionLedgerRetention
storageAuthorityReference
tamperEvidenceReference
programOwnerReference
accountingReviewerReference
securityReleaseReviewerReference
credentialCustodianReference
executionApproverReference
incidentOwnerReference
providerCapabilityDecision
createdAt
expiresAt
```

The packet must not contain credentials, approval secrets, raw tokens or full invoice/customer/accounting payloads. Identifiers and internal role references remain only in the approved protected store.

## Repository-safe attestation

Only the following evidence may enter the repository or PR:

```text
DECISION_PACKET_SHA256=<64 lowercase hex characters>
DECISION_PACKET_VERSION=<non-sensitive version>
DECISION_PACKET_EXPIRES_AT=<UTC timestamp>
ORGANIZATION_REFERENCE_HASH_BOUND=true
CUSTOMER_SELECTION_RULE_BOUND=true
ACCOUNTING_LIMITS_BOUND=true
VAT_TREATMENT_REVIEWED=true
FISCAL_PERIOD_RULE_BOUND=true
DUPLICATE_RULE_BOUND=true
RETENTION_DECISIONS_BOUND=true
SEPARATION_OF_DUTIES_REVIEWED=true
CREDENTIAL_CUSTODY_REVIEWED=true
INCIDENT_OWNERSHIP_REVIEWED=true
PROVIDER_CAPABILITY_DECISION_RECORDED=true
PROTECTED_VALUE_PRINTED=false
```

The packet hash proves identity of the reviewed protected record; it does not reveal or replace its contents.

## Required protected review

Before the packet can be marked complete:

1. The program owner confirms the packet scope and expiry.
2. The accounting reviewer approves the exact customer-selection, VAT, amount, date/period and duplicate rules.
3. The security/release reviewer approves separation of duties, provider capability risk, storage and tamper evidence.
4. The credential custodian confirms production/sandbox/client credential separation and revocation readiness.
5. The incident owner confirms the out-of-band response and GET-only reconciliation path.
6. A protected process computes the packet SHA-256 without printing protected contents.
7. The public attestation is reviewed against the hash and all required booleans.

One person may not self-attest incompatible duties merely to pass the gate. Any accepted role overlap must be an explicit security risk decision inside the protected packet.

## Decision-gate stop conditions

Stop and keep implementation unauthorized if:

- a required protected field or reviewer is missing;
- any proposed public limit is changed without a new review;
- the packet hash is malformed or no longer matches;
- protected values appear in Git, chat, logs or public workflow inputs;
- the provider cannot support the required scope and no explicit risk decision exists;
- organization/customer identity is ambiguous;
- accounting, retention, credential or incident review is incomplete;
- the packet expires.

## Gate output

Successful completion produces only:

```text
PROTECTED_DECISIONS_COMPLETE=true
IMPLEMENTATION_AUTHORIZATION_REQUEST_READY=true
IMPLEMENTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

Implementation remains a later, separately authorized work unit.
