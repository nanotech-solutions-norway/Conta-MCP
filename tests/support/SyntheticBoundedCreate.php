<?php

declare(strict_types=1);

/**
 * Offline-only governed work-unit simulator. No HTTP client, credentials,
 * environment access, or live-dispatch method exists in this class.
 * NEVER include this file in the deployed production runtime.
 */
final class SyntheticBoundedCreate
{
    public const ACTION = 'invoice_draft_create_v2';
    private const POLICY = 'synthetic-repeatable-create-v1';

    public function __construct(
        private readonly WriteExecutionLedger $ledger,
        private readonly InvoiceDraftReadbackVerifier $readbackVerifier,
        private readonly string $fixtureSigningKey,
        private readonly int $clock
    ) {
        if (strlen($fixtureSigningKey) < 32) {
            throw new RuntimeException('synthetic_signing_key_invalid');
        }
    }

    /** Deterministic, payload-free review projection. */
    public static function preview(array $unit, array $preRead): array
    {
        self::assertUnit($unit);
        self::assertPreRead($unit, $preRead);
        return [
            'work_id_hash' => hash('sha256', $unit['workId']),
            'action' => self::ACTION,
            'method' => 'POST',
            'organization_hash' => hash('sha256', $unit['organizationRef']),
            'customer_hash' => hash('sha256', $unit['customerRef']),
            'payload_hash' => InvoiceDraftPreview::payloadHash($unit['payload']),
            'pre_read_hash' => InvoiceDraftPreview::payloadHash($preRead),
            'max_attempts' => 1,
            'readback_method' => 'GET',
        ];
    }

    /**
     * Simulated outcome is inert input data; no callback/provider transport is accepted.
     * Successful and failed runs always produce repository-safe evidence only.
     */
    public function run(array $unit, array $preRead, array $approval, array $switches, array $outcome): array
    {
        $attempts = 0;
        $verified = false;
        $reason = 'denied';
        $reserved = false;
        $idempotencyKey = null;
        $preview = null;
        try {
            $preview = self::preview($unit, $preRead);
            if (($preRead['observedAt'] ?? null) > $this->clock ||
                !is_int($preRead['observedAt'] ?? null) ||
                $preRead['observedAt'] < $this->clock - 300) {
                throw new RuntimeException('pre_read_stale_or_invalid');
            }
            if (($switches['globalBlocked'] ?? true) !== false ||
                ($switches['actionBlocked'] ?? true) !== false ||
                ($switches['executionAllowed'] ?? false) !== true) {
                throw new RuntimeException('kill_switch_closed');
            }
            $this->checkApproval($approval, $preview);
            if (($outcome['attempts'] ?? null) !== 1 || ($outcome['method'] ?? null) !== 'POST') {
                throw new RuntimeException('one_attempt_contract_invalid');
            }
            $idempotencyKey = $approval['idempotencyKey'];
            $permit = new WriteDispatchPermit(
                action: self::ACTION,
                method: 'POST',
                path: '/invoice/organizations/' . rawurlencode($unit['organizationRef']) . '/invoice-drafts',
                organizationId: $unit['organizationRef'],
                payloadHash: $preview['payload_hash'],
                idempotencyKey: $idempotencyKey,
                approvalId: $approval['approvalId'],
                approvalNonce: $approval['nonce'],
                authorizationId: $approval['authorizationId'],
                policyVersion: self::POLICY,
                releaseManifestHash: str_repeat('0', 64)
            );
            // Persistent reservation occurs BEFORE any simulated dispatch.
            $this->ledger->reserve($permit);
            $reserved = true;
            $attempts = 1;
            $reason = 'provider_outcome_unverified';
            if (($outcome['status'] ?? null) !== 'accepted') {
                throw new RuntimeException('provider_outcome_unverified');
            }
            $readback = $outcome['readback'] ?? null;
            if (!is_array($readback) || ($readback['method'] ?? null) !== 'GET' ||
                ($readback['status'] ?? null) !== 'ok' ||
                ($readback['organizationRef'] ?? null) !== $unit['organizationRef'] ||
                ($readback['customerRef'] ?? null) !== $unit['customerRef'] ||
                !is_string($readback['draftId'] ?? null) ||
                !str_starts_with($readback['draftId'], 'synthetic-draft-') ||
                !is_array($readback['payload'] ?? null)) {
                throw new RuntimeException('get_only_readback_unavailable_or_mismatched');
            }
            $check = $this->readbackVerifier->verify($unit['payload'], $readback['payload']);
            if (($check['verified'] ?? false) !== true) {
                throw new RuntimeException('controlled_fields_readback_mismatch');
            }
            $verified = true;
            $reason = 'verified';
        } catch (Throwable $error) {
            $reason = $error->getMessage();
        } finally {
            if ($reserved) {
                try {
                    $this->ledger->complete((string) $idempotencyKey, $verified);
                } catch (Throwable) {
                    $verified = false;
                    $reason = 'ledger_terminal_closure_failed';
                }
            }
        }
        return [
            'state' => $verified ? 'COMPLETED_VERIFIED' : ($attempts ? 'CLOSED_UNVERIFIED_NO_RETRY' : 'DENIED_CLOSED'),
            'reason' => $reason,
            'synthetic_attempt_count' => $attempts,
            'readback_verified' => $verified,
            'simulated_global_kill_switch_closed' => true,
            'simulated_action_kill_switch_closed' => true,
            'simulated_execution_allowed_after' => false,
            'runtime_configuration_untouched' => true,
            'provider_call_performed' => false,
            'production_mutation_performed' => false,
            'work_id_hash' => $preview['work_id_hash'] ?? null,
            'payload_hash' => $preview['payload_hash'] ?? null,
        ];
    }

