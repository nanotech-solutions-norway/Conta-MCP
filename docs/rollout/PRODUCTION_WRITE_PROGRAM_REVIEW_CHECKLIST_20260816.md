# Conta MCP Production-Write Program Review Checklist — 2026-08-16

## Review boundary

This checklist reviews `PRODUCTION_WRITE_PROGRAM_DESIGN_20260816.md`. Completing it may approve the design only. It cannot authorize implementation, configuration, credential provisioning, deployment, release activation, tool visibility or a provider mutation.

```text
REVIEW_STATUS=PENDING_OPERATOR_AND_ACCOUNTING_REVIEW
DESIGN_APPROVED=false
IMPLEMENTATION_AUTHORIZED=false
DEPLOYMENT_AUTHORIZED=false
RELEASE_APPROVED=false
FIRST_PRODUCTION_MUTATION_AUTHORIZED=false
```

## Required reviewers

Record protected/internal reviewer references outside this public repository. Repository evidence records roles and decision timestamps only.

- [ ] Program owner assigned.
- [ ] Accounting reviewer assigned.
- [ ] Security/release reviewer assigned.
- [ ] Credential custodian assigned.
- [ ] Execution approver assigned.
- [ ] Incident owner and out-of-band contact path assigned.
- [ ] Separation-of-duties conflicts reviewed and accepted or corrected.

## Scope decisions

- [ ] Only `invoice_draft_create_v2` is in scope.
- [ ] Exactly one unsent draft and one POST maximum are accepted.
- [ ] Sending, posting, update, delete, credit and cleanup remain blocked.
- [ ] Customer, voucher, ledger, payment, bank, payroll and statutory mutations remain blocked.
- [ ] No automatic retry is accepted.
- [ ] An indeterminate outcome requires GET-only reconciliation before any new decision.

## Organization and credential decisions

- [ ] Exactly one production organization-selection procedure is approved.
- [ ] Raw organization/customer identifiers remain outside Git and chat evidence.
- [ ] Protected GET-only identity validation and one-way hash evidence are approved.
- [ ] Production provider credential is separate from sandbox and MCP client credentials.
- [ ] Provider least-privilege capability is evidenced, or a documented capability-gap risk decision is approved.
- [ ] Credential provisioning, rotation, revocation and emergency-disable owners are approved.
- [ ] Production write credential is unavailable to the read-only process and general coding shell.

## Accounting and limit decisions

The reviewer must replace every pending value in protected governance/configuration records before implementation. Do not place real customer or organization data here.

| Decision | Required outcome | Status |
| --- | --- | --- |
| Currency | One approved currency | `PENDING` |
| Maximum lines | Explicit positive integer | `PENDING` |
| Maximum line amount | Explicit NOK cap | `PENDING` |
| Maximum draft total | Explicit NOK cap | `PENDING` |
| Customer selection | One protected exact-reference rule | `PENDING` |
| VAT treatment | Accounting-approved exact rule | `PENDING` |
| Fiscal date/period | Open-period validation rule | `PENDING` |
| Duplicate detection | Pre-read criteria and stop rule | `PENDING` |
| Correction | Human-reviewed unsent-draft procedure | `PENDING` |

## Release and runtime decisions

- [ ] Current official provider schema and route hashes are required.
- [ ] Immutable source commit and per-file deployment hashes are required.
- [ ] Production release manifest authority and signer are approved.
- [ ] Backup, rollback and independently recoverable source evidence are required.
- [ ] Fail-closed deployment precedes any release approval.
- [ ] Execution tool remains absent until the separate first-execution window.
- [ ] Global and action kill switches override all enablement.
- [ ] Gate-open duration and automatic closure behavior are approved.
- [ ] Runtime drift blocks execution.

## Approval and execution decisions

- [ ] Accounting approval binds the exact human-readable preview and payload hash.
- [ ] Security/release approval binds the exact runtime and manifest.
- [ ] One-use approval is signed, actor-bound, organization-bound, route-bound and expires within 900 seconds.
- [ ] Idempotency key and nonce replay are rejected.
- [ ] Permit is revalidated at final provider dispatch.
- [ ] Exactly one provider mutation is technically enforced.
- [ ] Mandatory GET readback and controlled-field comparison are enforced.
- [ ] Kill switches and execution gates close after every attempt.

## Audit, retention and incident decisions

| Decision | Required outcome | Status |
| --- | --- | --- |
| Audit metadata retention | Exact approved duration | `PENDING` |
| Execution-ledger retention | Exact approved duration | `PENDING` |
| Storage/access authority | Named protected system and roles | `PENDING` |
| Tamper evidence | Approved immutable or equivalent mechanism | `PENDING` |
| Legal hold/deletion | Approved procedure | `PENDING` |
| Incident notification | Out-of-band owners and timing | `PENDING` |
| Credential exposure | Revoke/rotate and kill-switch procedure | `PENDING` |
| Ambiguous provider outcome | GET-only reconciliation procedure | `PENDING` |

- [ ] Audit excludes credentials and full business payloads/responses.
- [ ] No automatic rollback or corrective mutation is implied.
- [ ] Cleanup/correction requires separate authorization.
- [ ] Root-cause and regression evidence are required before re-enable.

## State review

The reviewer must confirm this exact state at the end of design review:

```text
DESIGNED=REVIEWED_OR_CHANGES_REQUESTED
CONFIGURED=false
IMPLEMENTED=false
TESTED=false
VALIDATED=false
APPROVED=<design decision only>
RELEASE_APPROVED=false
LIVE=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Permitted design-review outcomes

Choose exactly one:

- `CHANGES_REQUESTED` — list only non-sensitive required changes.
- `DESIGN_APPROVED_ONLY` — accepts the control design and authorizes no implementation or execution.
- `DESIGN_REJECTED` — production-write work remains blocked.

Any approval must identify the exact reviewed Git commit and explicitly say `DESIGN_APPROVED_ONLY`. A later implementation request must be a separate operator instruction. No approval phrase in this template is itself an active approval.
