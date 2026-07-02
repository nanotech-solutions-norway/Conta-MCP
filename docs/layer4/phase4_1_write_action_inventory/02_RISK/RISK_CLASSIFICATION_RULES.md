# Risk Classification Rules — Phase 4.1 — 14:16, 01.07.2026

## R0 — Read-only
Existing or future read-only actions that do not mutate Conta data or external systems.

Policy: allowed only under the established Layer 3 controls.

## R1 — Draft-only / no external effect
Actions that either prepare internal write intents or create/modify draft objects without sending, posting, filing, or paying.

Policy: planning-only. Future consideration requires dry-run, sandbox proof, idempotency, audit, and explicit operator approval.

## R2 — Internal accounting or master-data mutation
Actions that change customers, suppliers, product/service items, vouchers, ledger state, receivables, payables, documents, or reconciliation status.

Policy: blocked. Requires source documentation, sandbox validation, rollback/correction path, audit, and explicit future implementation approval.

## R3 — External communication or external commercial effect
Actions that send invoices, credit notes, reminders, or accounting documents externally.

Policy: blocked. Requires two-step operator confirmation and verified recipient/content preview before any future implementation can be considered.

## R4 — Statutory, VAT, period close, or annual settlement actions
Actions that prepare, close, lock, or submit official tax/accounting/statutory material.

Policy: hard blocked unless separate legal/accounting/statutory approval is granted in a future phase.

## R5 — Payment or bank execution
Actions that initiate, approve, transmit, or modify payment/bank execution data.

Policy: hard blocked. Not part of the current Conta Bridge write roadmap.

## Default rule
If source documentation is incomplete or ambiguous, the action is classified at the higher-risk level and remains blocked.
