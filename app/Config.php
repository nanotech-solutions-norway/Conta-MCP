<?php

declare(strict_types=1);

final class Config
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public static function load(string $rootDir): self
    {
        $examplePath = $rootDir . '/config/conta_config.example.php';
        $localPath = $rootDir . '/config/conta_config.local.php';

        $values = is_file($examplePath) ? require $examplePath : [];
        if (is_file($localPath)) {
            $local = require $localPath;
            if (is_array($local)) {
                $values = array_replace($values, $local);
            }
        }

        return new self($values);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function environment(): string
    {
        $environment = strtolower((string) $this->get('environment', 'sandbox'));
        return in_array($environment, ['sandbox', 'production'], true) ? $environment : 'sandbox';
    }

    public function contaBaseUrl(): string
    {
        return $this->environment() === 'production'
            ? 'https://api.gateway.conta.no'
            : 'https://api.gateway.conta-sandbox.no';
    }

    public function apiKey(): string
    {
        return trim((string) $this->get('conta_api_key', ''));
    }

    public function organizationId(?string $override = null): string
    {
        $value = $override !== null && $override !== '' ? $override : (string) $this->get('default_organization_id', '');
        return trim($value);
    }

    public function bearerToken(): string
    {
        return trim((string) $this->get('mcp_bearer_token', ''));
    }

    public function allowedOrigin(): string
    {
        return trim((string) $this->get('allowed_origin', ''));
    }

    public function writePreviewEnabled(): bool
    {
        return $this->toBool($this->get('enable_write_preview', true));
    }

    public function writeToolsEnabled(): bool
    {
        return $this->toBool($this->get('enable_write_tools', false));
    }

    public function runtimeWriteBlocked(): bool
    {
        return $this->toBool($this->get('runtime_write_blocked', true));
    }

    public function executionAllowed(): bool
    {
        return $this->toBool($this->get('execution_allowed', false));
    }

    public function productionWriteApproved(): bool
    {
        return $this->toBool($this->get('production_write_approved', false));
    }

    public function writePolicyVersion(): string
    {
        $value = trim((string) $this->get('write_policy_version', '2026-08-19-production-gate1'));
        return $value !== '' ? $value : '2026-08-19-production-gate1';
    }

    public function allowedWriteOrganizationIds(): array
    {
        return $this->stringList($this->get('allowed_write_organization_ids', []));
    }

    public function allowedWriteActions(): array
    {
        return $this->stringList($this->get('allowed_write_actions', []));
    }

    public function productionOrganizationReferenceHash(): string
    {
        return strtolower(trim((string) $this->get('production_organization_reference_hash', '')));
    }

    public function productionDecisionPacketSha256(): string
    {
        return strtolower(trim((string) $this->get('production_decision_packet_sha256', '')));
    }

    public function maxInvoiceDraftLines(): int
    {
        return max(1, min((int) $this->get('production_max_invoice_draft_lines', 1), 100));
    }

    public function maxInvoiceDraftLineAmount(): float
    {
        $value = (float) $this->get('production_max_invoice_draft_line_amount', 1.00);
        return max(0.01, min($value, 100000000.0));
    }

    public function maxInvoiceDraftTotal(): float
    {
        $value = (float) $this->get('production_max_invoice_draft_total', 1.00);
        return max(0.01, min($value, 100000000.0));
    }

    public function approvalMaxTtlSeconds(): int
    {
        $ttl = (int) $this->get('approval_max_ttl_seconds', 900);
        return max(60, min($ttl, 3600));
    }

    public function requireSignedApprovals(): bool
    {
        return $this->toBool($this->get('require_signed_approvals', true));
    }

    public function approvalSigningKey(): string
    {
        return (string) $this->get('approval_signing_key', '');
    }

    public function approvalKeyId(): string
    {
        $value = trim((string) $this->get('approval_key_id', 'conta-approval-v1'));
        return $value !== '' ? $value : 'conta-approval-v1';
    }

    public function releaseCommit(): string
    {
        return strtolower(trim((string) $this->get('release_commit', '')));
    }

    public function providerSchemaSha256(): string
    {
        return strtolower(trim((string) $this->get('provider_schema_sha256', '')));
    }

    public function approvedReleaseManifestPath(): string
    {
        return (string) $this->get('approved_release_manifest_path', __DIR__ . '/../storage/approved-release-manifest.json');
    }

    public function writeKillSwitchPath(): string
    {
        return (string) $this->get('write_kill_switch_path', __DIR__ . '/../storage/write-kill-switch.json');
    }

    public function sandboxAuthorizationPath(): string
    {
        return (string) $this->get('sandbox_authorization_path', __DIR__ . '/../storage/sandbox-authorization.json');
    }

    public function productionAuthorizationPath(): string
    {
        return (string) $this->get('production_authorization_path', __DIR__ . '/../storage/production-authorization.json');
    }

    public function requestTimeoutSeconds(): int
    {
        $timeout = (int) $this->get('request_timeout_seconds', 20);
        return max(5, min($timeout, 60));
    }

    public function auditLogPath(): string
    {
        return (string) $this->get('audit_log_path', __DIR__ . '/../storage/audit.log');
    }

    public function writeLedgerPath(): string
    {
        return (string) $this->get('write_ledger_path', __DIR__ . '/../storage/write-ledger.json');
    }

    public function createInvoiceDraftRoute(): string
    {
        return trim((string) $this->get('create_invoice_draft_route', ''));
    }

    public function readbackInvoiceDraftRoute(): string
    {
        return trim((string) $this->get('readback_invoice_draft_route', ''));
    }

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '' && $this->bearerToken() !== '';
    }

    public function publicStatus(): array
    {
        return [
            'environment' => $this->environment(),
            'base_url' => $this->contaBaseUrl(),
            'has_conta_api_key' => $this->apiKey() !== '',
            'has_mcp_bearer_token' => $this->bearerToken() !== '',
            'has_default_organization_id' => $this->organizationId() !== '',
            'write_preview_enabled' => $this->writePreviewEnabled(),
            'write_tools_enabled' => $this->writeToolsEnabled(),
            'runtime_write_blocked' => $this->runtimeWriteBlocked(),
            'execution_allowed' => $this->executionAllowed(),
            'production_write_approved' => $this->productionWriteApproved(),
            'write_policy_version' => $this->writePolicyVersion(),
            'allowed_write_action_count' => count($this->allowedWriteActions()),
            'allowed_write_organization_count' => count($this->allowedWriteOrganizationIds()),
            'has_production_organization_reference_hash' => preg_match('/^[a-f0-9]{64}$/', $this->productionOrganizationReferenceHash()) === 1,
            'has_production_decision_packet_hash' => preg_match('/^[a-f0-9]{64}$/', $this->productionDecisionPacketSha256()) === 1,
            'production_max_invoice_draft_lines' => $this->maxInvoiceDraftLines(),
            'production_max_invoice_draft_line_amount' => $this->maxInvoiceDraftLineAmount(),
            'production_max_invoice_draft_total' => $this->maxInvoiceDraftTotal(),
            'require_signed_approvals' => $this->requireSignedApprovals(),
            'has_approval_signing_key' => $this->approvalSigningKey() !== '',
            'has_release_commit' => $this->releaseCommit() !== '',
            'has_provider_schema_hash' => $this->providerSchemaSha256() !== '',
            'has_create_route' => $this->createInvoiceDraftRoute() !== '',
            'has_readback_route' => $this->readbackInvoiceDraftRoute() !== '',
        ];
    }

    private function stringList(mixed $value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (!is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $item) {
            $item = trim((string) $item);
            if ($item !== '' && !in_array($item, $out, true)) {
                $out[] = $item;
            }
        }
        return $out;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value === 1;
        }
        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }
        return false;
    }
}
