<?php

declare(strict_types=1);

final class WritePolicy
{
    public const ACTION_INVOICE_DRAFT_CREATE_V2 = 'invoice_draft_create_v2';
    public const TOOL_PREVIEW_INVOICE_DRAFT = 'conta_preview_invoice_draft';
    public const TOOL_EXECUTE_INVOICE_DRAFT = 'conta_create_invoice_draft';

    public function __construct(private readonly Config $config)
    {
    }

    public function previewEnabled(): bool
    {
        return $this->config->writePreviewEnabled();
    }

    public function executionToolVisible(): bool
    {
        return $this->effectiveExecutionEnabled();
    }

    public function effectiveExecutionEnabled(): bool
    {
        if (!$this->config->writeToolsEnabled()) {
            return false;
        }
        if ($this->config->runtimeWriteBlocked()) {
            return false;
        }
        if (!$this->config->executionAllowed()) {
            return false;
        }
        if ($this->config->environment() === 'production' && !$this->config->productionWriteApproved()) {
            return false;
        }
        return true;
    }

    public function effectiveState(): array
    {
        return [
            'policy_version' => $this->config->writePolicyVersion(),
            'environment' => $this->config->environment(),
            'preview_enabled' => $this->previewEnabled(),
            'write_tools_enabled' => $this->config->writeToolsEnabled(),
            'runtime_write_blocked' => $this->config->runtimeWriteBlocked(),
            'execution_allowed' => $this->config->executionAllowed(),
            'production_write_approved' => $this->config->productionWriteApproved(),
            'effective_execution_enabled' => $this->effectiveExecutionEnabled(),
            'allowed_write_actions' => $this->config->allowedWriteActions(),
            'allowed_write_organization_count' => count($this->config->allowedWriteOrganizationIds()),
        ];
    }

    public function authorizeInvoiceDraftCreate(
        string $organizationId,
        string $path,
        string $payloadHash,
        array $approval
    ): WriteDispatchPermit {
        $action = self::ACTION_INVOICE_DRAFT_CREATE_V2;
        $method = 'POST';

        $this->assertExecutionGateOpen();
        $this->assertActionAllowed($action);
        $this->assertOrganizationAllowed($organizationId);
        $this->assertRouteMatches($organizationId, $path);
        $approvalData = $this->validateApproval($approval, $action, $organizationId, $payloadHash);

        return new WriteDispatchPermit(
            action: $action,
            method: $method,
            path: $path,
            organizationId: $organizationId,
            payloadHash: $payloadHash,
            idempotencyKey: $approvalData['idempotencyKey'],
            approvalId: $approvalData['approvalId'],
            approvalNonce: $approvalData['nonce'],
            policyVersion: $this->config->writePolicyVersion()
        );
    }

    public function assertDispatchPermit(WriteDispatchPermit $permit, string $method, string $path): void
    {
        $this->assertExecutionGateOpen();
        $this->assertActionAllowed($permit->action);

        if (strtoupper($method) !== $permit->method) {
            throw new RuntimeException('write_dispatch_method_mismatch');
        }
        if ($path !== $permit->path) {
            throw new RuntimeException('write_dispatch_path_mismatch');
        }
        if ($permit->policyVersion !== $this->config->writePolicyVersion()) {
            throw new RuntimeException('write_dispatch_policy_version_mismatch');
        }
        $this->assertOrganizationAllowed($permit->organizationId);
        $this->assertRouteMatches($permit->organizationId, $path);
    }

    private function assertExecutionGateOpen(): void
    {
        if (!$this->config->writeToolsEnabled()) {
            throw new RuntimeException('write_tools_disabled');
        }
        if ($this->config->runtimeWriteBlocked()) {
            throw new RuntimeException('runtime_write_blocked');
        }
        if (!$this->config->executionAllowed()) {
            throw new RuntimeException('execution_not_authorized');
        }
        if ($this->config->environment() === 'production' && !$this->config->productionWriteApproved()) {
            throw new RuntimeException('production_write_not_approved');
        }
    }

    private function assertActionAllowed(string $action): void
    {
        if (!in_array($action, $this->config->allowedWriteActions(), true)) {
            throw new RuntimeException('write_action_not_allowlisted');
        }
    }

    private function assertOrganizationAllowed(string $organizationId): void
    {
        $allowed = $this->config->allowedWriteOrganizationIds();
        if ($organizationId === '' || $allowed === [] || !in_array($organizationId, $allowed, true)) {
            throw new RuntimeException('write_organization_not_allowlisted');
        }
    }

    private function assertRouteMatches(string $organizationId, string $path): void
    {
        $template = $this->config->createInvoiceDraftRoute();
        if ($template === '') {
            throw new RuntimeException('create_invoice_draft_route_not_configured');
        }

        $encodedOrgId = rawurlencode($organizationId);
        $expected = str_replace(['{orgId}', '{opContextOrgId}'], $encodedOrgId, $template);
        if ($path !== $expected) {
            throw new RuntimeException('write_route_not_allowlisted');
        }
    }

    private function validateApproval(
        array $approval,
        string $action,
        string $organizationId,
        string $payloadHash
    ): array {
        $requiredStrings = [
            'approvalId', 'approvedBy', 'action', 'organizationId', 'environment',
            'payloadHash', 'issuedAt', 'expiresAt', 'nonce', 'idempotencyKey',
        ];

        foreach ($requiredStrings as $key) {
            if (!isset($approval[$key]) || !is_string($approval[$key]) || trim($approval[$key]) === '') {
                throw new RuntimeException('approval_missing_' . $key);
            }
        }

        if (($approval['approved'] ?? false) !== true) {
            throw new RuntimeException('approval_not_granted');
        }
        if (($approval['oneUse'] ?? false) !== true) {
            throw new RuntimeException('approval_must_be_one_use');
        }
        if ($approval['action'] !== $action) {
            throw new RuntimeException('approval_action_mismatch');
        }
        if ($approval['organizationId'] !== $organizationId) {
            throw new RuntimeException('approval_organization_mismatch');
        }
        if ($approval['environment'] !== $this->config->environment()) {
            throw new RuntimeException('approval_environment_mismatch');
        }
        if (!hash_equals($payloadHash, strtolower($approval['payloadHash']))) {
            throw new RuntimeException('approval_payload_hash_mismatch');
        }
        if (!preg_match('/^[a-f0-9]{64}$/', strtolower($approval['payloadHash']))) {
            throw new RuntimeException('approval_payload_hash_invalid');
        }
        if (strlen($approval['nonce']) < 16) {
            throw new RuntimeException('approval_nonce_too_short');
        }
        if (strlen($approval['idempotencyKey']) < 16) {
            throw new RuntimeException('idempotency_key_too_short');
        }

        $issuedAt = strtotime($approval['issuedAt']);
        $expiresAt = strtotime($approval['expiresAt']);
        $now = time();
        if ($issuedAt === false || $expiresAt === false) {
            throw new RuntimeException('approval_time_invalid');
        }
        if ($issuedAt > $now + 60) {
            throw new RuntimeException('approval_issued_in_future');
        }
        if ($expiresAt <= $now) {
            throw new RuntimeException('approval_expired');
        }
        if (($expiresAt - $issuedAt) > $this->config->approvalMaxTtlSeconds()) {
            throw new RuntimeException('approval_ttl_exceeds_policy');
        }

        return [
            'approvalId' => trim($approval['approvalId']),
            'nonce' => trim($approval['nonce']),
            'idempotencyKey' => trim($approval['idempotencyKey']),
        ];
    }
}
