<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

$args = array_slice($argv, 1);
$execute = in_array('--execute', $args, true);
$args = array_values(array_filter($args, static fn(string $arg): bool => $arg !== '--execute'));
$payloadPath = $args[0] ?? null;
$approvalPath = $args[1] ?? null;

if (!$execute) {
    fwrite(STDERR, "Execution flag missing. Use --execute only after explicit one-call operator authorization.\n");
    exit(3);
}
if (getenv('CONTA_SANDBOX_ONE_CALL_ACK') !== 'AUTHORIZE_EXACTLY_ONE_INVOICE_DRAFT_CREATE_V2') {
    fwrite(STDERR, "Exact one-call acknowledgement missing.\n");
    exit(3);
}
if (!is_string($payloadPath) || !is_file($payloadPath) || !is_string($approvalPath) || !is_file($approvalPath)) {
    fwrite(STDERR, "Usage: php bin/sandbox-one-call.php payload.json approval.json --execute\n");
    exit(2);
}

$payload = json_decode((string) file_get_contents($payloadPath), true);
$approval = json_decode((string) file_get_contents($approvalPath), true);
if (!is_array($payload) || !is_array($approval)) {
    fwrite(STDERR, "Payload and approval must be valid JSON objects.\n");
    exit(2);
}

require dirname(__DIR__) . '/app/bootstrap.php';
if ($config->environment() !== 'sandbox') {
    fwrite(STDERR, "This harness refuses non-sandbox execution.\n");
    exit(3);
}

$result = $contaClient->createInvoiceDraft($config->organizationId(), $payload, $approval);
fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
exit(($result['ok'] ?? false) === true ? 0 : 1);
