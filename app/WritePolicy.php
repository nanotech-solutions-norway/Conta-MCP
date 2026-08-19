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
        private readonly SandboxAuthorizationGate $sandboxAuthorizationGate,
        private readonly ProductionAuthorizationGate $productionAuthorizationGate
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
            'authorization_gate' => $this->authorizationGate()->status(),
            'readback_route_configured' => $this->config->readbackInvoiceDraftRoute() !== '',
            'production_limits' => [
                'maximum_lines' => $this->config->maxInvoiceDraftLines(),
                'maximum_line_amount' => $this->config->maxInvoiceDraftLineAmount(),
                'maximum_draft_total' => $this->config->maxInvoiceDraftTotal(),
                'maximum_provider_mutations' => 1,
                'automatic_retry' => false,
            ],
        ];
    }

    public function assertInvoiceDraftPayloadLimits(array $payload): void
    {
        if ($this->config->environment() !== 'production') {
            return;
        }

        $lines = $payload['invoiceDraftLines'] ?? null;
        if (!is_array($lines) || !array_is_list($lines) || $lines === []) {
            throw new RuntimeException('production_invoice_draft_lines_missing');
        }
        if (count($lines) > $this->config->maxInvoiceDraftLines()) {
            throw new RuntimeException('production_invoice_draft_line_limit_exceeded');
        }
        if (($payload['invoiceCurrency'] ?? null) !== 'NOK') {
            throw new RuntimeException('production_invoice_draft_currency_not_allowed');
        }

        $total = 0.0;
        foreach ($lines as $line) {
            if (!is_array($line)) {
                throw new RuntimeException('production_invoice_draft_line_invalid');
            }
            foreach (['price', 'quantity'] as $field) {
                if (!isset($line[$field]) || !is_numeric($line[$field])) {
                    throw new RuntimeException('production_invoice_draft_line_' . $field . '_invalid');
                }
            }

            $price = (float) $line['price'];
            $quantity = (float) $line['quantity'];
            $discount = isset($line['discount']) && is_numeric($line['discount']) ? (float) $line['discount'] : 0.0;
            if ($price < 0 || $quantity <= 0 || $discount < 0 || $discount > 100) {
                throw new RuntimeException('production_invoice_draft_line_numeric_bounds_invalid');
            }

            $lineAmount = $price * $quantity * (1 - ($discount / 100));
            if ($lineAmount > $this->config->maxInvoiceDraftLineAmount() + 0.000001) {
                throw new RuntimeException('production_invoice_draft_line_amount_exceeded');
            }
            $total += $lineAmount;
        }

        if ($total > $this->config->maxInvoiceDraftTotal() + 0.000001) {
            throw new RuntimeException('production_invoice_draft_total_exceeded');
        }
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
        $this->assertProductionOrganizationBinding($organizationId);
        $this->assertRouteMatches($organizationId, $path);
        $this->approvalVerifier->verify($approval);
        $approvalData = $this->validateApproval($approval, $action, $organizationId, $payloadHash, $method, $path);
        $authorizationId = $this->authorizationGate()->authorize(
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
        $this->assertProductionOrganizationBinding($permit->organizationId);
        $this->assertRouteMatches($permit->organizationId, $path);
        $authorizationId = $this->authorizationGate()->authorize(
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
        if ($this->config->environment() === 'production' && !$this->config->productionWriteApproved()) {
            throw new RuntimeException('production_write_not_approved');
        }
        if ($this->config->requireSignedApprovals() && $this->config->approvalSigningKey() === '') {
            throw new RuntimeException('approval_signing_key_missing');
        }
        if ($this->config->readbackInvoiceDraftRoute() === '') {
            throw new RuntimeException('invoice_draft_readback_route_not_configured');
        }

        $this->killSwitch->assertActionOpen($action);
        $manifestHash = $this->releaseManifestGuard->assertApproved();
        $this->authorizationGate()->assertPacketReady();
        return $manifestHash;
    }

    private function authorizationGate(): SandboxAuthorizationGate|ProductionAuthorizationGate
    {
        return $this->config->environment() === 'production'
            ? $this->productionAuthorizationGate
            : $this->sandboxAuthorizationGate;
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

    private function assertProductionOrganizationBinding(string $organizationId): void
    {
        if ($this->config->environment() !== 'production') {
            return;
        }

        $expected = $this->config->productionOrganizationReferenceHash();
        if (!preg_match('/^[a-f0-9]{64}$/', $expected)) {
            throw new RuntimeException('production_organization_reference_hash_missing');
        }
        if (!hash_equals($expected, hash('sha256', $organizationId))) {
            throw new RuntimeException('production_organization_reference_hash_mismatch');
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
        string $payloadHash,
        string $method,
        string $path
    ): array {
        $requiredStrings = [
            'approvalId', 'approvedBy', 'action', 'organizationId', 'environment',
            'payloadHash', 'issuedAt', 'expiresAt', 'nonce', 'idempotencyKey',
        ];
        if ($this->config->environment() === 'production') {
            $requiredStrings[] = 'method';
            $requiredStrings[] = 'path';
        }

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
        if ($this->config->environment() === 'production') {
            if (strtoupper($approval['method']) !== $method) {
                throw new RuntimeException('approval_method_mismatch');
            }
            if ($approval['path'] !== $path) {
                throw new RuntimeException('approval_path_mismatch');
            }
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
