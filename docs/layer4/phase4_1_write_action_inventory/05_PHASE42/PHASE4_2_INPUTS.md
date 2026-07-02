# Phase 4.2 Inputs — Dry-Run Contract Expansion — 14:16, 01.07.2026

## Candidate scope for Phase 4.2
Phase 4.2 should design dry-run contracts only. It must not implement live writes.

Recommended first dry-run candidates:

1. `R1-001 prepare_invoice_draft_intent`
2. `R1-002 create_invoice_draft` — only as a dry-run contract, not execution
3. `R1-005 prepare_customer_create_intent`
4. `R1-006 prepare_product_create_intent`

## Excluded from Phase 4.2 first pass
- R2 accounting mutations
- R3 external sending/communication
- R4 statutory/VAT/annual settlement submission
- R5 bank/payment actions

## Required Phase 4.2 outputs
- Dry-run request schema.
- Dry-run response schema.
- Validation error taxonomy.
- Risk scoring fields.
- Duplicate detection fields.
- Idempotency draft rules.
- Operator preview model.
- `writeExecuted=false` invariant.
