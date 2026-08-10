# Sandbox invoice-draft test-payload contract — 2026-08-11

## Status

```text
payload_status=PLACEHOLDER_ONLY_PENDING_OPERATOR_VALIDATION
payload_materialized=false
payload_hash_created=false
customer_selected=false
provider_call_authorized=false
sandbox_mutation_authorized=false
```

No executable JSON payload is created by this document.

## Documented contract

The official request schema requires `registrationSource`. Each supplied `invoiceDraftLines` item requires `description`, `vatCode`, `discount`, `price` and `quantity`. A meaningful sandbox test also requires an operator-approved sandbox customer and deliberately minimal, non-sensitive test values.

Proposed field policy:

| Field | Policy |
| --- | --- |
| `registrationSource` | Select a schema-permitted value only after provider/operator confirmation; do not guess |
| `customerId` | Resolve server-side from an operator-approved sandbox-only test customer; never commit it |
| `invoiceDraftLines` | Exactly one minimal synthetic line |
| `description` | Synthetic marker containing no customer, invoice or production data |
| `vatCode` | Select from the current schema only after business-rule review |
| `discount` | `0` unless the approved test explicitly requires otherwise |
| `price` | Small synthetic test amount approved by the operator |
| `quantity` | `1` |
| Optional references and attachments | Omit unless separately justified and approved |

## Materialization gate

Before a payload may be materialized, all of the following are required:

1. Sandbox customer selection and read-only existence validation.
2. Operator confirmation of `registrationSource`, VAT treatment and synthetic amount.
3. Canonical JSON generation in the protected runtime, not in Git or chat.
4. Payload SHA-256 recording without recording business data.
5. Preview comparison and explicit payload-bound approval.
6. Separate one-call sandbox mutation authorization.

Until those gates close, `provider_call_allowed=false` and `sandbox_execution_allowed=false` remain controlling.

