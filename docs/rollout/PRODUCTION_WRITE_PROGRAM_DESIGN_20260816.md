# Conta MCP Production-Write Program Design — 2026-08-16

## Purpose and authority boundary

This record proposes the control program for a possible first production `invoice_draft_create_v2` execution. It is a review artifact only.

```text
DESIGNED=PROPOSED_FOR_REVIEW
CONFIGURED=false
IMPLEMENTED=false
TESTED=false
VALIDATED=false
APPROVED=false
RELEASE_APPROVED=false
LIVE=false
PRODUCTION_WRITE_AUTHORIZED=false
```

Merging or approving this design must not enable a tool, change runtime configuration, create a release manifest, install a production write credential, approve a payload, deploy an open gate or invoke Conta.

## Proposed first-production scope

The only candidate action is creation of one unsent Conta invoice draft:

```text
candidate_id=invoice_draft_create_v2
provider_method=POST
provider_route=/invoice/organizations/{opContextOrgId}/invoice-drafts
maximum_provider_mutations_per_authorization=1
automatic_retry=false
send_or_finalize=false
```

The following remain out of scope:

- sending, posting or finalizing an invoice;
- update, delete, credit or cleanup actions;
- customer or product creation/update;
- vouchers, ledger entries, payments, bank actions, payroll or VAT/statutory submissions;
- batch operations or more than one organization;
- autonomous payload selection or approval by a model.

## Required separation of duties

All identities are server-side references. Names, account identifiers and credentials must not be committed to this repository.

| Responsibility | Required authority | Separation rule |
| --- | --- | --- |
| Program owner | Owns scope and risk acceptance | Cannot alone approve a release or execution |
| Accounting reviewer | Confirms customer, VAT, currency, amount and draft-only intent | Must review the human-readable preview |
| Security/release reviewer | Confirms source, schema, controls and deployment evidence | Must not possess the provider credential merely to review |
| Credential custodian | Provisions/rotates the restricted server-side Conta credential | Must not transmit credential material through Git, chat or Actions logs |
| Execution approver | Issues one-use approval for the exact payload hash | Must be a human; model judgment is not approval |
| Incident owner | Can close kill switches and coordinate containment | Must have an out-of-band response path |

At least two human decisions are required: accounting approval of the exact intent and security/release approval of the exact runtime. A single GitHub merge is neither decision.

## Production organization identity gate

Before implementation or execution:

1. The operator selects exactly one production organization through Conta's trusted UI or another approved out-of-band process.
2. The organization identifier is entered only through the protected server-side secret/configuration boundary.
3. A protected GET-only workflow confirms that the configured credential can see exactly the intended organization.
4. The operator compares the protected workflow result with the Conta UI/legal-company context without printing the identifier or company data.
5. Repository evidence records only a one-way organization-reference hash, aggregate match count and reviewer decision.
6. The allowlist contains exactly one organization and remains empty until the program implementation receives separate authorization.
7. Any identity mismatch, multiple match, inaccessible organization or changed organization hash stops the program.

## Credential custody and provider capability gate

- Use a production-only server-side Conta credential; never reuse the sandbox credential.
- Separate the MCP client credential from the Conta provider credential.
- Prefer a provider identity restricted to the single organization and invoice-draft capability. If Conta cannot enforce that restriction, document the capability gap and require an explicit security risk decision before implementation.
- Do not expose the production write credential to the read-only process, a general coding shell or unprotected CI.
- Prefer short-lived/workload credentials where supported. Any static provider key is a compatibility exception held only by the approved secret boundary.
- Rotation, revocation and emergency disable procedures must be tested without making a provider mutation.
- Credential presence, organization visibility and successful GET access are prerequisites, not write authorization.

## Provider and accounting prerequisites

Implementation remains blocked until all evidence is current for the selected production organization:

- official create and readback routes plus schema SHA-256;
- provider credential capability and organization access;
- invoicing and VAT prerequisites confirmed in Conta;
- permitted invoice language, currency and registration source;
- controlled VAT code and accounting interpretation;
- customer identity resolved through a protected boundary;
- fiscal date/period and duplicate-detection rules;
- readback behavior compatible with the narrow verified projection;
- correction path for an erroneous unsent draft.

Schema or route evidence must be refreshed if the provider contract changes or before the first execution if the reviewed evidence is stale.

## Initial execution limits

The first production program must enforce all limits below in policy or an equivalent fail-closed protected runner. A prose promise alone is insufficient.

| Limit | Required first-run value |
| --- | --- |
| Action | `invoice_draft_create_v2` only |
| Organization | Exactly one protected allowlisted organization |
| Provider mutations | Exactly one POST maximum |
| Objects | Exactly one unsent invoice draft |
| Lines | Explicit operator-approved maximum; `PENDING_ACCOUNTING_DECISION` |
| Total and per-line amount | Explicit NOK caps; `PENDING_ACCOUNTING_DECISION` |
| Currency | One operator-approved currency; proposed `NOK` |
| Customer | One exact protected customer reference/hash |
| VAT | Exact reviewed VAT treatment bound to payload hash |
| Approval lifetime | At most 900 seconds for the first run |
| Retry | None; ambiguous outcomes require GET reconciliation |
| Cleanup | None; separate authorization required |

No implementation may begin while either numeric amount cap, line cap, customer selection rule or accounting treatment remains pending.

## Release and deployment authority

The production release must bind:

