<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/ContaClient.php';

if (ContaClient::ORGANIZATIONS_ROUTE !== '/invoice/organizations') {
    throw new RuntimeException('ASSERTION_FAILED: organization-list route regression');
}

echo "CONTA_CLIENT_ROUTE_TESTS_PASSED\n";
