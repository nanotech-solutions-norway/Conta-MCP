<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Config.php';
require_once __DIR__ . '/../app/ApprovalEnvelopeVerifier.php';
require_once __DIR__ . '/../app/ReleaseManifestGuard.php';
require_once __DIR__ . '/../app/WriteKillSwitch.php';
require_once __DIR__ . '/../app/SandboxAuthorizationGate.php';
require_once __DIR__ . '/../app/ProductionAuthorizationGate.php';
require_once __DIR__ . '/../app/WriteDispatchPermit.php';
require_once __DIR__ . '/../app/WritePolicy.php';
require_once __DIR__ . '/../app/WriteExecutionLedger.php';
require_once __DIR__ . '/../app/InvoiceDraftPreview.php';

function assertProduction(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('ASSERTION_FAILED: ' . $message);
    }
}

function expectProductionException(callable $callback, string $expected): void
{
    try {
        $callback();
    } catch (Throwable $e) {
        assertProduction($e->getMessage() === $expected, 'Expected ' . $expected . ', got ' . $e->getMessage());
        return;
    }
    throw new RuntimeException('ASSERTION_FAILED: expected ' . $expected);
}

$root = sys_get_temp_dir() . '/conta-production-controls-' . bin2hex(random_bytes(5));
mkdir($root . '/app', 0750, true);
mkdir($root . '/storage', 0750, true);
file_put_contents($root . '/app/TestRuntime.php', "<?php\n");

$manifestPath = $root . '/storage/approved-release-manifest.json';
$killSwitchPath = $root . '/storage/write-kill-switch.json';
$productionAuthorizationPath = $root . '/storage/production-authorization.json';
$ledgerPath = $root . '/storage/write-ledger.json';
$organizationId = 'prod-org-test';
$organizationHash = hash('sha256', $organizationId);
$decisionPacketHash = str_repeat('d', 64);
$schemaHash = str_repeat('b', 64);
$releaseCommit = str_repeat('a', 40);
$signingKey = 'production-test-signing-key-at-least-32-bytes';
$routeTemplate = '/invoice/organizations/{opContextOrgId}/invoice-drafts';
$readbackRoute = '/invoice/organizations/{opContextOrgId}/invoice-drafts/{id}';
$path = '/invoice/organizations/' . rawurlencode($organizationId) . '/invoice-drafts';

$config = new Config([
    'environment' => 'production',
    'enable_write_preview' => true,
    'enable_write_tools' => true,
    'runtime_write_blocked' => false,
    'execution_allowed' => true,
    'production_write_approved' => true,
    'write_policy_version' => 'production-test-policy-v1',
    'allowed_write_actions' => [WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2],
    'allowed_write_organization_ids' => [$organizationId],
    'production_organization_reference_hash' => $organizationHash,
    'production_decision_packet_sha256' => $decisionPacketHash,
    'production_max_invoice_draft_lines' => 1,
    'production_max_invoice_draft_line_amount' => 1.00,
    'production_max_invoice_draft_total' => 1.00,
    'create_invoice_draft_route' => $routeTemplate,
    'readback_invoice_draft_route' => $readbackRoute,
    'release_commit' => $releaseCommit,
    'provider_schema_sha256' => $schemaHash,
    'approved_release_manifest_path' => $manifestPath,
    'write_kill_switch_path' => $killSwitchPath,
    'production_authorization_path' => $productionAuthorizationPath,
    'write_ledger_path' => $ledgerPath,
    'require_signed_approvals' => true,
    'approval_signing_key' => $signingKey,
    'approval_key_id' => 'production-test-key-v1',
    'approval_max_ttl_seconds' => 900,
]);

$approvalVerifier = new ApprovalEnvelopeVerifier($config);
$manifestGuard = new ReleaseManifestGuard($config, $root);
$killSwitch = new WriteKillSwitch($config);
$sandboxGate = new SandboxAuthorizationGate($config, $approvalVerifier);
$productionGate = new ProductionAuthorizationGate($config, $approvalVerifier);
$policy = new WritePolicy($config, $approvalVerifier, $manifestGuard, $killSwitch, $sandboxGate, $productionGate);

$manifest = $manifestGuard->buildObservedManifest(['app/TestRuntime.php']);
$manifest['status'] = 'APPROVED';
$manifest['approved_by'] = 'offline-test-only';
$manifest['approved_at_utc'] = gmdate('c');
file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents($killSwitchPath, json_encode([
    'globalBlocked' => false,
    'blockedActions' => [],
    'reason' => 'offline test fixture only',
    'updatedAtUtc' => gmdate('c'),
]));

