<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/InvoiceDraftPreview.php';
require_once __DIR__ . '/../app/InvoiceDraftReadbackVerifier.php';
require_once __DIR__ . '/../app/WriteDispatchPermit.php';
require_once __DIR__ . '/../app/WriteExecutionLedger.php';
require_once __DIR__ . '/support/SyntheticBoundedCreate.php';

const SYNTHETIC_KEY = 'offline-fixture-only-never-a-provider-credential';
const FIXED_CLOCK = 1800000000;

function fixture(): array
{
    $unit = [
        'workId' => 'synthetic-work-0001',
        'organizationRef' => 'synthetic-org-alpha',
        'customerRef' => 'synthetic-customer-one',
        'payload' => [
            'type' => 'NORMAL',
            'customerId' => 'synthetic-customer-one',
            'registrationSource' => 'CONTA',
            'invoiceLanguage' => 'NO',
            'invoiceCurrency' => 'NOK',
            'invoiceDraftLines' => [[
                'description' => 'synthetic-validation-only',
                'price' => 1.0,
                'quantity' => 1,
                'discount' => 0,
                'vatCode' => 'high',
                'lineNo' => 1,
            ]],
        ],
    ];
    $pre = [
        'method' => 'GET', 'status' => 'ok',
        'organizationRef' => $unit['organizationRef'],
        'customerRef' => $unit['customerRef'],
        'customerExists' => true, 'matchingDraftCount' => 0, 'ambiguous' => false,
        'observedAt' => FIXED_CLOCK - 10,
    ];
    $preview = SyntheticBoundedCreate::preview($unit, $pre);
    $auth = array_merge($preview, [
        'status' => 'APPROVED', 'environment' => 'synthetic', 'oneUse' => true,
        'maxProviderMutations' => 1, 'automaticRetry' => false, 'readbackRequired' => true,
        'authorizationId' => 'synthetic-auth-id-00000001',
        'approvalId' => 'synthetic-approval-id-000001',
        'nonce' => 'synthetic-nonce-0000000001',
        'idempotencyKey' => 'synthetic-idempotency-00001',
        'notBefore' => FIXED_CLOCK - 5, 'expiresAt' => FIXED_CLOCK + 300,
    ]);
    $auth = signed($auth);
    $outcome = [
        'attempts' => 1, 'method' => 'POST', 'status' => 'accepted',
        'readback' => [
            'method' => 'GET', 'status' => 'ok',
            'draftId' => 'synthetic-draft-one',
            'organizationRef' => $unit['organizationRef'],
            'customerRef' => $unit['customerRef'],
            'payload' => $unit['payload'],
        ],
    ];
    $switches = ['globalBlocked' => false, 'actionBlocked' => false, 'executionAllowed' => true];
    return [$unit, $pre, $auth, $switches, $outcome];
}

function signed(array $auth): array
{
    unset($auth['signature']);
    $auth['signature'] = hash_hmac('sha256', InvoiceDraftPreview::payloadHash($auth), SYNTHETIC_KEY);
    return $auth;
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('TEST_FAILURE: ' . $message);
    }
}

$root = sys_get_temp_dir() . '/conta98-synthetic-' . bin2hex(random_bytes(8));
if (!mkdir($root, 0700, true)) {
    throw new RuntimeException('test_directory_unavailable');
}
$checks = 0;
$makeRunner = static function (string $name) use ($root): SyntheticBoundedCreate {
    return new SyntheticBoundedCreate(
        new WriteExecutionLedger($root . '/' . $name . '.json'),
        new InvoiceDraftReadbackVerifier(), SYNTHETIC_KEY, FIXED_CLOCK
    );
};
$runCase = static function (string $name, callable $mutation, string $state, string $reason, int $attempts) use ($makeRunner, &$checks): void {
    [$unit, $pre, $auth, $switches, $outcome] = fixture();
    $mutation($unit, $pre, $auth, $switches, $outcome);
    $r = $makeRunner($name)->run($unit, $pre, $auth, $switches, $outcome);
    check($r['state'] === $state && $r['reason'] === $reason && $r['synthetic_attempt_count'] === $attempts, $name . ': ' . json_encode($r));
    check($r['simulated_global_kill_switch_closed'] === true && $r['simulated_action_kill_switch_closed'] === true &&
        $r['simulated_execution_allowed_after'] === false && $r['runtime_configuration_untouched'] === true && $r['provider_call_performed'] === false &&
        $r['production_mutation_performed'] === false, $name . ': unsafe exit');
    $checks++;
};

