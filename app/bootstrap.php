<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/HttpClient.php';
require_once __DIR__ . '/ApprovalEnvelopeVerifier.php';
require_once __DIR__ . '/ReleaseManifestGuard.php';
require_once __DIR__ . '/WriteKillSwitch.php';
require_once __DIR__ . '/SandboxAuthorizationGate.php';
require_once __DIR__ . '/WriteDispatchPermit.php';
require_once __DIR__ . '/WritePolicy.php';
require_once __DIR__ . '/WriteExecutionLedger.php';
require_once __DIR__ . '/InvoiceDraftPreview.php';
require_once __DIR__ . '/InvoiceDraftReadbackVerifier.php';
require_once __DIR__ . '/ContaClient.php';
require_once __DIR__ . '/ContaTools.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/McpServer.php';

$config = Config::load($rootDir);
$auditLogger = new AuditLogger($config->auditLogPath());
$httpClient = new HttpClient();
$approvalVerifier = new ApprovalEnvelopeVerifier($config);
$releaseManifestGuard = new ReleaseManifestGuard($config, $rootDir);
$killSwitch = new WriteKillSwitch($config);
$sandboxAuthorizationGate = new SandboxAuthorizationGate($config, $approvalVerifier);
$writePolicy = new WritePolicy($config, $approvalVerifier, $releaseManifestGuard, $killSwitch, $sandboxAuthorizationGate);
$writeLedger = new WriteExecutionLedger($config->writeLedgerPath());
$invoiceDraftPreview = new InvoiceDraftPreview($config, $writePolicy);
$readbackVerifier = new InvoiceDraftReadbackVerifier();
$contaClient = new ContaClient($config, $httpClient, $writePolicy, $writeLedger, $auditLogger, $readbackVerifier);
$contaTools = new ContaTools($config, $contaClient, $auditLogger, $writePolicy, $invoiceDraftPreview);
$mcpServer = new McpServer($config, $contaTools, $auditLogger);
