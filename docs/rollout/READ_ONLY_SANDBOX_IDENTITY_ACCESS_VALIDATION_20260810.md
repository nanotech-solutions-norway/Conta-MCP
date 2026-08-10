# Read-only sandbox identity and access validation — 2026-08-10

## Scope and authorization

The operator authorized `AUTHORIZE_READ_ONLY_SANDBOX_IDENTITY_ACCESS_VALIDATION`. The authorization permitted identity and organization-access validation against Conta's sandbox using `GET` only. It did not authorize create, update, delete, send, post, credit, write-tool enablement, sandbox mutation or production access.

## Canonical execution evidence

| Item | Evidence |
| --- | --- |
| Repository | `nanotech-solutions-norway/Conta-MCP` |
| Branch | `main` |
| Reviewed commit | `563a73bfe80b5c990ea8a1ee98ac546943f22052` |
| Workflow | `Conta Sandbox Identity Access Validation` |
| GitHub Actions run | `31418133692` |
| Run URL | <https://github.com/nanotech-solutions-norway/Conta-MCP/actions/runs/31418133692> |
| Protected environment | `conta-sandbox-secrets` |
| Result | `SUCCESS` |

The protected environment enforced:

```text
CONTA_ENVIRONMENT=sandbox
CONTA_API_BASE_URL=https://api.gateway.conta-sandbox.no
```

Credential values, organization identifiers, organization names, subscription details and provider response bodies were not printed or recorded.

## Sanitized runtime result

```text
SANDBOX_ENVIRONMENT_ENFORCED=true
SANDBOX_GATEWAY_ENFORCED=true
SANDBOX_API_AUTHENTICATED=true
CONFIGURED_SANDBOX_ORGANIZATION_ACCESSIBLE=true
ORGANIZATION_SCOPED_SUBSCRIPTION_ENDPOINT_ACCESSIBLE=true
HTTP_METHODS_USED=GET_ONLY
PROVIDER_READ_CALL_PERFORMED=true
PROVIDER_WRITE_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
RESPONSE_BODY_PRINTED=false
SECRET_OR_IDENTITY_VALUE_PRINTED=false
WRITE_ENABLEMENT_CHANGED=false
```

The workflow required HTTP `200` from both `GET /invoice/organizations` and `GET /invoice/organizations/{opContextOrgId}/subscription-plan`. It confirmed that the configured organization appeared in the authenticated user's accessible organization list before calling the organization-scoped endpoint.

## Evidence boundary

This result proves that the configured sandbox API identity authenticates, the configured sandbox organization is bound to that identity, and an organization-scoped read endpoint is accessible. It does not prove invoice-draft create entitlement, runtime create-response behavior, runtime readback behavior, provider-native idempotency, rectification readiness or write-path safety.

The active execution boundary remains fail closed:

```text
WRITE_TOOLS_ENABLED=false
RUNTIME_WRITE_BLOCKED=true
ONE_CALL_OPERATOR_AUTHORIZATION=NOT_GRANTED
PRODUCTION_WRITE_APPROVED=false
```

Any sandbox mutation requires a separate payload-bound authorization and explicit operator instruction.

