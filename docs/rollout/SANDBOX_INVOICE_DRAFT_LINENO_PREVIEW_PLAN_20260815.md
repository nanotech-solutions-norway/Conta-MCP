# Conta Sandbox Invoice Draft lineNo Preview Plan — 15.08.2026

## Purpose

Run a protected GET-only preview for the next bounded synthetic invoice-draft candidate after VAT capability run #3 confirmed that `output.high` is present on active 3000–3999 sales accounts.

## Candidate delta

Preserve the previously previewed and attempted synthetic payload, including `vatCode=high`, and add exactly one request-model field to the single invoice draft line:

```json
"lineNo": 1
```

This matches Conta's published invoice-draft example, which supplies sequential line numbers. No other business field is changed by this plan.

## Required sequence

1. Resolve and verify the exact synthetic sandbox customer using GET only.
2. Materialize the candidate locally with `vatCode=high` and `lineNo=1`.
3. Validate the candidate locally.
4. Calculate the canonical payload SHA-256 using the repository's `InvoiceDraftPreview::payloadHash` implementation.
5. Emit only the safe preview summary and hash.
6. Perform no provider mutation and grant no execution authorization.

Only after the protected preview completes successfully may the runtime execution binding be updated to the new exact payload hash. Any later POST remains bounded by the existing one-provider-mutation-per-attempt, reconciliation, replay-rejection, kill-switch, and production-false controls.
