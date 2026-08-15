<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
foreach ([
    'Config.php',
    'WritePolicy.php',
    'InvoiceDraftPreview.php',
    'InvoiceDraftReadbackVerifier.php',
    'HttpClient.php',
] as $file) {
    require_once $root . '/app/' . $file;
}

const FIXTURE_NAME = 'Atlas MCP Sandbox Test Customer';
const LINE_DESCRIPTION = 'Atlas MCP Sandbox Invoice Draft Validation';
const EXPECTED_DRAFT_ID_SHA256 = 'eab8ff114cc63fd8ab3d9f42249e20b8ce5ecce463e8368e98747f03c50eeabb';
const EXPECTED_PAYLOAD_SHA256 = '79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7';

function envRequired(string $name): string
{
    $value = trim((string) getenv($name));
    if ($value === '') {
        throw new RuntimeException('missing_required_environment:' . $name);
    }
    return $value;
}

function scalarType(mixed $value): string
{
    return match (true) {
        is_int($value) => 'integer',
        is_float($value) => 'float',
        is_string($value) => 'string',
        is_bool($value) => 'boolean',
        $value === null => 'null',
        default => gettype($value),
    };
}

function printSafeFieldDiagnostic(string $path, mixed $expected, mixed $actual, bool $printValue = false): void
{
    $key = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $path) ?? $path);
    echo 'FIELD_' . $key . '_EXPECTED_TYPE=' . scalarType($expected) . PHP_EOL;
    echo 'FIELD_' . $key . '_ACTUAL_TYPE=' . scalarType($actual) . PHP_EOL;
    echo 'FIELD_' . $key . '_STRICT_EQUAL=' . ($expected === $actual ? 'true' : 'false') . PHP_EOL;
    if (is_numeric($expected) && is_numeric($actual)) {
        echo 'FIELD_' . $key . '_NUMERIC_EQUAL=' . ((float) $expected === (float) $actual ? 'true' : 'false') . PHP_EOL;
    }
    if ($printValue && is_scalar($actual)) {
        $value = preg_replace('/[\r\n\t]+/', ' ', trim((string) $actual)) ?? '';
        echo 'FIELD_' . $key . '_ACTUAL_VALUE=' . substr($value, 0, 120) . PHP_EOL;
    }
}

function collectExactNamedObjects(mixed $node, string $name, array &$matches): void
{
    if (!is_array($node)) {
        return;
    }

    if (($node['name'] ?? null) === $name && isset($node['id']) && is_scalar($node['id'])) {
        $id = trim((string) $node['id']);
        if ($id !== '') {
            $matches[$id] = $node;
        }
    }

    foreach ($node as $child) {
        if (is_array($child)) {
            collectExactNamedObjects($child, $name, $matches);
        }
    }
}

function printSafeHttpDiagnostic(string $prefix, array $result): void
{
    $safePrefix = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $prefix) ?? $prefix);
    echo $safePrefix . '_STATUS=' . (string) ($result['status'] ?? 0) . PHP_EOL;
    $body = $result['body'] ?? null;

    $encoded = is_string($body)
        ? $body
        : json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);

    if (is_string($encoded)) {
        echo $safePrefix . '_BODY_SHA256=' . hash('sha256', $encoded) . PHP_EOL;
    }

    if (is_array($body)) {
        $keys = array_map('strval', array_keys($body));
        sort($keys, SORT_STRING);
        echo $safePrefix . '_TOP_LEVEL_KEYS=' . implode(',', array_slice($keys, 0, 20)) . PHP_EOL;

        foreach (['name', 'category', 'code', 'type', 'title', 'message', 'detail', 'error'] as $key) {
            if (!array_key_exists($key, $body) || !is_scalar($body[$key])) {
                continue;
            }
            $value = preg_replace('/[\r\n\t]+/', ' ', trim((string) $body[$key])) ?? '';
            $value = preg_replace('/\b\d{3,}\b/', '[redacted-number]', $value) ?? $value;
            $value = preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/', '[redacted-email]', $value) ?? $value;
            echo $safePrefix . '_' . strtoupper($key) . '=' . substr($value, 0, 240) . PHP_EOL;
        }
    }
}

$environment = envRequired('CONTA_ENVIRONMENT');
$baseUrl = rtrim(envRequired('CONTA_API_BASE_URL'), '/');
$apiKey = envRequired('CONTA_API_KEY');
$organizationId = envRequired('CONTA_ORG_ID');

if ($environment !== 'sandbox' || $baseUrl !== 'https://api.gateway.conta-sandbox.no') {
    throw new RuntimeException('sandbox_boundary_mismatch');
}
if (!preg_match('/^[0-9]+$/', $organizationId)) {
    throw new RuntimeException('invalid_organization_id_format');
}

