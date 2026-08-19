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
require_once __DIR__ . '/../app/InvoiceDraftReadbackVerifier.php';

function assertControl(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('ASSERTION_FAILED: ' . $message);
    }
}

function expectControlException(callable $callback, string $expected): void
{
    try {
        $callback();
    } catch (Throwable $e) {
        assertControl($e->getMessage() === $expected, 'Expected ' . $expected . ', got ' . $e->getMessage());
        return;
    }
    throw new RuntimeException('ASSERTION_FAILED: expected ' . $expected);
}

$root = sys_get_temp_dir() . '/conta-controls-' . bin2hex(random_bytes(5));
mkdir($root . '/app', 0750, true);
mkdir($root . '/storage', 0750, true);
file_put_contents($root . '/app/TestRuntime.php', "<?php\n");

$manifestPath = $root . '/storage/approved-release-manifest.json';
$killSwitchPath = $root . '/storage/write-kill-switch.json';
$authorizationPath = $root . '/storage/sandbox-authorization.json';
$ledgerPath = $root . '/storage/write-ledger.json';
$schemaHash = str_repeat('b', 64);
$releaseCommit = str_repeat('a', 40);
$signingKey = 'test-signing-key-at-least-32-bytes-long';
$route = '/invoice/organizations/{opContextOrgId}/invoice-drafts';
$readbackRoute = '/invoice/organizations/{opContextOrgId}/invoice-drafts/{id}';

$config = new Config([
    'environment' => 'sandbox',
    'enable_write_preview' => true,
    'enable_write_tools' => true,
    'runtime_write_blocked' => false,
    'execution_allowed' => true,
    'production_write_approved' => false,
    'write_policy_version' => 'test-policy-v2',
    'allowed_write_actions' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'allowed_write_organization_ids' => 'org-test',
    'create_invoice_draft_route' => $route,
    'readback_invoice_draft_route' => $readbackRoute,
    'release_commit' => $releaseCommit,
    'provider_schema_sha256' => $schemaHash,
    'approved_release_manifest_path' => $manifestPath,
    'write_kill_switch_path' => $killSwitchPath,
    'sandbox_authorization_path' => $authorizationPath,
    'write_ledger_path' => $ledgerPath,
    'require_signed_approvals' => true,
    'approval_signing_key' => $signingKey,
    'approval_key_id' => 'test-key-v1',
    'approval_max_ttl_seconds' => 900,
]);

$approvalVerifier = new ApprovalEnvelopeVerifier($config);
$manifestGuard = new ReleaseManifestGuard($config, $root);
$killSwitch = new WriteKillSwitch($config);
$sandboxGate = new SandboxAuthorizationGate($config, $approvalVerifier);
$productionGate = new ProductionAuthorizationGate($config, $approvalVerifier);
$policy = new WritePolicy($config, $approvalVerifier, $manifestGuard, $killSwitch, $sandboxGate, $productionGate);

