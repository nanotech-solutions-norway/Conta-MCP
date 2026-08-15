# Conta MCP First Sandbox Invoice-Draft Write — Verified Success — 2026-08-16

## Decision

The first controlled Conta sandbox invoice-draft write is **successfully created and independently verified**.

This closes the Phase 6 first-mutation validation gate for the sandbox invoice-draft-create action. It does **not** authorize production writes, invoice sending, posting/finalization, update, delete, credit, or any other mutation.

## Provider-side prerequisite discovered

The sandbox organization had invoice VAT invoicing disabled in the Conta UI. The operator enabled the provider-side `fakturere med MVA` / invoice-with-VAT setting on 2026-08-16.

After that setting was enabled, Conta accepted the protected invoice-draft create request using the official invoice-route VAT token `high`.

## Exact protected payload

```json
{
  "registrationSource": "CONTA",
  "invoiceDraftLines": [
    {
      "description": "Atlas MCP Sandbox Invoice Draft Validation",
      "price": 1.0,
      "quantity": 1,
      "discount": 0,
      "vatCode": "high",
      "lineNo": 1
    }
  ],
  "type": "NORMAL",
  "customerId": "<protected synthetic customer>",
  "invoiceLanguage": "NO",
  "invoiceCurrency": "NOK"
}
```

Canonical payload SHA-256:

```text
79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7
```

## Successful provider mutation evidence

Protected workflow run:

```text
Conta Sandbox Invoice Draft Retry Until Complete
run_id=31913305907
run_attempt=1
```

The first attempt performed exactly one provider POST and created a sandbox invoice-draft object. Safety controls observed:

```text
LEDGER_RESERVED=true
SAME_KEY_REPLAY_REJECTED=true
KILL_SWITCH_CLOSED=true
PRODUCTION_WRITE_AUTHORIZED=false
AUTOMATIC_RETRY_PERFORMED=false
DRAFT_ID_SHA256=eab8ff114cc63fd8ab3d9f42249e20b8ce5ecce463e8368e98747f03c50eeabb
```

The original runtime returned a local `502` only because the strict readback verifier rejected two representation differences after the object had already been created. That local result was not a provider create failure and no second draft was authorized.

A later GitHub rerun (`run_attempt=2`) was correctly rejected before dispatch by `workflow_rerun_not_authorized`.

## GET-only reconciliation findings

Protected GET-only reconciliation run `31914316870` located exactly one draft and isolated the two verifier mismatches:

1. `invoiceDraftLines.0.price`: expected PHP `float 1.0`, provider readback decoded as PHP `integer 1`; numeric value equal.
2. `registrationSource`: present in the create payload but omitted from readback.

All substantive business fields matched, including:

- synthetic customer ID;
- invoice type `NORMAL`;
- language `NO`;
- currency `NOK`;
- line description;
- quantity `1`;
- discount `0`;
- VAT code `high`;
- line number `1`.

The verifier was narrowed to accept only:

- integer/float equality when both values are actual PHP numeric scalars; numeric strings remain rejected;
- omission of `registrationSource` from readback; if the provider returns it, its value is still verified.

Regression tests preserve fail-closed behavior for substantive mismatches, including VAT mismatches.

## Definitive independent readback verification

After the compatibility fix was merged, protected workflow run:

```text
Conta Sandbox Created Draft Readback Reconciliation
run_id=31914643955
run_number=3
run_attempt=1
```

completed successfully with:

```text
GET_ONLY_BOUNDARY_VERIFIED=true
INVOICE_DRAFT_SEARCH_STATUS=200
INVOICE_DRAFT_SEARCH_HIT_COUNT=1
EXPECTED_PAYLOAD_SHA256=79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7
EXPECTED_PAYLOAD_HASH_MATCH=true
EXPECTED_DRAFT_FOUND=true
DRAFT_ID_SHA256=eab8ff114cc63fd8ab3d9f42249e20b8ce5ecce463e8368e98747f03c50eeabb
READBACK_VERIFIED=true
MISMATCH_COUNT=0
PROVIDER_HTTP_METHODS_USED=GET_ONLY
PROVIDER_MUTATION_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

This is the definitive verification record for the first sandbox invoice-draft mutation.

## Permanent source stabilization

Post-success stabilization bakes the validated behavior into source rather than applying runtime patches:

- preserve numeric organization IDs as strings in write allowlists;
- bind the validated invoice-draft payload directly (`high`, `lineNo=1`);
- bind canonical payload SHA-256 `79ae9a...cee7` directly;
- support the observed Conta invoice-draft list shape where `hitCount` may be present without `hits`;
- retain redacted provider diagnostics;
- retain one-provider-mutation-per-attempt limits;
- retain mandatory GET readback verification;
- retain same-key replay rejection;
- retain kill-switch closure;
- retain GitHub `run_attempt=1` rerun refusal;
- remove temporary runtime payload/config patchers;
- make future controlled sandbox mutation execution manual-only behind the protected environment.

## Current authorization boundary

```text
SANDBOX_INVOICE_DRAFT_CREATE=VERIFIED
READBACK_VERIFICATION=VERIFIED
REPLAY_PROTECTION=VERIFIED
KILL_SWITCH_CLOSURE=VERIFIED
PRODUCTION_WRITE_AUTHORIZED=false
PRODUCTION_WRITE_PROGRAM=NOT_IMPLEMENTED
```

No additional sandbox invoice draft is authorized by this record. The existing verified draft is retained as evidence unless a separately authorized cleanup action is later defined.
