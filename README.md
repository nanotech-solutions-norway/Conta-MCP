# Conta MCP Server

**Project:** Conta-MCP  
**Target runtime:** Domeneshop PHP hosting  
**Default public endpoint:** `https://www.nanococept.no/conta-mcp/mcp`  
**Conta mode:** Sandbox first, production after validation  

This repository contains a minimal, dependency-free PHP MCP-style JSON-RPC server for connecting an AI orchestrator to Conta through Conta's official REST API.

## Important security position

This repository is currently public. Therefore:

- Do **not** commit real Conta API keys.
- Do **not** commit `.env`, `conta_config.local.php`, customer data, invoice data, voucher files, bank data, accounting exports, or payload logs.
- Deploy secrets only on the Domeneshop server as server-side configuration.
- Start in sandbox/read-only mode.
- Keep write tools disabled until explicit approval and separate validation.

## Architecture

```text
ChatGPT / Atlas AI orchestrator
        ↓
Remote MCP-style JSON-RPC endpoint
        ↓
Domeneshop PHP runtime
        ↓
Conta REST API client
        ↓
Conta API Gateway
```

## Repository structure

```text
Conta-MCP/
├── .htaccess
├── .gitignore
├── README.md
├── app/
│   ├── AuditLogger.php
│   ├── Config.php
│   ├── ContaClient.php
│   ├── ContaTools.php
│   ├── HttpClient.php
│   ├── McpServer.php
│   ├── Security.php
│   └── bootstrap.php
├── config/
│   ├── conta_config.example.php
│   └── tool_policy.php
├── public/
│   ├── .htaccess
│   ├── health.php
│   └── index.php
├── storage/
│   └── .gitkeep
├── tests/
│   └── smoke-test.sh
└── docs/
    ├── CHATGPT_CONNECTION.md
    ├── DEPLOY_DOMENESHOP.md
    ├── SECURITY_POLICY.md
    ├── TOOL_SCOPE.md
    └── VALIDATION_CHECKLIST.md
```

## Default tools

The initial tool set is deliberately conservative:

| Tool | Mode | Description |
|---|---|---|
| `conta_health_check` | Read-only | Checks MCP configuration and optionally Conta reachability. |
| `conta_list_organizations` | Read-only | Lists organizations available to the Conta API key. Route may need verification in Swagger. |
| `conta_list_customers` | Read-only | Lists/searches customers for a configured organization. |
| `conta_get_customer` | Read-only | Retrieves one customer by ID. |
| `conta_list_invoices` | Read-only | Lists/searches invoices for a configured organization. |
| `conta_get_invoice` | Read-only | Retrieves one invoice by ID. |
| `conta_create_invoice_draft` | Draft write | Disabled by default. Requires `CONTA_ENABLE_WRITE_TOOLS=true` and route verification. |

## Quick deployment summary

1. Upload repository contents to `/public_html/conta-mcp/` on Domeneshop.
2. Copy `config/conta_config.example.php` to `config/conta_config.local.php` on the server only.
3. Insert the real server-side values in `conta_config.local.php`.
4. Confirm `.htaccess` blocks direct access to `/app`, `/config`, `/storage`, `/docs`, `/tests`, and `/scripts`.
5. Test `https://www.nanococept.no/conta-mcp/health`.
6. Test JSON-RPC initialization against `https://www.nanococept.no/conta-mcp/mcp`.
7. Connect the endpoint to the AI/MCP client only after sandbox validation.

## Domain note

The requested deployment domain is written as `www.nanococept.no`. Existing NTSN context indicates `www.nanoconcept.no` is also used. Verify the spelling before production DNS and endpoint configuration.
