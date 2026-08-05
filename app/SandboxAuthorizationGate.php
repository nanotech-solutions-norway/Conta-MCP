<?php

declare(strict_types=1);

final class SandboxAuthorizationGate
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
            'organizationId' => $organizationId,
            'payloadHash' => strtolower($payloadHash),
            'method' => strtoupper($method),
            'path' => $path,
        ];
        foreach ($expected as $key => $value) {
            if (!isset($packet[$key]) || !is_string($packet[$key]) || !hash_equals($value, $key === 'payloadHash' ? strtolower($packet[$key]) : $packet[$key])) {
                throw new RuntimeException('sandbox_authorization_' . $key . '_mismatch');
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
                'expires_at' => $packet['expiresAt'],
            ];
        } catch (Throwable $e) {
            return ['ready' => false, 'error' => $e->getMessage()];
        }
    }

    private function loadPacket(): array
    {
        $path = $this->config->sandboxAuthorizationPath();
        if ($path === '' || !is_file($path)) {
            throw new RuntimeException('sandbox_authorization_packet_missing');
        }
        $raw = file_get_contents($path);
        $packet = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($packet)) {
            throw new RuntimeException('sandbox_authorization_packet_invalid');
        }
        return $packet;
    }

    private function validateCommon(array $packet): void
    {
        foreach (['authorizationId', 'candidateId', 'action', 'environment', 'organizationId', 'payloadHash', 'method', 'path', 'notBefore', 'expiresAt'] as $key) {
            if (!isset($packet[$key]) || !is_string($packet[$key]) || trim($packet[$key]) === '') {
                throw new RuntimeException('sandbox_authorization_missing_' . $key);
            }
        }
        if (($packet['status'] ?? null) !== 'APPROVED') {
            throw new RuntimeException('sandbox_authorization_not_approved');
        }
        if (($packet['candidateId'] ?? null) !== WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2) {
            throw new RuntimeException('sandbox_authorization_candidate_mismatch');
        }
        if (($packet['environment'] ?? null) !== 'sandbox' || $this->config->environment() !== 'sandbox') {
            throw new RuntimeException('sandbox_authorization_environment_mismatch');
        }
        if (($packet['maxProviderMutations'] ?? null) !== 1) {
            throw new RuntimeException('sandbox_authorization_one_call_required');
        }
        if (($packet['readbackRequired'] ?? null) !== true) {
            throw new RuntimeException('sandbox_authorization_readback_required');
        }
        if (($packet['providerRouteValidated'] ?? null) !== true || ($packet['testCompanyValidated'] ?? null) !== true) {
            throw new RuntimeException('sandbox_authorization_evidence_incomplete');
        }

        $notBefore = strtotime($packet['notBefore']);
        $expiresAt = strtotime($packet['expiresAt']);
        $now = time();
        if ($notBefore === false || $expiresAt === false) {
            throw new RuntimeException('sandbox_authorization_time_invalid');
        }
        if ($notBefore > $now) {
            throw new RuntimeException('sandbox_authorization_not_yet_valid');
        }
        if ($expiresAt <= $now) {
            throw new RuntimeException('sandbox_authorization_expired');
        }
    }
}
