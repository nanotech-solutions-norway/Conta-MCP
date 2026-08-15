<?php

declare(strict_types=1);

final class InvoiceDraftReadbackVerifier
{
    private const TOP_LEVEL_FIELDS = [
        'registrationSource', 'type', 'departmentId', 'projectId', 'customerId',
        'customerGroupId', 'invoiceLanguage', 'invoiceCurrency', 'exchangeRate',
        'exchangeRateReferenceDate', 'personalMessage', 'customerReference',
        'customerReferenceContactId', 'saleId',
    ];

    private const LINE_FIELDS = [
        'productId', 'description', 'price', 'quantity', 'discount', 'vatCode', 'lineNo',
    ];

    /**
     * Conta's published RouteV1InvoiceDraftModel exposes registrationSource but does
     * not mark it as required on readback. The sandbox provider has been observed
     * omitting it after a successful create, so absence alone is not a mismatch.
     * If the provider does return it, the value is still verified normally.
     */
    private const OPTIONAL_READBACK_PATHS = [
        'registrationSource',
    ];

    public function verify(array $proposed, array $readback): array
    {
        $expected = $this->controlledProjection($proposed);
        $actual = $this->controlledProjection($readback);
        $mismatches = [];
        $this->compare($expected, $actual, '', $mismatches);

        return [
            'verified' => $mismatches === [],
            'mismatches' => $mismatches,
            'expected_projection_hash' => InvoiceDraftPreview::payloadHash($expected),
            'actual_projection_hash' => InvoiceDraftPreview::payloadHash($actual),
        ];
    }

    private function controlledProjection(array $payload): array
    {
        $out = [];
        foreach (self::TOP_LEVEL_FIELDS as $field) {
            if (array_key_exists($field, $payload)) {
                $out[$field] = $payload[$field];
            }
        }

        $lines = $payload['invoiceDraftLines'] ?? [];
        if (is_array($lines)) {
            $out['invoiceDraftLines'] = [];
            foreach ($lines as $line) {
                if (!is_array($line)) {
                    continue;
                }
                $projected = [];
                foreach (self::LINE_FIELDS as $field) {
                    if (array_key_exists($field, $line)) {
                        $projected[$field] = $line[$field];
                    }
                }
                $out['invoiceDraftLines'][] = $projected;
            }
        }
        return InvoiceDraftPreview::canonicalize($out);
    }

    private function compare(mixed $expected, mixed $actual, string $path, array &$mismatches): void
    {
        if (is_array($expected)) {
            if (!is_array($actual)) {
                $mismatches[] = ['path' => $path, 'reason' => 'type_mismatch'];
                return;
            }
            foreach ($expected as $key => $value) {
                $childPath = $path === '' ? (string) $key : $path . '.' . $key;
                if (!array_key_exists($key, $actual)) {
                    if (in_array($childPath, self::OPTIONAL_READBACK_PATHS, true)) {
                        continue;
                    }
                    $mismatches[] = ['path' => $childPath, 'reason' => 'missing_in_readback'];
                    continue;
                }
                $this->compare($value, $actual[$key], $childPath, $mismatches);
            }
            return;
        }

        // JSON Schema `number` values may decode as either PHP int or float
        // depending on the provider's lexical representation (for example 1 vs 1.0).
        // Accept numeric equivalence only when both values are actual PHP numbers;
        // numeric strings are intentionally not coerced.
        if ((is_int($expected) || is_float($expected)) && (is_int($actual) || is_float($actual))) {
            if ((float) $expected === (float) $actual) {
                return;
            }
        }

        if ($expected !== $actual) {
            $mismatches[] = ['path' => $path, 'reason' => 'value_mismatch'];
        }
    }
}
