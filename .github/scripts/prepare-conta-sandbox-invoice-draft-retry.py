#!/usr/bin/env python3
from pathlib import Path

path = Path('.github/scripts/conta-sandbox-invoice-draft-one-call.php')
text = path.read_text(encoding='utf-8')

# Bind the exact corrected payload produced by protected GET-only preview run
# 31884357398. Keep the full payload hash gate intact; change only the VAT code
# and its corresponding canonical payload hash.
payload_bindings = {
    "const EXPECTED_PAYLOAD_SHA256 = 'dab571f2807745e1236a30dc93ae34ca8b8d2b15daaa26034f68a255e170b786';": "const EXPECTED_PAYLOAD_SHA256 = '61bb8961a82a45f0304909473c020f2f721d738aa4ea6c934722a258d2f346e0';",
    "        'vatCode' => 'no.vat',": "        'vatCode' => 'high',",
}
for old, new in payload_bindings.items():
    if text.count(old) != 1:
        raise SystemExit(f'payload_binding_anchor_count_invalid:{old}:{text.count(old)}')
    text = text.replace(old, new, 1)

pre_start_marker = "    // Current Conta OpenAPI contract for v1SearchInvoiceDrafts:\n"
pre_end_marker = "    // Conservative compatibility fallback for any previously observed list envelope.\n"
pre_start = text.find(pre_start_marker)
pre_end = text.find(pre_end_marker, pre_start)
if pre_start < 0 or pre_end < 0:
    raise SystemExit('prestate_patch_anchor_missing')

pre_replacement = """    // Conta sandbox runtime shape observed 2026-08-13 may return only aggregate fields:\n    // { hitCount: integer, pageCount: integer, sum: number } with no hits array.\n    // Treat hitCount as authoritative for zero/non-zero pre-state. If hits is present,\n    // require it to be consistent with hitCount.\n    if (array_key_exists('hits', $body) || array_key_exists('hitCount', $body)) {\n        if (!array_key_exists('hitCount', $body) || !is_numeric($body['hitCount'])) {\n            throw new RuntimeException('invoice_draft_prestate_unrecognized');\n        }\n\n        $hitCount = (int) $body['hitCount'];\n        if ($hitCount !== 0) {\n            throw new RuntimeException('invoice_draft_prestate_not_empty');\n        }\n\n        if (array_key_exists('hits', $body)) {\n            if (!is_array($body['hits'])) {\n                throw new RuntimeException('invoice_draft_prestate_unrecognized');\n            }\n            if (count($body['hits']) !== 0) {\n                throw new RuntimeException('invoice_draft_prestate_inconsistent');\n            }\n        }\n        return;\n    }\n\n"""
text = text[:pre_start] + pre_replacement + text[pre_end:]

post_start_marker = "        } elseif (array_key_exists('hits', $postBody) || array_key_exists('hitCount', $postBody)) {\n"
post_end_marker = "        } else {\n"
post_start = text.find(post_start_marker)
post_end = text.find(post_end_marker, post_start)
if post_start < 0 or post_end < 0:
    raise SystemExit('poststate_patch_anchor_missing')

post_replacement = """        } elseif (array_key_exists('hitCount', $postBody)) {\n            if (is_numeric($postBody['hitCount'])) {\n                $hitCount = (int) $postBody['hitCount'];\n                if (array_key_exists('hits', $postBody)) {\n                    if (is_array($postBody['hits'])) {\n                        $listedCount = count($postBody['hits']);\n                        if ($hitCount === $listedCount) {\n                            $postStateObserved = $hitCount > 0;\n                        }\n                    }\n                } else {\n                    $postStateObserved = $hitCount > 0;\n                }\n            }\n"""
text = text[:post_start] + post_replacement + text[post_end:]

replacements = {
    "operator_explicit_chat_authorization_20260813": "operator_explicit_chat_authorization_20260814_retry_until_complete",
    "conta-sandbox-onecall-20260813": "conta-sandbox-retry-20260814",
    "single explicitly authorized Conta sandbox invoice-draft validation": "authorized retry-series Conta sandbox invoice-draft validation; one provider mutation per attempt",
    "one-call sandbox authorization consumed or closed": "retry-attempt sandbox authorization consumed or closed",
    "echo 'AUTOMATIC_RETRY_PERFORMED=false' . PHP_EOL;": "echo 'AUTOMATIC_RETRY_PERFORMED=' . (((int) (getenv('CONTA_RETRY_ATTEMPT') ?: '1')) > 1 ? 'true' : 'false') . PHP_EOL;",
}
for old, new in replacements.items():
    if old not in text:
        raise SystemExit(f'retry_patch_anchor_missing:{old}')
    text = text.replace(old, new)

packet_anchor = "    'maxProviderMutations' => 1,\n    'readbackRequired' => true,\n"
packet_replacement = "    'maxProviderMutations' => 1,\n    'retrySeriesAuthorized' => true,\n    'retryUntilCompleted' => true,\n    'readbackRequired' => true,\n"
if packet_anchor not in text:
    raise SystemExit('authorization_packet_patch_anchor_missing')
