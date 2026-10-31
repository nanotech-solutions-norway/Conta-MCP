<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/WriteExecutionLedger.php';
$root = sys_get_temp_dir() . '/conta98-empty-' . bin2hex(random_bytes(8));
mkdir($root, 0700);
try {
    foreach (['', ' ', '{broken', '{}', '[]', '{"entries":{},"approval_nonces":{}}', '{"entries":{},"approval_nonces":{},"authorization_ids":null}', '{"entries":{"synthetic-key":{"status":"unknown","payload_hash":"synthetic"}},"approval_nonces":{},"authorization_ids":{}}'] as $i => $raw) {
        $path = $root . '/' . $i . '.json';
        file_put_contents($path, $raw);
        try {
            (new WriteExecutionLedger($path))->complete('synthetic-key', false);
            throw new LogicException('corrupt ledger accepted');
        } catch (RuntimeException $error) {
            if ($error->getMessage() !== 'write_ledger_corrupt') { throw $error; }
        }
        if (file_get_contents($path) !== $raw) { throw new LogicException('corrupt evidence modified'); }
    }
    foreach ([false, true] as $existing) {
        $target = $root . '/target-' . (int)$existing;
        $link = $root . '/link-' . (int)$existing;
        if ($existing) { file_put_contents($target, 'synthetic-preserved'); }
        if (!symlink($target, $link)) { throw new LogicException('symlink fixture failed'); }
        try {
            (new WriteExecutionLedger($link))->complete('synthetic-key', false);
            throw new LogicException('symlink accepted');
        } catch (RuntimeException $error) {
            if ($error->getMessage() !== 'write_ledger_corrupt') { throw $error; }
        }
        if (!is_link($link) || ($existing && file_get_contents($target) !== 'synthetic-preserved') ||
            (!$existing && file_exists($target))) { throw new LogicException('symlink evidence modified'); }
    }
    echo "EMPTY_LEDGER_DENIAL_TESTS_PASSED=10\n";
} finally {
    foreach (glob($root . '/*') ?: [] as $path) { unlink($path); }
    rmdir($root);
}
