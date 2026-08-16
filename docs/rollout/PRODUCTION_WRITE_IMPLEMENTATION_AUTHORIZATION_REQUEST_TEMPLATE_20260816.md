# Conta MCP Production-Write Implementation Authorization Request Template — 2026-08-16

## Status

```text
REQUEST_STATUS=NOT_READY
PROTECTED_DECISIONS_COMPLETE=false
IMPLEMENTATION_AUTHORIZED=false
```

This template becomes usable only after the protected decision gate produces a complete, unexpired repository-safe attestation.

## Required evidence

The future request must bind:

- approved design merge commit `1dfcaf8f662105923fc49e412f7d6122b5d5457f`;
- exact decision-gate definition commit;
- protected decision-packet SHA-256 and expiry;
- all required decision attestations;
- exact implementation scope and files permitted to change;
- explicit exclusions for configuration, credentials, deployment and provider calls;
- required offline tests and fail-closed acceptance criteria.

## Future authorization syntax

Only after the request is complete and reviewed:

```text
AUTHORIZE_CONTA_PRODUCTION_WRITE_IMPLEMENTATION for commit <exact-reviewed-request-commit>
```

That later instruction may authorize code and offline tests only if the completed request says so. It must not implicitly authorize credential provisioning, deployment, release approval, write-tool visibility or a provider mutation.

No phrase in this template is an active authorization.
