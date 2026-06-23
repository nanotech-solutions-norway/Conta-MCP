# Step-by-Step Creation Instructions — Conta MCP

## Objective

Create a controlled Conta MCP endpoint where ChatGPT or another AI client can orchestrate approved Conta tools without ever receiving Conta API credentials.

## Phase 1 — GitHub repository

1. Use repository: `nanotech-solutions-norway/Conta-MCP`.
2. Keep the repository free of secrets and accounting data.
3. Review these files:
   - `README.md`
   - `docs/SECURITY_POLICY.md`
   - `docs/TOOL_SCOPE.md`
   - `docs/DEPLOY_DOMENESHOP.md`
   - `docs/VALIDATION_CHECKLIST.md`
4. Recommended before production: change repository visibility to private.

## Phase 2 — Conta sandbox

1. Create or log in to Conta sandbox.
2. Create a restricted user for API testing.
3. Generate an API key from the Conta user account settings.
4. Use Conta Swagger to identify:
   - organization ID
   - customer list/get routes
   - invoice list/get routes
   - invoice draft route if later needed
5. Do not use a production API key until sandbox validation is complete.

## Phase 3 — Domeneshop upload

1. Open Domeneshop file manager or SFTP.
2. Create folder:

```text
/public_html/conta-mcp/
```

3. Upload repository contents into that folder.
4. Confirm this file exists:

```text
/public_html/conta-mcp/public/index.php
```

5. Confirm root `.htaccess` exists:

```text
/public_html/conta-mcp/.htaccess
```

## Phase 4 — Server-only configuration

1. On the server, copy:

```text
config/conta_config.example.php
```

to:

```text
config/conta_config.local.php
```

2. Edit only the local file.
3. Insert:
   - Conta sandbox API key
   - MCP bearer token
   - default Conta organization ID
   - allowed origin
4. Keep:

```php
'environment' => 'sandbox',
'enable_write_tools' => false,
```

## Phase 5 — Health validation

Open:

```text
https://www.nanoconcept.no/conta-mcp/health
```

Expected result:

```json
{
  "status": "ok",
  "service": "conta-mcp-server",
  "configured": true
}
```

If `configured` is false, check:

- `conta_api_key`
- `mcp_bearer_token`
- file path of `conta_config.local.php`

## Phase 6 — MCP protocol validation

Test initialize:

```bash
curl -sS -X POST 'https://www.nanoconcept.no/conta-mcp/mcp' \
  -H 'Authorization: Bearer REPLACE_WITH_MCP_BEARER_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"manual-test","version":"1.0.0"}}}'
```

Test tools:

```bash
curl -sS -X POST 'https://www.nanoconcept.no/conta-mcp/mcp' \
  -H 'Authorization: Bearer REPLACE_WITH_MCP_BEARER_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":2,"method":"tools/list"}'
```

## Phase 7 — Conta sandbox validation

1. Run `conta_health_check` with `checkConta=false`.
2. Run `conta_health_check` with `checkConta=true` after verifying organization route.
3. Validate customer list/get.
4. Validate invoice list/get.
5. Review audit log metadata.
6. Confirm no sensitive payloads are logged.

## Phase 8 — AI client connection

Use endpoint:

```text
https://www.nanoconcept.no/conta-mcp/mcp
```

Use authorization:

```http
Authorization: Bearer <CONTA_MCP_BEARER_TOKEN>
```

Use client config template:

```text
mcp-client-config.example.json
```

## Phase 9 — Production migration

Only after sandbox validation:

1. Create restricted production Conta API key.
2. Change server-only config:

```php
'environment' => 'production',
'conta_api_key' => 'PRODUCTION_KEY',
```

3. Keep write tools disabled.
4. Repeat validation checklist.

## Phase 10 — Write-tool approval gate

Do not enable draft write tools until all are complete:

- route verified in Conta Swagger
- sandbox draft invoice created successfully
- human approval workflow defined
- rollback procedure documented
- audit log reviewed
- explicit approval received
