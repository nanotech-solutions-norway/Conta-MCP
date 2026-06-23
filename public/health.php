<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

Security::applyCors($config);
Security::handleOptions();

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'status' => 'ok',
    'service' => 'conta-mcp-server',
    'configured' => $config->isConfigured(),
    'config' => $config->publicStatus(),
    'timestamp_utc' => gmdate('c'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
