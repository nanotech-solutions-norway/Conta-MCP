# Conta MCP First Production Invoice-Draft Execution Result — 2026-08-25

## Result

The exact one-use authorization for request commit
`a6b9470204775598c4f569ea2837c0bc712b0dc3` was consumed. The bounded
production execution created exactly one unsent NOK 1.00 validation invoice
draft. Mandatory GET-only reconciliation verified the controlled fields. No
retry, send, posting, finalization, correction, deletion, customer mutation or
other provider mutation occurred.

```text
REQUEST_COMMIT=a6b9470204775598c4f569ea2837c0bc712b0dc3
DEPLOYED_IMPLEMENTATION_COMMIT=19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac
EXECUTION_CONTROLLER_COMMIT=780632a939ca4c4e0cd999037580b9b91cfe4f8f
EXECUTION_RUN_ID=32784433504
CONTAINMENT_RUN_ID=32784606318
GET_ONLY_RECONCILIATION_RUN_ID=32785410695
PROVIDER_MUTATION_COUNT=1
AUTOMATIC_RETRY_PERFORMED=false
PRESTATE_INVOICE_DRAFT_COUNT=1
FINAL_INVOICE_DRAFT_COUNT=2
MATCHING_VALIDATION_DRAFT_COUNT=1
READBACK_PERFORMED=true
READBACK_VERIFIED=true
INVOICE_DRAFT_ID_SHA256=6e9cdea72100d81658584608d9eb64711ce855683a855a4de14b1c4a11d6bce9
CONTROLLED_PROJECTION_SHA256=192ee589dbd44f13439352c964f42e955fbd6fb10060aa0c99e04245d65b0606
KILL_SWITCH_GLOBAL_BLOCKED=true
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
EXECUTION_ALLOWED=false
PRODUCTION_WRITE_APPROVED=false
ALLOWED_WRITE_ACTION_COUNT=0
ALLOWED_WRITE_ORGANIZATION_COUNT=0
RAW_DRAFT_ID_PRINTED=false
RAW_ORGANIZATION_ID_PRINTED=false
RAW_CUSTOMER_ID_PRINTED=false
FULL_PAYLOAD_PRINTED=false
ONE_USE_AUTHORIZATION_CONSUMED=true
FURTHER_PRODUCTION_MUTATION_AUTHORIZED=false
```

## Evidence

- Authorization and final issue record:
  `https://github.com/nanotech-solutions-norway/Conta-MCP/issues/92`
- Authorized execution:
  `https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/actions/runs/32784433504`
- Emergency fail-closed containment:
  `https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/actions/runs/32784606318`
- Successful mandatory GET-only reconciliation:
  `https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/actions/runs/32785410695`
- Matching-or-ambiguous duplicate control:
  `https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/pull/59`
- Closure hardening and GET-only reconciliation controller:
  `https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/pull/60`
- Canonical optional `registrationSource` alignment:
  `https://github.com/nanotech-solutions-norway/Domeneshop---MCP-/pull/61`

## Execution sequence

1. The GET-only pre-read found one existing production invoice draft.
2. The existing draft was GET-read by stable identifier and proved not to
   contain the unique validation marker. It was not modified or deleted.
3. The protected customer reference, deterministic preview, payload hash,
   fiscal date, deployed runtime, signed one-use approval, decision packet,
   organization hash, release manifest and kill-switch boundary passed.
4. The controller opened the bounded execution window and invoked exactly one
   `invoice_draft_create_v2` operation with no automatic retry.
5. GET-only reconciliation observed the draft count increase from one to two.
6. A later protected GET-only run found exactly one draft containing the unique
   validation marker and verified every required controlled readback field.

## Closure incident and containment

The execution controller wrote the globally blocked kill-switch state in its
`finally` path and restored the closed server configuration. Its immediate
public-health check nevertheless observed the temporary open PHP configuration
still effective and stopped with
`post_attempt_fail_closed_health_verification_failed` before emitting final
result markers.

This was treated as an active containment incident. The existing fail-closed
deployment controller was invoked without any Conta provider call. It backed up
the current server state, forced the write flags and allowlists closed,
validated the globally blocked kill switch, verified remote hashes and passed
the public contract. Live health then confirmed the closed state.

The execution controller was hardened in Domeneshop PR #60 to keep the kill
switch authoritative, ensure a distinct restored PHP configuration timestamp
and poll the public closed state before declaring closure. This hardening is
implemented, tested and merged; it does not authorize another execution.

## State classification

```text
CONTROL_DESIGN=APPROVED
PRODUCTION_WRITE_IMPLEMENTATION=IMPLEMENTED
FAIL_CLOSED_RUNTIME=LIVE
FIRST_BOUNDED_PRODUCTION_INVOICE_DRAFT=VALIDATED
FIRST_BOUNDED_EXECUTION_CYCLE=COMPLETE
GENERAL_PRODUCTION_WRITE_RELEASE=NOT_APPROVED
WRITE_TOOLS_CURRENTLY_LIVE=false
PRODUCTION_WRITE_PATH_CURRENTLY_OPEN=false
```

The successful draft proves the bounded controlled-create path for this exact
authorized work unit. It does not establish a standing production-write
approval or authorize broader invoice, customer, ledger, payment, payroll,
bank, voucher, correction, deletion, send, post or finalize capabilities.

## Consumed-gate closeout

After the one-use execution cycle was closed, the protected non-confidential
execution-date prerequisite was changed from the completed work unit's date to
the non-date marker `BLOCKED_AUTHORIZATION_CONSUMED`. The execution controller
requires an exact `YYYY-MM-DD` value matching the current Oslo date, so this
marker fails closed before server or provider contact.

The protected production-decision environment has no operator-review
attestation variable. Its accounting-review and credential-custody attestation
variables remain false. A future decision packet therefore cannot be produced
from the completed cycle's review state.

```text
CONTA_PROD_INVOICE_DATE=BLOCKED_AUTHORIZATION_CONSUMED
CONTA_PROD_OPERATOR_REVIEW_ATTESTED=NOT_PRESENT
CONTA_PROD_ACCOUNTING_REVIEW_ATTESTED=false
CONTA_PROD_CREDENTIAL_CUSTODY_ATTESTED=false
CONSUMED_EXECUTION_WORKFLOW_REUSE_BLOCKED=true
PROVIDER_CALL_PERFORMED=false
PRODUCTION_MUTATION_PERFORMED=false
```

Protected-variable closeout evidence is recorded in issue #92:
`https://github.com/nanotech-solutions-norway/Conta-MCP/issues/92#issuecomment-5403181117`.

## Progress record

```text
TARGET=controlled invoice-draft create capability
EVIDENCE_WEIGHTED_PROGRESS=100%
CURRENT_PROCESS=first bounded production execution cycle closed
NEXT_GATE=new operator-defined rollout scope and fresh decision/authorization cycle
SAFETY_STATE=production runtime fail-closed; one-use authorization consumed; no further mutation authorized
```

The 100 percent value applies only to the controlled invoice-draft create
capability target and this completed first-execution cycle. It does not apply to
the low-risk production-write rollout or full project closure, and it grants no
future execution authority.

## Next gate

No automatic next production mutation exists. Any further production write
requires a newly defined work unit, current governance evidence, a fresh exact
authorization, protected-environment approval and the complete one-use runtime
gate. Until then, production remains fail-closed.
