<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/WriteExecutionLedger.php';
require_once __DIR__ . '/../app/WriteDispatchPermit.php';
function permit(): WriteDispatchPermit {
    return new WriteDispatchPermit('synthetic-create', 'POST', '/synthetic',
        'synthetic-org', str_repeat('a',64), 'synthetic-key', 'synthetic-approval',
        'synthetic-nonce', 'synthetic-auth', 'synthetic-v1', str_repeat('b',64));
}
function demand(bool $ok, string $why): void {
    if (!$ok) { throw new RuntimeException($why); }
}
if (($argv[1] ?? '') === 'worker') {
    // Parent holds a stable ledger inode lock until both workers have started.
    try {
        (new WriteExecutionLedger($argv[2]))->reserve(permit());
        echo "reserved"; exit(0);
    } catch (RuntimeException $error) {
        echo $error->getMessage(); exit(2);
    }
}
if (($argv[1] ?? '') === 'crash') {
    (new WriteExecutionLedger($argv[2]))->reserve(permit());
    // Abrupt termination after durable reservation, before any synthetic outcome.
    exit(77);
}
$root = sys_get_temp_dir() . '/conta98-process-' . bin2hex(random_bytes(8));
demand(mkdir($root, 0700), 'fixture directory');
$children = [];
try {
    $path = $root . '/collision.json';
    file_put_contents($path, '{"entries":{},"approval_nonces":{},"authorization_ids":{}}');
    $lock = fopen($path . '.lock', 'c+');
    demand(flock($lock, LOCK_EX), 'fixture lock');
    for ($i=0; $i<2; $i++) {
        $pipes = [];
        $process = proc_open([PHP_BINARY, __FILE__, 'worker', $path],
            [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
        demand(is_resource($process), 'worker start');
        fclose($pipes[0]);
        $children[] = [$process, $pipes];
    }
    flock($lock, LOCK_UN); fclose($lock);
    $outcomes = [];
    foreach ($children as [$process,$pipes]) {
        $outcomes[] = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        $code = proc_close($process);
        demand($err === '' && in_array($code,[0,2],true), 'worker failed');
    }
    $children = [];
    sort($outcomes);
    demand($outcomes === ['idempotency_key_already_used','reserved'], 'collision was not single-winner');
    $state = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    demand(count($state['entries']) === 1 && count($state['approval_nonces']) === 1, 'collision ledger integrity');
    $path = $root . '/crash.json';
    $pipes = [];
    $process = proc_open([PHP_BINARY,__FILE__,'crash',$path],
        [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
    demand(is_resource($process),'crash fixture start');
    foreach ($pipes as $pipe) { fclose($pipe); }
    demand(proc_close($process) === 77, 'crash fixture exit');
    $state = json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
    demand($state['entries']['synthetic-key']['status'] === 'reserved', 'reservation lost on crash');
    try {
        (new WriteExecutionLedger($path))->reserve(permit());
        throw new LogicException('ambiguous dispatch replay accepted');
    } catch (RuntimeException $error) {
        demand($error->getMessage() === 'idempotency_key_already_used', 'wrong replay denial');
    }
    (new WriteExecutionLedger($path))->complete('synthetic-key', false);
    try {
        (new WriteExecutionLedger($path))->reserve(permit());
        throw new LogicException('uncertain terminal replay accepted');
    } catch (RuntimeException $error) {
        demand($error->getMessage() === 'idempotency_key_already_used', 'wrong uncertain denial');
    }
    // A crash before atomic rename leaves a partial temporary file. It must
    // never replace the committed snapshot or reopen the consumed reservation.
    $before = file_get_contents($path);
    file_put_contents($path . '.pending-orphan', '{"entries":');
    try {
        (new WriteExecutionLedger($path))->reserve(permit());
        throw new LogicException('orphan reset reservation');
    } catch (RuntimeException $error) {
        demand($error->getMessage() === 'idempotency_key_already_used', 'orphan replay denial');
    }
    demand(file_get_contents($path) === $before, 'committed snapshot changed by orphan');
    demand(file_get_contents($path . '.pending-orphan') === '{"entries":', 'orphan evidence changed');
    clearstatcache(true, $path);
    $inode = fileinode($path);
    (new WriteExecutionLedger($path))->complete('synthetic-key', false);
    clearstatcache(true, $path);
    demand(fileinode($path) !== $inode, 'snapshot was truncated instead of replaced');
    $raw = file_get_contents($path);
    demand(!str_contains($raw, 'synthetic-org') && !str_contains($raw,'synthetic-approval'), 'unredacted audit fields');
    echo "PROCESS_LEDGER_REGRESSIONS_PASSED=10\nPROVIDER_CALL_PERFORMED=false\n";
} finally {
    foreach ($children as [$process,$pipes]) {
        proc_terminate($process);
        foreach ($pipes as $pipe) { if (is_resource($pipe)) { fclose($pipe); } }
        proc_close($process);
    }
    foreach (glob($root . '/*') ?: [] as $path) { unlink($path); }
    rmdir($root);
}
