# Write Action Inventory — Phase 4.1 — 14:16, 01.07.2026

## Scope
This inventory lists possible future Conta Bridge write-related actions and assigns a planning risk class. It is not an implementation backlog.

## Inventory summary

| Action ID | Domain | Action key | Source status | Risk | Default decision | Implementation status |
|---|---|---|---|---|---|---|
| `R0-001` | control | `read_write_status` | Layer 3 validated | R0 | allowed-readonly | existing-readonly |
| `R0-002` | reporting | `read_management_summary` | Layer 3 validated | R0 | allowed-readonly | existing-readonly |
| `R0-003` | reporting | `read_accounts_receivable_summary` | Layer 3 validated | R0 | allowed-readonly | existing-readonly |
| `R0-004` | reporting | `read_accounts_payable_summary` | Layer 3 validated | R0 | allowed-readonly | existing-readonly |
| `R0-005` | reporting | `read_route_map` | Layer 3 validated | R0 | allowed-readonly | existing-readonly |
| `R1-001` | invoice | `prepare_invoice_draft_intent` | Phase 4.0 architecture | R1 | future-dry-run-only | planning-only |
| `R1-002` | invoice | `create_invoice_draft` | Repo route map says disabled until Swagger verified | R1 | blocked-until-source-and-sandbox | not-implemented |
| `R1-003` | invoice | `update_invoice_draft` | Source required | R1 | blocked-until-source-and-sandbox | not-implemented |
| `R1-004` | invoice | `delete_invoice_draft` | Source required | R1 | blocked-until-source-and-sandbox | not-implemented |
| `R1-005` | customer | `prepare_customer_create_intent` | Phase 4.0 architecture | R1 | future-dry-run-only | planning-only |
| `R1-006` | product | `prepare_product_create_intent` | Phase 4.0 architecture | R1 | future-dry-run-only | planning-only |
| `R2-001` | customer | `create_customer` | Repo confirms customer route examples for list/get only | R2 | blocked | not-implemented |
| `R2-002` | customer | `update_customer` | Repo confirms customer route examples for list/get only | R2 | blocked | not-implemented |
| `R2-003` | customer | `delete_or_archive_customer` | Source required | R2 | blocked | not-implemented |
| `R2-004` | supplier | `create_supplier` | Source required | R2 | blocked | not-implemented |
| `R2-005` | supplier | `update_supplier` | Source required | R2 | blocked | not-implemented |
| `R2-006` | product | `create_product_or_service` | Source required | R2 | blocked | not-implemented |
| `R2-007` | product | `update_product_or_service` | Source required | R2 | blocked | not-implemented |
| `R2-008` | invoice | `register_invoice_payment` | Source required | R2 | blocked | not-implemented |
| `R2-009` | invoice | `create_credit_note_draft` | Source required | R2 | blocked | not-implemented |
| `R2-010` | purchase | `register_purchase_draft` | Source required | R2 | blocked | not-implemented |
| `R2-011` | voucher | `create_voucher_draft` | Source required | R2 | blocked | not-implemented |
| `R2-012` | ledger | `post_general_journal_entry` | Source required | R2 | blocked | not-implemented |
| `R2-013` | attachment | `upload_document_attachment` | Source required | R2 | blocked | not-implemented |
| `R2-014` | bank | `import_bank_statement` | Source required | R2 | blocked | not-implemented |
| `R2-015` | bank | `match_bank_transaction` | Source required | R2 | blocked | not-implemented |
| `R3-001` | invoice | `send_invoice` | Source required | R3 | blocked | not-implemented |
| `R3-002` | invoice | `send_credit_note` | Source required | R3 | blocked | not-implemented |
| `R3-003` | invoice | `send_payment_reminder` | Source required | R3 | blocked | not-implemented |
| `R3-004` | email | `send_accounting_document_email` | Source required | R3 | blocked | not-implemented |
| `R4-001` | vat | `prepare_vat_report_draft` | Source required | R4 | blocked | not-implemented |
| `R4-002` | vat | `submit_vat_report` | Source required | R4 | blocked-hard | not-implemented |
| `R4-003` | annual_settlement | `prepare_annual_settlement` | Source required | R4 | blocked | not-implemented |
| `R4-004` | annual_settlement | `submit_annual_settlement` | Source required | R4 | blocked-hard | not-implemented |
| `R4-005` | period | `close_or_lock_accounting_period` | Source required | R4 | blocked-hard | not-implemented |
| `R5-001` | payment | `initiate_bank_payment` | Source required | R5 | blocked-hard | not-implemented |
| `R5-002` | payment | `approve_or_send_bank_payment` | Source required | R5 | blocked-hard | not-implemented |
| `R5-003` | payment | `modify_bank_account_details` | Source required | R5 | blocked-hard | not-implemented |

## Rule
Every non-R0 action remains blocked until the relevant source documentation, sandbox validation, dry-run contract, idempotency model, audit model, and operator approval path exist.
