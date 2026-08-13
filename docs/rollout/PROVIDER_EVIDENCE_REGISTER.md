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
| Request schema closure hash | VERIFIED_OFFICIAL_DOCS_20260811 | `e9bd13fc868d2e549576c39df6923f0eac5295d482a7f55f4ee75b8f9df545a4` |
| Success-response schema closure hash | VERIFIED_OFFICIAL_DOCS_20260811 | `fb8282b847343172e6b994453449755cceb5657bb0e69c66a8bd1ac0aed3032a` |
| Current official schema freshness | VERIFIED_OFFICIAL_DOCS_20260810 | OpenAPI 3.0.3, Conta API 1.0.0; official documents last modified 08.08.2026 |
| Provider access model | VERIFIED_SANDBOX_RUNTIME_20260810 | Protected GET-only run `31418133692` authenticated the sandbox API key and confirmed organization-scoped subscription-endpoint access; no response body or identity value recorded |
| Provider-native idempotency | PENDING_REVIEW | Generic idempotency exception exists, but no create-operation header, parameter, or guarantee is documented |
| Sandbox/test-company identity | VERIFIED_SANDBOX_RUNTIME_20260810 | Configured sandbox organization appeared in the authenticated identity's accessible organization list; real ID remains server-side only |
| Sandbox test-customer inventory | OPERATOR_REPORTED_EMPTY_20260813 | Operator reports no test customers in configured Conta sandbox; later customer GET `404` is reconciled as missing fixture prerequisite |
| Sandbox test-invoice inventory | OPERATOR_REPORTED_EMPTY_20260813 | Operator reports no test invoices in configured Conta sandbox |
| Synthetic sandbox test-customer fixture | PENDING_OPERATOR_APPROVAL | One synthetic sandbox-only customer must be separately approved and created before GET-only customer binding validation can close |
| Sandbox test-customer create authorization | NOT_GRANTED | Fixture creation is a separate sandbox mutation and requires explicit operator authorization |
| Create response identifier | VERIFIED_SCHEMA_OPTIONAL | HTTP 200 model contains integer `id`; it is not declared top-level required |
| Readback route | VERIFIED_OFFICIAL_DOCS_20260810 | `GET /invoice/organizations/{opContextOrgId}/invoice-drafts/{id}`; operation `v1ReadInvoiceDraft` |
| Rectification procedure | DRAFTED_PENDING_OPERATOR_APPROVAL | Conservative draft preserves any unsent sandbox object, blocks automatic retry/delete and requires separate cleanup authorization |
| One-call invoice-draft operator authorization | NOT_GRANTED | Separate explicit payload-bound authorization required after fixture validation |

No unresolved provider item is interpreted as approval.

Detailed refresh evidence: `docs/rollout/CURRENT_PROVIDER_SCHEMA_SANDBOX_EVIDENCE_REFRESH_20260810.md`.

Authenticated sandbox evidence: `docs/rollout/READ_ONLY_SANDBOX_IDENTITY_ACCESS_VALIDATION_20260810.md`.

Post-validation reconciliation: `docs/rollout/POST_READ_ONLY_SANDBOX_RECONCILIATION_20260811.md`.

Canonical schema hash evidence: `docs/rollout/CANONICAL_INVOICE_DRAFT_SCHEMA_HASHES_20260811.md`.

Synthetic fixture proposal: `docs/rollout/SANDBOX_TEST_CUSTOMER_FIXTURE_PROPOSAL_20260813.md`.
