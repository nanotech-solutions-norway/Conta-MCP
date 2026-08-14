#!/usr/bin/env python3
from pathlib import Path

path = Path('.github/scripts/conta-sandbox-invoice-draft-one-call.php')
text = path.read_text(encoding='utf-8')

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

path.write_text(text, encoding='utf-8')
print('RUNTIME_COMPATIBILITY_PATCH_APPLIED=true')
print('RETRY_SERIES_AUTHORIZED=true')
print('PER_ATTEMPT_PROVIDER_MUTATION_LIMIT=1')
