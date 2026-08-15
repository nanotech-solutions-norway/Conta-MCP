<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/InvoiceDraftPreview.php';
require_once __DIR__ . '/../app/InvoiceDraftReadbackVerifier.php';

function verifierAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('ASSERTION_FAILED: ' . $message);
    }
}

$verifier = new InvoiceDraftReadbackVerifier();

$proposed = [
    'registrationSource' => 'CONTA',
    'invoiceDraftLines' => [[
        'description' => 'Atlas MCP Sandbox Invoice Draft Validation',
        'price' => 1.0,
        'quantity' => 1,
        'discount' => 0,
        'vatCode' => 'high',
        'lineNo' => 1,
    ]],
    'type' => 'NORMAL',
    'customerId' => 123456789,
    'invoiceLanguage' => 'NO',
    'invoiceCurrency' => 'NOK',
];

$exact = $verifier->verify($proposed, $proposed);
verifierAssert(($exact['verified'] ?? false) === true, 'Exact readback must verify.');

$observedContaShape = $proposed;
unset($observedContaShape['registrationSource']);
$observedContaShape['invoiceDraftLines'][0]['price'] = 1;
$observed = $verifier->verify($proposed, $observedContaShape);
verifierAssert(($observed['verified'] ?? false) === true, 'Observed Conta omission and int/float representation must verify.');
verifierAssert(($observed['mismatches'] ?? null) === [], 'Observed Conta shape must have no mismatches.');

$wrongRegistrationSource = $observedContaShape;
$wrongRegistrationSource['registrationSource'] = 'TIMERABBIT';
$wrongRegistration = $verifier->verify($proposed, $wrongRegistrationSource);
verifierAssert(($wrongRegistration['verified'] ?? true) === false, 'Returned registrationSource must still be validated.');

$numericString = $observedContaShape;
$numericString['invoiceDraftLines'][0]['price'] = '1';
$stringResult = $verifier->verify($proposed, $numericString);
verifierAssert(($stringResult['verified'] ?? true) === false, 'Numeric strings must not be coerced into numeric equality.');

$wrongVat = $observedContaShape;
$wrongVat['invoiceDraftLines'][0]['vatCode'] = 'zero.rate';
$vatResult = $verifier->verify($proposed, $wrongVat);
verifierAssert(($vatResult['verified'] ?? true) === false, 'Substantive VAT mismatch must fail verification.');

echo "INVOICE_DRAFT_READBACK_VERIFIER_TESTS_PASSED\n";
