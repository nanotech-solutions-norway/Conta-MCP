<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
foreach ([
    'Config.php',
    'ApprovalEnvelopeVerifier.php',
    'ReleaseManifestGuard.php',
    'WriteKillSwitch.php',
    'SandboxAuthorizationGate.php',
    'WriteDispatchPermit.php',
    'WritePolicy.php',
    'WriteExecutionLedger.php',
    'AuditLogger.php',
    'InvoiceDraftPreview.php',
    'InvoiceDraftReadbackVerifier.php',
    'HttpClient.php',
    'ContaClient.php',
] as $file) {
    require_once $root . '/app/' . $file;
}

const EXPECTED_PAYLOAD_SHA256 = 'dab571f2807745e1236a30dc93ae34ca8b8d2b15daaa26034f68a255e170b786';
const PROVIDER_SCHEMA_SHA256 = '8c8be48fb6cabf22f097f4879be495dbc789a68ceebbad763b526bff85b598a6';
const FIXTURE_NAME = 'Atlas MCP Sandbox Test Customer';
const LINE_DESCRIPTION = 'Atlas MCP Sandbox Invoice Draft Validation';

function envRequired(string $name): string
{
    $value = trim((string) getenv($name));
    if ($value === '') {
        throw new RuntimeException('missing_required_environment:' . $name);
    }
    return $value;
}

