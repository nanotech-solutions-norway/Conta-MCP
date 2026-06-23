# Conta Route Map — v0.1

This file separates routes visible in Conta's public API help article from routes that must be confirmed in Conta Swagger before production use.

## Source basis

Conta's public API help states that Conta exposes REST/JSON APIs through production and sandbox base URLs and that API keys are sent using the `apiKey` HTTP header. It also gives concrete examples for customer routes and list query parameters.

## Base URLs

| Environment | Base URL |
|---|---|
| Sandbox | `https://api.gateway.conta-sandbox.no` |
| Production | `https://api.gateway.conta.no` |

## Routes currently used by the MCP

| MCP tool | Route template | Status |
|---|---|---|
| `conta_list_organizations` | `/organizations` | Must verify in Swagger before production. Public help references organization-list API but does not show concrete route. |
| `conta_list_customers` | `/invoice/organizations/{orgId}/customers` | Public help provides this route example. |
| `conta_get_customer` | `/invoice/organizations/{orgId}/customers/{customerId}` | Public help provides this route example. |
| `conta_list_invoices` | `/invoice/organizations/{orgId}/invoices` | Must verify in Swagger before production. Public help shows invoice/payment examples under this route family. |
| `conta_get_invoice` | `/invoice/organizations/{orgId}/invoices/{invoiceId}` | Must verify in Swagger before production. Public help shows invoice/payment examples under this route family. |
| `conta_create_invoice_draft` | Server-side configurable only | Disabled until Swagger route is verified. |

## Validation procedure

1. Open Conta sandbox Swagger.
2. Confirm organization-list route.
3. Confirm customer list/get routes.
4. Confirm invoice list/get routes.
5. Confirm the exact draft-invoice creation route and payload schema.
6. Update `CONTA_ROUTE_CREATE_INVOICE_DRAFT` only on the server, not in GitHub.
7. Keep `CONTA_ENABLE_WRITE_TOOLS=false` until sandbox creation is validated.

## Why this map exists

This project is intended for accounting-system integration. Incorrect API assumptions can cause false results or write-action risks. Route assumptions must be documented instead of silently embedded.