- immutable source commit and per-file hashes;
- provider schema hash and exact route templates;
- write-policy version;
- the single allowed action;
- the protected organization-reference hash;
- the tested approval, ledger, kill-switch and readback controls;
- the exact deployment target and backup/rollback evidence.

Required sequence:

1. Build and test with every production execution gate closed.
2. Review a release manifest that contains no secrets, payload or raw organization/customer identifier.
3. Deploy fail-closed and verify the exact runtime hashes.
4. Verify authenticated discovery still hides the execution tool.
5. Obtain a separate release-approval decision for the exact deployed commit.
6. Open only the minimum gates for a time-bounded, one-use execution window.
7. Close the kill switch and execution gates immediately after the attempt, regardless of outcome.

Design approval, implementation approval, deployment approval, release approval and execution approval are separate decisions.

## Intent, preview and approval contract

The production intent must be constructed outside model context from protected operator inputs. Before approval it must produce:

- action, environment and protected organization reference;
- canonical payload hash;
- human-readable invoice-draft preview;
- customer-reference hash;
- currency, VAT, line count and total amount;
- duplicate/pre-read result;
- provider schema, policy and release versions;
- risk class and correction plan.

The one-use signed approval must bind the exact action, environment, organization, method, route, payload hash, approval ID, approver reference, nonce, idempotency key, issue time and expiry. Any mismatch or expiry rejects execution.

## First execution protocol

1. Confirm exact accepted runtime and closed default health state.
2. Confirm current schema/route evidence and protected organization identity.
3. Perform GET-only pre-read and duplicate checks.
4. Generate deterministic preview and canonical payload hash.
5. Obtain accounting approval of the preview and security/release approval of the runtime.
6. Generate the signed one-use approval in protected runner-temporary storage.
7. Open the action-specific kill-switch window and minimum execution gates.
8. Revalidate every permit field immediately before network I/O.
9. Dispatch at most one POST.
10. Never automatically retry, including on timeout or ambiguous provider response.
11. Perform GET readback and compare the controlled projection.
12. Close the kill switch and all execution gates.
13. Record privacy-minimized audit closure and require human review.

Success requires provider create success, exact draft identification and mandatory readback verification. A local verification error after a possible provider success is an indeterminate outcome, not permission to retry.

## Readback semantics

The controlled projection must strictly compare all substantive approved fields. The sandbox evidence permits only these narrow representation accommodations:

- integer/float numeric equality when both decoded values are numeric scalars;
- omission of `registrationSource`; when returned, its value must match.

Numeric strings, VAT differences, customer differences, amount differences, missing invoice lines or any other substantive mismatch fail verification and enter containment.

## Audit and retention design

Audit evidence is metadata-only and tamper-evident. It records lifecycle state, timestamps, actor/workload reference, protected organization/customer hashes, action/risk class, policy/schema/release versions, approval/idempotency references, payload hash, provider request reference/hash, sanitized status, readback hash and containment/closure state.

It must not contain credentials, raw organization/customer identifiers, full invoice payloads, full responses or unnecessary accounting data.

Retention is a blocking accounting/security decision. Before implementation, the program owner and accounting reviewer must approve:

- exact retention duration for audit metadata and execution-ledger evidence;
- storage authority, encryption and access controls;
- tamper-evidence/immutability mechanism;
- deletion/legal-hold procedure;
- separation between operational logs and accounting records.

No duration is inferred by this design, and activation is blocked while `AUDIT_RETENTION_DECISION=PENDING`.

## Incident containment and correction

On any mismatch, unexpected tool visibility, gate drift, timeout, ambiguous result, suspected credential exposure or audit failure:

1. stop before another provider mutation;
2. close global/action kill switches and execution gates;
3. revoke/rotate affected credentials when exposure cannot be excluded;
4. preserve privacy-minimized evidence and runtime hashes;
5. use GET-only reconciliation to determine provider state;
6. retain an erroneous unsent draft unchanged unless a separate correction/cleanup action is explicitly authorized;
7. notify the incident owner and accounting reviewer out of band;
8. complete root-cause, regression and renewed approval before re-enable.

There is no automatic rollback. Deleting, editing, crediting, sending or posting a draft is a separate provider mutation and requires its own reviewed program and authorization.

## Promotion gates

| Gate | Required evidence | Current state |
| --- | --- | --- |
| Design review | Approved program and completed decision checklist | `PENDING` |
| Implementation authorization | Explicit authorization for code/config work | `NOT_GRANTED` |
| Implementation | Production-specific controls and offline tests | `NOT_IMPLEMENTED` |
| Protected validation | Exact candidate tests with no provider mutation | `NOT_VALIDATED` |
| Fail-closed deployment | Backup, hashes and closed-gate health | `NOT_AUTHORIZED` |
| Release approval | Exact deployed commit and manifest | `NOT_APPROVED` |
| First execution approval | Exact payload-bound one-use approval | `NOT_GRANTED` |
| Live verification | One POST plus mandatory GET readback and closure | `NOT_LIVE` |

Every gate fails closed. Passing one gate does not imply or authorize a later gate.

## Design stop conditions

The proposal must remain unapproved if any of these is unresolved:

- named human roles and separation of duties;
- exact production organization identity procedure;
- provider credential capability/least-privilege decision;
- current schema and production accounting prerequisites;
- line and amount caps;
- customer selection and duplicate rules;
- audit retention and immutable storage decision;
- incident contacts and correction procedure;
- exact implementation, release and execution approval syntax.

The associated review checklist is `PRODUCTION_WRITE_PROGRAM_REVIEW_CHECKLIST_20260816.md`.
