#!/usr/bin/env python3
from pathlib import Path

path = Path('.github/scripts/conta-sandbox-invoice-draft-one-call.php')
text = path.read_text(encoding='utf-8')

old_hash = "const EXPECTED_PAYLOAD_SHA256 = '61bb8961a82a45f0304909473c020f2f721d738aa4ea6c934722a258d2f346e0';"
new_hash = "const EXPECTED_PAYLOAD_SHA256 = '79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7';"
line_anchor = "        'vatCode' => 'high',\n"
line_replacement = "        'vatCode' => 'high',\n        'lineNo' => 1,\n"

if text.count(old_hash) != 1:
    raise SystemExit(f'lineno_payload_hash_anchor_count_invalid:{text.count(old_hash)}')
if text.count(line_anchor) != 1:
    raise SystemExit(f'lineno_payload_anchor_count_invalid:{text.count(line_anchor)}')

text = text.replace(old_hash, new_hash, 1)
text = text.replace(line_anchor, line_replacement, 1)
path.write_text(text, encoding='utf-8')

final_text = path.read_text(encoding='utf-8')
if final_text.count(new_hash) != 1:
    raise SystemExit('lineno_payload_hash_binding_failed')
if final_text.count("        'lineNo' => 1,\n") != 1:
    raise SystemExit('lineno_payload_binding_failed')
if final_text.count("        'vatCode' => 'high',\n") != 1:
    raise SystemExit('high_vat_binding_missing_after_lineno_patch')

print('LINE_NUMBER_BOUND=1')
print('CORRECTED_VAT_CODE_BOUND=high')
print('CORRECTED_PAYLOAD_SHA256_BOUND=79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7')
print('PRODUCTION_WRITE_AUTHORIZED=false')
