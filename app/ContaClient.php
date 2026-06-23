<?php

declare(strict_types=1);

final class ContaClient
{
    public function __construct(
        private readonly Config $config,
        private readonly HttpClient $httpClient
    ) {
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, null, $query);
    }

    public function post(string $path, array $body): array
    {
        return $this->request('POST', $path, $body);
    }

    public function put(string $path, array $body): array
    {
        return $this->request('PUT', $path, $body);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    public function listOrganizations(): array
    {
        // Verify route in Conta Swagger before production if this route differs for the account/product plan.
        return $this->get('/organizations');
    }

    public function listCustomers(string $organizationId, array $query = []): array
    {
        return $this->get('/invoice/organizations/' . rawurlencode($organizationId) . '/customers', $this->sanitizeListQuery($query));
    }

    public function getCustomer(string $organizationId, string $customerId): array
    {
        return $this->get('/invoice/organizations/' . rawurlencode($organizationId) . '/customers/' . rawurlencode($customerId));
    }

    public function listInvoices(string $organizationId, array $query = []): array
    {
        return $this->get('/invoice/organizations/' . rawurlencode($organizationId) . '/invoices', $this->sanitizeListQuery($query));
    }

    public function getInvoice(string $organizationId, string $invoiceId): array
    {
        return $this->get('/invoice/organizations/' . rawurlencode($organizationId) . '/invoices/' . rawurlencode($invoiceId));
    }

    public function createInvoiceDraft(string $organizationId, array $invoicePayload): array
    {
        $route = $this->config->createInvoiceDraftRoute();
        if ($route === '') {
            return [
                'status' => 501,
                'ok' => false,
                'body' => [
                    'error' => 'create_invoice_draft_route_not_configured',
                    'message' => 'Set CONTA_ROUTE_CREATE_INVOICE_DRAFT after verifying the correct route in Conta Swagger.',
                ],
            ];
        }

        $path = str_replace('{orgId}', rawurlencode($organizationId), $route);
        return $this->post($path, $invoicePayload);
    }

    private function request(string $method, string $path, ?array $body = null, array $query = []): array
    {
        if ($this->config->apiKey() === '') {
            return [
                'status' => 500,
                'ok' => false,
                'body' => [
                    'error' => 'conta_api_key_missing',
                    'message' => 'Conta API key is not configured server-side.',
                ],
            ];
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
}
