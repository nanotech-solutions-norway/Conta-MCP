# Conta MCP Operational V1 Acceptance — 2026-10-13

## Result

The protected non-mutating operational acceptance completed successfully against the live production Conta MCP endpoint after explicit approval of the `domeneshop-readonly-validation` environment gate.

```text
CONTA_MCP_V1_OPERATIONAL=true
ACCEPTANCE_RUN_ID=38380188722
ACCEPTANCE_JOB_ID=115238460405
DEPLOYMENT_CONTROL_COMMIT=10189bb163adcc74494b05c4d39304e92a0b4775
DEPLOYED_CONTA_SOURCE_COMMIT=19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac
PUBLIC_CONTRACT_VALIDATED=true
REMOTE_RUNTIME_HASHES_VERIFIED=true
DEPLOYED_RELEASE_COMMIT_VERIFIED=true
KILL_SWITCH_GLOBAL_BLOCKED=true
PRODUCTION_AUTHORIZATION_PRESENT=false
APPROVED_RELEASE_MANIFEST_PRESENT=false
AUTHENTICATED_INITIALIZE_VERIFIED=true
TOOLS_LIST_CONTRACT_VERIFIED=true
EXECUTION_TOOL_VISIBLE=false
READ_ONLY_MCP_SMOKE_VERIFIED=true
GET_INVOICE_READBACK_EXERCISED=true
PREVIEW_VERIFIED=true
PREVIEW_PROVIDER_CALL_PERFORMED=false
PROVIDER_MUTATION_PERFORMED=false
CONTA_MCP_V1_ACCEPTANCE_PASSED=true
```

## Acceptance scope

The acceptance controller verified, without opening a write gate:

1. public `/`, `/health` and `/mcp` contract;
2. protected application/config/storage paths remained non-public;
3. unauthenticated MCP initialization remained rejected;
4. production health remained fail-closed;
5. global kill switch remained blocked;
6. no production authorization packet was present;
7. no approved release manifest was present;
8. the deployed release commit matched the expected Conta implementation;
9. all 19 protected runtime files matched the exact deployed source commit;
10. authenticated MCP initialization succeeded;
11. `tools/list` exposed the expected read/preview contract;
12. the execution tool remained absent while closed;
13. read-only Conta health, organization, customer and invoice MCP calls succeeded;
14. an invoice GET readback was exercised;
15. invoice-draft preview succeeded with `provider_call_performed=false` and execution ineligible while closed.

## Operational V1 boundary

Conta MCP V1 is operational for:

- authenticated MCP health and initialization;
- organization/customer/invoice read operations;
- deterministic non-mutating invoice-draft preview;
- previously validated bounded unsent invoice-draft creation when a new, explicit, one-use production work unit is authorized through the existing control plane.

No standing production-write authority exists. The default live state remains fail-closed.

The following remain outside standing V1 capability and require separate action-specific rollout and authorization: invoice send/finalize, corrections, deletion, customer/product mutation, ledger, bank, payment, payroll, VAT/statutory and broader accounting mutations.

## Safety result

The operational acceptance itself performed no provider mutation and did not enable production execution.

Evidence:
- acceptance workflow: https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/actions/runs/38380188722
- acceptance controller merge: https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/pull/77
- first bounded production execution: `docs/rollout/PRODUCTION_FIRST_INVOICE_DRAFT_EXECUTION_RESULT_20260825.md`

## Remaining security hygiene

Operational V1 acceptance is complete. One separate hygiene item remains outside the MCP functional acceptance: rotate the legacy Domeneshop webhotel credential identified in historical Drive evidence and remove the plaintext historical copy only after successful credential rotation and connectivity validation.

This hygiene item does not reopen or invalidate the successful MCP acceptance, but it should be closed before declaring the broader hosting credential estate fully hardened.