function writeJson(string $path, array $value): void
{
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    if ($json === false || file_put_contents($path, $json . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException('unable_to_write_control_file');
    }
    @chmod($path, 0600);
}

function collectExactNamedObjects(mixed $value, string $name, array &$out): void
{
    if (!is_array($value)) {
        return;
    }
    if (isset($value['name'], $value['id']) && (string) $value['name'] === $name) {
        $out[(string) $value['id']] = $value;
    }
    foreach ($value as $child) {
        collectExactNamedObjects($child, $name, $out);
    }
}

function assertZeroDraftPrestate(mixed $body): void
{
    if (!is_array($body)) {
        throw new RuntimeException('invoice_draft_prestate_not_json');
    }

    if (array_is_list($body)) {
        if (count($body) !== 0) {
            throw new RuntimeException('invoice_draft_prestate_not_empty');
        }
        return;
    }

    // Current Conta OpenAPI contract for v1SearchInvoiceDrafts:
    // RouteV1QueryResultInvoiceListExtendedInfoModel { hits: array, hitCount: integer, ... }.
    if (array_key_exists('hits', $body) || array_key_exists('hitCount', $body)) {
        if (!array_key_exists('hits', $body) || !is_array($body['hits'])) {
            throw new RuntimeException('invoice_draft_prestate_unrecognized');
        }
        if (!array_key_exists('hitCount', $body) || !is_numeric($body['hitCount'])) {
            throw new RuntimeException('invoice_draft_prestate_unrecognized');
        }

        $hitCount = (int) $body['hitCount'];
        $listedCount = count($body['hits']);
        if ($hitCount !== $listedCount) {
            throw new RuntimeException('invoice_draft_prestate_inconsistent');
        }
        if ($hitCount !== 0 || $listedCount !== 0) {
            throw new RuntimeException('invoice_draft_prestate_not_empty');
        }
        return;
    }

    // Conservative compatibility fallback for any previously observed list envelope.
    $recognized = false;
    foreach (['totalCount', 'totalElements', 'total', 'count'] as $key) {
        if (array_key_exists($key, $body) && is_numeric($body[$key])) {
            $recognized = true;
            if ((int) $body[$key] !== 0) {
                throw new RuntimeException('invoice_draft_prestate_not_empty');
            }
        }
    }
    foreach (['items', 'content', 'data', 'results', 'invoiceDrafts'] as $key) {
        if (array_key_exists($key, $body) && is_array($body[$key])) {
            $recognized = true;
            if (count($body[$key]) !== 0) {
                throw new RuntimeException('invoice_draft_prestate_not_empty');
            }
        }
    }
    if (!$recognized) {
        throw new RuntimeException('invoice_draft_prestate_unrecognized');
    }
}

function extractDraftIdFromResult(?array $result): ?string
{
    if (!is_array($result)) {
        return null;
    }
    $body = $result['body'] ?? null;
    if (!is_array($body)) {
        return null;
    }
    $create = isset($body['create']) && is_array($body['create']) ? $body['create'] : $body;
    foreach (['id', 'invoiceDraftId', 'invoice_draft_id'] as $key) {
        if (isset($create[$key]) && is_scalar($create[$key]) && trim((string) $create[$key]) !== '') {
            return trim((string) $create[$key]);
        }
    }
    return null;
}

$environment = envRequired('CONTA_ENVIRONMENT');
$baseUrl = rtrim(envRequired('CONTA_API_BASE_URL'), '/');
$apiKey = envRequired('CONTA_API_KEY');
$organizationId = envRequired('CONTA_ORG_ID');
$runAttempt = envRequired('GITHUB_RUN_ATTEMPT');
$releaseCommit = strtolower(envRequired('GITHUB_SHA'));
$runnerTemp = envRequired('RUNNER_TEMP');

if ($runAttempt !== '1') {
    throw new RuntimeException('workflow_rerun_not_authorized');
}
if ($environment !== 'sandbox' || $baseUrl !== 'https://api.gateway.conta-sandbox.no') {
    throw new RuntimeException('sandbox_boundary_mismatch');
}
if (!preg_match('/^[0-9]+$/', $organizationId)) {
    throw new RuntimeException('invalid_organization_id_format');
}
if (!preg_match('/^[a-f0-9]{40}$/', $releaseCommit)) {
    throw new RuntimeException('invalid_release_commit');
}

$workDir = $runnerTemp . '/conta-one-call-' . bin2hex(random_bytes(8));
if (!mkdir($workDir, 0700, true) && !is_dir($workDir)) {
    throw new RuntimeException('temporary_control_directory_unavailable');
}

$manifestPath = $workDir . '/approved-release-manifest.json';
$killSwitchPath = $workDir . '/write-kill-switch.json';
$authorizationPath = $workDir . '/sandbox-authorization.json';
$auditPath = $workDir . '/audit.log';
$ledgerPath = $workDir . '/write-ledger.json';
$approvalSigningKey = bin2hex(random_bytes(32));
$authorizationId = 'sandbox-auth-' . bin2hex(random_bytes(16));
$approvalId = 'approval-' . bin2hex(random_bytes(16));
$approvalNonce = bin2hex(random_bytes(16));
$idempotencyKey = 'idem-' . bin2hex(random_bytes(16));

$config = new Config([
    'environment' => 'sandbox',
    'conta_api_key' => $apiKey,
    'default_organization_id' => $organizationId,
    'enable_write_preview' => true,
    'enable_write_tools' => true,
    'runtime_write_blocked' => false,
    'execution_allowed' => true,
    'production_write_approved' => false,
    'allowed_write_organization_ids' => [$organizationId],
    'allowed_write_actions' => [WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2],
    'create_invoice_draft_route' => '/invoice/organizations/{opContextOrgId}/invoice-drafts',
    'readback_invoice_draft_route' => '/invoice/organizations/{opContextOrgId}/invoice-drafts/{invoiceDraftId}',
    'release_commit' => $releaseCommit,
    'provider_schema_sha256' => PROVIDER_SCHEMA_SHA256,
    'write_policy_version' => '2026-07-16-gate0-4',
    'require_signed_approvals' => true,
    'approval_signing_key' => $approvalSigningKey,
    'approval_key_id' => 'conta-sandbox-onecall-20260813',
    'approval_max_ttl_seconds' => 900,
    'request_timeout_seconds' => 30,
    'approved_release_manifest_path' => $manifestPath,
    'write_kill_switch_path' => $killSwitchPath,
    'sandbox_authorization_path' => $authorizationPath,
    'audit_log_path' => $auditPath,
    'write_ledger_path' => $ledgerPath,
]);

$http = new HttpClient();
$headers = ['apiKey' => $apiKey, 'Accept' => 'application/json'];
$org = rawurlencode($organizationId);

// Fail closed if the sandbox invoice-draft pre-state is not explicitly empty.
$prestate = $http->request('GET', $baseUrl . '/invoice/organizations/' . $org . '/invoice-drafts?hits=100&page=0&sort=id', $headers, null, 30);
if (($prestate['ok'] ?? false) !== true) {
    throw new RuntimeException('invoice_draft_prestate_get_failed_http_' . (string) ($prestate['status'] ?? 0));
}
assertZeroDraftPrestate($prestate['body'] ?? null);

// Resolve and verify the exact authorized synthetic sandbox fixture using GET only.
$searchUrl = $baseUrl . '/invoice/organizations/' . $org . '/customers?' . http_build_query(['q' => FIXTURE_NAME, 'hits' => 100, 'page' => 0]);
$customerSearch = $http->request('GET', $searchUrl, $headers, null, 30);
if (($customerSearch['ok'] ?? false) !== true) {
    throw new RuntimeException('synthetic_customer_search_failed');
}
$matches = [];
collectExactNamedObjects($customerSearch['body'] ?? null, FIXTURE_NAME, $matches);
if (count($matches) !== 1) {
    throw new RuntimeException('synthetic_customer_exact_match_count_' . count($matches));
}
$customerId = (string) array_key_first($matches);
if (!preg_match('/^[0-9]+$/', $customerId)) {
    throw new RuntimeException('synthetic_customer_id_invalid');
}

$customerReadback = $http->request('GET', $baseUrl . '/invoice/organizations/' . $org . '/customers/' . rawurlencode($customerId), $headers, null, 30);
$customerBody = $customerReadback['body'] ?? null;
if (($customerReadback['ok'] ?? false) !== true || !is_array($customerBody)) {
    throw new RuntimeException('synthetic_customer_readback_failed');
}
if ((string) ($customerBody['id'] ?? '') !== $customerId || ($customerBody['name'] ?? null) !== FIXTURE_NAME || ($customerBody['customerType'] ?? null) !== 'INDIVIDUAL' || ($customerBody['isActive'] ?? null) !== true) {
    throw new RuntimeException('synthetic_customer_identity_mismatch');
}

$payload = [
    'registrationSource' => 'CONTA',
    'invoiceDraftLines' => [[
        'description' => LINE_DESCRIPTION,
        'price' => 1.0,
        'quantity' => 1,
        'discount' => 0,
        'vatCode' => 'no.vat',
    ]],
    'type' => 'NORMAL',
    'customerId' => (int) $customerId,
    'invoiceLanguage' => 'NO',
    'invoiceCurrency' => 'NOK',
];
$payloadHash = InvoiceDraftPreview::payloadHash($payload);
if (!hash_equals(EXPECTED_PAYLOAD_SHA256, $payloadHash)) {
    throw new RuntimeException('approved_payload_hash_mismatch');
}

$verifier = new ApprovalEnvelopeVerifier($config);
$releaseGuard = new ReleaseManifestGuard($config, $root);
$runtimeFiles = [
    'app/Config.php',
    'app/ApprovalEnvelopeVerifier.php',
    'app/ReleaseManifestGuard.php',
    'app/WriteKillSwitch.php',
    'app/SandboxAuthorizationGate.php',
    'app/WriteDispatchPermit.php',
    'app/WritePolicy.php',
    'app/WriteExecutionLedger.php',
    'app/AuditLogger.php',
    'app/InvoiceDraftPreview.php',
    'app/InvoiceDraftReadbackVerifier.php',
    'app/HttpClient.php',
    'app/ContaClient.php',
];
$manifest = $releaseGuard->buildObservedManifest($runtimeFiles);
$manifest['status'] = 'APPROVED';
$manifest['approved_by'] = 'operator_explicit_chat_authorization_20260813';
$manifest['approved_at_utc'] = gmdate('c');
writeJson($manifestPath, $manifest);

writeJson($killSwitchPath, [
    'globalBlocked' => false,
    'blockedActions' => [],
    'reason' => 'single explicitly authorized Conta sandbox invoice-draft validation',
    'updatedAtUtc' => gmdate('c'),
]);

$path = '/invoice/organizations/' . $org . '/invoice-drafts';
$now = time();
$authorization = $verifier->sign([
    'authorizationId' => $authorizationId,
    'candidateId' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'action' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'environment' => 'sandbox',
    'organizationId' => $organizationId,
    'payloadHash' => $payloadHash,
    'method' => 'POST',
    'path' => $path,
    'notBefore' => gmdate('c', $now - 30),
    'expiresAt' => gmdate('c', $now + 600),
    'status' => 'APPROVED',
    'maxProviderMutations' => 1,
    'readbackRequired' => true,
    'providerRouteValidated' => true,
    'testCompanyValidated' => true,
]);
writeJson($authorizationPath, $authorization);

$approval = $verifier->sign([
    'approvalId' => $approvalId,
    'approvedBy' => 'operator_explicit_chat_authorization_20260813',
    'approved' => true,
    'oneUse' => true,
    'action' => WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2,
    'organizationId' => $organizationId,
    'environment' => 'sandbox',
    'payloadHash' => $payloadHash,
    'issuedAt' => gmdate('c', $now),
    'expiresAt' => gmdate('c', $now + 600),
    'nonce' => $approvalNonce,
    'idempotencyKey' => $idempotencyKey,
]);

$killSwitch = new WriteKillSwitch($config);
$sandboxGate = new SandboxAuthorizationGate($config, $verifier);
$writePolicy = new WritePolicy($config, $verifier, $releaseGuard, $killSwitch, $sandboxGate);
$ledger = new WriteExecutionLedger($ledgerPath);
$audit = new AuditLogger($auditPath);
$readbackVerifier = new InvoiceDraftReadbackVerifier();
$client = new ContaClient($config, $http, $writePolicy, $ledger, $audit, $readbackVerifier);

$result = null;
$primaryError = null;
$replayRejected = false;
$ledgerReserved = false;
$killSwitchClosed = false;

try {
    try {
        $result = $client->createInvoiceDraft($organizationId, $payload, $approval);
    } catch (Throwable $e) {
        $primaryError = $e->getMessage();
    }

    if (is_file($ledgerPath)) {
        $ledgerState = json_decode((string) file_get_contents($ledgerPath), true);
        $ledgerReserved = is_array($ledgerState) && isset($ledgerState['entries'][$idempotencyKey]);
    }

    // Validate same-key replay rejection only when the first call definitely reserved the ledger entry.
    if ($ledgerReserved) {
        try {
            $client->createInvoiceDraft($organizationId, $payload, $approval);
            throw new RuntimeException('same_key_replay_was_not_rejected');
        } catch (Throwable $e) {
            $replayRejected = in_array($e->getMessage(), [
                'idempotency_key_already_used',
                'approval_nonce_already_used',
                'sandbox_authorization_already_used',
            ], true);
            if (!$replayRejected) {
                throw $e;
            }
        }
    }
} finally {
    writeJson($killSwitchPath, [
        'globalBlocked' => true,
        'blockedActions' => [WritePolicy::ACTION_INVOICE_DRAFT_CREATE_V2],
        'reason' => 'one-call sandbox authorization consumed or closed',
        'updatedAtUtc' => gmdate('c'),
    ]);
    $killSwitchClosed = true;
    @unlink($authorizationPath);
    $approvalSigningKey = str_repeat('0', strlen($approvalSigningKey));
}

$draftId = extractDraftIdFromResult($result);
$verification = is_array($result['body']['verification'] ?? null) ? $result['body']['verification'] : null;
$readbackVerified = is_array($verification) && ($verification['verified'] ?? false) === true;

$outcome = 'INDETERMINATE';
if (is_array($result) && ($result['ok'] ?? false) === true && $draftId !== null && $readbackVerified) {
    $outcome = 'SUCCEEDED_OBJECT_VERIFIED';
} elseif ($draftId !== null) {
    $outcome = 'SUCCEEDED_OBJECT_OBSERVED_UNVERIFIED';
} elseif (is_array($result) && ($result['ok'] ?? false) === false) {
    $outcome = 'FAILED_OR_INDETERMINATE_NO_RETURNED_OBJECT_ID';
} elseif ($primaryError !== null) {
    $outcome = 'INDETERMINATE_EXCEPTION_AFTER_OR_BEFORE_DISPATCH';
}

// If no ID was returned, use an authorized GET-only list to classify whether an object exists after the attempt.
$postStateObserved = null;
if ($draftId === null && $ledgerReserved) {
    $post = $http->request('GET', $baseUrl . '/invoice/organizations/' . $org . '/invoice-drafts?hits=100&page=0&sort=id', $headers, null, 30);
    if (($post['ok'] ?? false) === true && is_array($post['body'] ?? null)) {
        $postBody = $post['body'];
        if (array_is_list($postBody)) {
            $postStateObserved = count($postBody) > 0;
        } elseif (array_key_exists('hits', $postBody) || array_key_exists('hitCount', $postBody)) {
            if (array_key_exists('hits', $postBody) && is_array($postBody['hits']) && array_key_exists('hitCount', $postBody) && is_numeric($postBody['hitCount'])) {
                $hitCount = (int) $postBody['hitCount'];
                $listedCount = count($postBody['hits']);
                if ($hitCount === $listedCount) {
                    $postStateObserved = $hitCount > 0;
                }
            }
        } else {
            foreach (['totalCount', 'totalElements', 'total', 'count'] as $key) {
                if (array_key_exists($key, $postBody) && is_numeric($postBody[$key])) {
                    $postStateObserved = ((int) $postBody[$key]) > 0;
                    break;
                }
            }
            if ($postStateObserved === null) {
                foreach (['items', 'content', 'data', 'results', 'invoiceDrafts'] as $key) {
                    if (array_key_exists($key, $postBody) && is_array($postBody[$key])) {
                        $postStateObserved = count($postBody[$key]) > 0;
                        break;
                    }
                }
            }
        }
        if ($postStateObserved === true) {
            $outcome = 'SUCCEEDED_OBJECT_OBSERVED_BY_POSTSTATE_GET_UNVERIFIED';
        } elseif ($postStateObserved === false) {
            $outcome = 'FAILED_NO_OBJECT_OBSERVED_BY_POSTSTATE_GET';
        }
    }
}

$providerStatus = is_array($result) ? (int) ($result['status'] ?? 0) : 0;
echo 'EXECUTION_OUTCOME=' . $outcome . PHP_EOL;
echo 'PAYLOAD_SHA256=' . $payloadHash . PHP_EOL;
echo 'LEDGER_RESERVED=' . ($ledgerReserved ? 'true' : 'false') . PHP_EOL;
echo 'SAME_KEY_REPLAY_REJECTED=' . ($replayRejected ? 'true' : 'false') . PHP_EOL;
echo 'KILL_SWITCH_CLOSED=' . ($killSwitchClosed ? 'true' : 'false') . PHP_EOL;
echo 'PRODUCTION_WRITE_AUTHORIZED=false' . PHP_EOL;
echo 'AUTOMATIC_RETRY_PERFORMED=false' . PHP_EOL;
echo 'PROVIDER_RESULT_STATUS=' . $providerStatus . PHP_EOL;
echo 'READBACK_VERIFIED=' . ($readbackVerified ? 'true' : 'false') . PHP_EOL;
if ($draftId !== null) {
    echo 'DRAFT_ID_SHA256=' . hash('sha256', $draftId) . PHP_EOL;
}
if ($verification !== null && is_string($verification['actual_projection_hash'] ?? null)) {
    echo 'READBACK_PROJECTION_SHA256=' . $verification['actual_projection_hash'] . PHP_EOL;
}
if ($primaryError !== null) {
    echo 'PRIMARY_ERROR_CLASS=' . preg_replace('/[^a-zA-Z0-9_\-:.]/', '_', $primaryError) . PHP_EOL;
}

// A created-but-unverified object is still retained as evidence; fail the job so no success is inferred.
if ($outcome !== 'SUCCEEDED_OBJECT_VERIFIED' || !$replayRejected || !$killSwitchClosed) {
    exit(1);
}
