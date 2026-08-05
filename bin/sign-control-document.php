<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(2);
}

$input = $argv[1] ?? null;
$output = $argv[2] ?? null;
if (!is_string($input) || $input === '' || !is_file($input)) {
    fwrite(STDERR, "Usage: php bin/sign-control-document.php input.json [output.json]\n");
    exit(2);
}

$rootDir = dirname(__DIR__);
require_once $rootDir . '/app/Config.php';
require_once $rootDir . '/app/ApprovalEnvelopeVerifier.php';

$config = Config::load($rootDir);
$verifier = new ApprovalEnvelopeVerifier($config);
$raw = file_get_contents($input);
$document = is_string($raw) ? json_decode($raw, true) : null;
if (!is_array($document)) {
    fwrite(STDERR, "Invalid JSON input\n");
    exit(1);
}
$signed = $verifier->sign($document);
$encoded = json_encode($signed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if (is_string($output) && $output !== '') {
    if (file_put_contents($output, $encoded) === false) {
        fwrite(STDERR, "Unable to write signed document\n");
        exit(1);
    }
    fwrite(STDOUT, $output . PHP_EOL);
} else {
    fwrite(STDOUT, $encoded);
}
