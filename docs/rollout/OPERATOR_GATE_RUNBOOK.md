# Operator Gate Runbook — One Controlled Sandbox Call

## Preconditions

Do not proceed unless every item is complete:

- PR reviewed and CI successful.
- Deployed commit recorded.
- Current provider schema and route evidence validated.
- Approved release manifest generated, reviewed and marked `APPROVED`.
- Server-only kill switch reviewed; open only for `invoice_draft_create_v2`.
- Test-company and organization allowlist validated.
- Readback route validated.
- Preview payload approved and payload hash recorded.
- Sandbox authorization packet signed and limited to one mutation.
- One-use approval envelope signed and unexpired.
- Explicit operator instruction authorizes exactly one sandbox mutation.

## Preparation

```text
php bin/generate-release-manifest.php storage/observed-release-manifest.json
php bin/sign-control-document.php unsigned-sandbox-authorization.json storage/sandbox-authorization.json
php bin/sign-control-document.php unsigned-approval.json approved-envelope.json
php bin/control-readiness.php
```

`control-readiness.php` must return exit code `0`. It performs no provider call.

## Execution

The harness has no retry loop and refuses non-sandbox operation.

```text
CONTA_SANDBOX_ONE_CALL_ACK=AUTHORIZE_EXACTLY_ONE_INVOICE_DRAFT_CREATE_V2 \
php bin/sandbox-one-call.php approved-payload.json approved-envelope.json --execute
```

## Mandatory closure

Immediately after the call:

1. Confirm readback verification result.
2. Confirm the ledger consumed the authorization ID, approval nonce and idempotency key.
3. Set the kill switch to globally blocked.
4. Set `execution_allowed=false` and `runtime_write_blocked=true`.
5. Re-run `php bin/control-readiness.php` and confirm non-zero/blocked result.
6. Preserve sanitized audit, ledger, request-hash, response-hash and readback evidence.
7. Register the result as passed, failed or indeterminate. Never repeat automatically.
