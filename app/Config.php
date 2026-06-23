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

    public function writeToolsEnabled(): bool
    {
        return $this->toBool($this->get('enable_write_tools', false));
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

    public function createInvoiceDraftRoute(): string
    {
        return trim((string) $this->get('create_invoice_draft_route', ''));
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
            'write_tools_enabled' => $this->writeToolsEnabled(),
        ];
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