text = text.replace(packet_anchor, packet_replacement, 1)

# Add redacted diagnostics for provider-side validation/client errors. The raw
# response body is never printed. We emit only a body hash, JSON key names, and
# selected scalar error fields after masking identifiers, emails and long numbers.
diagnostic_anchor = "\n// A created-but-unverified object is still retained as evidence; fail the job so no success is inferred.\n"
if diagnostic_anchor not in text:
    raise SystemExit('provider_diagnostic_patch_anchor_missing')

diagnostic_block = r'''
$providerBody = is_array($result) ? ($result['body'] ?? null) : null;
if ($providerStatus >= 400 && $providerBody !== null) {
    $providerBodyEncoded = is_string($providerBody)
        ? $providerBody
        : json_encode($providerBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    if (is_string($providerBodyEncoded)) {
        echo 'PROVIDER_ERROR_BODY_SHA256=' . hash('sha256', $providerBodyEncoded) . PHP_EOL;
    }

    if (is_array($providerBody)) {
        $topKeys = array_map('strval', array_keys($providerBody));
        sort($topKeys, SORT_STRING);
        echo 'PROVIDER_ERROR_TOP_LEVEL_KEYS=' . implode(',', array_slice($topKeys, 0, 20)) . PHP_EOL;

        $keyPaths = [];
        $collectKeyPaths = function (mixed $node, string $prefix = '') use (&$collectKeyPaths, &$keyPaths): void {
            if (!is_array($node) || count($keyPaths) >= 40) {
                return;
            }
            foreach ($node as $key => $value) {
                if (count($keyPaths) >= 40) {
                    break;
                }
                $keyString = (string) $key;
                $path = $prefix === '' ? $keyString : $prefix . '.' . $keyString;
                $keyPaths[] = $path;
                if (is_array($value)) {
                    $collectKeyPaths($value, $path);
                }
            }
        };
        $collectKeyPaths($providerBody);
        $safePaths = array_map(
            static fn(string $value): string => preg_replace('/[^a-zA-Z0-9_.\-\[\]]/', '_', $value) ?? '',
            $keyPaths
        );
        echo 'PROVIDER_ERROR_KEY_PATHS=' . implode(',', $safePaths) . PHP_EOL;

        foreach (['error', 'code', 'type', 'name', 'title', 'message', 'detail', 'errorMessage', 'exception'] as $key) {
            if (!array_key_exists($key, $providerBody) || !is_scalar($providerBody[$key])) {
                continue;
            }
            $value = trim((string) $providerBody[$key]);
            $value = str_replace([$organizationId, $customerId], '[redacted-id]', $value);
            $value = preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/', '[redacted-email]', $value) ?? $value;
            $value = preg_replace('/\b\d{3,}\b/', '[redacted-number]', $value) ?? $value;
            $value = preg_replace('/[\r\n\t]+/', ' ', $value) ?? $value;
            $value = substr($value, 0, 240);
            $safeKey = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $key) ?? $key);
            echo 'PROVIDER_ERROR_' . $safeKey . '=' . $value . PHP_EOL;
        }
    }
}
'''
text = text.replace(diagnostic_anchor, "\n" + diagnostic_block + diagnostic_anchor, 1)

path.write_text(text, encoding='utf-8')

# Preserve numeric organization IDs as strings in the sandbox write allowlist.
# PHP coerces numeric-string array keys to integers; Config::stringList previously
# deduplicated via keyed arrays and therefore broke strict string comparisons.
config_path = Path('app/Config.php')
config_text = config_path.read_text(encoding='utf-8')
old = """        $out = [];\n        foreach ($value as $item) {\n            $item = trim((string) $item);\n            if ($item !== '') {\n                $out[$item] = true;\n            }\n        }\n        return array_keys($out);\n"""
new = """        $out = [];\n        foreach ($value as $item) {\n            $item = trim((string) $item);\n            if ($item !== '' && !in_array($item, $out, true)) {\n                $out[] = $item;\n            }\n        }\n        return $out;\n"""
if old not in config_text:
    raise SystemExit('config_string_list_patch_anchor_missing')
config_text = config_text.replace(old, new, 1)
config_path.write_text(config_text, encoding='utf-8')

print('RUNTIME_COMPATIBILITY_PATCH_APPLIED=true')
print('NUMERIC_ORGANIZATION_ALLOWLIST_PATCH_APPLIED=true')
print('PROVIDER_ERROR_DIAGNOSTICS_PATCH_APPLIED=true')
print('CORRECTED_VAT_CODE_BOUND=high')
print('CORRECTED_PAYLOAD_SHA256_BOUND=61bb8961a82a45f0304909473c020f2f721d738aa4ea6c934722a258d2f346e0')
print('RETRY_SERIES_AUTHORIZED=true')
print('PER_ATTEMPT_PROVIDER_MUTATION_LIMIT=1')
