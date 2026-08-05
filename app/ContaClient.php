<?php

declare(strict_types=1);

final class ContaClient
{
    public function __construct(
        private readonly Config $config,
        private readonly HttpClient $httpClient,
        private readonly WritePolicy $writePolicy,
        private readonly WriteExecutionLedger $writeLedger,
        private readonly AuditLogger $auditLogger,
        private readonly InvoiceDraftReadbackVerifier $readbackVerifier
    ) {
    }

    public function listOrganizations(): array
    {
        return $this->request('GET', '/organizations');
    }

    public function listCustomers(string $organizationId, array $query = []): array
    {
        return $this->request('GET', '/invoice/organizations/' . rawurlencode($organizationId) . '/customers', null, $this->sanitizeListQuery($query));
    }

    public function getCustomer(string $organizationId, string $customerId): array
    {
        return $this->request('GET', '/invoice/organizations/' . rawurlencode($organizationId) . '/customers/' . rawurlencode($customerId));
    }

    public function listInvoices(string $organizationId, array $query = []): array
    {
        return $this->request('GET', '/invoice/organizations/' . rawurlencode($organizationId) . '/invoices', null, $this->sanitizeListQuery($query));
    }

    public function getInvoice(string $organizationId, string $invoiceId): array
    {
        return $this->request('GET', '/invoice/organizations/' . rawurlencode($organizationId) . '/invoices/' . rawurlencode($invoiceId));
    }

    public function createInvoiceDraft(string $organizationId, array $invoicePayload, array $approval): array
    {
        $route = $this->config->createInvoiceDraftRoute();
        if ($route === '') {
            return ['status' => 501, 'ok' => false, 'body' => ['error' => 'create_invoice_draft_route_not_configured']];
        }

        $path = str_replace(['{orgId}', '{opContextOrgId}'], rawurlencode($organizationId), $route);
        $payloadHash = InvoiceDraftPreview::payloadHash($invoicePayload);
        $permit = $this->writePolicy->authorizeInvoiceDraftCreate($organizationId, $path, $payloadHash, $approval);

        $this->writeLedger->reserve($permit);
        $this->auditLogger->record('provider_write_authorized', [
            'action' => $permit->action,
            'method' => $permit->method,
            'path_template' => $route,
            'organization_id_hash' => hash('sha256', $organizationId),
            'payload_hash' => $payloadHash,
            'approval_id_hash' => hash('sha256', $permit->approvalId),
            'authorization_id_hash' => hash('sha256', $permit->authorizationId),
            'idempotency_key_hash' => hash('sha256', $permit->idempotencyKey),
            'policy_version' => $permit->policyVersion,
            'release_manifest_hash' => $permit->releaseManifestHash,
        ]);

        try {
            $create = $this->request('POST', $path, $invoicePayload, [], $permit);
            $providerRequestId = $this->extractProviderRequestId($create);
            if (($create['ok'] ?? false) !== true) {
                $this->writeLedger->complete($permit->idempotencyKey, false, $providerRequestId);
                return $create;
            }

            $draftId = $this->extractDraftId($create);
            if ($draftId === null) {
                $this->writeLedger->complete($permit->idempotencyKey, false, $providerRequestId);
                return [
                    'status' => 502,
                    'ok' => false,
                    'body' => ['error' => 'invoice_draft_id_missing_after_create', 'create' => $create['body'] ?? null],
                ];
            }

            $readbackPath = str_replace(
                ['{orgId}', '{opContextOrgId}', '{id}', '{invoiceDraftId}'],
                [rawurlencode($organizationId), rawurlencode($organizationId), rawurlencode($draftId), rawurlencode($draftId)],
                $this->config->readbackInvoiceDraftRoute()
            );
            $readback = $this->request('GET', $readbackPath);
            if (($readback['ok'] ?? false) !== true || !is_array($readback['body'] ?? null)) {
                $this->writeLedger->complete($permit->idempotencyKey, false, $providerRequestId);
                return [
                    'status' => 502,
                    'ok' => false,
                    'body' => ['error' => 'invoice_draft_readback_failed', 'create' => $create['body'] ?? null, 'readback' => $readback['body'] ?? null],
                ];
            }

            $verification = $this->readbackVerifier->verify($invoicePayload, $readback['body']);
            $verified = ($verification['verified'] ?? false) === true;
            $this->writeLedger->complete(
                $permit->idempotencyKey,
                $verified,
                $providerRequestId,
                is_string($verification['actual_projection_hash'] ?? null) ? $verification['actual_projection_hash'] : null
            );
            $this->auditLogger->record('provider_write_readback_verified', [
                'action' => $permit->action,
                'verified' => $verified,
                'draft_id_hash' => hash('sha256', $draftId),
                'readback_projection_hash' => $verification['actual_projection_hash'] ?? null,
                'mismatch_count' => is_array($verification['mismatches'] ?? null) ? count($verification['mismatches']) : null,
            ]);

            return [
                'status' => $verified ? 200 : 502,
                'ok' => $verified,
                'body' => [
                    'create' => $create['body'] ?? null,
                    'readback' => $readback['body'],
                    'verification' => $verification,
                ],
            ];
        } catch (Throwable $e) {
            $this->writeLedger->complete($permit->idempotencyKey, false);
            throw $e;
        }
    }

    private function request(string $method, string $path, ?array $body = null, array $query = [], ?WriteDispatchPermit $permit = null): array
    {
        $method = strtoupper($method);
        if ($method !== 'GET') {
            if ($permit === null) {
                throw new RuntimeException('provider_mutation_requires_dispatch_permit');
            }
            $this->writePolicy->assertDispatchPermit($permit, $method, $path);
        }

        if ($this->config->apiKey() === '') {
            return ['status' => 500, 'ok' => false, 'body' => ['error' => 'conta_api_key_missing']];
        }

        $url = rtrim($this->config->contaBaseUrl(), '/') . '/' . ltrim($path, '/');
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        return $this->httpClient->request(
            $method,
            $url,
            ['apiKey' => $this->config->apiKey(), 'Accept' => 'application/json'],
            $body,
            $this->config->requestTimeoutSeconds()
        );
    }

    private function sanitizeListQuery(array $query): array
    {
        $allowed = ['q', 'hits', 'page', 'sort'];
        $out = [];
        foreach ($allowed as $key) {
            if (isset($query[$key]) && $query[$key] !== '') {
                $out[$key] = $query[$key];
            }
        }
        return $out;
    }

    private function extractProviderRequestId(array $result): ?string
    {
        $body = $result['body'] ?? null;
        if (!is_array($body)) {
            return null;
        }
        foreach (['requestId', 'request_id', 'correlationId', 'correlation_id'] as $key) {
            if (isset($body[$key]) && is_scalar($body[$key])) {
                return (string) $body[$key];
            }
        }
        return null;
    }

    private function extractDraftId(array $result): ?string
    {
        $body = $result['body'] ?? null;
        if (!is_array($body)) {
            return null;
        }
        foreach (['id', 'invoiceDraftId', 'invoice_draft_id'] as $key) {
            if (isset($body[$key]) && is_scalar($body[$key]) && trim((string) $body[$key]) !== '') {
                return (string) $body[$key];
            }
        }
        return null;
    }
}