$runCase('positive', static function () {}, 'SIMULATED_VERIFIED', 'verified', 1);
$runCase('global_kill_default', static function (&$u, &$p, &$a, &$s) { unset($s['globalBlocked']); }, 'SIMULATED_DENIED_CLOSED', 'kill_switch_closed', 0);
$runCase('action_kill', static function (&$u, &$p, &$a, &$s) { $s['actionBlocked'] = true; }, 'SIMULATED_DENIED_CLOSED', 'kill_switch_closed', 0);
$runCase('execution_disabled', static function (&$u, &$p, &$a, &$s) { $s['executionAllowed'] = false; }, 'SIMULATED_DENIED_CLOSED', 'kill_switch_closed', 0);
$runCase('stale_pre_read', static function (&$u, &$p) { $p['observedAt'] = FIXED_CLOCK - 400; }, 'SIMULATED_DENIED_CLOSED', 'pre_read_stale_or_invalid', 0);
$runCase('future_pre_read', static function (&$u, &$p) { $p['observedAt'] = FIXED_CLOCK + 5; }, 'SIMULATED_DENIED_CLOSED', 'pre_read_stale_or_invalid', 0);
$runCase('stale', static function (&$u, &$p, &$a) { $a['expiresAt'] = FIXED_CLOCK - 1; $a = signed($a); }, 'SIMULATED_DENIED_CLOSED', 'authorization_expired_or_invalid', 0);
$runCase('long_ttl', static function (&$u, &$p, &$a) { $a['expiresAt'] += 1000; $a = signed($a); }, 'SIMULATED_DENIED_CLOSED', 'authorization_expired_or_invalid', 0);
$runCase('changed_hash', static function (&$u, &$p, &$a) { $a['payload_hash'] = str_repeat('f',64); $a = signed($a); }, 'SIMULATED_DENIED_CLOSED', 'authorization_payload_hash_mismatch', 0);
$runCase('changed_payload', static function (&$u) { $u['payload']['invoiceDraftLines'][0]['price'] = 0.5; }, 'SIMULATED_DENIED_CLOSED', 'authorization_payload_hash_mismatch', 0);
$runCase('altered_preview', static function (&$u, &$p, &$a) { $a['pre_read_hash'] = str_repeat('0',64); $a = signed($a); }, 'SIMULATED_DENIED_CLOSED', 'authorization_pre_read_hash_mismatch', 0);
$runCase('unsigned_mutation', static function (&$u, &$p, &$a) { $a['nonce'] = 'synthetic-nonce-changed!'; }, 'SIMULATED_DENIED_CLOSED', 'authorization_signature_invalid', 0);
$runCase('mismatched_org_approval', static function (&$u, &$p, &$a) { $a['organization_hash'] = hash('sha256','synthetic-org-beta'); $a = signed($a); }, 'SIMULATED_DENIED_CLOSED', 'authorization_organization_hash_mismatch', 0);
$runCase('mismatched_customer_approval', static function (&$u, &$p, &$a) { $a['customer_hash'] = hash('sha256','synthetic-customer-two'); $a = signed($a); }, 'SIMULATED_DENIED_CLOSED', 'authorization_customer_hash_mismatch', 0);
$runCase('wrong_org', static function (&$u) { $u['organizationRef'] = 'synthetic-org-beta'; }, 'SIMULATED_DENIED_CLOSED', 'pre_read_missing_duplicate_or_ambiguous', 0);
$runCase('wrong_customer', static function (&$u) { $u['customerRef'] = 'synthetic-customer-two'; }, 'SIMULATED_DENIED_CLOSED', 'synthetic_payload_scope_invalid', 0);
$runCase('duplicate', static function (&$u, &$p) { $p['matchingDraftCount'] = 1; }, 'SIMULATED_DENIED_CLOSED', 'pre_read_missing_duplicate_or_ambiguous', 0);
$runCase('ambiguous_pre_read', static function (&$u, &$p) { $p['ambiguous'] = true; }, 'SIMULATED_DENIED_CLOSED', 'pre_read_missing_duplicate_or_ambiguous', 0);
$runCase('pre_read_post_forbidden', static function (&$u, &$p) { $p['method'] = 'POST'; }, 'SIMULATED_DENIED_CLOSED', 'pre_read_missing_duplicate_or_ambiguous', 0);
$runCase('wrong_action', static function (&$u, &$p, &$a) { $a['action'] = 'invoice_draft_delete'; $a = signed($a); }, 'SIMULATED_DENIED_CLOSED', 'authorization_scope_invalid', 0);
$runCase('no_one_use', static function (&$u, &$p, &$a) { $a['oneUse'] = false; $a = signed($a); }, 'SIMULATED_DENIED_CLOSED', 'authorization_scope_invalid', 0);
$runCase('multi_attempt', static function (&$u, &$p, &$a, &$s, &$o) { $o['attempts'] = 2; }, 'SIMULATED_DENIED_CLOSED', 'one_attempt_contract_invalid', 0);
$runCase('duplicate_provider_outcome', static function (&$u, &$p, &$a, &$s, &$o) { $o['status'] = 'conflict'; }, 'SIMULATED_CLOSED_UNVERIFIED_NO_RETRY', 'provider_outcome_unverified', 1);
$runCase('timeout', static function (&$u, &$p, &$a, &$s, &$o) { $o['status'] = 'timeout'; }, 'SIMULATED_CLOSED_UNVERIFIED_NO_RETRY', 'provider_outcome_unverified', 1);
$runCase('readback_missing', static function (&$u, &$p, &$a, &$s, &$o) { unset($o['readback']); }, 'SIMULATED_CLOSED_UNVERIFIED_NO_RETRY', 'get_only_readback_unavailable_or_mismatched', 1);
$runCase('readback_post_forbidden', static function (&$u, &$p, &$a, &$s, &$o) { $o['readback']['method'] = 'POST'; }, 'SIMULATED_CLOSED_UNVERIFIED_NO_RETRY', 'get_only_readback_unavailable_or_mismatched', 1);
$runCase('readback_wrong_org', static function (&$u, &$p, &$a, &$s, &$o) { $o['readback']['organizationRef'] = 'synthetic-org-beta'; }, 'SIMULATED_CLOSED_UNVERIFIED_NO_RETRY', 'get_only_readback_unavailable_or_mismatched', 1);
$runCase('readback_wrong_customer', static function (&$u, &$p, &$a, &$s, &$o) { $o['readback']['customerRef'] = 'synthetic-customer-two'; }, 'SIMULATED_CLOSED_UNVERIFIED_NO_RETRY', 'get_only_readback_unavailable_or_mismatched', 1);
$runCase('readback_wrong_vat', static function (&$u, &$p, &$a, &$s, &$o) { $o['readback']['payload']['invoiceDraftLines'][0]['vatCode'] = 'no.vat'; }, 'SIMULATED_CLOSED_UNVERIFIED_NO_RETRY', 'controlled_fields_readback_mismatch', 1);
$runCase('readback_missing_line', static function (&$u, &$p, &$a, &$s, &$o) { $o['readback']['payload']['invoiceDraftLines'] = []; }, 'SIMULATED_CLOSED_UNVERIFIED_NO_RETRY', 'controlled_fields_readback_mismatch', 1);
$runCase('non_synthetic_org', static function (&$u) { $u['organizationRef'] = 'real-org-123'; }, 'SIMULATED_DENIED_CLOSED', 'synthetic_scope_invalid', 0);
$runCase('oversized_amount', static function (&$u) { $u['payload']['invoiceDraftLines'][0]['price'] = 2; }, 'SIMULATED_DENIED_CLOSED', 'synthetic_payload_limits_exceeded', 0);

