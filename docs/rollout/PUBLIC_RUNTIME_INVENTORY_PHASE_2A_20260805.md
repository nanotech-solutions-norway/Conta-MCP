# Conta MCP Public Runtime Inventory — Phase 2A — 14:05, 05.08.2026

## Classification

```text
PUBLIC_RUNTIME_INVENTORY_PHASE_2A_VERIFIED
ACTIVE_RUNTIME_ENDPOINT_DISCOVERED
DOCUMENTED_NANOCONCEPT_ENDPOINT_STALE
RUNTIME_DEPLOYMENT_COMMIT_NOT_VERIFIED
RUNTIME_FILE_HASHES_NOT_VERIFIED
AUTHENTICATED_TOOL_INVENTORY_PENDING
NO_DEPLOYMENT
NO_CONFIG_CHANGE
NO_PROVIDER_MUTATION
WRITE_TOOLS_DISABLED
```

## Operator proof

The operator ran the approved public read-only inventory against the active endpoint and reported:

```text
PUBLIC_RUNTIME_INVENTORY_PHASE_2A_PASSED=true
```

The approved script reaches this marker only after:

1. the active endpoint returns HTTP 200 and valid JSON;
2. the service identifies as `conta-mcp-server`;
3. the service reports `status=ready`;
4. the service reports `configured=true`;
5. an unauthenticated MCP `initialize` POST is rejected with HTTP 401 or 403;
6. public GET checks do not expose `/app/`, `/config/`, `/storage/`, `/docs/`, `/tests/` or `/bin/` with HTTP 200.

## Endpoint result

Active public MCP endpoint:

```text
https://mcp.atlas-ai.no
```

The previously documented endpoint below returned GitHub Pages 404 responses and is therefore stale for the active PHP runtime:

```text
https://www.nanoconcept.no/conta-mcp/mcp
```

## Evidence limits

The final proof marker alone does not establish:

- whether a dedicated `/health` route was discovered;
- whether every expected security response header was present;
- the deployed server directory;
- the deployed release commit;
- runtime PHP or web-server version;
- deployed file hashes;
- sanitized runtime configuration booleans beyond public `configured=true`;
- authenticated `initialize` or `tools/list` results;
- read-only provider connectivity.

These remain pending operator validation.

## Preserved safety state

```text
provider_call_performed=false
deployment_performed=false
configuration_changed=false
sandbox_mutation_performed=false
write_tools_enabled=false
```

## Authorized next action

```text
AUTHENTICATED_READ_ONLY_MCP_INVENTORY
NO_WRITE_TOOL_CALL
NO_PROVIDER_MUTATION
NO_DEPLOYMENT
NO_CONFIG_CHANGE
```

The next phase may use the existing MCP bearer token only in-memory for authenticated `initialize` and `tools/list`. The token must not be printed, committed, saved into evidence, or pasted into chat.
