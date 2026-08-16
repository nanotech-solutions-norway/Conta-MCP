# Conta MCP Production-Write Program Design Decision — 2026-08-16

## Decision

The operator reviewed the production-write control proposal at commit:

```text
14f60d04b10dc87a68d55d85704fa5ebdd7880f2
```

and instructed: `Approved. Proceed accordingly.`

Under the decision vocabulary defined by `PRODUCTION_WRITE_PROGRAM_REVIEW_CHECKLIST_20260816.md`, this is recorded as:

```text
DECISION=DESIGN_APPROVED_ONLY
DESIGNED=APPROVED_CONTROL_FRAMEWORK
CONFIGURED=false
IMPLEMENTED=false
TESTED=false
VALIDATED=false
APPROVED=DESIGN_ONLY
RELEASE_APPROVED=false
LIVE=false
IMPLEMENTATION_AUTHORIZED=false
DEPLOYMENT_AUTHORIZED=false
FIRST_PRODUCTION_MUTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Approved scope

The approval accepts the proposed control framework for a possible first production `invoice_draft_create_v2` operation:

- one protected organization;
- one unsent invoice draft;
- one provider POST maximum;
- no automatic retry;
- deterministic preview and payload binding;
- separate accounting and security/release decisions;
- signed one-use approval;
- idempotency/replay protection;
- mandatory GET readback;
- immediate kill-switch and execution-gate closure;
- metadata-only audit and fail-closed incident containment.

It also accepts the permanent exclusion of sending, posting, update, delete, credit, cleanup, customer/product mutation, ledger, payment, bank, payroll and statutory operations from this first-production program.

## Conditions that remain unresolved

Design approval does not fill in or waive the protected operational decisions. Implementation remains blocked until authorized human reviewers decide and record, outside public repository data where necessary:

- named program, accounting, security/release, credential, execution and incident authorities;
- exact production organization validation and protected allowlist binding;
- provider least-privilege capability or an explicit capability-gap risk decision;
- maximum line count, maximum line amount and maximum draft total;
- exact protected customer-selection and duplicate-detection rules;
- production VAT, currency and fiscal-period rules;
- audit and execution-ledger retention durations and storage authority;
- tamper-evidence, legal-hold/deletion and incident notification procedures;
- exact implementation, deployment, release and first-execution approval records.

No unresolved condition may be inferred from sandbox evidence or supplied by a model.

## Next gate

The next permitted activity is protected completion of the pending governance decisions and preparation of a reviewable implementation authorization request. It must not include code/config implementation, credential provisioning, deployment, release approval, tool visibility or a Conta provider mutation.

A later operator instruction must explicitly authorize implementation for the exact approved design and completed decision record. Design approval alone is insufficient.
