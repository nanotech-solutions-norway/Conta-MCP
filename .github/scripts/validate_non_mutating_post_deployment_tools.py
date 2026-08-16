"""Validate the deployed Conta MCP through bounded, non-mutating tool calls."""

from __future__ import annotations

import json
import os
import sys
import urllib.error
import urllib.request
from typing import Any


MCP_ENDPOINT = "https://mcp.atlas-ai.no/mcp"
HEALTH_ENDPOINT = "https://mcp.atlas-ai.no/health"
MAX_RESPONSE_BYTES = 262_144
EXECUTION_TOOL = "conta_create_invoice_draft"
EXPECTED_EXECUTION_REJECTION = "invoice must be an object matching the verified Conta schema."


class ValidationError(RuntimeError):
    """Bounded validation failure safe for CI output."""


def _read_json(response: Any) -> dict[str, Any]:
    raw = response.read(MAX_RESPONSE_BYTES + 1)
    if len(raw) > MAX_RESPONSE_BYTES:
        raise ValidationError("response_too_large")
    try:
        decoded = json.loads(raw)
    except (UnicodeDecodeError, json.JSONDecodeError):
        raise ValidationError("invalid_json") from None
    if not isinstance(decoded, dict):
        raise ValidationError("invalid_json_envelope")
    return decoded


def request_json(url: str, *, token: str | None = None, payload: dict[str, Any] | None = None) -> dict[str, Any]:
    headers = {"Accept": "application/json"}
    data = None
    method = "GET"
    if payload is not None:
        data = json.dumps(payload, separators=(",", ":")).encode()
        headers["Content-Type"] = "application/json"
        method = "POST"
    if token is not None:
        headers["Authorization"] = f"Bearer {token}"
    request = urllib.request.Request(url, data=data, method=method, headers=headers)
    try:
        with urllib.request.urlopen(request, timeout=20) as response:
            if response.status != 200:
                raise ValidationError("unexpected_http_status")
            return _read_json(response)
    except urllib.error.HTTPError as exc:
        raise ValidationError(f"http_{exc.code}") from None
    except urllib.error.URLError:
        raise ValidationError("connection_failed") from None


def call_tool(token: str, request_id: int, name: str, arguments: dict[str, Any]) -> dict[str, Any]:
    envelope = request_json(
        MCP_ENDPOINT,
        token=token,
        payload={
            "jsonrpc": "2.0",
            "id": request_id,
            "method": "tools/call",
            "params": {"name": name, "arguments": arguments},
        },
    )
    if "error" in envelope:
        raise ValidationError(f"{name}_jsonrpc_error")
    result = envelope.get("result")
    if not isinstance(result, dict):
        raise ValidationError(f"{name}_result_missing")
    structured = result.get("structuredContent")
    if not isinstance(structured, dict):
        raise ValidationError(f"{name}_structured_content_missing")
    return {"isError": result.get("isError"), "structuredContent": structured}


def assert_execution_tool_absent(token: str) -> None:
    envelope = request_json(
        MCP_ENDPOINT,
        token=token,
        payload={"jsonrpc": "2.0", "id": 10, "method": "tools/list", "params": {}},
    )
    if "error" in envelope:
        raise ValidationError("tools_list_jsonrpc_error")
    result = envelope.get("result")
    tools = result.get("tools") if isinstance(result, dict) else None
    if not isinstance(tools, list):
        raise ValidationError("tools_list_missing")
    names = [tool.get("name") for tool in tools if isinstance(tool, dict)]
    if len(names) != len(tools) or EXECUTION_TOOL in names:
        raise ValidationError("execution_tool_visible")


def successful_data(result: dict[str, Any], name: str) -> Any:
    structured = result["structuredContent"]
    if result.get("isError") is not False or structured.get("ok") is not True or structured.get("status") != 200:
        status = structured.get("status")
        safe_status = status if isinstance(status, int) and not isinstance(status, bool) else "unknown"
        raise ValidationError(f"{name}_failed_status_{safe_status}")
    return structured.get("data")


def assert_fail_closed_config(config: Any, *, prefix: str) -> None:
    if not isinstance(config, dict):
        raise ValidationError(f"{prefix}_config_missing")
    expected = {
        "write_preview_enabled": True,
        "write_tools_enabled": False,
        "runtime_write_blocked": True,
        "execution_allowed": False,
        "production_write_approved": False,
    }
    for key, value in expected.items():
        if config.get(key) is not value:
            raise ValidationError(f"{prefix}_{key}_mismatch")
    if config.get("allowed_write_action_count") != 0:
        raise ValidationError(f"{prefix}_allowed_action_count_nonzero")
    if config.get("allowed_write_organization_count") != 0:
        raise ValidationError(f"{prefix}_allowed_organization_count_nonzero")