$http = new HttpClient();
$headers = ['apiKey' => $apiKey, 'Accept' => 'application/json'];
$org = rawurlencode($organizationId);

// Resolve the exact protected synthetic customer using the same recursive matching
// strategy as the successful controlled-write script. This is GET only.
$customerSearchUrl = $baseUrl . '/invoice/organizations/' . $org . '/customers?' . http_build_query([
    'q' => FIXTURE_NAME,
    'hits' => 100,
    'page' => 0,
]);
$customerSearch = $http->request('GET', $customerSearchUrl, $headers, null, 30);
if (($customerSearch['ok'] ?? false) !== true || !is_array($customerSearch['body'] ?? null)) {
    printSafeHttpDiagnostic('customer_search', $customerSearch);
    throw new RuntimeException('synthetic_customer_search_failed');
}
$customerMatches = [];
collectExactNamedObjects($customerSearch['body'], FIXTURE_NAME, $customerMatches);
if (count($customerMatches) !== 1) {
    echo 'SYNTHETIC_CUSTOMER_EXACT_MATCH_COUNT=' . count($customerMatches) . PHP_EOL;
    throw new RuntimeException('synthetic_customer_exact_match_count_' . count($customerMatches));
}
$customerId = (string) array_key_first($customerMatches);
if (!preg_match('/^[0-9]+$/', $customerId)) {
    throw new RuntimeException('synthetic_customer_id_invalid');
}

// The pre-write run proved the sandbox invoice-draft collection was empty.
// Exactly one provider POST was then executed and returned a draft ID.
// Therefore reconcile from the full draft list using only the previously
// proven search parameters; do not add customer/type/currency filters.
$draftSearchUrl = $baseUrl . '/invoice/organizations/' . $org . '/invoice-drafts?' . http_build_query([
    'hits' => 100,
    'page' => 0,
    'sort' => 'id',
]);
$draftSearch = $http->request('GET', $draftSearchUrl, $headers, null, 30);
if (($draftSearch['ok'] ?? false) !== true || !is_array($draftSearch['body'] ?? null)) {
    printSafeHttpDiagnostic('invoice_draft_search', $draftSearch);
    throw new RuntimeException('invoice_draft_search_failed');
}

$draftSearchBody = $draftSearch['body'];
$hitCount = is_numeric($draftSearchBody['hitCount'] ?? null)
    ? (int) $draftSearchBody['hitCount']
    : null;
$draftHits = $draftSearchBody['hits'] ?? null;

echo 'INVOICE_DRAFT_SEARCH_STATUS=' . (string) ($draftSearch['status'] ?? 0) . PHP_EOL;
echo 'INVOICE_DRAFT_SEARCH_HIT_COUNT=' . ($hitCount === null ? 'unknown' : (string) $hitCount) . PHP_EOL;

if ($hitCount !== 1) {
    throw new RuntimeException('invoice_draft_search_expected_exactly_one_hit');
}
if (!is_array($draftHits) || count($draftHits) !== 1) {
    if (is_array($draftSearchBody)) {
        $keys = array_map('strval', array_keys($draftSearchBody));
        sort($keys, SORT_STRING);
        echo 'INVOICE_DRAFT_SEARCH_TOP_LEVEL_KEYS=' . implode(',', array_slice($keys, 0, 20)) . PHP_EOL;
    }
    throw new RuntimeException('invoice_draft_search_expected_one_hit_object');
}

$candidate = $draftHits[0];
if (!is_array($candidate) || !isset($candidate['id']) || !is_scalar($candidate['id'])) {
    throw new RuntimeException('invoice_draft_search_candidate_id_missing');
}
$targetDraftId = trim((string) $candidate['id']);
if (!preg_match('/^[0-9]+$/', $targetDraftId)) {
    throw new RuntimeException('invoice_draft_search_candidate_id_invalid');
}
if (!hash_equals(EXPECTED_DRAFT_ID_SHA256, hash('sha256', $targetDraftId))) {
    throw new RuntimeException('expected_created_draft_id_hash_mismatch');
}

$readback = $http->request(
    'GET',
    $baseUrl . '/invoice/organizations/' . $org . '/invoice-drafts/' . rawurlencode($targetDraftId),
    $headers,
    null,
    30
);
if (($readback['ok'] ?? false) !== true || !is_array($readback['body'] ?? null)) {
    printSafeHttpDiagnostic('created_draft_readback', $readback);
    throw new RuntimeException('created_draft_readback_failed');
}
$body = $readback['body'];

