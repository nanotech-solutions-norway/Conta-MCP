<?php
/**
 * Conta MCP local configuration template.
 *
 * Copy to config/conta_config.local.php on the server only.
 * Never commit the local file or real values.
 */

return [
    // sandbox | production
    'environment' => getenv('CONTA_ENVIRONMENT') ?: 'sandbox',

    // Provider and MCP credentials. Server-side only.
    'conta_api_key' => getenv('CONTA_API_KEY') ?: '',
    'default_organization_id' => getenv('CONTA_ORG_ID') ?: '',
    'mcp_bearer_token' => getenv('CONTA_MCP_BEARER_TOKEN') ?: '',
    'allowed_origin' => getenv('CONTA_MCP_ALLOWED_ORIGIN') ?: 'https://www.nanoconcept.no',

    // Preview is non-executing and may be enabled while all writes remain blocked.
    'enable_write_preview' => filter_var(getenv('CONTA_ENABLE_WRITE_PREVIEW') ?: true, FILTER_VALIDATE_BOOLEAN),

    // Independent execution gates. All default to fail-closed.
    'enable_write_tools' => filter_var(getenv('CONTA_ENABLE_WRITE_TOOLS') ?: false, FILTER_VALIDATE_BOOLEAN),
    'runtime_write_blocked' => filter_var(getenv('CONTA_RUNTIME_WRITE_BLOCKED') ?: true, FILTER_VALIDATE_BOOLEAN),
    'execution_allowed' => filter_var(getenv('CONTA_EXECUTION_ALLOWED') ?: false, FILTER_VALIDATE_BOOLEAN),
    'production_write_approved' => filter_var(getenv('CONTA_PRODUCTION_WRITE_APPROVED') ?: false, FILTER_VALIDATE_BOOLEAN),

    // Comma-separated explicit allowlists. Empty means no execution is allowed.
    'allowed_write_organization_ids' => getenv('CONTA_ALLOWED_WRITE_ORG_IDS') ?: '',
    'allowed_write_actions' => getenv('CONTA_ALLOWED_WRITE_ACTIONS') ?: '',

    // Verified provider route only. Do not infer or guess this value.
    'create_invoice_draft_route' => getenv('CONTA_ROUTE_CREATE_INVOICE_DRAFT') ?: '',

    // Approval and policy controls.
    'write_policy_version' => getenv('CONTA_WRITE_POLICY_VERSION') ?: '2026-07-16-gate0-3',
    'approval_max_ttl_seconds' => (int) (getenv('CONTA_APPROVAL_MAX_TTL_SECONDS') ?: 900),

    'request_timeout_seconds' => (int) (getenv('CONTA_REQUEST_TIMEOUT_SECONDS') ?: 20),

    // Metadata-only logs. Server-side and blocked from public access.
    'audit_log_path' => __DIR__ . '/../storage/audit.log',
    'write_ledger_path' => __DIR__ . '/../storage/write-ledger.json',
];
