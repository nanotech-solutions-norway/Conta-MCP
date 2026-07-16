# Conta MCP Canonical Baseline Lock — 16.07.2026

## Status

```text
IMPLEMENTED_AS_CONTROLLED_CHANGE
PENDING_OPERATOR_VALIDATION
NO_PROVIDER_CALL
NO_SANDBOX_MUTATION
NO_PRODUCTION_DEPLOYMENT
LIVE_WRITE_NOT_APPROVED
```

## Purpose

Gate 0 establishes one verifiable source of truth before any sandbox or production accounting mutation. The deployed runtime, repository commit, active schema, route map, tool registry and policy must agree.

## Required operator evidence

Populate `config/release_manifest.example.json` with validated values and store the resulting manifest outside the public repository if it contains environment-specific identifiers.

Required evidence:

1. Repository commit deployed to the runtime.
2. SHA-256 hashes for all deployed PHP runtime files.
3. Active client/OpenAPI schema hash.
4. Effective authenticated `tools/list` response hash.
5. Verified route-map hash.
6. Effective write-policy state.
7. Runtime and server configuration evidence with secrets redacted.
8. Operator identity and approval reference.

## Acceptance rule

Any unexplained mismatch is classified `PENDING_REVIEW`. No mismatch may be silently merged or interpreted as authorization.

## Preserved controls

```text
write_tools_enabled=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
provider_write_calls_allowed=false
```
