# Security Policy — Conta MCP

## Classification

This integration touches accounting data and must be treated as business-sensitive.

## Hard rules

1. Never commit Conta API keys to GitHub.
2. Never commit `config/conta_config.local.php`.
3. Never commit real customer, invoice, voucher, payment, bank, payroll, VAT or accounting data.
4. Do not log full Conta request/response payloads.
5. Start with sandbox and read-only tools.
6. Write tools must remain disabled until explicit approval.
7. Destructive/accounting-posting tools are out of scope for the first version.

## Repository visibility

The repository is currently public. This increases the importance of keeping the repository limited to:

- source code
- documentation
- configuration templates
- validation scripts
- non-sensitive examples

For production accounting use, private repository visibility is recommended.

## Runtime authentication

The MCP endpoint requires:

```http
Authorization: Bearer <CONTA_MCP_BEARER_TOKEN>
```

This token is separate from the Conta API key. The client uses the MCP bearer token. The server uses the Conta API key.

## Conta API authentication

The Conta API key is sent from the server-side runtime to Conta using the `apiKey` HTTP header. The API key must only exist in server-side configuration or environment variables.

## Write-tool policy

Default:

```php
'enable_write_tools' => false
```

To enable draft-write tools, all of the following must be true:

1. Sandbox tests completed.
2. Correct Conta Swagger route verified.
3. `create_invoice_draft_route` configured server-side.
4. Human approval workflow defined.
5. Audit log reviewed.
6. Production impact understood.

## Blocked operations

The first implementation intentionally does not include:

- sending invoices
- deleting invoices
- deleting customers
- posting accounting entries
- modifying payments
- submitting VAT returns
- payroll functions
- bank integration actions

## Audit logging

Audit log should contain metadata only:

- timestamp
- tool name
- status code
- success/failure
- error text if applicable

Audit log should not contain:

- API keys
- bearer tokens
- full invoice payloads
- customer records
- voucher files
- bank data
- complete API responses

## Incident response

If a secret is accidentally committed:

1. Revoke the Conta API key immediately.
2. Rotate the MCP bearer token immediately.
3. Remove the secret from the live server.
4. Purge the Git history if necessary.
5. Review audit logs.
6. Create a replacement restricted API key.
7. Validate that no sensitive data was exposed.
