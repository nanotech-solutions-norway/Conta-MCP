<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Config.php';
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
$blockedPolicy = new WritePolicy($blockedConfig);
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

$enabledConfig = new Config([
    'environment' => 'sandbox',
    'enable_write_preview' => true,
    'enable_write_tools' => true,
    'runtime_write_blocked' => false,
    'execution_allowed' => true,
    'production_write_approved' => false,
    'write_policy_version' => 'test-policy',
    'approval_max_ttl_seconds' => 900,
    'allowed_write_actions' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'allowed_write_organization_ids' => 'org-test',
    'create_invoice_draft_route' => '/invoice/organizations/{opContextOrgId}/invoice-drafts',
]);
$enabledPolicy = new WritePolicy($enabledConfig);
$now = time();
$approval = [
    'approved' => true,
    'oneUse' => true,
    'approvalId' => 'approval-test-001',
    'approvedBy' => 'operator-test',
    'action' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'organizationId' => 'org-test',
    'environment' => 'sandbox',
    'payloadHash' => InvoiceDraftPreview::payloadHash($payloadA),
    'issuedAt' => gmdate('c', $now - 5),
    'expiresAt' => gmdate('c', $now + 300),
    'nonce' => 'nonce-1234567890abcdef',
    'idempotencyKey' => 'idem-1234567890abcdef',
];
$permit = $enabledPolicy->authorizeInvoiceDraftCreate(
    'org-test',
    '/invoice/organizations/org-test/invoice-drafts',
    $approval['payloadHash'],
    $approval
);
$enabledPolicy->assertDispatchPermit($permit, 'POST', '/invoice/organizations/org-test/invoice-drafts');

$ledgerPath = sys_get_temp_dir() . '/conta-write-ledger-' . bin2hex(random_bytes(4)) . '.json';
$ledger = new WriteExecutionLedger($ledgerPath);
$ledger->reserve($permit);
expectException(fn() => $ledger->reserve($permit), 'idempotency_key_already_used');
$ledger->complete($permit->idempotencyKey, true, 'provider-request-test');
@unlink($ledgerPath);

$productionConfig = new Config([
    'environment' => 'production',
    'enable_write_tools' => true,
    'runtime_write_blocked' => false,
    'execution_allowed' => true,
    'production_write_approved' => false,
    'allowed_write_actions' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'allowed_write_organization_ids' => 'org-test',
    'create_invoice_draft_route' => '/invoice/organizations/{opContextOrgId}/invoice-drafts',
]);
assertTrue(!(new WritePolicy($productionConfig))->effectiveExecutionEnabled(), 'Production must remain blocked without independent approval.');

echo "CONTROLLED_WRITE_FOUNDATION_TESTS_PASSED\n";
