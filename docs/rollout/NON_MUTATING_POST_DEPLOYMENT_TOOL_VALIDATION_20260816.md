# Conta MCP Non-Mutating Post-Deployment Tool Validation — 2026-08-16

## Result

The protected non-mutating parity gate is verified against the live fail-closed runtime.

```text
AUTHENTICATED_NON_PROVIDER_HEALTH=VERIFIED
SYNTHETIC_INVOICE_DRAFT_PREVIEW=VERIFIED
PREVIEW_PROVIDER_CALL_PERFORMED=false
READ_ONLY_PROVIDER_CALL_COUNT=1
READ_ONLY_PROVIDER_CALL=VERIFIED
ORGANIZATION_AGGREGATE_COUNT=1
EXECUTION_TOOL_VISIBLE=false
EXECUTION_TOOL_DIRECT_CALL=REJECTED_BEFORE_PROVIDER_DISPATCH
FINAL_FAIL_CLOSED_HEALTH=VERIFIED
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

No provider response body, organization identifier, organization name, customer data, accounting data or credential was retained in the workflow evidence.

## Canonical evidence

| Item | Evidence |
| --- | --- |
| Conta source commit | `b34e6bc44de297541c49922218b3c2411f64b071` |
| Validation workflow | `Conta Non-Mutating Post-Deployment Tools` |
| Successful validation run | [31949616296](https://github.com/nanotech-solutions-norway/Conta-MCP/actions/runs/31949616296) |
| Protected environment | `conta-sandbox-secrets` |
| Deployment-control commit | `8248e7f8577f10e9a8afa5c4fd1e756ece71bb8b` |
| Corrective deployment run | [31949481643](https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/actions/runs/31949481643) |
| Conta route correction | [PR #70](https://github.com/nanotech-solutions-norway/Conta-MCP/pull/70) |
| Deployment binding | [Domeneshop PR #28](https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/pull/28) |

## Fail-closed diagnostic history

The first protected run, `31948975163`, stopped at the organization tool without exposing provider data. A diagnostic-only follow-up, `31949134217`, retained only the sanitized HTTP status `404`. Repository evidence showed that `ContaClient` still used the stale `/organizations` route while the previously authenticated sandbox validation had verified `GET /invoice/organizations`.

The correction changed only the read-only organization-list route and added a PHP regression test. The protected deployment:

- backed up all 18 overwrite targets;
- verified every deployed payload hash;
- preserved the public bridge and server-only configuration;
- passed immediate fail-closed HTTP validation;
- performed no Conta provider mutation;
- authorized no production write.

The successful validation then performed the planned bounded sequence:

1. authenticated `conta_health_check` with `checkConta=false`;
2. synthetic `conta_preview_invoice_draft`, requiring `provider_call_performed=false`;
3. exactly one `conta_list_organizations` provider GET, retaining only aggregate count;
4. `tools/list` confirmation that `conta_create_invoice_draft` remained absent;
5. direct execution-tool probe rejected during MCP input validation before provider dispatch;
6. final public health confirmation of the fail-closed state.

## Boundary after validation

This result validates deployed non-mutating tool parity. It does not authorize or implement a production write program.

```text
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
EXECUTION_ALLOWED=false
PRODUCTION_WRITE_APPROVED=false
ALLOWED_WRITE_ACTION_COUNT=0
ALLOWED_WRITE_ORGANIZATION_COUNT=0
```

The next safe work unit is design and review of a separate production-write program. Configuration changes, write-tool visibility, deployment of an open write gate, and any provider mutation require new evidence and explicit operator authorization.
