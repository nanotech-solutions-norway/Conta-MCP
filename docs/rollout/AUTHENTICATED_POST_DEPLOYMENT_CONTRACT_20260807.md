# Authenticated Post-Deployment MCP Contract Validation — 2026-08-07

## Classification

`AUTHENTICATED_POST_DEPLOYMENT_MCP_CONTRACT_VALIDATED`

## Runtime under validation

- Endpoint: `https://mcp.atlas-ai.no`
- Protected-runtime deployment source commit: `54b0d02d6eafe423a1d4485dfc88bdb85a28f5d0`
- Deployment payload SHA-256: `a7dc6133080c4c0fa70101da10624e012ea1c070e1ad9ddef5330be9ec2399b2`
- Deployment result evidence SHA-256: `af660c9e36c566270867b1aa21e4ffea5a6c7be4db96fb021a9623f127543ad6`
- Authenticated-contract evidence SHA-256: `7fc9cfc425b1ca09d1044e4735d10c503e2d871a21c4564b19fbb3f3ce04af47`

## Fail-closed health contract

```text
HEALTH_HTTP=200
HEALTH_ENVIRONMENT=production
HEALTH_CONFIGURED=True
HEALTH_WRITE_PREVIEW_ENABLED=True
HEALTH_WRITE_TOOLS_ENABLED=False
HEALTH_RUNTIME_WRITE_BLOCKED=True
HEALTH_EXECUTION_ALLOWED=False
HEALTH_PRODUCTION_WRITE_APPROVED=False
FAIL_CLOSED_HEALTH_CONTRACT_VERIFIED=true
```

## Authenticated initialize

```text
AUTH_INITIALIZE_HTTP=200
MCP_PROTOCOL_VERSION=2025-06-18
MCP_SERVER_NAME=conta-mcp-server
MCP_SERVER_VERSION=0.1.0
AUTHENTICATED_INITIALIZE_VERIFIED=true
```

## Authenticated tools/list

```text
AUTH_TOOLS_LIST_HTTP=200
MCP_TOOL_COUNT=7
MCP_TOOL=conta_get_customer
MCP_TOOL=conta_get_invoice
MCP_TOOL=conta_health_check
MCP_TOOL=conta_list_customers
MCP_TOOL=conta_list_invoices
MCP_TOOL=conta_list_organizations
MCP_TOOL=conta_preview_invoice_draft
BASE_READ_TOOL_COUNT=6
PREVIEW_TOOL_VISIBLE=true
EXECUTION_TOOL_VISIBLE=false
MCP_TOOL_SET_VERIFIED=true
```

The six canonical read tools are present. The non-mutating preview tool is visible. The execution tool `conta_create_invoice_draft` is not advertised while the execution gates remain closed.

## Execution safety

```text
TOOLS_CALL_PERFORMED=false
CONTA_TOOL_EXECUTED=false
PROVIDER_TOOL_CALLED=false
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
WRITE_TOOLS_ENABLED=false
```

No MCP `tools/call` request was issued during this validation. No Conta provider API request and no mutation occurred.

## Validator correction note

The first authenticated validation attempt reached authenticated `initialize` with HTTP 200 but stopped locally because PowerShell `Set-StrictMode` rejected direct access to an absent optional JSON-RPC `error` property. The validator was corrected to test optional properties through `PSObject.Properties`. No server rollback or runtime change was required because the failure was in local validation logic only.

## Gate result

```text
AUTHENTICATED_POST_DEPLOYMENT_MCP_CONTRACT_VALIDATED=true
FAIL_CLOSED_HEALTH_CONTRACT_VERIFIED=true
AUTHENTICATED_INITIALIZE_VERIFIED=true
MCP_TOOL_SET_VERIFIED=true
BASE_READ_TOOL_COUNT=6
PREVIEW_TOOL_VISIBLE=true
EXECUTION_TOOL_VISIBLE=false
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
WRITE_TOOLS_ENABLED=false
NEXT_REQUIRED_GATE=CURRENT_CONTA_PROVIDER_SCHEMA_AND_SANDBOX_EVIDENCE_REFRESH
```

## Next gate

Refresh current official Conta provider evidence for schema, route, scopes, sandbox/test-company constraints, create-response draft identifier location, and readback support. This remains evidence-only/read-only work. It does not authorize provider execution, write enablement, sandbox mutation, production write, release-manifest approval, kill-switch opening, or one-use authorization issuance.
