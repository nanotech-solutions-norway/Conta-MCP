<?php

declare(strict_types=1);

final class InvoiceDraftPreview
{
    public function __construct(
        private readonly Config $config,
        private readonly WritePolicy $writePolicy
    ) {
    }

    public function build(string $organizationId, array $invoice): array
    {
        if (!$this->writePolicy->previewEnabled()) {
            throw new RuntimeException('write_preview_disabled');
        }
        if ($invoice === []) {
            throw new InvalidArgumentException('invoice must be a non-empty object matching the verified Conta schema.');
        }

        $normalized = self::canonicalize($invoice);
        $payloadHash = self::payloadHash($normalized);
        $routeTemplate = $this->config->createInvoiceDraftRoute();
        $path = $routeTemplate === ''
            ? null
            : str_replace(['{orgId}', '{opContextOrgId}'], rawurlencode($organizationId), $routeTemplate);

        return [
            'candidate_id' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
            'mode' => 'preview_only_no_provider_call',
            'organization_id_hash' => hash('sha256', $organizationId),
            'method' => 'POST',
            'path' => $path,
            'route_configured' => $path !== null,
            'payload_hash_sha256' => $payloadHash,
            'normalized_payload' => $normalized,
            'field_paths' => self::fieldPaths($normalized),
            'risk_class' => 'controlled_non_posting_accounting_draft_write',
            'required_approval' => [
                'approved' => true,
                'oneUse' => true,
                'action' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
                'organizationId' => $organizationId,
                'environment' => $this->config->environment(),
                'method' => 'POST',
                'path' => $path ?? 'PENDING_VALIDATED_ROUTE',
                'payloadHash' => $payloadHash,
                'approvalId' => 'PENDING_OPERATOR_VALIDATION',
                'approvedBy' => 'PENDING_OPERATOR_VALIDATION',
                'issuedAt' => 'PENDING_OPERATOR_VALIDATION',
                'expiresAt' => 'PENDING_OPERATOR_VALIDATION',
                'nonce' => 'PENDING_OPERATOR_VALIDATION',
                'idempotencyKey' => 'PENDING_OPERATOR_VALIDATION',
            ],
            'policy' => $this->writePolicy->effectiveState(),
            'execution_eligible_now' => $this->writePolicy->effectiveExecutionEnabled(),
            'provider_call_performed' => false,
        ];
    }

    public static function payloadHash(array $payload): string
    {
        $canonical = self::canonicalize($payload);
        $json = json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        if ($json === false) {
            throw new RuntimeException('payload_canonicalization_failed');
        }
        return hash('sha256', $json);
    }

    public static function canonicalize(array $value): array
    {
        if (!array_is_list($value)) {
            ksort($value, SORT_STRING);
        }
        foreach ($value as $key => $child) {
            if (is_array($child)) {
                $value[$key] = self::canonicalize($child);
            }
        }
        return $value;
    }

    private static function fieldPaths(array $value, string $prefix = ''): array
    {
        $paths = [];
        foreach ($value as $key => $child) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($child)) {
                $paths = array_merge($paths, self::fieldPaths($child, $path));
            } else {
                $paths[] = $path;
            }
        }
        sort($paths, SORT_STRING);
        return $paths;
    }
}