    private function checkApproval(array $approval, array $preview): void
    {
        if (($approval['status'] ?? null) !== 'APPROVED' ||
            ($approval['environment'] ?? null) !== 'synthetic' ||
            ($approval['action'] ?? null) !== self::ACTION ||
            ($approval['method'] ?? null) !== 'POST' ||
            ($approval['oneUse'] ?? null) !== true ||
            ($approval['maxProviderMutations'] ?? null) !== 1 ||
            ($approval['automaticRetry'] ?? null) !== false ||
            ($approval['readbackRequired'] ?? null) !== true) {
            throw new RuntimeException('authorization_scope_invalid');
        }
        foreach (['work_id_hash', 'organization_hash', 'customer_hash', 'payload_hash', 'pre_read_hash'] as $field) {
            if (!is_string($approval[$field] ?? null) ||
                !hash_equals($preview[$field], $approval[$field])) {
                throw new RuntimeException('authorization_' . $field . '_mismatch');
            }
        }
        foreach (['authorizationId', 'approvalId', 'nonce', 'idempotencyKey'] as $field) {
            if (!is_string($approval[$field] ?? null) || strlen($approval[$field]) < 16) {
                throw new RuntimeException('authorization_identifier_invalid');
            }
        }
        if (!is_int($approval['notBefore'] ?? null) || !is_int($approval['expiresAt'] ?? null) ||
            $approval['notBefore'] > $this->clock || $approval['expiresAt'] <= $this->clock ||
            $approval['expiresAt'] <= $approval['notBefore'] ||
            $approval['expiresAt'] - $approval['notBefore'] > 900) {
            throw new RuntimeException('authorization_expired_or_invalid');
        }
        $signature = $approval['signature'] ?? null;
        unset($approval['signature']);
        $expected = hash_hmac('sha256', InvoiceDraftPreview::payloadHash($approval), $this->fixtureSigningKey);
        if (!is_string($signature) || !hash_equals($expected, $signature)) {
            throw new RuntimeException('authorization_signature_invalid');
        }
    }

    private static function assertUnit(array $unit): void
    {
        foreach (['workId' => 'synthetic-work-', 'organizationRef' => 'synthetic-org-', 'customerRef' => 'synthetic-customer-'] as $key => $prefix) {
            if (!is_string($unit[$key] ?? null) || !str_starts_with($unit[$key], $prefix) || strlen($unit[$key]) <= strlen($prefix)) {
                throw new RuntimeException('synthetic_scope_invalid');
            }
        }
        $p = $unit['payload'] ?? null;
        if (!is_array($p) || array_diff(array_keys($p), [
            'registrationSource', 'type', 'customerId', 'invoiceLanguage', 'invoiceCurrency', 'invoiceDraftLines'
        ]) !== [] ||
            ($p['customerId'] ?? null) !== $unit['customerRef'] ||
            ($p['type'] ?? null) !== 'NORMAL' ||
            ($p['invoiceCurrency'] ?? null) !== 'NOK' ||
            ($p['invoiceLanguage'] ?? null) !== 'NO' ||
            ($p['registrationSource'] ?? null) !== 'CONTA' ||
            !is_array($p['invoiceDraftLines'] ?? null) || count($p['invoiceDraftLines']) !== 1) {
            throw new RuntimeException('synthetic_payload_scope_invalid');
        }
        $line = $p['invoiceDraftLines'][0];
        if (!is_array($line) || array_diff(array_keys($line), [
            'description', 'price', 'quantity', 'discount', 'vatCode', 'lineNo'
        ]) !== [] ||
            ($line['vatCode'] ?? null) !== 'high' || ($line['lineNo'] ?? null) !== 1 ||
            !is_string($line['description'] ?? null) || !str_starts_with($line['description'], 'synthetic-') ||
            !is_numeric($line['price'] ?? null) || !is_numeric($line['quantity'] ?? null) ||
            !is_numeric($line['discount'] ?? null)) {
            throw new RuntimeException('synthetic_payload_line_invalid');
        }
        $price = (float) $line['price'];
        $quantity = (float) $line['quantity'];
        $discount = (float) $line['discount'];
        $total = $price * $quantity * (1 - $discount / 100);
        if (!is_finite($total) || $price < 0 || $quantity <= 0 || $discount < 0 || $discount > 100 || $total > 1.0) {
            throw new RuntimeException('synthetic_payload_limits_exceeded');
        }
    }

    private static function assertPreRead(array $unit, array $preRead): void
    {
        if (($preRead['method'] ?? null) !== 'GET' || ($preRead['status'] ?? null) !== 'ok' ||
            ($preRead['organizationRef'] ?? null) !== $unit['organizationRef'] ||
            ($preRead['customerRef'] ?? null) !== $unit['customerRef'] ||
            ($preRead['customerExists'] ?? null) !== true ||
            ($preRead['ambiguous'] ?? true) !== false ||
            ($preRead['matchingDraftCount'] ?? null) !== 0 ||
            !is_int($preRead['observedAt'] ?? null)) {
            throw new RuntimeException('pre_read_missing_duplicate_or_ambiguous');
        }
    }
}
