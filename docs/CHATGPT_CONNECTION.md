# ChatGPT / AI Orchestrator Connection Notes

## Intended operating model

```text
ChatGPT / Atlas AI orchestrator
        ↓ calls approved MCP tools
Conta MCP endpoint on Domeneshop
        ↓ server-side API key only
Conta REST API
```

The AI client must never receive the Conta API key. The client receives only the MCP endpoint URL and MCP bearer token.

## Endpoint

Default deployment URL:

```text
https://www.nanococept.no/conta-mcp/mcp
```

Health URL:

```text
https://www.nanococept.no/conta-mcp/health
```

## Authentication

Client calls must include:

```http
Authorization: Bearer <CONTA_MCP_BEARER_TOKEN>
Content-Type: application/json
```

## Manual JSON-RPC examples

### Initialize

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "initialize",
  "params": {
    "protocolVersion": "2025-06-18",
    "capabilities": {},
    "clientInfo": {
      "name": "manual-test",
      "version": "1.0.0"
    }
  }
}
```

### List tools

```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/list"
}
```

### Call health tool

```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "conta_health_check",
    "arguments": {
      "checkConta": false
    }
  }
}
```

## Client configuration example

Exact remote-MCP setup depends on the AI client or ChatGPT connector/app environment available to the account. Use this as the configuration intent:

```json
{
  "mcpServers": {
    "conta-mcp": {
      "type": "http",
      "url": "https://www.nanococept.no/conta-mcp/mcp",
      "headers": {
        "Authorization": "Bearer REPLACE_WITH_MCP_BEARER_TOKEN"
      }
    }
  }
}
```

## Recommended ChatGPT instructions for this connector

Use this operational instruction when connecting the MCP to an AI assistant:

```text
Use the Conta MCP only for approved accounting-assistance tasks. Prefer read-only tools. Do not create, send, delete, post, pay, submit, or modify accounting records unless the user explicitly asks and the MCP tool is enabled server-side. Summarize results without exposing unnecessary customer, invoice, bank, payroll, or voucher details. If data is incomplete, stale, or unavailable, say so clearly.
```

## First validation sequence

1. Confirm health endpoint is available.
2. Confirm bearer-token rejection works with no token.
3. Confirm bearer-token rejection works with wrong token.
4. Confirm `initialize` works with correct token.
5. Confirm `tools/list` returns only approved tools.
6. Confirm `conta_health_check` works with `checkConta=false`.
7. Confirm sandbox Conta API works.
8. Confirm read-only customer/invoice retrieval works.
9. Keep write tools disabled.
