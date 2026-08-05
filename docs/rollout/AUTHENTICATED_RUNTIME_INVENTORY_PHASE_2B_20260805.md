# Conta MCP Authenticated Runtime Inventory — Phase 2B

**Date:** 2026-08-05  
**Runtime endpoint:** `https://mcp.atlas-ai.no`  
**Runtime identity:** `conta-mcp-server`  
**Runtime version:** `0.3.0-mcp-atlas-ai`  
**Protocol version:** `2025-06-18`

## Scope

This record documents authenticated MCP initialization, authenticated `tools/list`, the legacy production runtime write-gate investigation, and the resulting containment state.

No provider tool was called. No Conta API request, deployment, sandbox mutation, invoice-draft operation, or production write operation was performed.

## Authenticated runtime inventory

Authenticated `initialize` returned HTTP 200 with:

- server name: `conta-mcp-server`;
- server version: `0.3.0-mcp-atlas-ai`;
- protocol version: `2025-06-18`.

Authenticated `tools/list` returned seven tools:

- `conta_health_check`;
- `conta_list_organizations`;
- `conta_list_customers`;
- `conta_get_customer`;
- `conta_list_invoices`;
- `conta_get_invoice`;
- `conta_create_invoice_draft`.

The legacy runtime does not expose `conta_preview_invoice_draft`.

## Initial discrepancy

The execution tool remained visible after the server-side configuration was changed to:

```text
enable_write_tools=false
```

This initially appeared to be a tool-discovery containment failure.

## FTP deployment verification

The server-side configuration file was verified at:

```text
/Custom Models/conta-mcp/config/conta_config.local.php
```

Sanitized state:

```text
environment=production
enable_write_tools=false
```

The modified timestamp confirmed that the containment edit persisted.

The active runtime version marker `0.3.0-mcp-atlas-ai` was found in the same FTP runtime tree in `app/McpServer.php`.

Relevant deployed file hashes observed during the read-only inventory:

```text
app/McpServer.php  207c872f0d9b523ab9238f499a5922beb51c14c7332f7b894367ba7beaf13ae4
app/ContaTools.php 263cc581fb2a9954f3ea6d6d9559a5921704a0b905989ffa232039ffad322404
app/Config.php     052441da15c78b4c82d289531220a8078c55de947a902a4a35514ca0f8d250b0
```

## Legacy control-logic finding

The deployed legacy runtime always includes `conta_create_invoice_draft` in `tools/list`. Tool visibility is therefore not an execution-state indicator.

The tool description changes according to `writeToolsEnabled()`:

- enabled: execution-oriented description;
- disabled: `Disabled by policy. Enable only after sandbox validation and explicit approval.`

The actual tool-call path checks `writeToolsEnabled()` at the start of `createInvoiceDraft()` and returns a non-provider `403` result with `write_tools_disabled` when disabled.

## Live containment verification

A final authenticated `tools/list` returned HTTP 200 and the execution-tool description:

```text
Disabled by policy. Enable only after sandbox validation and explicit approval.
```

Accepted verification markers:

```text
LEGACY_WRITE_GATE_CONTAINMENT_VERIFIED=true
LIVE_CONFIG_WRITE_TOOLS_DISABLED=true
EXECUTION_GATE_DESCRIPTION_VERIFIED=true
TOOLS_CALL_PERFORMED=false
PROVIDER_TOOL_CALLED=false
PROVIDER_CALL_PERFORMED=false
CONFIGURATION_CHANGED=false
SANDBOX_MUTATION_PERFORMED=false
```

## Classification

```text
AUTHENTICATED_MCP_ACCESS_VERIFIED
AUTHENTICATED_TOOLS_LIST_VERIFIED
PRODUCTION_RUNTIME_IDENTIFIED_AS_LEGACY_0_3_0
SERVER_CONFIG_ENABLE_WRITE_TOOLS_FALSE
LEGACY_EXECUTION_PATH_FAILS_CLOSED_BEFORE_PROVIDER_OPERATION
LEGACY_TOOL_DISCOVERY_ALWAYS_ADVERTISES_EXECUTION_TOOL
PREVIEW_TOOL_NOT_IMPLEMENTED_OR_NOT_EXPOSED
PROVIDER_TOOL_CALLED=false
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
WRITE_TOOLS_ENABLED=false
```

## Remaining gap

The production runtime is contained, but it is not aligned with the canonical controlled-write implementation on `main`.

Remaining work must preserve the current fail-closed state and should include:

1. inventory and compare the full deployed legacy source against canonical `main`;
2. prepare a controlled deployment plan for the canonical runtime;
3. retain `enable_write_tools=false`, `runtime_write_blocked=true`, `execution_allowed=false`, `production_write_approved=false`, and the global kill switch during migration;
4. verify authenticated read-only tool discovery after deployment;
5. do not authorize provider read calls or any write execution without a separate operator gate.

## Safety state

```text
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
kill_switch_global_blocked=true
provider_call_performed=false
deployment_performed=false
sandbox_mutation_performed=false
write_tools_enabled=false
```
