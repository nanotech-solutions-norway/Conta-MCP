# Sandbox invoice-draft test-payload contract — 2026-08-11

## Status

```text
payload_status=PLACEHOLDER_ONLY_PENDING_OPERATOR_VALIDATION
payload_materialized=false
payload_hash_created=false
customer_selected=false
sandbox_test_customer_fixture_present=false
provider_call_authorized=false
sandbox_mutation_authorized=false
```

No executable JSON payload is created by this document.

## 13.08.2026 prerequisite reconciliation

The operator reported that the configured Conta sandbox environment currently contains no test customers and no test invoices. Therefore the prior assumption that an existing sandbox customer could simply be selected is superseded.

Before this invoice-draft payload can be materialized, exactly one synthetic sandbox-only test customer must first be designed, separately approved, created under an explicit sandbox-fixture authorization, and then validated through the existing GET-only test-customer workflow. Customer creation is a separate mutation and must not be implicitly authorized by later invoice-draft approval.

Until that prerequisite is closed:

```text
SANDBOX_TEST_CUSTOMER_FIXTURE_DESIGNED=false
SANDBOX_TEST_CUSTOMER_FIXTURE_APPROVED=false
SANDBOX_TEST_CUSTOMER_CREATE_AUTHORIZED=false
CUSTOMER_SELECTED=false
```

## Documented contract

The official request schema requires `registrationSource`. Each supplied `invoiceDraftLines` item requires `description`, `vatCode`, `discount`, `price` and `quantity`. A meaningful sandbox test also requires an operator-approved sandbox customer and deliberately minimal, non-sensitive test values.

Proposed field policy:

| Field | Policy |
| --- | --- |
| `registrationSource` | Select a schema-permitted value only after provider/operator confirmation; do not guess |
| `customerId` | Resolve server-side from the separately approved and validated synthetic sandbox-only test customer; never commit it |
| `invoiceDraftLines` | Exactly one minimal synthetic line |
| `description` | Synthetic marker containing no customer, invoice or production data |
| `vatCode` | Select from the current schema only after business-rule review |
| `discount` | `0` unless the approved test explicitly requires otherwise |
| `price` | Small synthetic test amount approved by the operator |
| `quantity` | `1` |
| Optional references and attachments | Omit unless separately justified and approved |

## Materialization gate

Before a payload may be materialized, all of the following are required:

1. Synthetic sandbox test-customer fixture designed and approved.
2. Separate explicit authorization to create exactly one sandbox-only customer.
3. Customer created without production/private customer data.
4. Protected customer identifier configured without exposing it in Git, Drive or chat.
5. GET-only sandbox customer existence validation succeeds.
6. Operator confirmation of `registrationSource`, VAT treatment and synthetic amount.
7. Canonical JSON generation in the protected runtime, not in Git or chat.
8. Payload SHA-256 recording without recording business data.
9. Preview comparison and explicit payload-bound approval.
10. Separate one-call sandbox invoice-draft mutation authorization.

Until those gates close, `provider_call_allowed=false` and `sandbox_execution_allowed=false` remain controlling.

The protected `Conta Sandbox Test Customer Validation` workflow provides the read-only existence gate after a fixture exists. Its required `CONTA_SANDBOX_TEST_CUSTOMER_ID` value must be entered only into the protected environment secret and must never be committed or printed.
