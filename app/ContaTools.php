<?php

declare(strict_types=1);

final class ContaTools
{
    public function __construct(
        private readonly Config $config,
        private readonly ContaClient $contaClient,
        private readonly AuditLogger $auditLogger,
        private readonly WritePolicy $writePolicy,
        private readonly InvoiceDraftPreview $invoiceDraftPreview
    ) {
    }

    public function listTools(): array
    {
        $tools = [
            $this->tool('conta_health_check', 'Conta Health Check', 'Check MCP configuration and Conta API readiness without exposing secrets.', [
                'type' => 'object',
                'properties' => [
                    'checkConta' => ['type' => 'boolean', 'description' => 'If true, attempts a lightweight Conta API call.'],
                ],
            ]),
            $this->tool('conta_list_organizations', 'List Conta Organizations', 'List organizations available to the configured Conta API key.', [
                'type' => 'object',
                'properties' => new stdClass(),
            ]),
            $this->tool('conta_list_customers', 'List Conta Customers', 'Search/list customers for an organization.', [
                'type' => 'object',
                'properties' => [
                    'organizationId' => ['type' => 'string', 'description' => 'Optional Conta organization ID. Uses default if omitted.'],
                    'q' => ['type' => 'string', 'description' => 'Optional full-text search query.'],
                    'hits' => ['type' => 'integer', 'description' => 'Optional page size.'],
                    'page' => ['type' => 'integer', 'description' => 'Optional page number.'],
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
            $this->tool('conta_list_invoices', 'List Conta Invoices', 'Search/list invoices for an organization.', [
                'type' => 'object',
                'properties' => [
                    'organizationId' => ['type' => 'string', 'description' => 'Optional Conta organization ID. Uses default if omitted.'],
                    'q' => ['type' => 'string', 'description' => 'Optional full-text search query.'],
                    'hits' => ['type' => 'integer', 'description' => 'Optional page size.'],
                    'page' => ['type' => 'integer', 'description' => 'Optional page number.'],
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
        ];

        if ($this->writePolicy->previewEnabled()) {
            $tools[] = $this->tool(
                WritePolicy::TOOL_PREVIEW_INVOICE_DRAFT,
                'Preview Conta Invoice Draft',
                'Normalize and hash an invoice draft proposal without calling Conta or mutating provider state.',
                [
                    'type' => 'object',
                    'required' => ['invoice'],
                    'properties' => [
                        'organizationId' => ['type' => 'string', 'description' => 'Optional Conta organization ID. Uses default if omitted.'],
                        'invoice' => ['type' => 'object', 'description' => 'Proposed invoice draft payload matching the verified Conta schema.'],
                    ],
                ]
            );
        }

        if ($this->writePolicy->executionToolVisible()) {
            $tools[] = $this->tool(
                WritePolicy::TOOL_EXECUTE_INVOICE_DRAFT,
                'Create Conta Invoice Draft',
                'Create one allowlisted invoice draft using a one-use approval envelope and idempotency key.',
                [
                    'type' => 'object',
                    'required' => ['invoice', 'approval'],
                    'properties' => [
                        'organizationId' => ['type' => 'string', 'description' => 'Optional Conta organization ID. Uses default if omitted.'],
                        'invoice' => ['type' => 'object', 'description' => 'Approved invoice draft payload matching the verified Conta schema.'],
                        'approval' => ['type' => 'object', 'description' => 'One-use approval envelope bound to action, organization, environment and payload hash.'],
                    ],
                ]
            );
        }

        return $tools;
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
                WritePolicy::TOOL_PREVIEW_INVOICE_DRAFT => $this->previewInvoiceDraft($arguments),
                WritePolicy::TOOL_EXECUTE_INVOICE_DRAFT => $this->createInvoiceDraft($arguments),
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
        $status['effective_write_policy'] = $this->writePolicy->effectiveState();

        if (!$checkConta) {
            return ['status' => 200, 'ok' => true, 'body' => ['mcp' => 'ok', 'config' => $status]];
        }

        $conta = $this->contaClient->listOrganizations();
        return ['status' => $conta['status'], 'ok' => $conta['ok'], 'body' => ['mcp' => 'ok', 'config' => $status, 'conta' => $conta['body']]];
    }

    private function previewInvoiceDraft(array $arguments): array
    {
        $invoice = $arguments['invoice'] ?? null;
        if (!is_array($invoice)) {
            throw new InvalidArgumentException('invoice must be an object matching the verified Conta schema.');
        }

        return [
            'status' => 200,
            'ok' => true,
            'body' => $this->invoiceDraftPreview->build($this->requireOrgId($arguments), $invoice),
        ];
    }

    private function createInvoiceDraft(array $arguments): array
    {
        $invoice = $arguments['invoice'] ?? null;
        $approval = $arguments['approval'] ?? null;
        if (!is_array($invoice)) {
            throw new InvalidArgumentException('invoice must be an object matching the verified Conta schema.');
        }
        if (!is_array($approval)) {
            throw new InvalidArgumentException('approval must be a one-use approval envelope.');
        }

        return $this->contaClient->createInvoiceDraft($this->requireOrgId($arguments), $invoice, $approval);
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
