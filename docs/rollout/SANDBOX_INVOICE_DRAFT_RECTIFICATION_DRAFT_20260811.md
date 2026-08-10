# Sandbox invoice-draft rectification draft — 2026-08-11

## Status

```text
procedure_status=DRAFTED_PENDING_OPERATOR_APPROVAL
provider_call_authorized=false
sandbox_mutation_authorized=false
automatic_delete_authorized=false
automatic_retry_authorized=false
```

This document is a non-executing proposal for handling the outcome of a future, separately authorized, single sandbox invoice-draft creation. It does not authorize that creation or any corrective mutation.

## Safe outcome handling

### Expected successful creation

1. Capture only sanitized request hash, response hash and returned draft identifier in the protected evidence plane.
2. Perform the approved `GET` readback once.
3. Confirm that the result remains a draft and has not been sent, posted or converted into an invoice.
4. Close the kill switch immediately and consume/revoke the one-use authorization.
5. Preserve the sandbox draft unchanged for operator inspection unless a separate rectification instruction is granted.

### Provider failure or indeterminate response

1. Do not retry automatically.
2. Close the kill switch and revoke the one-use authorization.
3. Use an authorized `GET` search/readback to determine whether a draft was created, using the approved correlation evidence without printing business payload data.
4. Classify the outcome as `FAILED_NO_OBJECT`, `SUCCEEDED_OBJECT_OBSERVED` or `INDETERMINATE`.
5. Escalate `INDETERMINATE`; never infer that failure means no object exists.

### Unexpected or incorrect sandbox draft

1. Do not send, post, credit, convert, update or delete it automatically.
2. Preserve its identifier and sanitized hashes in the protected evidence plane.
3. Keep all write gates closed.
4. Require a separate operator decision for any manual sandbox cleanup through the Conta UI or any provider-side corrective call.

## Approval requirements

Before this procedure can be marked approved, the operator must confirm:

- whether the sandbox draft should be retained as durable test evidence or removed manually;
- the exact authorized cleanup method, if any;
- who performs and independently verifies cleanup;
- what sanitized evidence proves the final state;
- that cleanup authorization is separate from create authorization.

Until then, the conservative default is to preserve the unsent sandbox draft and make no further mutation.

