# Canonical invoice-draft schema hashes — 2026-08-11

## Source and method

The official sandbox OpenAPI document was downloaded with Windows TLS validation from:

<https://docs.gateway.conta-sandbox.no/docs/conta-external-api.json>

Its raw SHA-256 matched the previously recorded official document hash:

```text
document_sha256=8c8be48fb6cabf22f097f4879be495dbc789a68ceebbad763b526bff85b598a6
operation_id=v1MakeInvoiceDraft
route=/invoice/organizations/{opContextOrgId}/invoice-drafts
request_body_required=true
success_status=200
```

`docs/rollout/tools/conta_schema_hashes.py` canonicalizes JSON with UTF-8, recursively sorted object keys and compact separators. A direct hash covers the operation's schema object. A closure hash covers that root plus every transitively referenced local schema, keyed by JSON reference. The closure hash is the controlling contract hash.

## Results

```text
request_direct_sha256=138791aee00616cf567a1d5525c2e9888d93f9899582dc05decb8be3d42ad4fb
request_direct_bytes=5872
request_closure_sha256=e9bd13fc868d2e549576c39df6923f0eac5295d482a7f55f4ee75b8f9df545a4
request_closure_bytes=6238
request_closure_refs=#/components/schemas/enumRouteV1VatCodeTypeConst

success_response_direct_sha256=1c0dec08a2a433a607168ec0b991ce4cc5e245370d9df342738ccd6210602a09
success_response_direct_bytes=56
success_response_closure_sha256=fb8282b847343172e6b994453449755cceb5657bb0e69c66a8bd1ac0aed3032a
success_response_closure_bytes=7537
success_response_closure_refs=#/components/schemas/RouteV1InvoiceDraftLineModel,#/components/schemas/RouteV1InvoiceDraftModel,#/components/schemas/enumInvoiceDraftRegistrationSourceEnum,#/components/schemas/enumInvoiceTypeEnum,#/components/schemas/enumLanguageEnum,#/components/schemas/enumRouteV1VatCodeTypeConst
```

## Evidence boundary

These hashes verify the documented schema contract only. They do not prove create entitlement, business-rule validity, provider-native idempotency, runtime response shape or runtime readback behavior. No authenticated Conta call or provider mutation was performed.

