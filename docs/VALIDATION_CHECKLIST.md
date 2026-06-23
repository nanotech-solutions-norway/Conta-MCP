# Validation Checklist — Conta MCP

## Repository validation

- [ ] Repository contains no real API keys.
- [ ] Repository contains no `.env` file.
- [ ] Repository contains no `config/conta_config.local.php`.
- [ ] Repository contains no real customer, invoice, voucher, payment, bank, VAT, payroll or accounting data.
- [ ] `.gitignore` blocks runtime secrets and logs.

## Domeneshop upload validation

- [ ] Files uploaded to `/public_html/conta-mcp/`.
- [ ] `config/conta_config.local.php` created on the server only.
- [ ] `storage/` is writable by PHP.
- [ ] `https://www.nanoconcept.no/conta-mcp/health` returns JSON.
- [ ] Direct access to `/conta-mcp/config/conta_config.local.php` is blocked.
- [ ] Direct access to `/conta-mcp/storage/audit.log` is blocked.
- [ ] Direct directory listing is disabled.

## Authentication validation

- [ ] `/conta-mcp/mcp` rejects requests without bearer token.
- [ ] `/conta-mcp/mcp` rejects requests with wrong bearer token.
- [ ] `/conta-mcp/mcp` accepts requests with correct bearer token.
- [ ] Conta API key is not visible in any response.
- [ ] Conta API key is not visible in audit log.

## MCP protocol validation

- [ ] `initialize` returns protocol version and tool capability.
- [ ] `tools/list` returns approved tools only.
- [ ] `tools/call` validates tool name.
- [ ] `tools/call` rejects missing required parameters.
- [ ] JSON parse errors return JSON-RPC parse error.
- [ ] Unknown methods return JSON-RPC method-not-found error.

## Conta sandbox validation

- [ ] `conta_health_check` works with `checkConta=false`.
- [ ] `conta_health_check` works with `checkConta=true` after route/API validation.
- [ ] Organization-list route verified against Conta Swagger.
- [ ] Customer list route verified.
- [ ] Customer get route verified.
- [ ] Invoice list route verified.
- [ ] Invoice get route verified.

## Production readiness

- [ ] Production Conta API key generated for restricted user.
- [ ] Production organization ID configured.
- [ ] Write tools still disabled.
- [ ] Manual approval workflow defined.
- [ ] Audit log reviewed.
- [ ] Repo visibility reviewed. Private repository recommended for production accounting integration.
- [ ] Domain verified: `www.nanoconcept.no`.

## Approval gate for write tools

Do not enable `CONTA_ENABLE_WRITE_TOOLS=true` until:

- [ ] User explicitly approves write tools.
- [ ] Correct draft-invoice route is verified in Conta Swagger.
- [ ] Sandbox draft creation is validated.
- [ ] Audit log result is reviewed.
- [ ] Production rollback procedure exists.
