<?php

declare(strict_types=1);

final class WritePolicy
{
    public const ACTION_INVOICE_DRAFT_CREATE_V2 = 'invoice_draft_create_v2';
    public const TOOL_PREVIEW_INVOICE_DRAFT = 'conta_preview_invoice_draft';
    public const TOOL_EXECUTE_INVOICE_DRAFT = 'conta_create_invoice_draft';

    public function __construct(
        private readonly Config $config,
        private readonly ApprovalEnvelopeVerifier $approvalVerifier,
        private readonly ReleaseManifestGuard $releaseManifestGuard,
        private readonly WriteKillSwitch $killSwitch,
        private readonly SandboxAuthorizationGate $sandboxAuthorizationGate
    ) {
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
        try {
            $this->assertExecutionGateOpen(self::ACTION_INVOICE_DRAFT_CREATE_V2);
            return true;
        } catch (Throwable) {
            return false;
        }
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
            'signed_approvals_required' => $this->config->requireSignedApprovals(),
            'release_manifest' => $this->releaseManifestGuard->status(),
            'kill_switch' => $this->killSwitch->status(self::ACTION_INVOICE_DRAFT_CREATE_V2),
            'sandbox_authorization' => $this->sandboxAuthorizationGate->status(),
            'readback_route_configured' => $this->config->readbackInvoiceDraftRoute() !== '',
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

        $manifestHash = $this->assertExecutionGateOpen($action);
        $this->assertActionAllowed($action);
        $this->assertOrganizationAllowed($organizationId);
        $this->assertRouteMatches($organizationId, $path);
        $this->approvalVerifier->verify($approval);
        $approvalData = $this->validateApproval($approval, $action, $organizationId, $payloadHash);
        $authorizationId = $this->sandboxAuthorizationGate->authorize(
            $action,
            $organizationId,
            $payloadHash,
            $method,
            $path
        );

        return new WriteDispatchPermit(
            action: $action,
            method: $method,
            path: $path,
            organizationId: $organizationId,
            payloadHash: $payloadHash,
            idempotencyKey: $approvalData['idempotencyKey'],
            approvalId: $approvalData['approvalId'],
            approvalNonce: $approvalData['nonce'],
            authorizationId: $authorizationId,
            policyVersion: $this->config->writePolicyVersion(),
            releaseManifestHash: $manifestHash
        );
    }

    public function assertDispatchPermit(WriteDispatchPermit $permit, string $method, string $path): void
    {
        $manifestHash = $this->assertExecutionGateOpen($permit->action);
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
        if (!hash_equals($manifestHash, $permit->releaseManifestHash)) {
            throw new RuntimeException('write_dispatch_release_manifest_changed');
        }
        $this->assertOrganizationAllowed($permit->organizationId);
        $this->assertRouteMatches($permit->organizationId, $path);
        $authorizationId = $this->sandboxAuthorizationGate->authorize(
            $permit->action,
            $permit->organizationId,
            $permit->payloadHash,
            $permit->method,
            $permit->path
        );
        if (!hash_equals($authorizationId, $permit->authorizationId)) {
            throw new RuntimeException('write_dispatch_authorization_changed');
        }
    }

    private function assertExecutionGateOpen(string $action): string
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
        if ($this->config->environment() === 'production') {
            if (!$this->config->productionWriteApproved()) {
                throw new RuntimeException('production_write_not_approved');
            }
            throw new RuntimeException('production_write_program_not_implemented');
        }
        if ($this->config->requireSignedApprovals() && $this->config->approvalSigningKey() === '') {
            throw new RuntimeException('approval_signing_key_missing');
        }
        if ($this->config->readbackInvoiceDraftRoute() === '') {
            throw new RuntimeException('invoice_draft_readback_route_not_configured');
        }

        $this->killSwitch->assertActionOpen($action);
        $manifestHash = $this->releaseManifestGuard->assertApproved();
        $this->sandboxAuthorizationGate->assertPacketReady();
        return $manifestHash;
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
