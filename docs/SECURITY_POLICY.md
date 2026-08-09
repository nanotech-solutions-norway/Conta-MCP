# Security Policy — Conta MCP

## Universal Atlas/MCP baseline — adopted candidate 09.08.2026

Conta MCP must meet the NTSN Atlas AI & MCP Universal Security Standard. The universal baseline is maintained in the private `nanotech-solutions-norway/ntsn-mcp-integrations` governance repository. This public repository repeats the non-sensitive mandatory rules needed for implementation.

Mandatory additions to the existing Conta controls:

1. Raw API keys, access/refresh tokens, passwords and private keys must never enter LLM/agent prompts, memory, RAG, tool results, screenshots, logs, Drive evidence or Git.
2. Prefer OAuth/OIDC, workload identity, GitHub Apps and short-lived/dynamic credentials. Static keys are a provider-compatibility fallback and must remain in an approved server-side secret manager/credential boundary.
3. The MCP client credential and Conta/upstream credential are separate security domains. Client tokens must never be passed through to Conta.
4. Read and write service identities/credentials must be separated where provider capabilities permit. Production write credentials must not be available to the read-only process or a general AI coding shell.
5. Authorization must bind actor/workload, organization/tenant, environment, exact action, target/route, risk class, scope, schema/policy version and expiry.
6. Consequential actions require a short-lived one-use approval bound to actor, organization, action, target, canonical payload hash, nonce and expiry. Model judgment is not authorization.
7. Disabled execute tools must be absent from effective tool discovery and rejected again at final provider dispatch.
8. Mutations require idempotency/replay protection, pre-read where supported, deterministic preview, controlled execution, mandatory readback, audit closure and recovery/rectification evidence.
9. Ambiguous provider results are not automatically retried. Read back state first.
10. Global/action kill switches must fail closed and override all other enablement state.
11. Audit is privacy-minimized and tamper-evident; no authorization headers, full secrets or unnecessary accounting/customer payloads.
12. GitHub CI/CD should use OIDC/workload federation rather than long-lived cloud deployment secrets where supported; secret scanning/push protection and dependency/code scanning are required according to repository capability.
13. Runtime drift from the approved release manifest blocks write activation.
14. Existing high-risk accounting boundaries remain more restrictive than this universal baseline.

Governance adoption does not mean the current production runtime has implemented these additions. Runtime status must continue to be described separately as designed/configured/implemented/tested/validated/approved/release-approved/live.

## Classification

This integration touches accounting data and must be treated as business-sensitive.

## Hard rules

1. Never commit Conta API keys to GitHub.
2. Never commit `config/conta_config.local.php`.
3. Never commit real customer, invoice, voucher, payment, bank, payroll, VAT or accounting data.
4. Do not log full Conta request/response payloads.
5. Start with sandbox and read-only tools.
6. Write tools must remain disabled until explicit approval.
7. Destructive/accounting-posting tools are out of scope for the first version.

## Repository visibility

The repository is currently public. This increases the importance of keeping the repository limited to:

- source code
- documentation
- configuration templates
- validation scripts
- non-sensitive examples

For production accounting use, private repository visibility is recommended.

## Runtime authentication

The MCP endpoint requires an authenticated client credential. The client credential is separate from the Conta API key. The client uses the MCP authorization layer; the server-side provider adapter uses the Conta credential.

The long-term target for HTTP MCP is OAuth-based resource/audience-bound authorization rather than one permanent shared bearer token. Until that migration is implemented and validated, the existing bearer boundary must remain tightly protected and must not be treated as sufficient authorization for production accounting writes.

## Conta API authentication

The Conta API credential is sent only from the trusted server-side runtime to Conta. The API credential must exist only in an approved server-side secret store/configuration boundary and must never be returned by an MCP tool.

## Write-tool policy

Default:

```php
'enable_write_tools' => false
```

To enable draft-write tools, all of the following must be true:

1. Sandbox tests completed.
2. Correct Conta Swagger route verified.
3. `create_invoice_draft_route` configured server-side.
4. Human approval workflow defined.
5. Audit log reviewed.
6. Production impact understood.
7. Universal baseline authorization, approval, idempotency, readback and kill-switch gates pass for the exact action/environment.

## Blocked operations

The first implementation intentionally does not include:

- sending invoices
- deleting invoices
- deleting customers
- posting accounting entries
- modifying payments
- submitting VAT returns
- payroll functions
- bank integration actions

These remain blocked unless a separate high-risk security/accounting program explicitly approves them.

## Audit logging

Audit log should contain metadata only, including as applicable:

- timestamp and correlation ID
- actor/workload and organization
- tool/action and risk class
- policy/schema/release version
- approval/idempotency reference
- canonical payload hash
- provider reference/request ID
- status/sanitized error class
- readback and recovery status

Audit log must not contain:

- API keys
- bearer/access/refresh tokens
- passwords or private keys
- full invoice payloads
- customer records
- voucher files
- bank data
- complete API responses

## Incident response

If a secret is accidentally committed or otherwise exposed:

1. Treat the credential as compromised and revoke it immediately.
2. Rotate the affected MCP/client authorization material immediately.
3. Remove the secret from the live server or compromised boundary.
4. Activate the relevant kill switch if misuse cannot be excluded.
5. Preserve privacy-minimized security evidence and review audit logs.
6. Purge Git history if necessary after revocation; history cleanup does not replace rotation.
7. Create a replacement restricted credential through the approved secret boundary.
8. Validate that no sensitive data or unauthorized mutations were exposed.
9. Complete root-cause and regression testing before re-enable.
