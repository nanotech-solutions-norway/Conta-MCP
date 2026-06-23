# Tool Scope — Conta MCP

## Scope principle

The initial Conta MCP exposes a narrow set of tools for controlled AI-assisted accounting review. The AI orchestrator should retrieve and summarize; it should not perform financial changes without explicit human approval.

## Implemented tools

### `conta_health_check`

Checks MCP configuration and optionally Conta API reachability.

Arguments:

```json
{
  "checkConta": false
}
```

### `conta_list_organizations`

Lists Conta organizations available to the configured API key.

Status: route should be verified in Conta Swagger before production because Conta documentation references an organization-list API but does not show the concrete route in the public help article.

### `conta_list_customers`

Lists/searches customers.

Arguments:

```json
{
  "organizationId": "optional if configured server-side",
  "q": "optional search string",
  "hits": 10,
  "page": 0,
  "sort": "id"
}
```

### `conta_get_customer`

Retrieves one customer by ID.

Arguments:

```json
{
  "organizationId": "optional if configured server-side",
  "customerId": "123"
}
```

### `conta_list_invoices`

Lists/searches invoices.

Arguments:

```json
{
  "organizationId": "optional if configured server-side",
  "q": "optional search string",
  "hits": 10,
  "page": 0,
  "sort": "id"
}
```

### `conta_get_invoice`

Retrieves one invoice by ID.

Arguments:

```json
{
  "organizationId": "optional if configured server-side",
  "invoiceId": "44"
}
```

### `conta_create_invoice_draft`

Creates a draft invoice only. Disabled by default.

Required server-side conditions:

```php
'enable_write_tools' => true,
'create_invoice_draft_route' => '/verified/route/from/conta/swagger/{orgId}/...',
```

The payload must match Conta Swagger.

## Explicitly excluded from v0.1

- send invoice
- delete invoice
- credit invoice automatically
- create payment
- delete payment
- post accounting transaction
- submit VAT return
- payroll actions
- bank integration actions
- voucher upload with attachments
- unrestricted REST proxy

## Why no generic REST proxy?

A generic `conta_request` tool would allow the AI client to call arbitrary Conta routes. That is not appropriate for an accounting system. The MCP must expose specific, reviewed, narrowly-scoped tools only.
