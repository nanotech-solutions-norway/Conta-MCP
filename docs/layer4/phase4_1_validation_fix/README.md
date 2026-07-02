# Conta Bridge v2 — Layer 4 / Phase 4.1 Validation Script Fix v2

Timestamp: 10:42, 02.07.2026

Purpose:
- Replace the Phase 4.1 baseline validator with a Windows PowerShell 5.1-compatible script.
- Avoid PowerShell 7-only syntax such as `?.`.
- Preserve Layer 3 / Phase 3.10.1 accepted write-status contract.
- No production runtime change.
- No OpenAPI schema change.
- No write capability implementation or exposure.

Run:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force

& "C:\Users\meyer\My Drive\NanoTech Solutions Norway\Prosjekter\Atlas Project\Custom ChatGPT models\Conta MCP\Conta_L4P41_ValidationScriptFix_v2_1042_02072026\08_TESTS\phase4_1_confirm_readonly_baseline_v3_ps51.ps1" -BaseUrl "https://mcp.atlas-ai.no"
```

Expected:

```text
PASSED: write-status read-only controls verified.
Resolved incomeTotal from summary.incomeTotal: 1422862
Resolved expenseTotal from summary.expenseTotal: 1414895
Resolved result from summary.result: 7967
PASSED: Production baseline matches Layer 3 read-only RC1 controls and 2024 management-summary baseline.
```
