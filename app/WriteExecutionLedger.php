<?php

declare(strict_types=1);

final class WriteExecutionLedger
{
    public function __construct(private readonly string $path)
    {
    }

    public function reserve(WriteDispatchPermit $permit): void
    {
        $this->mutate(function (array &$state) use ($permit): void {
            $entries = $state['entries'] ?? [];
            $nonces = $state['approval_nonces'] ?? [];
            $authorizations = $state['authorization_ids'] ?? [];

            if (isset($entries[$permit->idempotencyKey])) {
                $existing = $entries[$permit->idempotencyKey];
                if (($existing['payload_hash'] ?? '') !== $permit->payloadHash) {
                    throw new RuntimeException('idempotency_key_payload_collision');
                }
                throw new RuntimeException('idempotency_key_already_used');
            }
            if (isset($nonces[$permit->approvalNonce])) {
                throw new RuntimeException('approval_nonce_already_used');
            }
            if (isset($authorizations[$permit->authorizationId])) {
                throw new RuntimeException('sandbox_authorization_already_used');
            }

            $entries[$permit->idempotencyKey] = [
                'status' => 'reserved',
                'action' => $permit->action,
                'organization_id_hash' => hash('sha256', $permit->organizationId),
                'payload_hash' => $permit->payloadHash,
                'approval_id_hash' => hash('sha256', $permit->approvalId),
                'authorization_id_hash' => hash('sha256', $permit->authorizationId),
                'policy_version' => $permit->policyVersion,
                'release_manifest_hash' => $permit->releaseManifestHash,
                'reserved_at_utc' => gmdate('c'),
            ];
            $nonces[$permit->approvalNonce] = [
                'idempotency_key_hash' => hash('sha256', $permit->idempotencyKey),
                'used_at_utc' => gmdate('c'),
            ];
            $authorizations[$permit->authorizationId] = [
                'idempotency_key_hash' => hash('sha256', $permit->idempotencyKey),
                'used_at_utc' => gmdate('c'),
            ];

            $state['entries'] = $entries;
            $state['approval_nonces'] = $nonces;
            $state['authorization_ids'] = $authorizations;
        });
    }

    public function complete(string $idempotencyKey, bool $ok, ?string $providerRequestId = null, ?string $readbackHash = null): void
    {
        $this->mutate(function (array &$state) use ($idempotencyKey, $ok, $providerRequestId, $readbackHash): void {
            if (!isset($state['entries'][$idempotencyKey])) {
                throw new RuntimeException('idempotency_reservation_not_found');
            }
            $state['entries'][$idempotencyKey]['status'] = $ok ? 'completed_verified' : 'failed_or_unverified';
            $state['entries'][$idempotencyKey]['completed_at_utc'] = gmdate('c');
            if ($providerRequestId !== null && $providerRequestId !== '') {
                $state['entries'][$idempotencyKey]['provider_request_id_hash'] = hash('sha256', $providerRequestId);
            }
            if ($readbackHash !== null && $readbackHash !== '') {
                $state['entries'][$idempotencyKey]['readback_projection_hash'] = $readbackHash;
            }
        });
    }

    /**
     * Cooperating writers must all use this implementation. The stable lock file
     * must never be removed while writers exist. Local same-filesystem rename is
     * required; this does not certify network filesystem or power-loss durability.
     */
    private function mutate(callable $callback): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('write_ledger_directory_unavailable');
        }
        $lock = @fopen($this->path . '.lock', 'c+');
        if ($lock === false) {
            throw new RuntimeException('write_ledger_unavailable');
        }
        $temporary = null;
        $output = null;
        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('write_ledger_lock_failed');
            }
            clearstatcache(true, $this->path);
            $exists = file_exists($this->path);
            $raw = $exists ? @file_get_contents($this->path) : null;
            if ($exists && (!is_string($raw) || trim($raw) === '')) {
                throw new RuntimeException('write_ledger_corrupt');
            }
            $state = $exists ? json_decode($raw, true) : [];
            if (!is_array($state)) {
                throw new RuntimeException('write_ledger_corrupt');
            }
            $callback($state);
            $encoded = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($encoded === false) {
                throw new RuntimeException('write_ledger_encode_failed');
            }
            $temporary = $this->path . '.pending-' . bin2hex(random_bytes(16));
            $output = @fopen($temporary, 'x+b');
            if ($output === false || !@chmod($temporary, 0600)) {
                throw new RuntimeException('write_ledger_write_failed');
            }
            $bytes = $encoded . PHP_EOL;
            $offset = 0;
            while ($offset < strlen($bytes)) {
                $written = fwrite($output, substr($bytes, $offset));
                if ($written === false || $written === 0) {
                    throw new RuntimeException('write_ledger_write_failed');
                }
                $offset += $written;
            }
            if (!fflush($output) || !fsync($output)) {
                throw new RuntimeException('write_ledger_write_failed');
            }
            fclose($output);
            $output = null;
            // Publish only a complete snapshot; never truncate the prior ledger.
            if (!@rename($temporary, $this->path)) {
                throw new RuntimeException('write_ledger_write_failed');
            }
            $temporary = null;
        } finally {
            if (is_resource($output)) { fclose($output); }
            if ($temporary !== null && is_file($temporary)) { @unlink($temporary); }
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