$payload = [
    'registrationSource' => 'CONTA',
    'invoiceDraftLines' => [[
        'description' => 'Offline production control test',
        'price' => 1.00,
        'quantity' => 1,
        'discount' => 0,
        'vatCode' => 'high',
        'lineNo' => 1,
    ]],
    'type' => 'NORMAL',
    'customerId' => 123,
    'invoiceLanguage' => 'NO',
    'invoiceCurrency' => 'NOK',
];
$payloadHash = InvoiceDraftPreview::payloadHash($payload);
$now = time();

$authorization = [
    'status' => 'APPROVED',
    'authorizationId' => 'production-auth-test-001',
    'candidateId' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'action' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'environment' => 'production',
    'organizationIdHash' => $organizationHash,
    'payloadHash' => $payloadHash,
    'method' => 'POST',
    'pathHash' => hash('sha256', $path),
    'decisionPacketSha256' => $decisionPacketHash,
    'notBefore' => gmdate('c', $now - 5),
    'expiresAt' => gmdate('c', $now + 300),
    'maxProviderMutations' => 1,
    'automaticRetry' => false,
    'readbackRequired' => true,
    'releaseApproved' => true,
];
$authorization = $approvalVerifier->sign($authorization);
file_put_contents($productionAuthorizationPath, json_encode($authorization, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$approval = [
    'approved' => true,
    'oneUse' => true,
    'approvalId' => 'production-approval-test-001',
    'approvedBy' => 'operator-test',
    'action' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'organizationId' => $organizationId,
    'environment' => 'production',
    'payloadHash' => $payloadHash,
    'method' => 'POST',
    'path' => $path,
    'issuedAt' => gmdate('c', $now - 5),
    'expiresAt' => gmdate('c', $now + 300),
    'nonce' => 'production-nonce-1234567890abcdef',
    'idempotencyKey' => 'production-idem-1234567890abcdef',
];
$approval = $approvalVerifier->sign($approval);

$policy->assertInvoiceDraftPayloadLimits($payload);
assertProduction($policy->effectiveExecutionEnabled(), 'Fully satisfied offline production fixture should be policy-eligible.');
$permit = $policy->authorizeInvoiceDraftCreate($organizationId, $path, $payloadHash, $approval);
$policy->assertDispatchPermit($permit, 'POST', $path);
assertProduction($permit->authorizationId === 'production-auth-test-001', 'Production permit must bind authorization ID.');

$ledger = new WriteExecutionLedger($ledgerPath);
$ledger->reserve($permit);
expectProductionException(fn() => $ledger->reserve($permit), 'idempotency_key_already_used');

$wrongCurrency = $payload;
$wrongCurrency['invoiceCurrency'] = 'EUR';
expectProductionException(
    fn() => $policy->assertInvoiceDraftPayloadLimits($wrongCurrency),
    'production_invoice_draft_currency_not_allowed'
);
$tooLarge = $payload;
$tooLarge['invoiceDraftLines'][0]['price'] = 1.01;
expectProductionException(fn() => $policy->assertInvoiceDraftPayloadLimits($tooLarge), 'production_invoice_draft_line_amount_exceeded');
$tooMany = $payload;
$tooMany['invoiceDraftLines'][] = $tooMany['invoiceDraftLines'][0];
expectProductionException(fn() => $policy->assertInvoiceDraftPayloadLimits($tooMany), 'production_invoice_draft_line_limit_exceeded');

$wrongOrgConfig = new Config([
    'environment' => 'production',
    'production_organization_reference_hash' => hash('sha256', 'different-org'),
    'production_decision_packet_sha256' => $decisionPacketHash,
    'production_authorization_path' => $productionAuthorizationPath,
    'require_signed_approvals' => true,
    'approval_signing_key' => $signingKey,
    'approval_key_id' => 'production-test-key-v1',
]);
$wrongOrgVerifier = new ApprovalEnvelopeVerifier($wrongOrgConfig);
$wrongOrgGate = new ProductionAuthorizationGate($wrongOrgConfig, $wrongOrgVerifier);
expectProductionException(fn() => $wrongOrgGate->assertPacketReady(), 'production_organization_reference_hash_mismatch');

$expired = $authorization;
unset($expired['signature']);
$expired['notBefore'] = gmdate('c', $now - 600);
$expired['expiresAt'] = gmdate('c', $now - 1);
$expired = $approvalVerifier->sign($expired);
file_put_contents($productionAuthorizationPath, json_encode($expired, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
assertProduction(!$policy->effectiveExecutionEnabled(), 'Expired production authorization must close the execution policy.');

@unlink($manifestPath);
@unlink($killSwitchPath);
@unlink($productionAuthorizationPath);
@unlink($ledgerPath);
@unlink($root . '/app/TestRuntime.php');
@rmdir($root . '/app');
@rmdir($root . '/storage');
@rmdir($root);

echo "PRODUCTION_WRITE_CONTROLS_TESTS_PASSED\n";
