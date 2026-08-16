from __future__ import annotations

import importlib.util
from pathlib import Path
import unittest


SCRIPT = Path(__file__).resolve().parents[1] / ".github" / "scripts" / "validate_non_mutating_post_deployment_tools.py"
SPEC = importlib.util.spec_from_file_location("non_mutating_validator", SCRIPT)
assert SPEC is not None and SPEC.loader is not None
validator = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(validator)


class OrganizationCountTests(unittest.TestCase):
    def test_counts_top_level_list(self) -> None:
        self.assertEqual(validator.organization_count([{}, {}]), 2)

    def test_counts_known_collection_without_inspecting_records(self) -> None:
        self.assertEqual(validator.organization_count({"hits": [{}, {}, {}]}), 3)

    def test_accepts_aggregate_count(self) -> None:
        self.assertEqual(validator.organization_count({"hitCount": 4}), 4)

    def test_rejects_unknown_shape(self) -> None:
        with self.assertRaisesRegex(validator.ValidationError, "organization_count_unavailable"):
            validator.organization_count({"privatePayload": {"id": "must-not-be-logged"}})


class FailClosedConfigTests(unittest.TestCase):
    def setUp(self) -> None:
        self.config = {
            "write_preview_enabled": True,
            "write_tools_enabled": False,
            "runtime_write_blocked": True,
            "execution_allowed": False,
            "production_write_approved": False,
            "allowed_write_action_count": 0,
            "allowed_write_organization_count": 0,
        }

    def test_accepts_expected_state(self) -> None:
        validator.assert_fail_closed_config(self.config, prefix="test")

    def test_rejects_open_execution_gate(self) -> None:
        self.config["execution_allowed"] = True
        with self.assertRaisesRegex(validator.ValidationError, "test_execution_allowed_mismatch"):
            validator.assert_fail_closed_config(self.config, prefix="test")


if __name__ == "__main__":
    unittest.main()
