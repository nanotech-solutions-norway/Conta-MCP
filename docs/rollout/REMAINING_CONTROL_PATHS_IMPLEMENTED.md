# Remaining Control Paths Implemented — 16.07.2026

## Classification

```text
IMPLEMENTED_ON_DRAFT_BRANCH
NO_PROVIDER_CALL
NO_SANDBOX_MUTATION
NO_PRODUCTION_DEPLOYMENT
CONTROLLED_WRITE_PENDING_OPERATOR_VALIDATION
```

## Implemented controls

1. Approved release-manifest guard with runtime file, release commit, provider schema and route-map verification.
2. Dynamic global and action-specific kill-switch file. Missing or invalid switch files block execution.
3. HMAC-SHA256 signatures for approval envelopes and sandbox authorization packets.
4. Payload-bound sandbox authorization requiring one provider mutation, validated test company, validated route, readback and an active authorization window.
5. Authorization-ID consumption in the write ledger, in addition to approval-nonce and idempotency-key replay protection.
6. Mandatory provider readback and controlled-field comparison after invoice-draft creation.
7. A CLI readiness report that performs no provider call.
8. A release-manifest generator that emits `PENDING_OPERATOR_VALIDATION` evidence.
9. A control-document signer for offline/operator use.
10. A one-call sandbox harness that refuses execution without `--execute`, an exact environment acknowledgement, sandbox configuration and all runtime gates.
11. CI tests for baseline drift, kill switch, signatures, authorization binding, one-call consumption, readback mismatch and production refusal.

## Effective default state

```text
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
approved_release_manifest=missing
write_kill_switch=blocked_or_missing
sandbox_authorization=missing
readback_route=PENDING_OPERATOR_VALIDATION
```

The execute tool remains absent from `tools/list` in the default state.

## Evidence status

The verified static Swagger artifact has SHA-256:

```text
B5C6493964F9895F2626730487B6230899C9979CC61B92D56ADAD44D69AF4A43
```

It confirms:

```text
POST /invoice/organizations/{opContextOrgId}/invoice-drafts
operationId=v1MakeInvoiceDraft
```

The static artifact remains offline evidence. Before execution, the operator must validate the current provider schema, the sandbox/test-company identity, required provider scopes, provider idempotency behaviour and a supported readback route.

## Production boundary

The current code explicitly rejects production execution with:

```text
production_write_program_not_implemented
```

Production rollout requires a separate reviewed program and cannot be enabled by changing configuration flags alone.
