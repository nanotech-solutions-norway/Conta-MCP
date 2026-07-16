<?php

declare(strict_types=1);

final readonly class WriteDispatchPermit
{
    public function __construct(
        public string $action,
        public string $method,
        public string $path,
        public string $organizationId,
        public string $payloadHash,
        public string $idempotencyKey,
        public string $approvalId,
        public string $approvalNonce,
        public string $authorizationId,
        public string $policyVersion,
        public string $releaseManifestHash
    ) {
    }
}
