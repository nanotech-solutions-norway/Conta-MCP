<?php
/**
 * Conta MCP local configuration template.
 *
 * Deployment step:
 * 1. Copy this file on the server to: config/conta_config.local.php
 * 2. Insert real secrets only in conta_config.local.php on the Domeneshop server.
 * 3. Never commit conta_config.local.php to GitHub.
 */

return [
    // sandbox | production
    'environment' => getenv('CONTA_ENVIRONMENT') ?: 'sandbox',

    // Conta API key. Prefer server environment variable if possible.
    'conta_api_key' => getenv('CONTA_API_KEY') ?: '',

    // Organization ID used by customer/invoice tools.
    // Leave empty until discovered through Conta/Swagger or organization-list tool.
    'default_organization_id' => getenv('CONTA_ORG_ID') ?: '',

    // Bearer token required by clients calling this MCP endpoint.
    // Generate a long random token and store it only server-side.
    'mcp_bearer_token' => getenv('CONTA_MCP_BEARER_TOKEN') ?: '',

    // Exact allowed browser/client origin. Keep narrow.
    'allowed_origin' => getenv('CONTA_MCP_ALLOWED_ORIGIN') ?: 'https://www.nanoconcept.no',

    // Disabled by default. Read-only first.
    'enable_write_tools' => filter_var(getenv('CONTA_ENABLE_WRITE_TOOLS') ?: false, FILTER_VALIDATE_BOOLEAN),

    // Optional route override for Conta draft-invoice creation after Swagger verification.
    // Example only: /invoice/organizations/{orgId}/invoices/draft
    'create_invoice_draft_route' => getenv('CONTA_ROUTE_CREATE_INVOICE_DRAFT') ?: '',

    // HTTP timeout for Conta API calls.
    'request_timeout_seconds' => (int) (getenv('CONTA_REQUEST_TIMEOUT_SECONDS') ?: 20),

    // Audit log stores metadata only. Do not log full payloads.
    'audit_log_path' => __DIR__ . '/../storage/audit.log',
];
