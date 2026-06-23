<?php

declare(strict_types=1);

final class ContaTools
{
    public function __construct(
        private readonly Config $config,
        private readonly ContaClient $contaClient,
        private readonly AuditLogger $auditLogger
    ) {
    }

    public function listTools(): array
    {
        $writeEnabled = $this->config->writeToolsEnabled();

        return [
            $this->tool('conta_health_check', 'Conta Health Check', 'Check MCP configuration and Conta API readiness without exposing secrets.', [
                'type' => 'object',
                'properties' => [
                    'checkConta' => ['type' => 'boolean', 'description' => 'If true, attempts a lightweight Conta API call.'],
                ],
            ]),
            $this->tool('conta_list_organizations', 'List Conta Organizations', 'List organizations available to the configured Conta API key. Verify route in Conta Swagger before production.', [
                'type' => 'object',
                'properties' => new stdClass(),
            ]),
            $this->tool('conta_list_customers', 'List Conta Customers', 'Search/list customers for an organization. Uses Conta list parameters q, hits, page and sort.', [
                'type' => 'object',
                'properties' => [
                    'organizationId' => ['type' => 'string', 'description' => 'Optional Conta organization ID. Uses default if omitted.'],
                    'q' => ['type' => 'string', 'description' => 'Optional full-text search query.'],
                    'hits' => ['type' => 'integer', 'description' => 'Optional page size.'],
                    'page' => ['type' => 'integer', 'description' => 'Optional page number, zero-based in Conta examples.'],
                    'sort' => ['type' => 'string', 'description' => 'Optional sort field.'],
                ],
            ]),
            $this->tool('conta_get_customer', 'Get Conta Customer', 'Retrieve a single Conta customer by ID.', [
                'type' => 'object',
                'required' => ['customerId'],
                'properties' => [
                    'organizationId' => ['type' => 'string', 'description' => 'Optional Conta organization ID. Uses default if omitted.'],
                    'customerId' => ['type' => 'string', 'description' => 'Conta customer ID.'],
                ],
            ]),
            $this->tool('conta_list_invoices', 'List Conta Invoices', 'Search/list invoices for an organization. Uses Conta list parameters q, hits, page and sort.', [
                'type' => 'object',
                'properties' => [
                    'organizationId' => ['type' => 'string', 'description' => 'Optional Conta organization ID. Uses default if omitted.'],
                    'q' => ['type' => 'string', 'description' => 'Optional full-text search query.'],
                    'hits' => ['type' => 'integer', 'description' => 'Optional page size.'],
                    'page' => ['type' => 'integer', 'description' => 'Optional page number, zero-based in Conta examples.'],
                    'sort' => ['type' => 'string', 'description' => 'Optional sort field.'],
                ],
            ]),
            $this->tool('conta_get_invoice', 'Get Conta Invoice', 'Retrieve a single Conta invoice by ID.', [
                'type' => 'object',
                'required' => ['invoiceId'],
                'properties' => [
                    'organizationId' => ['type' => 'string', 'description' => 'Optional Conta organization ID. Uses default if omitted.'],
                    'invoiceId' => ['type' => 'string', 'description' => 'Conta invoice ID.'],
                ],
            ]),
            $this->tool('conta_create_invoice_draft', 'Create Conta Invoice Draft', $writeEnabled
                ? 'Create an invoice draft. Requires route verification and server-side write-tool enablement.'
                : 'Disabled by policy. Enable only after sandbox validation and explicit approval.', [
                'type' => 'object',
                'required' => ['invoice'],
                'properties' => [
                    'organizationId' => ['type' => 'string', 'description' => 'Optional Conta organization ID. Uses default if omitted.'],
                    'invoice' => ['type' => 'object', 'description' => 'Conta invoice draft payload matching Conta Swagger.'],
                ],
            ]),
        ];
    }

    public function call(string $name, array $arguments): array
    {
        $this->auditLogger->record('tool_call_started', ['tool' => $name]);

        try {
            $result = match ($name) {
                'conta_health_check' => $this->healthCheck((bool) ($arguments['checkConta'] ?? false)),
                'conta_list_organizations' => $this->contaClient->listOrganizations(),
                'conta_list_customers' => $this->contaClient->listCustomers($this->requireOrgId($arguments), $this->listQuery($arguments)),
                'conta_get_customer' => $this->contaClient->getCustomer($this->requireOrgId($arguments), $this->requireString($arguments, 'customerId')),
                'conta_list_invoices' => $this->contaClient->listInvoices($this->requireOrgId($arguments), $this->listQuery($arguments)),
                'conta_get_invoice' => $this->contaClient->getInvoice($this->requireOrgId($arguments), $this->requireString($arguments, 'invoiceId')),
                'conta_create_invoice_draft' => $this->createInvoiceDraft($arguments),
                default => throw new InvalidArgumentException('Unknown tool: ' . $name),
            };

            $this->auditLogger->record('tool_call_completed', [
                'tool' => $name,
                'status' => $result['status'] ?? null,
                'ok' => $result['ok'] ?? null,
            ]);
            return $result;
        } catch (Throwable $e) {
            $this->auditLogger->record('tool_call_failed', [
                'tool' => $name,
                'error' => $e->getMessage(),
            ]);
            return [
                'status' => 400,
                'ok' => false,
                'body' => [
                    'error' => 'tool_call_failed',
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    private function healthCheck(bool $checkConta): array
    {
        $status = $this->config->publicStatus();

        if (!$checkConta) {
            return ['status' => 200, 'ok' => true, 'body' => ['mcp' => 'ok', 'config' => $status]];
        }

        $conta = $this->contaClient->listOrganizations();
        return ['status' => $conta['status'], 'ok' => $conta['ok'], 'body' => ['mcp' => 'ok', 'config' => $status, 'conta' => $conta['body']]];
    }

    private function createInvoiceDraft(array $arguments): array
    {
        if (!$this->config->writeToolsEnabled()) {
            return [
                'status' => 403,
                'ok' => false,
                'body' => [
                    'error' => 'write_tools_disabled',
                    'message' => 'Draft/write tools are disabled by server-side policy.',
                ],
            ];
        }

        $invoice = $arguments['invoice'] ?? null;
        if (!is_array($invoice)) {
            throw new InvalidArgumentException('invoice must be an object matching Conta Swagger.');
        }

        return $this->contaClient->createInvoiceDraft($this->requireOrgId($arguments), $invoice);
    }

    private function requireOrgId(array $arguments): string
    {
        $orgId = $this->config->organizationId(isset($arguments['organizationId']) ? (string) $arguments['organizationId'] : null);
        if ($orgId === '') {
            throw new InvalidArgumentException('organizationId is required or must be configured as default_organization_id.');
        }
        return $orgId;
    }

    private function requireString(array $arguments, string $key): string
    {
        $value = trim((string) ($arguments[$key] ?? ''));
        if ($value === '') {
            throw new InvalidArgumentException($key . ' is required.');
        }
        return $value;
    }

    private function listQuery(array $arguments): array
    {
        return array_intersect_key($arguments, array_flip(['q', 'hits', 'page', 'sort']));
    }

    private function tool(string $name, string $title, string $description, array $inputSchema): array
    {
        return [
            'name' => $name,
            'title' => $title,
            'description' => $description,
            'inputSchema' => $inputSchema,
        ];
    }
}
