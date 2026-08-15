# Sandbox invoice-draft retry 422 incident — 15.08.2026

## Classification

```text
incident_status=DIAGNOSED_RETRY_POLICY_DEFECT
candidate_id=invoice_draft_create_v2
environment=sandbox
production_write_authorized=false
object_created_observed=false
provider_status=422
completed_attempts=58
run_conclusion=CANCELLED
cancelled_during_backoff=true
```

## Run evidence

Workflow run `31881756053` / run #4 entered the authorized retry loop after the protected sandbox environment was approved.

The operator first supplied live output through `RETRY_ATTEMPT=48`. The completed GitHub job log was subsequently recovered and confirms that attempts continued through `RETRY_ATTEMPT=58` before the workflow was manually cancelled.

Every completed attempt reported the same material state:

```text
EXECUTION_OUTCOME=FAILED_NO_OBJECT_OBSERVED_BY_POSTSTATE_GET
LEDGER_RESERVED=true
SAME_KEY_REPLAY_REJECTED=true
KILL_SWITCH_CLOSED=true
PRODUCTION_WRITE_AUTHORIZED=false
PROVIDER_RESULT_STATUS=422
READBACK_VERIFIED=false
```

Attempts 2+ reported `AUTOMATIC_RETRY_PERFORMED=true`. The loop progressively backed off to 30 seconds and then continued retrying at that interval.

Attempt 58 fully completed before cancellation. Its provider response was HTTP 422, GET post-state again observed no invoice draft, same-key replay rejection passed, and the kill switch closed. The workflow then emitted `SAFE_RETRY_CONFIRMED=true` and `RETRY_DELAY_SECONDS=30`. GitHub recorded `The operation was canceled` during that subsequent sleep/backoff period at approximately `2026-08-15T11:52:33Z`.

Therefore:

```text
cancellation_interrupted_provider_post=false
cancellation_interrupted_readback=false
last_completed_attempt=58
last_completed_attempt_provider_status=422
last_completed_attempt_object_observed=false
```

## Findings

1. The write-control path reached provider dispatch: the execution ledger was reserved and Conta returned an HTTP response.
2. Conta consistently returned HTTP 422 for the same approved payload hash `dab571f2807745e1236a30dc93ae34ca8b8d2b15daaa26034f68a255e170b786`.
3. The mandatory GET post-state reconciliation reported no invoice draft after every completed attempt.
4. Same-key replay rejection and kill-switch closure continued to pass.
5. No production write was authorized.
6. The retry workflow incorrectly treated `FAILED_NO_OBJECT_OBSERVED_BY_POSTSTATE_GET` as sufficient for another provider mutation without also requiring a transient provider status. This allowed a deterministic 422 validation failure to loop.
7. Manual cancellation was safe because it occurred during backoff after attempt 58 had fully completed and closed its write controls.

## Corrective policy

The sandbox retry workflow is changed so that:

- HTTP 4xx responses are terminal except explicit `429` throttling;
- a provider retry is allowed only for `429` or `5xx` and only when GET post-state definitively proves that no draft exists;
- read-only or pre-dispatch failures may retry only when no provider mutation was reserved;
- the series is capped at three attempts;
- the job timeout is reduced to ten minutes;
- provider error diagnostics emit only a response-body hash, JSON key names, and selected redacted scalar error fields; raw provider error bodies are not printed.

## Payload diagnosis boundary

The repeated HTTP 422 establishes a request/content/business-rule rejection but does not identify the exact invalid field. Run #4 did not print the provider error body. The next authorized diagnostic attempt must therefore stop after its first non-retryable 4xx and expose only the redacted provider error diagnostic markers.

No payload field should be changed solely from inference before that diagnostic evidence is captured.
