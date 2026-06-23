#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://www.nanoconcept.no/conta-mcp}"
TOKEN="${CONTA_MCP_BEARER_TOKEN:-}"

if [[ -z "$TOKEN" ]]; then
  echo "ERROR: Set CONTA_MCP_BEARER_TOKEN before running this test." >&2
  exit 1
fi

echo "Testing health endpoint..."
curl -sS "$BASE_URL/health"
echo

echo "Testing initialize..."
curl -sS -X POST "$BASE_URL/mcp" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"smoke-test","version":"1.0.0"}}}'
echo

echo "Testing tools/list..."
curl -sS -X POST "$BASE_URL/mcp" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":2,"method":"tools/list"}'
echo

echo "Testing conta_health_check tool..."
curl -sS -X POST "$BASE_URL/mcp" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":3,"method":"tools/call","params":{"name":"conta_health_check","arguments":{"checkConta":false}}}'
echo

echo "Smoke test completed."
