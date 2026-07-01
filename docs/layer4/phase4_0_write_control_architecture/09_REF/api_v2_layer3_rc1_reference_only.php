<?php
/**
 * Conta Bridge v2 — Layer 2 / Phase 2 read-only overlay.
 * Generated: 16:42, 25.06.2026
 *
 * Deployment requirement:
 * - Existing Layer 1.1 /www/cm/api_v2.php must be backed up to /www/cm/api_v2_layer1_backup.php before this file is uploaded.
 * - This entrypoint handles Phase 2 derived endpoints and forwards all other requests to the Layer 1.1 backup.
 */

declare(strict_types=1);

$privateBase = realpath(__DIR__ . '/../private/app/ContaV2');
if ($privateBase === false) {
    $privateBase = __DIR__ . '/../private/app/ContaV2';
}

require_once $privateBase . '/Phase2Response.php';
require_once $privateBase . '/ContaWriteGuard.php';
require_once $privateBase . '/Phase2DataProvider.php';
require_once $privateBase . '/AccountingReportMath.php';
require_once $privateBase . '/AccountingResponseLimiter.php';
require_once $privateBase . '/AccountingTransactionAnalysisService.php';
require_once $privateBase . '/AccountingSummaryService.php';
require_once $privateBase . '/AccountingAgeingService.php';
require_once $privateBase . '/AccountingVatReviewService.php';
require_once $privateBase . '/AccountingDerivedController.php';

ContaWriteGuard::blockIfWriteMethod();

function phase2_current_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    if (strpos($path, '/api/v2') === 0) {
        $path = substr($path, strlen('/api/v2'));
    }
    $path = '/' . trim($path, '/');
    return $path === '/' ? '/' : $path;
}

function phase2_query(): array
{
    $query = $_GET ?? [];
    $clean = [];
    foreach ($query as $k => $v) {
        if (is_array($v)) {
            continue;
        }
        $clean[(string)$k] = is_bool($v) ? $v : (string)$v;
    }
    return $clean;
}

function phase2_base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function phase2_auth_header(): string
{
    $headers = [];
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    }
    foreach ($headers as $k => $v) {
        if (strtolower($k) === 'authorization') {
            return (string)$v;
        }
    }
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return (string)$_SERVER['HTTP_AUTHORIZATION'];
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return (string)$_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    return '';
}

$routes = [
    '/accounting/management-summary' => 'managementSummary',
    '/accounting/derived-profit-and-loss' => 'derivedProfitAndLoss',
    '/accounting/derived-balance-summary' => 'derivedBalanceSummary',
    '/accounting/general-ledger-summary' => 'generalLedgerSummary',
    '/accounting/invoice-journal-summary' => 'invoiceJournalSummary',
    '/accounting/revenue-stream-summary' => 'revenueStreamSummary',
    '/accounting/vat-review-summary' => 'vatReviewSummary',
    '/accounting/accounts-receivable-ageing' => 'accountsReceivableAgeing',
    '/accounting/accounts-reivable-ageing' => 'accountsReceivableAgeing',
    '/accounting/accounts-payable-ageing' => 'accountsPayableAgeing',
    '/accounting/transaction-search' => 'transactionSearch',
    '/accounting/review-flags' => 'reviewFlags',
];

$path = phase2_current_path();