if (!isset($body['id']) || !is_scalar($body['id']) || !hash_equals(EXPECTED_DRAFT_ID_SHA256, hash('sha256', (string) $body['id']))) {
    throw new RuntimeException('created_draft_identity_hash_mismatch');
}

$expected = [
    'registrationSource' => 'CONTA',
    'invoiceDraftLines' => [[
        'description' => LINE_DESCRIPTION,
        'price' => 1.0,
        'quantity' => 1,
        'discount' => 0,
        'vatCode' => 'high',
        'lineNo' => 1,
    ]],
    'type' => 'NORMAL',
    'customerId' => (int) $customerId,
    'invoiceLanguage' => 'NO',
    'invoiceCurrency' => 'NOK',
];

$expectedPayloadHash = InvoiceDraftPreview::payloadHash($expected);
echo 'EXPECTED_PAYLOAD_SHA256=' . $expectedPayloadHash . PHP_EOL;
echo 'EXPECTED_PAYLOAD_HASH_MATCH=' . (hash_equals(EXPECTED_PAYLOAD_SHA256, $expectedPayloadHash) ? 'true' : 'false') . PHP_EOL;
if (!hash_equals(EXPECTED_PAYLOAD_SHA256, $expectedPayloadHash)) {
    throw new RuntimeException('expected_payload_hash_mismatch');
}

$verifier = new InvoiceDraftReadbackVerifier();
$verification = $verifier->verify($expected, $body);
$mismatches = is_array($verification['mismatches'] ?? null) ? $verification['mismatches'] : [];

echo 'EXPECTED_DRAFT_FOUND=true' . PHP_EOL;
echo 'DRAFT_ID_SHA256=' . hash('sha256', $targetDraftId) . PHP_EOL;
echo 'READBACK_VERIFIED=' . (($verification['verified'] ?? false) === true ? 'true' : 'false') . PHP_EOL;
echo 'MISMATCH_COUNT=' . count($mismatches) . PHP_EOL;

foreach ($mismatches as $index => $mismatch) {
    $path = is_array($mismatch) ? (string) ($mismatch['path'] ?? '') : '';
    $reason = is_array($mismatch) ? (string) ($mismatch['reason'] ?? '') : '';
    $safePath = preg_replace('/[^A-Za-z0-9_.\-\[\]]/', '_', $path) ?? '';
    $safeReason = preg_replace('/[^A-Za-z0-9_.\-]/', '_', $reason) ?? '';
    echo 'MISMATCH_' . ($index + 1) . '_PATH=' . $safePath . PHP_EOL;
    echo 'MISMATCH_' . ($index + 1) . '_REASON=' . $safeReason . PHP_EOL;
}

$actualLine = is_array($body['invoiceDraftLines'][0] ?? null) ? $body['invoiceDraftLines'][0] : [];
printSafeFieldDiagnostic('registrationSource', 'CONTA', $body['registrationSource'] ?? null, true);
printSafeFieldDiagnostic('type', 'NORMAL', $body['type'] ?? null, true);
printSafeFieldDiagnostic('invoiceLanguage', 'NO', $body['invoiceLanguage'] ?? null, true);
printSafeFieldDiagnostic('invoiceCurrency', 'NOK', $body['invoiceCurrency'] ?? null, true);
printSafeFieldDiagnostic('customerId', (int) $customerId, $body['customerId'] ?? null, false);
printSafeFieldDiagnostic('invoiceDraftLines.0.description', LINE_DESCRIPTION, $actualLine['description'] ?? null, false);
printSafeFieldDiagnostic('invoiceDraftLines.0.price', 1.0, $actualLine['price'] ?? null, true);
printSafeFieldDiagnostic('invoiceDraftLines.0.quantity', 1, $actualLine['quantity'] ?? null, true);
printSafeFieldDiagnostic('invoiceDraftLines.0.discount', 0, $actualLine['discount'] ?? null, true);
printSafeFieldDiagnostic('invoiceDraftLines.0.vatCode', 'high', $actualLine['vatCode'] ?? null, true);
printSafeFieldDiagnostic('invoiceDraftLines.0.lineNo', 1, $actualLine['lineNo'] ?? null, true);

echo 'EXPECTED_PROJECTION_SHA256=' . (string) ($verification['expected_projection_hash'] ?? '') . PHP_EOL;
echo 'ACTUAL_PROJECTION_SHA256=' . (string) ($verification['actual_projection_hash'] ?? '') . PHP_EOL;
echo 'PROVIDER_HTTP_METHODS_USED=GET_ONLY' . PHP_EOL;
echo 'PROVIDER_MUTATION_PERFORMED=false' . PHP_EOL;
echo 'PRODUCTION_WRITE_AUTHORIZED=false' . PHP_EOL;
