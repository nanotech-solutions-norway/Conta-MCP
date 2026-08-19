<?php

declare(strict_types=1);

final class ProductionAuthorizationGate
{
    public function __construct(
        private readonly Config $config,
        private readonly ApprovalEnvelopeVerifier $signatureVerifier
    ) {
    }

    public function assertPacketReady(): void
    {
        $packet = $this->loadPacket();
        $this->signatureVerifier->verify($packet);
        $this->validateCommon($packet);
    }

    public function authorize(string $action, string $organizationId, string $payloadHash, string $method, string $path): string
    {
        $packet = $this->loadPacket();
        $this->signatureVerifier->verify($packet);
        $this->validateCommon($packet);

        $expected = [
            'action' => $action,
            'organizationIdHash' => hash('sha256', $organizationId),
            'payloadHash' => strtolower($payloadHash),
            'method' => strtoupper($method),
            'pathHash' => hash('sha256', $path),
            'decisionPacketSha256' => $this->config->productionDecisionPacketSha256(),
        ];

        foreach ($expected as $key => $value) {
            $actual = isset($packet[$key]) && is_string($packet[$key]) ? trim($packet[$key]) : '';
            if ($key === 'payloadHash' || $key === 'organizationIdHash' || $key === 'pathHash' || $key === 'decisionPacketSha256') {
                $actual = strtolower($actual);
            }
            if ($actual === '' || !hash_equals($value, $actual)) {
                throw new RuntimeException('production_authorization_' . $key . '_mismatch');
            }
        }

        return trim((string) $packet['authorizationId']);
    }

    public function status(): array
    {
        try {
            $packet = $this->loadPacket();
            $this->signatureVerifier->verify($packet);
            $this->validateCommon($packet);
            return [
                'ready' => true,
                'error' => null,
                'authorization_id_hash' => hash('sha256', (string) $packet['authorizationId']),
                'organization_reference_hash_bound' => true,
                'decision_packet_hash_bound' => true,
                'expires_at' => $packet['expiresAt'],
            ];
        } catch (Throwable $e) {
            return ['ready' => false, 'error' => $e->getMessage()];
        }
    }

    private function loadPacket(): array
    {
        $path = $this->config->productionAuthorizationPath();
        if ($path === '' || !is_file($path)) {
            throw new RuntimeException('production_authorization_packet_missing');
        }

        $raw = file_get_contents($path);
        $packet = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($packet)) {
            throw new RuntimeException('production_authorization_packet_invalid');
        }
        return $packet;
    }

    private function validateCommon(array $packet): void
    {
        foreach ([
            'authorizationId', 'candidateId', 'action', 'environment', 'organizationIdHash',
            'payloadHash', 'method', 'pathHash', 'decisionPacketSha256', 'notBefore', 'expiresAt',
        ] as $key) {
            if (!isset($packet[$key]) || !is_string($packet[$key]) || trim($packet[$key]) === '') {
                throw new RuntimeException('production_authorization_missing_' . $key);
            }
        }

        if (($packet['status'] ?? null) !== 'APPROVED') {
            throw new RuntimeException('production_authorization_not_approved');
        }
        if (($packet['candidateId'] ?? null) !== WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2) {
            throw new RuntimeException('production_authorization_candidate_mismatch');
        }
        if (($packet['environment'] ?? null) !== 'production' || $this->config->environment() !== 'production') {
            throw new RuntimeException('production_authorization_environment_mismatch');
        }
        if (($packet['maxProviderMutations'] ?? null) !== 1) {
            throw new RuntimeException('production_authorization_one_call_required');
        }
        if (($packet['automaticRetry'] ?? null) !== false) {
            throw new RuntimeException('production_authorization_no_retry_required');
        }
        if (($packet['readbackRequired'] ?? null) !== true) {
            throw new RuntimeException('production_authorization_readback_required');
        }
        if (($packet['releaseApproved'] ?? null) !== true) {
            throw new RuntimeException('production_authorization_release_not_approved');
        }

        $orgHash = strtolower(trim((string) $packet['organizationIdHash']));
        $configuredOrgHash = $this->config->productionOrganizationReferenceHash();
        if (!preg_match('/^[a-f0-9]{64}$/', $configuredOrgHash)) {
            throw new RuntimeException('production_organization_reference_hash_missing');
        }
        if (!preg_match('/^[a-f0-9]{64}$/', $orgHash) || !hash_equals($configuredOrgHash, $orgHash)) {
            throw new RuntimeException('production_organization_reference_hash_mismatch');
        }

        $decisionHash = strtolower(trim((string) $packet['decisionPacketSha256']));
        $configuredDecisionHash = $this->config->productionDecisionPacketSha256();
        if (!preg_match('/^[a-f0-9]{64}$/', $configuredDecisionHash)) {
            throw new RuntimeException('production_decision_packet_hash_missing');
        }
        if (!preg_match('/^[a-f0-9]{64}$/', $decisionHash) || !hash_equals($configuredDecisionHash, $decisionHash)) {
            throw new RuntimeException('production_decision_packet_hash_mismatch');
        }

        $notBefore = strtotime($packet['notBefore']);
        $expiresAt = strtotime($packet['expiresAt']);
        $now = time();
        if ($notBefore === false || $expiresAt === false) {
            throw new RuntimeException('production_authorization_time_invalid');
        }
        if ($notBefore > $now) {
            throw new RuntimeException('production_authorization_not_yet_valid');
        }
        if ($expiresAt <= $now) {
            throw new RuntimeException('production_authorization_expired');
        }
        if (($expiresAt - $notBefore) > $this->config->approvalMaxTtlSeconds()) {
            throw new RuntimeException('production_authorization_ttl_exceeds_policy');
        }
    }
}
