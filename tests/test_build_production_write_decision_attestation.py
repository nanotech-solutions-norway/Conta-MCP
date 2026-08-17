import importlib.util
import io
import json
import os
import tempfile
import unittest
from contextlib import redirect_stdout
from datetime import datetime, timezone
from pathlib import Path
from unittest.mock import patch


SCRIPT = Path(__file__).parents[1] / ".github" / "scripts" / "build_production_write_decision_attestation.py"
WORKFLOW = Path(__file__).parents[1] / ".github" / "workflows" / "conta-production-write-decision-attestation.yml"
SPEC = importlib.util.spec_from_file_location("decision_attestation", SCRIPT)
MODULE = importlib.util.module_from_spec(SPEC)
assert SPEC.loader is not None
SPEC.loader.exec_module(MODULE)


def protected_environment() -> dict[str, str]:
    values = {
        name: f"protected-value-{index}"
        for index, name in enumerate(MODULE.DECISION_VARIABLE_FIELDS.values(), start=1)
    }
    values.update({name: "true" for name in MODULE.REVIEW_VARIABLES})
    return values


class ProductionWriteDecisionAttestationTests(unittest.TestCase):
    def test_governance_workflow_consumes_only_organization_reference_secret(self):
        workflow = WORKFLOW.read_text(encoding="utf-8")
        self.assertEqual(workflow.count("secrets."), 1)
        self.assertIn("secrets.CONTA_PROD_ORGANIZATION_REFERENCE", workflow)
        variable_names = set(MODULE.DECISION_VARIABLE_FIELDS.values()) - {
            "CONTA_PROD_ORGANIZATION_REFERENCE"
        }
        for name in (*variable_names, *MODULE.REVIEW_VARIABLES):
            self.assertIn(f"vars.{name}", workflow)
        for obsolete in (
            "CONTA_PROD_ACCOUNTING_REVIEW_ATTESTED",
            "CONTA_PROD_SECURITY_REVIEW_ATTESTED",
            "CONTA_PROD_CREDENTIAL_CUSTODY_ATTESTED",
            "CONTA_PROD_INCIDENT_REVIEW_ATTESTED",
            "CONTA_PROD_ACCOUNTING_REVIEWER_REFERENCE",
            "CONTA_PROD_SECURITY_RELEASE_REVIEWER_REFERENCE",
            "CONTA_PROD_CREDENTIAL_CUSTODIAN_REFERENCE",
            "CONTA_PROD_EXECUTION_APPROVER_REFERENCE",
            "CONTA_PROD_INCIDENT_OWNER_REFERENCE",
        ):
            self.assertNotIn(obsolete, workflow)

    def test_builds_deterministic_safe_attestation(self):
        env = protected_environment()
        now = datetime(2026, 8, 17, 10, 0, tzinfo=timezone.utc)
        first = MODULE.build_attestation(env, now)
        second = MODULE.build_attestation(env, now)

        self.assertEqual(first, second)
        self.assertRegex(first["DECISION_PACKET_SHA256"], r"^[0-9a-f]{64}$")
        self.assertEqual(first["DECISION_PACKET_VERSION"], "2")
        self.assertEqual(first["GOVERNANCE_MODEL"], "single_human_operator")
        self.assertEqual(first["DECISION_PACKET_EXPIRES_AT"], "2026-08-18T10:00:00Z")
        self.assertTrue(first["SINGLE_HUMAN_OPERATOR_REVIEWED"])
        self.assertFalse(first["PROTECTED_VALUE_PRINTED"])
        self.assertFalse(first["PROVIDER_CALL_PERFORMED"])
        self.assertFalse(first["IMPLEMENTATION_AUTHORIZED"])
        self.assertFalse(first["PRODUCTION_WRITE_AUTHORIZED"])
        serialized = json.dumps(first)
        for value in env.values():
            if value != "true":
                self.assertNotIn(value, serialized)

    def test_rejects_missing_or_unreviewed_values(self):
        env = protected_environment()
        del env["CONTA_PROD_ORGANIZATION_REFERENCE"]
        with self.assertRaisesRegex(ValueError, "CONTA_PROD_ORGANIZATION_REFERENCE"):
            MODULE.build_attestation(env, datetime.now(timezone.utc))

        env = protected_environment()
        env["CONTA_PROD_OPERATOR_REVIEW_ATTESTED"] = "false"
        with self.assertRaisesRegex(ValueError, "operator review"):
            MODULE.build_attestation(env, datetime.now(timezone.utc))

    def test_single_operator_requires_no_separation_of_duties(self):
        env = protected_environment()
        attestation = MODULE.build_attestation(env, datetime.now(timezone.utc))
        self.assertEqual(attestation["GOVERNANCE_MODEL"], "single_human_operator")
        self.assertNotIn("SEPARATION_OF_DUTIES_REVIEWED", attestation)

    def test_cli_prints_only_safe_attestation(self):
        env = protected_environment()
        with tempfile.TemporaryDirectory() as directory:
            output = Path(directory) / "attestation.json"
            stdout = io.StringIO()
            with patch.dict(os.environ, {**env, "ATTESTATION_OUTPUT": str(output)}, clear=True):
                with redirect_stdout(stdout):
                    self.assertEqual(MODULE.main(), 0)
            rendered = stdout.getvalue() + output.read_text(encoding="utf-8")
            for value in env.values():
                if value != "true":
                    self.assertNotIn(value, rendered)


if __name__ == "__main__":
    unittest.main()