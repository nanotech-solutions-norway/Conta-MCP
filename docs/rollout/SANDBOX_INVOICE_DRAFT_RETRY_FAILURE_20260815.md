# Conta sandbox invoice-draft retry failure record — 2026-08-15

Source: operator-supplied GitHub Actions log archive `logs_86358333554.zip` for retry workflow run #3.

Observed result:

```text
RETRY_ATTEMPT=1
EXECUTION_OUTCOME=INDETERMINATE_EXCEPTION_AFTER_OR_BEFORE_DISPATCH
LEDGER_RESERVED=false
SAME_KEY_REPLAY_REJECTED=false
KILL_SWITCH_CLOSED=true
PRODUCTION_WRITE_AUTHORIZED=false
PROVIDER_RESULT_STATUS=0
READBACK_VERIFIED=false
PRIMARY_ERROR_CLASS=write_organization_not_allowlisted
RETRY_TERMINAL_SAFETY_STOP=true
```

Assessment:

- No provider mutation was dispatched because the write ledger was not reserved and provider result status remained 0.
- Root cause is local configuration normalization: numeric organization IDs are passed through `Config::stringList()`, which uses the string value as an array key. PHP converts numeric-string array keys to integers, so strict organization allowlist comparison fails (`string` runtime organization ID versus `int` allowlist entry).
- The retry workflow also classified a known pre-dispatch exception as terminal even when `LEDGER_RESERVED=false`.

Remediation:

1. Preserve string types in `Config::stringList()` without numeric-string key coercion.
2. Add a regression test for numeric organization IDs.
3. Treat `write_organization_not_allowlisted` as retryable only when `LEDGER_RESERVED=false`; unknown failures remain terminal.
4. Re-run the authorized sandbox retry workflow after merge. Production writes remain unauthorized.
