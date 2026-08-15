# Sandbox invoice-draft retry 422 incident — 15.08.2026

## Classification

```text
incident_status=DIAGNOSED_RETRY_POLICY_DEFECT
candidate_id=invoice_draft_create_v2
environment=sandbox
production_write_authorized=false
object_created_observed=false
provider_status=422
minimum_observed_attempts=48
```

## Operator-supplied run evidence

Workflow run `31881756053` / run #4 entered the authorized retry loop after the protected sandbox environment was approved. The operator supplied the live step output through `RETRY_ATTEMPT=48`.

Every supplied attempt reported the same material state:

```text
EXECUTION_OUTCOME=FAILED_NO_OBJECT_OBSERVED_BY_POSTSTATE_GET
LEDGER_RESERVED=true
SAME_KEY_REPLAY_REJECTED=true
KILL_SWITCH_CLOSED=true
PRODUCTION_WRITE_AUTHORIZED=false
PROVIDER_RESULT_STATUS=422
READBACK_VERIFIED=false
```

Attempts 2+ also reported `AUTOMATIC_RETRY_PERFORMED=true`. The loop progressively backed off to 30 seconds and then continued retrying at that interval.

## Findings

1. The write-control path reached provider dispatch: the execution ledger was reserved and Conta returned an HTTP response.
2. Conta consistently returned HTTP 422 for the same approved payload hash `dab571f2807745e1236a30dc93ae34ca8b8d2b15daaa26034f68a255e170b786`.
3. The mandatory GET post-state reconciliation reported no invoice draft after each supplied attempt.
4. Same-key replay rejection and kill-switch closure continued to pass.
5. No production write was authorized.
6. The retry workflow incorrectly treated `FAILED_NO_OBJECT_OBSERVED_BY_POSTSTATE_GET` as sufficient for another provider mutation without also requiring a transient provider status. This allowed a deterministic 422 validation failure to loop until the job timeout.

## Corrective policy

The sandbox retry workflow is changed so that:

- HTTP 4xx responses are terminal except explicit `429` throttling;
- a provider retry is allowed only for `429` or `5xx` and only when GET post-state definitively proves that no draft exists;
- read-only or pre-dispatch failures may retry only when no provider mutation was reserved;
- the series is capped at three attempts;
- the job timeout is reduced to ten minutes;
- provider error diagnostics emit only a response-body hash, JSON key names, and selected redacted scalar error fields; raw provider error bodies are not printed.

## Payload diagnosis boundary

The repeated HTTP 422 establishes a request/content/business-rule rejection but does not identify the exact invalid field. The current run did not print the provider error body. The next authorized diagnostic attempt must therefore stop after its first non-retryable 4xx and expose only the redacted provider error diagnostic markers.

No payload field should be changed solely from inference before that diagnostic evidence is captured.
