<?php
/**
 * Conta MCP local configuration template.
 *
 * Copy to config/conta_config.local.php on the server only.
 * Never commit the local file or real values.
 */

return [
    'environment' => getenv('CONTA_ENVIRONMENT') ?: 'sandbox',

    // Provider and MCP credentials. Server-side only.
    'conta_api_key' => getenv('CONTA_API_KEY') ?: '',
    'default_organization_id' => getenv('CONTA_ORG_ID') ?: '',
    'mcp_bearer_token' => getenv('CONTA_MCP_BEARER_TOKEN') ?: '',
    'allowed_origin' => getenv('CONTA_MCP_ALLOWED_ORIGIN') ?: 'https://www.nanoconcept.no',

    // Preview is non-executing and may remain enabled while execution is blocked.
    'enable_write_preview' => filter_var(getenv('CONTA_ENABLE_WRITE_PREVIEW') ?: true, FILTER_VALIDATE_BOOLEAN),

    // Independent execution gates. All fail closed by default.
    'enable_write_tools' => filter_var(getenv('CONTA_ENABLE_WRITE_TOOLS') ?: false, FILTER_VALIDATE_BOOLEAN),
    'runtime_write_blocked' => filter_var(getenv('CONTA_RUNTIME_WRITE_BLOCKED') ?: true, FILTER_VALIDATE_BOOLEAN),
    'execution_allowed' => filter_var(getenv('CONTA_EXECUTION_ALLOWED') ?: false, FILTER_VALIDATE_BOOLEAN),
    'production_write_approved' => filter_var(getenv('CONTA_PRODUCTION_WRITE_APPROVED') ?: false, FILTER_VALIDATE_BOOLEAN),

    // Comma-separated allowlists. Empty means no execution.
    'allowed_write_organization_ids' => getenv('CONTA_ALLOWED_WRITE_ORG_IDS') ?: '',
    'allowed_write_actions' => getenv('CONTA_ALLOWED_WRITE_ACTIONS') ?: '',

    // Both routes must be validated against the current official provider schema.
    'create_invoice_draft_route' => getenv('CONTA_ROUTE_CREATE_INVOICE_DRAFT') ?: '',
    'readback_invoice_draft_route' => getenv('CONTA_ROUTE_READBACK_INVOICE_DRAFT') ?: '',

    // Release and provider evidence. Never guess these values.
    'release_commit' => getenv('CONTA_RELEASE_COMMIT') ?: '',
    'provider_schema_sha256' => getenv('CONTA_PROVIDER_SCHEMA_SHA256') ?: '',
    'write_policy_version' => getenv('CONTA_WRITE_POLICY_VERSION') ?: '2026-07-16-gate0-4',

    // Signed one-use approval controls. Signing key is server-side only.
    'require_signed_approvals' => filter_var(getenv('CONTA_REQUIRE_SIGNED_APPROVALS') ?: true, FILTER_VALIDATE_BOOLEAN),
    'approval_signing_key' => getenv('CONTA_APPROVAL_SIGNING_KEY') ?: '',
    'approval_key_id' => getenv('CONTA_APPROVAL_KEY_ID') ?: 'conta-sandbox-approval-v1',
    'approval_max_ttl_seconds' => (int) (getenv('CONTA_APPROVAL_MAX_TTL_SECONDS') ?: 900),

    'request_timeout_seconds' => (int) (getenv('CONTA_REQUEST_TIMEOUT_SECONDS') ?: 20),

    // Server-only control and evidence files. Missing files block execution.
    'approved_release_manifest_path' => __DIR__ . '/../storage/approved-release-manifest.json',
    'write_kill_switch_path' => __DIR__ . '/../storage/write-kill-switch.json',
    'sandbox_authorization_path' => __DIR__ . '/../storage/sandbox-authorization.json',
    'audit_log_path' => __DIR__ . '/../storage/audit.log',
    'write_ledger_path' => __DIR__ . '/../storage/write-ledger.json',
];
