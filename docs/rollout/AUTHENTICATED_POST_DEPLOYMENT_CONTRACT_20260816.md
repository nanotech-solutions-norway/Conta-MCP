# Authenticated Post-Deployment MCP Contract — 2026-08-16

## Classification

`AUTHENTICATED_POST_DEPLOYMENT_MCP_CONTRACT_VALIDATED`

## Runtime and validator identity

```text
endpoint=https://mcp.atlas-ai.no/mcp
deployed_candidate_source_commit=7d97a6330e4aff5a6e251ad19d717d7408cf3825
validator_main_commit=cc5585e059dfab1c96d40e8bb116a86c7f6d3b82
workflow_run=31945197474
workflow_conclusion=success
```

The validator used only the protected `CONTA_MCP_BEARER_TOKEN` secret. It issued exactly two JSON-RPC methods: authenticated `initialize` and authenticated `tools/list`. It did not issue `tools/call`, call the Conta provider, or print response payloads or credentials.

## Authenticated initialize

```text
AUTHENTICATED_INITIALIZE_VERIFIED=true
MCP_PROTOCOL_VERSION=2025-06-18
MCP_SERVER_NAME=conta-mcp-server
MCP_SERVER_VERSION=0.1.0
```

## Authenticated tool contract

```text
MCP_TOOL_COUNT=7
BASE_READ_TOOL_COUNT=6
PREVIEW_TOOL_VISIBLE=true
EXECUTION_TOOL_VISIBLE=false
MCP_TOOL_SET_VERIFIED=true
```

The exact visible tool set was:

```text
conta_get_customer
conta_get_invoice
conta_health_check
conta_list_customers
conta_list_invoices
conta_list_organizations
conta_preview_invoice_draft
```

The execution tool `conta_create_invoice_draft` was absent.

## Execution safety

```text
TOOLS_CALL_PERFORMED=false
PROVIDER_CALL_PERFORMED=false
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
AUTHENTICATED_POST_DEPLOYMENT_MCP_CONTRACT_VALIDATED=true
```

## Next gate

The next safe work unit is protected, non-mutating post-deployment tool validation: local health behavior, preview-only behavior, a sanitized read-only provider check, and direct execution refusal. It must not create another sandbox draft or perform any production mutation. Production-write program design remains a separate future gate.

