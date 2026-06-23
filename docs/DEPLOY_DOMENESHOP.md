# Domeneshop Deployment Guide — Conta MCP

**Target domain:** `https://www.nanococept.no`  
**Recommended folder:** `/public_html/conta-mcp/`  
**Primary endpoint:** `https://www.nanococept.no/conta-mcp/mcp`  
**Health endpoint:** `https://www.nanococept.no/conta-mcp/health`

> Verify whether the intended domain is `nanococept.no` or `nanoconcept.no` before production release.

## 1. Prepare Conta access

1. Log in to Conta sandbox first.
2. Create or use a restricted Conta user for API testing.
3. Generate an API key under Conta user settings.
4. Retrieve the relevant organization ID from Conta/Swagger or the organization-list tool.
5. Keep production API keys separate from sandbox keys.

## 2. Prepare the GitHub repository locally

Clone the repository:

```bash
git clone https://github.com/nanotech-solutions-norway/Conta-MCP.git
cd Conta-MCP
```

Do not add any real secrets to GitHub.

## 3. Upload to Domeneshop

Upload the repository contents to:

```text
/public_html/conta-mcp/
```

Expected server-side layout:

```text
/public_html/conta-mcp/.htaccess
/public_html/conta-mcp/public/index.php
/public_html/conta-mcp/public/health.php
/public_html/conta-mcp/app/*.php
/public_html/conta-mcp/config/conta_config.example.php
/public_html/conta-mcp/config/conta_config.local.php  <-- server-only
/public_html/conta-mcp/storage/
```

## 4. Create server-only configuration

On the Domeneshop server, copy:

```bash
cp config/conta_config.example.php config/conta_config.local.php
```

Edit `config/conta_config.local.php` on the server only:

```php
<?php
return [
    'environment' => 'sandbox',
    'conta_api_key' => 'PASTE_CONTA_SANDBOX_API_KEY_HERE',
    'default_organization_id' => 'PASTE_ORG_ID_HERE',
    'mcp_bearer_token' => 'PASTE_LONG_RANDOM_MCP_TOKEN_HERE',
    'allowed_origin' => 'https://www.nanococept.no',
    'enable_write_tools' => false,
    'create_invoice_draft_route' => '',
    'request_timeout_seconds' => 20,
    'audit_log_path' => __DIR__ . '/../storage/audit.log',
];
```

Generate a strong random bearer token locally, for example:

```bash
openssl rand -hex 32
```

## 5. Set file permissions

Recommended shared-hosting permissions:

```text
Directories: 755
PHP/config files: 640 or 644 depending on Domeneshop requirements
storage/: writable by PHP runtime, not publicly browsable
```

The root `.htaccess` blocks `/app`, `/config`, `/storage`, `/docs`, and `/tests` from direct browser access.

## 6. Test health endpoint

Open:

```text
https://www.nanococept.no/conta-mcp/health
```

Expected:

```json
{
  "status": "ok",
  "service": "conta-mcp-server",
  "configured": true
}
```

If `configured` is false, the server is missing either `conta_api_key` or `mcp_bearer_token`.

## 7. Test MCP initialize

Run from your local machine:

```bash
curl -sS -X POST 'https://www.nanococept.no/conta-mcp/mcp' \
  -H 'Authorization: Bearer REPLACE_WITH_MCP_BEARER_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"manual-test","version":"1.0.0"}}}' | jq
```

Expected response includes:

```json
{
  "protocolVersion": "2025-06-18",
  "capabilities": {
    "tools": {
      "listChanged": false
    }
  }
}
```

## 8. Test tool listing

```bash
curl -sS -X POST 'https://www.nanococept.no/conta-mcp/mcp' \
  -H 'Authorization: Bearer REPLACE_WITH_MCP_BEARER_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":2,"method":"tools/list"}' | jq
```

## 9. Test health tool

```bash
curl -sS -X POST 'https://www.nanococept.no/conta-mcp/mcp' \
  -H 'Authorization: Bearer REPLACE_WITH_MCP_BEARER_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":3,"method":"tools/call","params":{"name":"conta_health_check","arguments":{"checkConta":false}}}' | jq
```

## 10. Production switch

Only after sandbox validation:

```php
'environment' => 'production',
'conta_api_key' => 'PRODUCTION_KEY_HERE',
'enable_write_tools' => false,
```

Do not enable write tools until a separate approval and validation pass is completed.
