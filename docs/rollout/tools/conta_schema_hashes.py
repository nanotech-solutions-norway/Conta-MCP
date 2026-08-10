#!/usr/bin/env python3
"""Hash the invoice-draft request and success-response schema from a local OpenAPI file."""

from __future__ import annotations

import argparse
import hashlib
import json
from pathlib import Path
from typing import Any


ROUTE = "/invoice/organizations/{opContextOrgId}/invoice-drafts"


def canonical(value: Any) -> bytes:
    return json.dumps(
        value,
        ensure_ascii=False,
        separators=(",", ":"),
        sort_keys=True,
    ).encode("utf-8")


def local_refs(value: Any) -> set[str]:
    refs: set[str] = set()
    if isinstance(value, dict):
        ref = value.get("$ref")
        if isinstance(ref, str) and ref.startswith("#/"):
            refs.add(ref)
        for child in value.values():
            refs.update(local_refs(child))
    elif isinstance(value, list):
        for child in value:
            refs.update(local_refs(child))
    return refs


def resolve(spec: dict[str, Any], ref: str) -> Any:
    value: Any = spec
    for part in ref[2:].split("/"):
        value = value[part.replace("~1", "/").replace("~0", "~")]
    return value


def closure(spec: dict[str, Any], root: Any) -> dict[str, Any]:
    result: dict[str, Any] = {"$root": root}
    pending = list(local_refs(root))
    seen: set[str] = set()
    while pending:
        ref = pending.pop()
        if ref in seen:
            continue
        seen.add(ref)
        node = resolve(spec, ref)
        result[ref] = node
        pending.extend(local_refs(node) - seen)
    return result


def digest(value: Any) -> tuple[str, int]:
    encoded = canonical(value)
    return hashlib.sha256(encoded).hexdigest(), len(encoded)


def report(label: str, spec: dict[str, Any], root: Any) -> None:
    direct_hash, direct_bytes = digest(root)
    schema_closure = closure(spec, root)
    closure_hash, closure_bytes = digest(schema_closure)
    refs = sorted(key for key in schema_closure if key != "$root")
    print(f"{label}_direct_sha256={direct_hash}")
    print(f"{label}_direct_bytes={direct_bytes}")
    print(f"{label}_closure_sha256={closure_hash}")
    print(f"{label}_closure_bytes={closure_bytes}")
    print(f"{label}_closure_refs={','.join(refs)}")


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("openapi_file", type=Path)
    args = parser.parse_args()

    raw = args.openapi_file.read_bytes()
    spec = json.loads(raw)
    operation = spec["paths"][ROUTE]["post"]
    request_schema = operation["requestBody"]["content"]["application/json"]["schema"]
    response_schema = operation["responses"]["200"]["content"]["application/json"]["schema"]

    print(f"document_sha256={hashlib.sha256(raw).hexdigest()}")
    print(f"operation_id={operation['operationId']}")
    print(f"request_body_required={str(operation['requestBody'].get('required', False)).lower()}")
    print("success_status=200")
    report("request", spec, request_schema)
    report("success_response", spec, response_schema)


if __name__ == "__main__":
    main()

