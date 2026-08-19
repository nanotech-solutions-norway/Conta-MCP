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

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('ASSERTION_FAILED: ' . $message);
    }
}

function expectException(callable $callback, string $expectedMessage): void
{
    try {
        $callback();
    } catch (Throwable $e) {
        assertTrue($e->getMessage() === $expectedMessage, 'Expected ' . $expectedMessage . ', got ' . $e->getMessage());
        return;
    }
    throw new RuntimeException('ASSERTION_FAILED: expected exception ' . $expectedMessage);
}

$blockedConfig = new Config([
    'environment' => 'sandbox',
    'default_organization_id' => 'org-test',
    'enable_write_preview' => true,
    'enable_write_tools' => false,
    'runtime_write_blocked' => true,
    'execution_allowed' => false,
    'production_write_approved' => false,
    'write_policy_version' => 'test-policy',
    'allowed_write_actions' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'allowed_write_organization_ids' => 'org-test',
    'create_invoice_draft_route' => '/invoice/organizations/{opContextOrgId}/invoice-drafts',
]);
$approvalVerifier = new ApprovalEnvelopeVerifier($blockedConfig);
$blockedPolicy = new WritePolicy(
    $blockedConfig,
    $approvalVerifier,
    new ReleaseManifestGuard($blockedConfig, dirname(__DIR__)),
    new WriteKillSwitch($blockedConfig),
    new SandboxAuthorizationGate($blockedConfig, $approvalVerifier),
    new ProductionAuthorizationGate($blockedConfig, $approvalVerifier)
);
$preview = new InvoiceDraftPreview($blockedConfig, $blockedPolicy);

assertTrue($blockedPolicy->previewEnabled(), 'Preview must be enabled.');
assertTrue(!$blockedPolicy->executionToolVisible(), 'Execution tool must be hidden while blocked.');

$payloadA = ['customerId' => 'c1', 'lines' => [['quantity' => 1, 'productId' => 'p1']]];
$payloadB = ['lines' => [['productId' => 'p1', 'quantity' => 1]], 'customerId' => 'c1'];
assertTrue(InvoiceDraftPreview::payloadHash($payloadA) === InvoiceDraftPreview::payloadHash($payloadB), 'Canonical hashes must be deterministic.');

$previewResult = $preview->build('org-test', $payloadA);
assertTrue($previewResult['provider_call_performed'] === false, 'Preview must not call provider.');
assertTrue($previewResult['execution_eligible_now'] === false, 'Blocked policy must not be executable.');
assertTrue($previewResult['required_approval']['approvalId'] === 'PENDING_OPERATOR_VALIDATION', 'Approval must remain pending.');

expectException(
    fn() => $blockedPolicy->authorizeInvoiceDraftCreate('org-test', '/invoice/organizations/org-test/invoice-drafts', $previewResult['payload_hash_sha256'], []),
    'write_tools_disabled'
);

$productionConfig = new Config([
    'environment' => 'production',
    'enable_write_tools' => true,
    'runtime_write_blocked' => false,
    'execution_allowed' => true,
    'production_write_approved' => true,
    'allowed_write_actions' => [WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2],
    'allowed_write_organization_ids' => ['org-prod'],
    'production_organization_reference_hash' => hash('sha256', 'org-prod'),
    'production_decision_packet_sha256' => str_repeat('a', 64),
    'create_invoice_draft_route' => '/invoice/organizations/{opContextOrgId}/invoice-drafts',
    'readback_invoice_draft_route' => '/invoice/organizations/{opContextOrgId}/invoice-drafts/{id}',
]);
$productionVerifier = new ApprovalEnvelopeVerifier($productionConfig);
$productionPolicy = new WritePolicy(
    $productionConfig,
    $productionVerifier,
    new ReleaseManifestGuard($productionConfig, dirname(__DIR__)),
    new WriteKillSwitch($productionConfig),
    new SandboxAuthorizationGate($productionConfig, $productionVerifier),
    new ProductionAuthorizationGate($productionConfig, $productionVerifier)
);
assertTrue(!$productionPolicy->effectiveExecutionEnabled(), 'Production must remain blocked while release/kill-switch/authorization evidence is absent.');

expectException(
    fn() => $productionPolicy->assertInvoiceDraftPayloadLimits([
        'invoiceCurrency' => 'NOK',
        'invoiceDraftLines' => [['price' => 1.01, 'quantity' => 1, 'discount' => 0]],
    ]),
    'production_invoice_draft_line_amount_exceeded'
);
$productionPolicy->assertInvoiceDraftPayloadLimits([
    'invoiceCurrency' => 'NOK',
    'invoiceDraftLines' => [['price' => 1.00, 'quantity' => 1, 'discount' => 0]],
]);

echo "CONTROLLED_WRITE_FOUNDATION_TESTS_PASSED\n";
