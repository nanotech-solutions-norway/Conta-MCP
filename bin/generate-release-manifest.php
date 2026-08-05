<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

$rootDir = dirname(__DIR__);
require_once $rootDir . '/app/Config.php';
require_once $rootDir . '/app/ReleaseManifestGuard.php';

$config = Config::load($rootDir);
$guard = new ReleaseManifestGuard($config, $rootDir);
$paths = [
    'app/Config.php',
    'app/ApprovalEnvelopeVerifier.php',
    'app/ReleaseManifestGuard.php',
    'app/WriteKillSwitch.php',
    'app/SandboxAuthorizationGate.php',
    'app/WriteDispatchPermit.php',
    'app/WritePolicy.php',
    'app/WriteExecutionLedger.php',
    'app/InvoiceDraftPreview.php',
    'app/InvoiceDraftReadbackVerifier.php',
    'app/ContaClient.php',
    'app/ContaTools.php',
    'app/bootstrap.php',
];

$manifest = $guard->buildObservedManifest($paths);
$output = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
$target = $argv[1] ?? null;
if (is_string($target) && $target !== '') {
    if (file_put_contents($target, $output) === false) {
        fwrite(STDERR, "Unable to write manifest\n");
        exit(1);
    }
    fwrite(STDOUT, $target . PHP_EOL);
    exit(0);
}
fwrite(STDOUT, $output);