$observed = $manifestGuard->buildObservedManifest(['app/TestRuntime.php']);
$observed['status'] = 'APPROVED';
$observed['approved_by'] = 'operator-test';
$observed['approved_at_utc'] = gmdate('c');
file_put_contents($manifestPath, json_encode($observed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
file_put_contents($killSwitchPath, json_encode(['globalBlocked' => false, 'blockedActions' => [], 'reason' => null, 'updatedAtUtc' => gmdate('c')]));

$payload = [
    'registrationSource' => 'CONTA',
    'customerId' => 42,
    'invoiceDraftLines' => [[
        'description' => 'Controlled test line',
        'price' => 100.0,
        'quantity' => 1.0,
        'discount' => 0.0,
        'vatCode' => 'HIGH',
    ]],
];
$payloadHash = InvoiceDraftPreview::payloadHash($payload);
$path = '/invoice/organizations/org-test/invoice-drafts';
$now = time();

$authorization = [
    'status' => 'APPROVED',
    'authorizationId' => 'sandbox-auth-test-001',
    'candidateId' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'action' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'environment' => 'sandbox',
    'organizationId' => 'org-test',
    'payloadHash' => $payloadHash,
    'method' => 'POST',
    'path' => $path,
    'notBefore' => gmdate('c', $now - 5),
    'expiresAt' => gmdate('c', $now + 300),
    'maxProviderMutations' => 1,
    'readbackRequired' => true,
    'providerRouteValidated' => true,
    'testCompanyValidated' => true,
    'operatorDecisionReference' => 'test-decision',
];
$authorization = $approvalVerifier->sign($authorization);
file_put_contents($authorizationPath, json_encode($authorization, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$approval = [
    'approved' => true,
    'oneUse' => true,
    'approvalId' => 'approval-test-001',
    'approvedBy' => 'operator-test',
    'action' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'organizationId' => 'org-test',
    'environment' => 'sandbox',
    'payloadHash' => $payloadHash,
    'issuedAt' => gmdate('c', $now - 5),
    'expiresAt' => gmdate('c', $now + 300),
    'nonce' => 'nonce-1234567890abcdef',
    'idempotencyKey' => 'idem-1234567890abcdef',
];
$signedApproval = $approvalVerifier->sign($approval);

assertControl($policy->effectiveExecutionEnabled(), 'All sandbox controls should be ready in the fixture.');
expectControlException(fn() => $approvalVerifier->verify($approval), 'approval_signature_missing_signature');

$permit = $policy->authorizeInvoiceDraftCreate('org-test', $path, $payloadHash, $signedApproval);
$policy->assertDispatchPermit($permit, 'POST', $path);
assertControl($permit->authorizationId === 'sandbox-auth-test-001', 'Permit must bind authorization ID.');

$ledger = new WriteExecutionLedger($ledgerPath);
$ledger->reserve($permit);
expectControlException(fn() => $ledger->reserve($permit), 'idempotency_key_already_used');
$ledger->complete($permit->idempotencyKey, true, 'request-test', str_repeat('c', 64));

$verifier = new InvoiceDraftReadbackVerifier();
$verified = $verifier->verify($payload, $payload + ['id' => 99]);
assertControl($verified['verified'] === true, 'Provider-added fields must not invalidate controlled readback.');
$mismatch = $payload;
$mismatch['customerId'] = 77;
assertControl($verifier->verify($payload, $mismatch)['verified'] === false, 'Controlled field mismatch must fail verification.');

file_put_contents($killSwitchPath, json_encode(['globalBlocked' => true, 'blockedActions' => [], 'reason' => 'test']));
assertControl(!$policy->effectiveExecutionEnabled(), 'Global kill switch must hide execution.');
expectControlException(fn() => $policy->assertDispatchPermit($permit, 'POST', $path), 'write_kill_switch_global_blocked');

file_put_contents($killSwitchPath, json_encode(['globalBlocked' => false, 'blockedActions' => [], 'reason' => null]));
file_put_contents($root . '/app/TestRuntime.php', "<?php\n// drift\n");
assertControl(!$policy->effectiveExecutionEnabled(), 'Runtime drift must hide execution.');

$productionConfig = new Config([
    'environment' => 'production',
    'enable_write_tools' => true,
    'runtime_write_blocked' => false,
    'execution_allowed' => true,
    'production_write_approved' => true,
    'allowed_write_actions' => [WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2],
    'allowed_write_organization_ids' => ['org-prod'],
    'production_organization_reference_hash' => hash('sha256', 'org-prod'),
    'production_decision_packet_sha256' => str_repeat('d', 64),
    'readback_invoice_draft_route' => $readbackRoute,
]);
$productionApprovalVerifier = new ApprovalEnvelopeVerifier($productionConfig);
$productionPolicy = new WritePolicy(
    $productionConfig,
    $productionApprovalVerifier,
    new ReleaseManifestGuard($productionConfig, $root),
    new WriteKillSwitch($productionConfig),
    new SandboxAuthorizationGate($productionConfig, $productionApprovalVerifier),
    new ProductionAuthorizationGate($productionConfig, $productionApprovalVerifier)
);
assertControl(!$productionPolicy->effectiveExecutionEnabled(), 'Production must remain blocked without an approved release, open kill switch and protected authorization packet.');

@unlink($manifestPath);
@unlink($killSwitchPath);
@unlink($authorizationPath);
@unlink($ledgerPath);
@unlink($root . '/app/TestRuntime.php');
@rmdir($root . '/app');
@rmdir($root . '/storage');
@rmdir($root);

echo "REMAINING_CONTROL_PATHS_TESTS_PASSED\n";
