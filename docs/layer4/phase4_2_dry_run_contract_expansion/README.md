# Conta Bridge v2 — Layer 4 / Phase 4.2 Dry-Run Contract Expansion

## Classification
Planning/governance documentation only.

## Accepted status
Validated by operator and used as input to Phase 4.3.

## Purpose
Phase 4.2 defines dry-run request/response contracts, validation taxonomy, operator preview requirements, idempotency binding, audit binding, rejection rules, and acceptance checks.

## Runtime boundary
- No production runtime change.
- No active Custom GPT OpenAPI schema change.
- No POST/PUT/PATCH/DELETE exposure.
- No live Conta accounting write activation.
- No statutory submission capability.
- No payment/bank execution capability.

## Core invariant

```text
writeExecuted=false
executableInCurrentPhase=false
```

## Next phase linkage
Phase 4.2 feeds Layer 4 / Phase 4.3 — Audit and Idempotency Design.
