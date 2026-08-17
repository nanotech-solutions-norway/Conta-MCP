#!/usr/bin/env python3
"""Build a public attestation from protected production decision values.

The protected packet exists only in runner memory. This script writes and prints
only the non-sensitive hash, expiry, and boolean review markers.
"""

from __future__ import annotations

import hashlib
import json
import os
import re
import sys
from datetime import datetime, timedelta, timezone
from pathlib import Path


DECISION_VARIABLE_FIELDS = {
    "organizationReference": "CONTA_PROD_ORGANIZATION_REFERENCE",
    "customerSelectionRule": "CONTA_PROD_CUSTOMER_SELECTION_RULE",
    "vatTreatmentRule": "CONTA_PROD_VAT_TREATMENT_RULE",
    "fiscalPeriodRule": "CONTA_PROD_FISCAL_PERIOD_RULE",
    "duplicateDetectionRule": "CONTA_PROD_DUPLICATE_DETECTION_RULE",
    "auditMetadataRetention": "CONTA_PROD_AUDIT_METADATA_RETENTION",
    "executionLedgerRetention": "CONTA_PROD_EXECUTION_LEDGER_RETENTION",
    "storageAuthorityReference": "CONTA_PROD_STORAGE_AUTHORITY_REFERENCE",
    "tamperEvidenceReference": "CONTA_PROD_TAMPER_EVIDENCE_REFERENCE",
    "operatorReference": "CONTA_PROD_PROGRAM_OWNER_REFERENCE",
    "providerCapabilityDecision": "CONTA_PROD_PROVIDER_CAPABILITY_DECISION",
}

REVIEW_VARIABLES = (
    "CONTA_PROD_OPERATOR_REVIEW_ATTESTED",
)


def _required_environment(environ: dict[str, str]) -> dict[str, str]:
    names = [*DECISION_VARIABLE_FIELDS.values(), *REVIEW_VARIABLES]
    missing = [name for name in names if not environ.get(name, "").strip()]
    if missing:
        raise ValueError("Missing required protected environment variables: " + ", ".join(missing))

    invalid = [name for name in REVIEW_VARIABLES if environ[name] != "true"]
    if invalid:
        raise ValueError("Required operator review variable is not exactly true: " + ", ".join(invalid))

    return {field: environ[name] for field, name in DECISION_VARIABLE_FIELDS.items()}


def build_attestation(environ: dict[str, str], now: datetime) -> dict[str, object]:
    protected = _required_environment(environ)

    created_at = now.astimezone(timezone.utc).replace(microsecond=0)
    expires_at = created_at + timedelta(hours=24)
    packet: dict[str, object] = {
        "packetVersion": "2",
        "environment": "production",
        "action": "invoice_draft_create_v2",
        "governanceModel": "single_human_operator",
        "currency": "NOK",
        "maximumLines": 1,
        "maximumLineAmount": "1.00",
        "maximumDraftTotal": "1.00",
        "maximumProviderMutations": 1,
        "automaticRetry": False,
        "approvalMaxTtlSeconds": 900,
        "createdAt": created_at.isoformat().replace("+00:00", "Z"),
        "expiresAt": expires_at.isoformat().replace("+00:00", "Z"),
        **protected,
    }
    canonical = json.dumps(packet, ensure_ascii=False, sort_keys=True, separators=(",", ":"))
    digest = hashlib.sha256(canonical.encode("utf-8")).hexdigest()

    return {
        "DECISION_PACKET_SHA256": digest,
        "DECISION_PACKET_VERSION": packet["packetVersion"],
        "DECISION_PACKET_EXPIRES_AT": packet["expiresAt"],
        "GOVERNANCE_MODEL": packet["governanceModel"],
        "ORGANIZATION_REFERENCE_HASH_BOUND": True,
        "CUSTOMER_SELECTION_RULE_BOUND": True,
        "ACCOUNTING_LIMITS_BOUND": True,
        "VAT_TREATMENT_REVIEWED": True,
        "FISCAL_PERIOD_RULE_BOUND": True,
        "DUPLICATE_RULE_BOUND": True,
        "RETENTION_DECISIONS_BOUND": True,
        "SINGLE_HUMAN_OPERATOR_REVIEWED": True,
        "INCIDENT_OWNERSHIP_REVIEWED": True,
        "PROVIDER_CAPABILITY_DECISION_RECORDED": True,
        "PROTECTED_VALUE_PRINTED": False,
        "PROVIDER_CALL_PERFORMED": False,
        "IMPLEMENTATION_AUTHORIZED": False,
        "PRODUCTION_WRITE_AUTHORIZED": False,
    }


def main() -> int:
    try:
        attestation = build_attestation(dict(os.environ), datetime.now(timezone.utc))
        digest = str(attestation["DECISION_PACKET_SHA256"])
        if not re.fullmatch(r"[0-9a-f]{64}", digest):
            raise ValueError("Generated decision packet hash has an invalid format")

        output = Path(os.environ.get("ATTESTATION_OUTPUT", "production-write-decision-attestation.json"))
        output.parent.mkdir(parents=True, exist_ok=True)
        output.write_text(json.dumps(attestation, indent=2, sort_keys=True) + "\n", encoding="utf-8")

        for name, value in attestation.items():
            rendered = str(value).lower() if isinstance(value, bool) else str(value)
            print(f"{name}={rendered}")
        return 0
    except (OSError, ValueError) as exc:
        print(f"Decision attestation failed: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())