def organization_count(data: Any) -> int:
    if isinstance(data, list):
        return len(data)
    if not isinstance(data, dict):
        raise ValidationError("organization_response_shape_invalid")
    for key in ("organizations", "hits", "data", "items"):
        value = data.get(key)
        if isinstance(value, list):
            return len(value)
    for key in ("hitCount", "total", "count"):
        value = data.get(key)
        if isinstance(value, int) and not isinstance(value, bool) and value >= 0:
            return value
    raise ValidationError("organization_count_unavailable")


def validate() -> None:
    token = os.environ.get("CONTA_MCP_BEARER_TOKEN", "")
    if not token:
        raise ValidationError("bearer_token_missing")

    health = successful_data(call_tool(token, 1, "conta_health_check", {"checkConta": False}), "health_tool")
    if not isinstance(health, dict) or health.get("mcp") != "ok":
        raise ValidationError("health_tool_payload_invalid")
    config = health.get("config")
    if not isinstance(config, dict):
        raise ValidationError("health_tool_config_missing")
    policy = config.get("effective_write_policy")
    if not isinstance(policy, dict):
        raise ValidationError("health_tool_policy_missing")
    if policy.get("preview_enabled") is not True or policy.get("write_tools_enabled") is not False:
        raise ValidationError("health_tool_policy_visibility_mismatch")
    if policy.get("runtime_write_blocked") is not True or policy.get("effective_execution_enabled") is not False:
        raise ValidationError("health_tool_execution_not_blocked")
    if policy.get("production_write_approved") is not False:
        raise ValidationError("health_tool_production_write_approved")

    preview_data = successful_data(
        call_tool(
            token,
            2,
            "conta_preview_invoice_draft",
            {
                "invoice": {
                    "validationMarker": "SYNTHETIC_NON_CUSTOMER_PREVIEW_ONLY",
                    "lines": [{"description": "Synthetic validation line", "quantity": 1, "unitPrice": 1}],
                }
            },
        ),
        "preview_tool",
    )
    if not isinstance(preview_data, dict):
        raise ValidationError("preview_payload_invalid")
    if preview_data.get("mode") != "preview_only_no_provider_call":
        raise ValidationError("preview_mode_mismatch")
    if preview_data.get("provider_call_performed") is not False:
        raise ValidationError("preview_provider_call_not_false")
    if preview_data.get("execution_eligible_now") is not False:
        raise ValidationError("preview_execution_eligible")
    payload_hash = preview_data.get("payload_hash_sha256")
    if not isinstance(payload_hash, str) or len(payload_hash) != 64:
        raise ValidationError("preview_hash_invalid")

    organizations = successful_data(call_tool(token, 3, "conta_list_organizations", {}), "organizations_tool")
    aggregate_count = organization_count(organizations)

    assert_execution_tool_absent(token)
    blocked = call_tool(
        token,
        4,
        EXECUTION_TOOL,
        {},
    )
    blocked_structured = blocked["structuredContent"]
    blocked_data = blocked_structured.get("data")
    if blocked.get("isError") is not True or blocked_structured.get("ok") is not False or blocked_structured.get("status") != 400:
        raise ValidationError("execution_tool_not_rejected")
    if not isinstance(blocked_data, dict) or blocked_data.get("error") != "tool_call_failed":
        raise ValidationError("execution_rejection_shape_invalid")
    if blocked_data.get("message") != EXPECTED_EXECUTION_REJECTION:
        raise ValidationError("execution_rejection_reason_mismatch")

    public_health = request_json(HEALTH_ENDPOINT)
    if public_health.get("status") != "ok" or public_health.get("service") != "conta-mcp-server":
        raise ValidationError("final_public_health_invalid")
    assert_fail_closed_config(public_health.get("config"), prefix="final_health")

    print("AUTHENTICATED_NON_PROVIDER_HEALTH_VERIFIED=true")
    print("SYNTHETIC_PREVIEW_VERIFIED=true")
    print("PREVIEW_PROVIDER_CALL_PERFORMED=false")
    print("READ_ONLY_PROVIDER_CALL_COUNT=1")
    print("READ_ONLY_PROVIDER_CALL_VERIFIED=true")
    print(f"ORGANIZATION_AGGREGATE_COUNT={aggregate_count}")
    print("EXECUTION_TOOL_VISIBLE=false")
    print("EXECUTION_TOOL_DIRECT_CALL_REJECTED=true")
    print("EXECUTION_REJECTION_STAGE=mcp_input_validation_before_provider_dispatch")
    print("PROVIDER_MUTATION_PERFORMED=false")
    print("FINAL_FAIL_CLOSED_HEALTH_VERIFIED=true")
    print("PRODUCTION_WRITE_AUTHORIZED=false")
    print("NON_MUTATING_POST_DEPLOYMENT_TOOL_VALIDATION=true")


def main() -> int:
    try:
        validate()
        return 0
    except ValidationError as exc:
        print(f"::error title=Non-mutating post-deployment validation stopped::{exc}")
        return 1
    except Exception:
        print("::error title=Non-mutating post-deployment validation stopped::unexpected_error")
        return 1


if __name__ == "__main__":
    sys.exit(main())
