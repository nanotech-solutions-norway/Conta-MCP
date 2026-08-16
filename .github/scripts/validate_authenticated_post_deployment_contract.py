"""Validate authenticated MCP initialize and tools/list without tools/call."""

from __future__ import annotations

import json
import os
import sys
import urllib.error
import urllib.request
from typing import Any


ENDPOINT = "https://mcp.atlas-ai.no/mcp"
EXPECTED_PROTOCOL = "2025-06-18"
EXPECTED_SERVER_NAME = "conta-mcp-server"
EXPECTED_SERVER_VERSION = "0.1.0"
EXPECTED_TOOLS = {
    "conta_get_customer",
    "conta_get_invoice",
    "conta_health_check",
    "conta_list_customers",
    "conta_list_invoices",
    "conta_list_organizations",
    "conta_preview_invoice_draft",
}
EXECUTION_TOOL = "conta_create_invoice_draft"
MAX_RESPONSE_BYTES = 262_144


class ContractError(RuntimeError):
    """Bounded contract failure safe for CI output."""


def post_json(token: str, payload: dict[str, Any]) -> dict[str, Any]:
    body = json.dumps(payload, separators=(",", ":")).encode()
    request = urllib.request.Request(
        ENDPOINT,
        data=body,
        method="POST",
        headers={
            "Authorization": f"Bearer {token}",
            "Content-Type": "application/json",
        },
    )
    try:
        with urllib.request.urlopen(request, timeout=20) as response:
            if response.status != 200:
                raise ContractError("unexpected_http_status")
            raw = response.read(MAX_RESPONSE_BYTES + 1)
    except urllib.error.HTTPError as exc:
        raise ContractError(f"http_{exc.code}") from None
    except urllib.error.URLError:
        raise ContractError("connection_failed") from None
    if len(raw) > MAX_RESPONSE_BYTES:
        raise ContractError("response_too_large")
    try:
        decoded = json.loads(raw)
    except (UnicodeDecodeError, json.JSONDecodeError):
        raise ContractError("invalid_json") from None
    if not isinstance(decoded, dict):
        raise ContractError("invalid_jsonrpc_envelope")
    if "error" in decoded:
        raise ContractError("jsonrpc_error")
    result = decoded.get("result")
    if not isinstance(result, dict):
        raise ContractError("missing_jsonrpc_result")
    return result


def validate() -> None:
    token = os.environ.get("CONTA_MCP_BEARER_TOKEN", "")
    if not token:
        raise ContractError("bearer_token_missing")

    initialize = post_json(
        token,
        {
            "jsonrpc": "2.0",
            "id": 1,
            "method": "initialize",
            "params": {
                "protocolVersion": EXPECTED_PROTOCOL,
                "capabilities": {},
                "clientInfo": {"name": "post-deployment-validator", "version": "1.0.0"},
            },
        },
    )
    server_info = initialize.get("serverInfo")
    if initialize.get("protocolVersion") != EXPECTED_PROTOCOL:
        raise ContractError("protocol_version_mismatch")
    if not isinstance(server_info, dict):
        raise ContractError("server_info_missing")
    if server_info.get("name") != EXPECTED_SERVER_NAME:
        raise ContractError("server_name_mismatch")
    if server_info.get("version") != EXPECTED_SERVER_VERSION:
        raise ContractError("server_version_mismatch")

    tools_result = post_json(
        token,
        {"jsonrpc": "2.0", "id": 2, "method": "tools/list", "params": {}},
    )
    tools = tools_result.get("tools")
    if not isinstance(tools, list):
        raise ContractError("tools_list_missing")
    names = [tool.get("name") for tool in tools if isinstance(tool, dict)]
    if len(names) != len(tools) or not all(isinstance(name, str) for name in names):
        raise ContractError("invalid_tool_entry")
    if len(names) != len(set(names)):
        raise ContractError("duplicate_tool_name")
    actual = set(names)
    if actual != EXPECTED_TOOLS:
        raise ContractError("tool_set_mismatch")
    if EXECUTION_TOOL in actual:
        raise ContractError("execution_tool_visible")

    print("AUTHENTICATED_INITIALIZE_VERIFIED=true")
    print(f"MCP_PROTOCOL_VERSION={EXPECTED_PROTOCOL}")
    print(f"MCP_SERVER_NAME={EXPECTED_SERVER_NAME}")
    print(f"MCP_SERVER_VERSION={EXPECTED_SERVER_VERSION}")
    print(f"MCP_TOOL_COUNT={len(actual)}")
    print("BASE_READ_TOOL_COUNT=6")
    print("PREVIEW_TOOL_VISIBLE=true")
    print("EXECUTION_TOOL_VISIBLE=false")
    print("MCP_TOOL_SET_VERIFIED=true")
    print("TOOLS_CALL_PERFORMED=false")
    print("PROVIDER_CALL_PERFORMED=false")
    print("PRODUCTION_WRITE_AUTHORIZED=false")
    print("AUTHENTICATED_POST_DEPLOYMENT_MCP_CONTRACT_VALIDATED=true")


def main() -> int:
    try:
        validate()
        return 0
    except ContractError as exc:
        print(f"::error title=Authenticated contract validation stopped::{exc}")
        return 1
    except Exception:
        print("::error title=Authenticated contract validation stopped::unexpected_error")
        return 1


if __name__ == "__main__":
    sys.exit(main())
