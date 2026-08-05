<?php

declare(strict_types=1);

final class ApprovalEnvelopeVerifier
{
    public function __construct(private readonly Config $config)
    {
    }

    public function sign(array $document): array
    {
        $key = $this->config->approvalSigningKey();
        if ($key === '') {
            throw new RuntimeException('approval_signing_key_missing');
        }

        unset($document['signature']);
        $document['signatureAlgorithm'] = 'HMAC-SHA256';
        $document['keyId'] = $this->config->approvalKeyId();
        $document['signature'] = hash_hmac('sha256', $this->canonicalJson($document), $key);
        return $document;
    }

    public function verify(array $document): void
    {
        if (!$this->config->requireSignedApprovals()) {
            return;
        }

        foreach (['signature', 'signatureAlgorithm', 'keyId'] as $key) {
            if (!isset($document[$key]) || !is_string($document[$key]) || trim($document[$key]) === '') {
                throw new RuntimeException('approval_signature_missing_' . $key);
            }
        }
        if ($document['signatureAlgorithm'] !== 'HMAC-SHA256') {
            throw new RuntimeException('approval_signature_algorithm_unsupported');
        }
        if (!hash_equals($this->config->approvalKeyId(), $document['keyId'])) {
            throw new RuntimeException('approval_key_id_mismatch');
        }

        $key = $this->config->approvalSigningKey();
        if ($key === '') {
            throw new RuntimeException('approval_signing_key_missing');
        }

        $provided = strtolower(trim($document['signature']));
        if (!preg_match('/^[a-f0-9]{64}$/', $provided)) {
            throw new RuntimeException('approval_signature_invalid');
        }

        $unsigned = $document;
        unset($unsigned['signature']);
        $expected = hash_hmac('sha256', $this->canonicalJson($unsigned), $key);
        if (!hash_equals($expected, $provided)) {
            throw new RuntimeException('approval_signature_mismatch');
        }
    }

    public function canonicalJson(array $document): string
    {
        $canonical = self::canonicalize($document);
        $json = json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        if ($json === false) {
            throw new RuntimeException('approval_canonicalization_failed');
        }
        return $json;
    }

    private static function canonicalize(array $value): array
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
}
