<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/HttpClient.php';
require_once __DIR__ . '/ContaClient.php';
require_once __DIR__ . '/ContaTools.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/McpServer.php';

$config = Config::load($rootDir);
$auditLogger = new AuditLogger($config->auditLogPath());
$httpClient = new HttpClient();
$contaClient = new ContaClient($config, $httpClient);
$contaTools = new ContaTools($config, $contaClient, $auditLogger);
$mcpServer = new McpServer($config, $contaTools, $auditLogger);
