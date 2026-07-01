# Start — Layer 4 / Phase 4.0

## Confirmed baseline

- Release: Conta Bridge v2 Layer 3 Read-Only RC1
- Tag: `conta-bridge-v2-layer3-readonly-rc1-20260701`
- Production base: `https://mcp.atlas-ai.no`
- Operator-controlled read-only use accepted
- Write activation not approved
- Formal report comparison gated
- Derived reports are hardened but not official Conta statutory reports

## Mandatory Phase 4.0 guardrail

Do not implement writes.
Do not modify production runtime.
Do not expose POST, PUT, PATCH, or DELETE in the active Custom GPT schema.
Do not activate live accounting writes.

## First control checks

Before any later phase work, confirm:

```text
writePaused=true
runtimeWriteBlocked=true
openApiGetOnly=true
blockedMethods includes POST, PUT, PATCH, DELETE
writeActionsExposed=false
statutorySubmissionExposed=false
```

## Working model

```text
intent -> dry-run -> risk classification -> human approval -> idempotency -> audit -> sandbox proof -> future explicit implementation
```

Phase 4.0 stops before implementation.