function phase2_private_admin_json(string $fileName): ?array
{
    $safe = basename($fileName);
    $path = __DIR__ . '/../private/admin/' . $safe;
    if (!is_file($path)) {
        return null;
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function phase2_control_endpoint(string $path): bool
{
    if ($path === '/write-status') {
        Phase2Response::send([
            'success' => true,
            'status' => 200,
            'writePaused' => true,
            'effectiveWriteState' => 'PAUSED_UNTIL_FULL_DEPLOYMENT',
            'readyFilesPresent' => true,
            'bridgeVersion' => '2.0-layer2-phase2',
            'phase' => 'Layer 2 / Phase 2 Read-Only Derived Accounting Intelligence',
            'runtimeWriteBlocked' => true,
            'openApiGetOnly' => true,
            'blockedMethods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
            'sourceFallback' => [
                'phase1BackupExpected' => 'api_v2_layer1_backup.php',
                'phase1BackupPresent' => is_file(__DIR__ . '/api_v2_layer1_backup.php'),
            ],
            'timestamp' => gmdate('c'),
        ], 200);
        return true;
    }

    if ($path === '/route-map') {
        $data = phase2_private_admin_json('route_catalog_phase2.json');
        if ($data === null) {
            Phase2Response::error('PHASE2_ROUTE_CATALOG_MISSING', 'Phase 2 route catalog is missing or invalid.', 500, [
                'writePaused' => true,
                'expectedPath' => '/www/private/admin/route_catalog_phase2.json',
            ]);
            return true;
        }
        Phase2Response::send([
            'success' => true,
            'status' => 200,
            'writePaused' => true,
            'bridgeVersion' => '2.0-layer2-phase2',
            'data' => $data,
            'timestamp' => gmdate('c'),
        ], 200);
        return true;
    }

    if ($path === '/capability-matrix') {
        $data = phase2_private_admin_json('capability_matrix_phase2.json');
        if ($data === null) {
            Phase2Response::error('PHASE2_CAPABILITY_MATRIX_MISSING', 'Phase 2 capability matrix is missing or invalid.', 500, [
                'writePaused' => true,
                'expectedPath' => '/www/private/admin/capability_matrix_phase2.json',
            ]);
            return true;
        }
        Phase2Response::send([
            'success' => true,
            'status' => 200,
            'writePaused' => true,
            'bridgeVersion' => '2.0-layer2-phase2',
            'data' => $data,
            'timestamp' => gmdate('c'),
        ], 200);
        return true;
    }

    return false;
}

if (phase2_control_endpoint($path)) {
    exit;
}

if (isset($routes[$path])) {
    $provider = new Phase2DataProvider(phase2_base_url(), phase2_auth_header());
    $controller = new AccountingDerivedController($provider);
    $method = $routes[$path];

    try {
        $payload = $controller->{$method}(phase2_query());

        // Phase 3.5 Patch: preserve canonical management-summary result normalization.
        // Required baseline for 2024: 1422862 - 1414895 = 7967.
        if ($path === '/accounting/management-summary' && isset($payload['summary']) && is_array($payload['summary'])) {
            $incomeTotal = isset($payload['summary']['incomeTotal']) ? (float)$payload['summary']['incomeTotal'] : 0.0;
            $expenseTotal = isset($payload['summary']['expenseTotal']) ? (float)$payload['summary']['expenseTotal'] : 0.0;
            $payload['summary']['result'] = (int)round($incomeTotal - $expenseTotal);
            $payload['summary']['resultSource'] = 'normalized_from_incomeTotal_minus_expenseTotal';
        }

        Phase2Response::send($payload, (int)($payload['status'] ?? 200));
    } catch (Throwable $e) {
        Phase2Response::error('PHASE2_UNHANDLED_EXCEPTION', 'Phase 2 endpoint failed in a controlled manner.', 500, [
            'exceptionType' => get_class($e),
            'safeMessage' => $e->getMessage(),
            'writePaused' => true,
        ]);
    }

    exit;
}

// Conta Bridge v2 Layer 3.1 formal report diagnostics hook.
// Must be after auth and Phase 2 routes, before Phase 1 fallback.
$phase3Diagnostics = __DIR__ . '/phase3_formal_report_diagnostics.php';

if (is_file($phase3Diagnostics)) {
    require_once $phase3Diagnostics;

    if (function_exists('cm_phase3_try_handle') && cm_phase3_try_handle(true)) {
        exit;
    }
}

$phase35Hardening = __DIR__ . '/phase3_5_derived_report_hardening.php';

if (is_file($phase35Hardening)) {
    require_once $phase35Hardening;

    if (function_exists('cm_phase3_5_try_handle') && cm_phase3_5_try_handle(true)) {
        exit;
    }
}

// Conta Bridge v2 Layer 3.6 read-only schema finalization metadata hook.
// Insert after Phase 3.5 derived hardening hook and before Phase 1 fallback.
// Do not insert a second opening PHP tag.
$phase36SchemaFinalization = __DIR__ . '/phase3_6_readonly_schema_finalization.php';

if (is_file($phase36SchemaFinalization)) {
    require_once $phase36SchemaFinalization;

    if (function_exists('cm_phase3_6_try_handle') && cm_phase3_6_try_handle(true)) {
        exit;
    }
}

$backupCandidates = [
    __DIR__ . '/api_v2_layer1_backup.php',
    __DIR__ . '/api_v2_layer1_1_backup.php',
];

foreach ($backupCandidates as $backup) {
    if (is_file($backup)) {
        require $backup;
        exit;
    }
}

Phase2Response::error('PHASE1_BACKUP_MISSING', 'Phase 2 route not matched and Layer 1 backup was not found. Restore /www/cm/api_v2_layer1_backup.php before using non-Phase-2 routes.', 500, [
    'writePaused' => true,
    'expectedBackup' => 'api_v2_layer1_backup.php',
    'requestedPath' => $path,
]);
