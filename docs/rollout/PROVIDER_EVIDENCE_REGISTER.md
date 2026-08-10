# Provider Evidence Register — Invoice Draft Create v2

| Evidence item | Status | Value / requirement |
|---|---|---|
| Historical static Swagger file hash | VERIFIED_OFFLINE_STALE | `B5C6493964F9895F2626730487B6230899C9979CC61B92D56ADAD44D69AF4A43` |
| Current production OpenAPI hash | VERIFIED_OFFICIAL_DOCS_20260810 | `764f10e8bbcce27e0b940149bfe81f257a0849bf2828f5ff2140c48eab07bbbd` |
| Current sandbox OpenAPI hash | VERIFIED_OFFICIAL_DOCS_20260810 | `8c8be48fb6cabf22f097f4879be495dbc789a68ceebbad763b526bff85b598a6` |
| Production/sandbox schema parity | VERIFIED_OFFICIAL_DOCS_20260810 | Structurally identical except for the `servers` URL |
| Create method | VERIFIED_OFFLINE | `POST` |
| Create route | VERIFIED_OFFLINE | `/invoice/organizations/{opContextOrgId}/invoice-drafts` |
| Operation ID | VERIFIED_OFFLINE | `v1MakeInvoiceDraft` |
| Top-level required field | VERIFIED_OFFLINE | `registrationSource` |
| Required line fields | VERIFIED_OFFLINE | `description`, `vatCode`, `discount`, `price`, `quantity` |
| Current official schema freshness | VERIFIED_OFFICIAL_DOCS_20260810 | OpenAPI 3.0.3, Conta API 1.0.0; official documents last modified 08.08.2026 |
| Provider access model | PARTIAL_CONTEXT | Official help documents `apiKey` access inherited from the creating user; effective account/plan access remains unobserved |
| Provider-native idempotency | PENDING_REVIEW | Generic idempotency exception exists, but no create-operation header, parameter, or guarantee is documented |
| Sandbox/test-company identity | PENDING_OPERATOR_VALIDATION | Server-side only; do not commit real IDs |
| Create response identifier | VERIFIED_SCHEMA_OPTIONAL | HTTP 200 model contains integer `id`; it is not declared top-level required |
| Readback route | VERIFIED_OFFICIAL_DOCS_20260810 | `GET /invoice/organizations/{opContextOrgId}/invoice-drafts/{id}`; operation `v1ReadInvoiceDraft` |
| Rectification procedure | PENDING_OPERATOR_VALIDATION | Must avoid send/post/delete and preserve evidence |
| One-call operator authorization | NOT_GRANTED | Separate explicit authorization required |

No unresolved provider item is interpreted as approval.

Detailed refresh evidence: `docs/rollout/CURRENT_PROVIDER_SCHEMA_SANDBOX_EVIDENCE_REFRESH_20260810.md`.
