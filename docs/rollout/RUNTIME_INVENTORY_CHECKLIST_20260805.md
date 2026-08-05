# Conta MCP Read-Only Runtime Inventory Checklist — 11:11, 05.08.2026

## Scope

Inspect the active Domeneshop Conta MCP runtime without modifying files, configuration, permissions, credentials, provider state or accounting data.

## Safety boundary

```text
READ_ONLY_INSPECTION_ONLY
NO_DEPLOYMENT
NO_CONFIG_CHANGE
NO_PROVIDER_MUTATION
NO_SECRET_DISCLOSURE
```

Do not print or preserve raw values for API keys, bearer tokens, approval signing keys, organization IDs, customer data, invoice data or accounting records.

## Runtime identity

- [ ] Public health endpoint identified.
- [ ] MCP endpoint identified.
- [ ] Server deployment directory identified.
- [ ] PHP version recorded.
- [ ] Web server/runtime type recorded.
- [ ] Deployed release commit recorded if available.
- [ ] Deployment timestamp recorded if available.
- [ ] Deployment method recorded if available.

## File and access controls

- [ ] Deployed file tree captured without file contents containing secrets.
- [ ] Runtime PHP file SHA-256 hashes generated.
- [ ] `.htaccess` or equivalent access protections verified.
- [ ] Direct access to `/app`, `/config`, `/storage`, `/docs`, `/tests` and `/bin` rejected.
- [ ] Local configuration file exists outside public source control.
- [ ] Local configuration file permissions are appropriately restricted.
- [ ] Storage directory is not publicly readable.
- [ ] Audit and ledger files are not publicly readable.

## Sanitized configuration state

Record booleans only:

| State | Expected before deployment/activation |
|---|---|
| Conta API credential present | Record true/false only |
| MCP bearer token present | Record true/false only |
| Default organization configured | Record true/false only |
| Approval signing key present | Record true/false only |
| Environment | `sandbox` |
| Write preview enabled | `true` |
| Write tools enabled | `false` |
| Runtime write blocked | `true` |
| Execution allowed | `false` |
| Production write approved | `false` |
| Allowed write actions | empty |
| Allowed write organizations | empty |
| Release manifest | absent or pending |
| Kill switch | globally blocked |
| Sandbox authorization | absent or pending |

## Endpoint validation

- [ ] Health endpoint returns expected non-secret status.
- [ ] Unauthorized MCP request is rejected.
- [ ] Authorized MCP `initialize` succeeds.
- [ ] Authorized `tools/list` succeeds.
- [ ] Read-only tools are listed as expected.
- [ ] Preview tool is listed when preview is enabled.
- [ ] `conta_create_invoice_draft` is absent.
- [ ] Direct execution attempt fails closed without provider I/O.
- [ ] No payload or credential appears in server logs.

## Read-only provider validation

Perform only after the runtime security checks pass:

- [ ] Lightweight health/read call succeeds.
- [ ] Organization listing succeeds without exposing identifiers in evidence.
- [ ] Customer/invoice list tools remain bounded and read-only.
- [ ] No non-GET provider request is emitted.

## Drift comparison

Compare the runtime against canonical repository commit:

```text
689cf28d943b761e26d9d1a7ef2eaddf5b78cc07
```

For each runtime file:

| Path | Repository hash | Runtime hash | Result |
|---|---|---|---|
| `PENDING_INVENTORY` | `PENDING` | `PENDING` | `PENDING_REVIEW` |

Any unexplained difference blocks release-manifest approval.

## Evidence package

Produce a sanitized evidence package containing:

- inventory timestamp;
- canonical repository commit;
- PHP/runtime version;
- endpoint status results;
- file hash comparison;
- boolean configuration-state summary;
- redacted `tools/list` result;
- access-control test results;
- explicit confirmation that no provider mutation occurred.

## Acceptance result

Use exactly one classification:

```text
RUNTIME_MATCH_CONFIRMED_READ_ONLY
RUNTIME_DRIFT_REQUIRES_REVIEW
RUNTIME_INVENTORY_INCOMPLETE
```
