# Source Gap Register — Phase 4.1 — 14:16, 01.07.2026

The public web search performed during Phase 4.1 package generation did not return usable current Conta write-operation documentation. Therefore, endpoint/method/payload entries remain source-required unless already evidenced by the repository route map.

| Source/document | Status | Reason |
|---|---|---|
| Conta API Swagger / OpenAPI for sandbox and production | Required | Must verify exact route, method, payload, error codes, permissions, idempotency support, and rate limits before any implementation phase. |
| Invoice draft creation route and payload | Required | The repo route map identifies this as server-side configurable only and disabled until Swagger route verification. |
| Customer create/update/delete route schemas | Required | Existing repo evidence covers route examples for list/get only; write methods/payloads remain unverified. |
| Invoice send/credit/reminder APIs | Required | External communication actions require verified route/payload plus operator preview model. |
| Voucher, purchase, journal-entry APIs | Required | Accounting mutation routes require ledger/VAT/period-lock rules and correction paths. |
| VAT report and annual settlement APIs | Required | Statutory actions remain blocked pending legal/accounting review and official documentation. |
| Bank/payment APIs | Required | Payment actions remain hard blocked; document only if future scope is explicitly expanded. |
| Conta sandbox/test company | Required | No future implementation without non-live tenant validation. |
| Chart of Accounts/VAT code/customer/vendor/product mappings | Required | Business-rule evidence for dry-run validation. |
