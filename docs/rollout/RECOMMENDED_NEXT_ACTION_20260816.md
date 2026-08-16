# Conta MCP Recommended Next Action — 2026-08-16

## Current state

The first controlled Conta sandbox invoice-draft mutation is complete and independently verified.
The authorized fail-closed protected-runtime deployment and authenticated MCP contract validation are also complete.
The protected non-mutating post-deployment tool parity gate is complete after correcting the stale read-only organization-list route.

```text
SANDBOX_INVOICE_DRAFT_CREATE=VERIFIED
READBACK_VERIFICATION=VERIFIED
SAME_KEY_REPLAY_REJECTION=VERIFIED
KILL_SWITCH_CLOSURE=VERIFIED
FAIL_CLOSED_DEPLOYMENT=VERIFIED
REMOTE_RUNTIME_HASHES=VERIFIED
AUTHENTICATED_INITIALIZE=VERIFIED
AUTHENTICATED_TOOL_CONTRACT=VERIFIED
NON_MUTATING_POST_DEPLOYMENT_TOOL_VALIDATION=VERIFIED
READ_ONLY_ORGANIZATION_CHECK=VERIFIED
EXECUTION_TOOL_VISIBLE=false
PRODUCTION_WRITE_AUTHORIZED=false
PRODUCTION_WRITE_PROGRAM=NOT_IMPLEMENTED
PRODUCTION_WRITE_PROGRAM_DESIGN=DESIGN_APPROVED_ONLY
PRODUCTION_WRITE_DECISION_GATE=AUTHORIZED
PRODUCTION_WRITE_DECISION_METHOD=GITHUB_ENVIRONMENT_SECRETS
PRODUCTION_WRITE_DECISION_SECRETS_CONFIGURED=false
PROTECTED_PRODUCTION_DECISIONS_COMPLETE=false
PRODUCTION_WRITE_IMPLEMENTATION_AUTHORIZED=false
```

Definitive evidence:

- `SANDBOX_INVOICE_DRAFT_VERIFIED_SUCCESS_20260816.md`
- `FAIL_CLOSED_DEPLOYMENT_RESULT_20260816.md`
- `AUTHENTICATED_POST_DEPLOYMENT_CONTRACT_20260816.md`
- `NON_MUTATING_POST_DEPLOYMENT_TOOL_VALIDATION_20260816.md`
- `PRODUCTION_WRITE_PROGRAM_DESIGN_20260816.md` (approved control framework; no implementation authority)
- `PRODUCTION_WRITE_PROGRAM_REVIEW_CHECKLIST_20260816.md`
- `PRODUCTION_WRITE_PROGRAM_DESIGN_DECISION_20260816.md` (design-only approval)
- `PRODUCTION_WRITE_PROTECTED_DECISION_GATE_20260816.md` (authorized governance scope)
- `PRODUCTION_WRITE_DECISION_GATE_AUTHORIZATION_REQUEST_20260816.md` (authorization consumed for exact commit)
- `PRODUCTION_WRITE_GITHUB_SECRETS_DECISION_GATE_20260816.md` (operator-selected remote method)
- `PRODUCTION_WRITE_IMPLEMENTATION_AUTHORIZATION_REQUEST_TEMPLATE_20260816.md` (not ready)

## Next authorized work unit

The next safe work unit is **protected completion and review of production-write governance decisions**. It remains a governance phase only.

The operator approved the control framework as `DESIGN_APPROVED_ONLY`. The next proposed gate prepares and reviews those protected decisions without putting their values in Git, chat or public workflow inputs. This gate requires an exact-commit authorization and does not authorize implementation.

Sequence:

1. Enter the pending decisions directly as protected secrets in the reviewer-protected `conta-production-write-decisions` GitHub environment.
2. Obtain explicit accounting and security/release decisions on customer selection, VAT treatment, credential custody, audit retention and incident response.
3. Manually dispatch the governance-only attestation workflow from `main` and approve its protected environment gate.
4. Produce only the repository-safe decision-packet hash and required attestations.
5. Prepare a bounded implementation-authorization request referencing the approved design and completed decisions.
6. Preserve the verified sandbox draft and non-mutating parity evidence; do not create another draft or perform cleanup without separate authorization.
7. Require a later, separate explicit operator authorization before any implementation, configuration, deployment, write-tool visibility or provider mutation.

## Required fail-closed deployment state

Any deployment candidate must preserve:

```text
enable_write_preview=true
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_organization_ids=[]
allowed_write_actions=[]
kill_switch_global_blocked=true
sandbox_authorization=missing_or_pending
```

The production runtime must not inherit sandbox credentials, sandbox authorization packets, temporary ledgers, one-use approvals, or the sandbox-only execution state.

## Production-write program remains a separate gate

Sandbox success proves that the invoice-draft create control path works against Conta under the tested sandbox conditions. It does not prove or authorize production execution.

Before any production mutation, separately define and approve at minimum:

- production organization allowlist and identity validation;
- production provider/VAT prerequisites;
- production release manifest and deployment hash authority;
- production approval authority and credential custody;
- production-specific rate/amount/customer/operation limits;
- production audit retention and incident response;
- rollback/containment procedures;
- production readback semantics;
- explicit operator approval for the first production mutation.

Until that program is implemented and approved, `ContaClient`/`WritePolicy` must continue to refuse production writes.

## Stop conditions

Stop before deployment or mutation if any of the following is true:

- stabilization CI/security gates are not green;
- runtime commit/hash cannot be established;
- active production endpoint differs unexpectedly from the selected candidate;
- any write-capable tool becomes executable in production;
- production config opens a write gate;
- sandbox secrets or sandbox authorization material appear in deployment artifacts;
- provider schema/route evidence is stale or conflicting;
- any provider mutation would be required merely to validate deployment.

## Manual operator gate

The fail-closed deployment and non-mutating tool validation authorizations have been consumed successfully. No further sandbox mutation is required, and no production mutation is authorized. Production-write design/review does not authorize implementation, configuration changes, deployment or execution.
