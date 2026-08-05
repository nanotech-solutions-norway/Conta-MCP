# Controlled Write Foundation — 16.07.2026

## Implemented scope

This change implements Gates 0–3 of the re-evaluated rollout process without making a provider call.

### Controls introduced

- Policy-derived tool discovery.
- Non-executing invoice-draft preview.
- Deterministic canonical payload hashing.
- Explicit action and organization allowlists.
- Independent write, runtime-block, execution and production-approval gates.
- One-use approval envelope validation.
- Maximum approval lifetime.
- Exact method and route enforcement.
- Dispatch-level mutation permit.
- Idempotency and approval-nonce ledger.
- Metadata-only audit events.
- Production-specific secondary approval gate.
- Offline tests and PHP lint workflow.

## Effective default state

```text
enable_write_preview=true
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_organization_ids=[]
allowed_write_actions=[]
```

The execute tool is absent from `tools/list` unless every effective execution gate is open. Direct invocation still fails closed.

## Execution boundary

A provider mutation can be dispatched only with a `WriteDispatchPermit` created after validation of:

- action;
- HTTP method;
- verified route;
- organization allowlist;
- environment;
- canonical payload hash;
- one-use approval;
- approval expiry;
- nonce;
- idempotency key;
- current policy version.

The provider client revalidates the permit immediately before network I/O.

## Not implemented or authorized by this change

- Provider route validation against a fresh official source.
- Runtime deployment.
- Sandbox/test-company execution.
- Production execution.
- Invoice sending.
- Customer edits.
- Voucher or ledger posting.
- Payments, bank actions or VAT submissions.
- Delete operations.
- Active Custom GPT/OpenAPI write-schema publication.

## Next gate

Create and validate the fresh `invoice_draft_create_v2` candidate authorization packet. A later, explicit operator authorization is required for one controlled sandbox call.
