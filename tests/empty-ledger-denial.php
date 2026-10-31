<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/WriteExecutionLedger.php';
$root = sys_get_temp_dir() . '/conta98-empty-' . bin2hex(random_bytes(8));
mkdir($root, 0700);
try {
    foreach (['', ' ', '{broken'] as $i => $raw) {
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
    echo "EMPTY_LEDGER_DENIAL_TESTS_PASSED=3\n";
} finally {
    foreach (glob($root . '/*') ?: [] as $path) { unlink($path); }
    rmdir($root);
}
