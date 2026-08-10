# Current Conta Provider Schema and Sandbox Evidence Refresh — 2026-08-10

## Classification

```text
CURRENT_PROVIDER_SCHEMA_REFRESH_COMPLETE=true
OFFICIAL_DOCUMENTATION_ONLY=true
ACCOUNT_LEVEL_PROVIDER_CALL_PERFORMED=false
MCP_TOOLS_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
WRITE_ENABLEMENT_CHANGED=false
```

This refresh used only public official Conta help and OpenAPI documentation. It did not use a Conta API key, inspect an organization identifier, authenticate to the MCP runtime, invoke a tool, or make an account-level provider request.

## Official sources

- Conta API help: <https://hjelp.conta.no/api/>
- Production OpenAPI: <https://docs.gateway.conta.no/docs/conta-external-api.json>
- Sandbox OpenAPI: <https://docs.gateway.conta-sandbox.no/docs/conta-external-api.json>

Observed document metadata:

```text
openapi=3.0.3
title=Conta API
version=1.0.0
production_last_modified=Sat, 08 Aug 2026 00:43:08 GMT
sandbox_last_modified=Sat, 08 Aug 2026 11:57:09 GMT
production_sha256=764f10e8bbcce27e0b940149bfe81f257a0849bf2828f5ff2140c48eab07bbbd
sandbox_sha256=8c8be48fb6cabf22f097f4879be495dbc789a68ceebbad763b526bff85b598a6
schema_equal_except_server_url=true
normalized_schema_sha256=303f2443dc25458ad3e7d6eb55b7c356234cc32f651d00dc671d8380bc37d041
```

The production and sandbox documents are structurally identical after removing the environment-specific `servers` value.

## Environment and authentication model

Official Conta help currently documents:

```text
production_base_uri=https://api.gateway.conta.no
sandbox_base_uri=https://api.gateway.conta-sandbox.no
authentication_header=apiKey
sandbox_app=https://app.conta-sandbox.no
sandbox_email_and_ehf_sending_unavailable=true
```

An API key inherits the access of the Conta user who created it. Effective organization and feature access is therefore user-, organization-, and subscription-dependent. The public OpenAPI document does not expose OAuth scopes or a granular API-key scope list.

## Invoice-draft contract

Create operation:

```text
method=POST
route=/invoice/organizations/{opContextOrgId}/invoice-drafts
operation_id=v1MakeInvoiceDraft
success_http=200
response_schema=RouteV1InvoiceDraftModel
top_level_required=registrationSource
required_line_fields=description,vatCode,discount,price,quantity
```

The success model contains `id` as an integer described as the invoice-draft ID. The response model does not declare `id` in a top-level `required` array, so runtime readback must still fail closed if the returned identifier is absent or invalid.

Readback operation:

```text
method=GET
route=/invoice/organizations/{opContextOrgId}/invoice-drafts/{id}
operation_id=v1ReadInvoiceDraft
success_http=200
response_schema=RouteV1InvoiceDraftModel
```

List/search remains available at `GET /invoice/organizations/{opContextOrgId}/invoice-drafts`, operation `v1SearchInvoiceDrafts`.

## Idempotency evidence

The OpenAPI document contains a generic `RestRequestIdempotencyException` model, but the invoice-draft create operation does not document an idempotency header, parameter, or provider guarantee. Provider-native idempotency therefore remains unverified. Local authorization-ID, nonce, payload-hash, approval, and idempotency-ledger controls remain mandatory.

## Unresolved account-level evidence

```text
EFFECTIVE_SANDBOX_USER_ACCESS=PENDING_OPERATOR_VALIDATION
SANDBOX_ORGANIZATION_IDENTITY=PENDING_OPERATOR_VALIDATION
SUBSCRIPTION_FEATURE_ACCESS=PENDING_OPERATOR_VALIDATION
PROVIDER_NATIVE_IDEMPOTENCY=PENDING_REVIEW
CREATE_RESPONSE_ID_RUNTIME_OBSERVATION=PENDING_OPERATOR_VALIDATION
READBACK_RUNTIME_OBSERVATION=PENDING_OPERATOR_VALIDATION
RECTIFICATION_PROCEDURE=PENDING_OPERATOR_VALIDATION
ONE_CALL_OPERATOR_AUTHORIZATION=NOT_GRANTED
```

The configured GitHub environment proves only that named secrets are present. It does not prove their environment, effective permissions, organization binding, subscription coverage, or runtime response behavior.

## Gate result

```text
CURRENT_OFFICIAL_SCHEMA_FRESHNESS=VERIFIED
CREATE_ROUTE_AND_METHOD=VERIFIED
CREATE_REQUEST_CONTRACT=VERIFIED
CREATE_RESPONSE_ID_LOCATION=VERIFIED_SCHEMA_OPTIONAL
READBACK_ROUTE=VERIFIED
SANDBOX_DOCUMENTATION=VERIFIED
ACCOUNT_LEVEL_SANDBOX_EVIDENCE=PARTIAL_CONTEXT
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
WRITE_TOOLS_ENABLED=false
```

No unresolved item is interpreted as execution approval. The next safe gate is a separately authorized, read-only sandbox identity/access validation. It must not create, update, delete, send, post, credit, or otherwise mutate any Conta object.