[$unit,$pre,$auth,$switches,$outcome] = fixture();
$runner = $makeRunner('replay_and_ledger');
$first = $runner->run($unit,$pre,$auth,$switches,$outcome);
check($first['state'] === 'SIMULATED_VERIFIED', 'first replay fixture failed');
$replay = $runner->run($unit,$pre,$auth,$switches,$outcome);
check($replay['state'] === 'SIMULATED_DENIED_CLOSED' && $replay['reason'] === 'idempotency_key_already_used', 'idempotency replay accepted');
$auth['idempotencyKey'] = 'synthetic-idempotency-NEW02';
$auth = signed($auth);
$nonceReplay = $runner->run($unit,$pre,$auth,$switches,$outcome);
check($nonceReplay['reason'] === 'approval_nonce_already_used', 'nonce replay accepted');
$auth['nonce'] = 'synthetic-nonce-NEW-0000001';
$auth = signed($auth);
$authorizationReplay = $runner->run($unit,$pre,$auth,$switches,$outcome);
check($authorizationReplay['reason'] === 'sandbox_authorization_already_used', 'authorization replay accepted');
$ledger = json_decode(file_get_contents($root . '/replay_and_ledger.json'),true);
check(($ledger['entries']['synthetic-idempotency-00001']['status'] ?? null) === 'completed_verified', 'verified ledger closure missing');
$failedLedger = json_decode(file_get_contents($root . '/timeout.json'),true);
check(($failedLedger['entries']['synthetic-idempotency-00001']['status'] ?? null) === 'failed_or_unverified', 'unverified ledger closure missing');
$checks += 5;

foreach (glob($root . '/*.json') ?: [] as $file) { unlink($file); }
rmdir($root);
echo 'REPEATABLE_BOUNDED_CREATE_SYNTHETIC_TESTS_PASSED=' . $checks . "\n";
