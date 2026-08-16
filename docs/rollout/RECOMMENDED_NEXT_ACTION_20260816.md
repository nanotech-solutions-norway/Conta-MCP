# Conta MCP Recommended Next Action — 2026-08-16

## Current state

The first controlled Conta sandbox invoice-draft mutation is complete and independently verified.
The authorized fail-closed protected-runtime deployment and authenticated MCP contract validation are also complete.

```text
SANDBOX_INVOICE_DRAFT_CREATE=VERIFIED
READBACK_VERIFICATION=VERIFIED
SAME_KEY_REPLAY_REJECTION=VERIFIED
KILL_SWITCH_CLOSURE=VERIFIED
FAIL_CLOSED_DEPLOYMENT=VERIFIED
REMOTE_RUNTIME_HASHES=VERIFIED
AUTHENTICATED_INITIALIZE=VERIFIED
AUTHENTICATED_TOOL_CONTRACT=VERIFIED
EXECUTION_TOOL_VISIBLE=false
PRODUCTION_WRITE_AUTHORIZED=false
PRODUCTION_WRITE_PROGRAM=NOT_IMPLEMENTED
```

Definitive evidence:

- `SANDBOX_INVOICE_DRAFT_VERIFIED_SUCCESS_20260816.md`
- `FAIL_CLOSED_DEPLOYMENT_RESULT_20260816.md`
- `AUTHENTICATED_POST_DEPLOYMENT_CONTRACT_20260816.md`

## Next authorized work unit

The next work unit is **protected non-mutating post-deployment tool validation**, not another provider mutation.

Sequence:

1. Treat the verified sandbox draft as retained evidence; do not create another draft or perform cleanup without separate authorization.
2. Validate the non-provider health tool through the authenticated MCP boundary.
3. Validate invoice-draft preview with synthetic, non-customer data and confirm no provider call occurs.
4. Perform at most one sanitized read-only Conta provider check; retain only aggregate success evidence and no organization/customer/accounting payload.
5. Directly verify that the absent execution tool is rejected before provider dispatch.
6. Reconfirm health remains fail-closed after the non-mutating checks.
7. Only after this parity gate is verified, design and review a separate production-write program.

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

The fail-closed deployment authorization has been consumed successfully. The next protected gate is non-mutating tool validation only. No further sandbox mutation is required, and no production mutation is authorized.
