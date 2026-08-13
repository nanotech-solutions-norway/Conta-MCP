# Sandbox invoice-draft rectification procedure — 2026-08-11

## Status — 13.08.2026

```text
procedure_status=RECOMMENDED_PENDING_OPERATOR_APPROVAL
provider_call_authorized=false
sandbox_mutation_authorized=false
automatic_delete_authorized=false
automatic_retry_authorized=false
```

This is a non-executing recommendation for handling the outcome of a future, separately authorized, single sandbox invoice-draft creation. It does not authorize that creation or any corrective mutation.

## Recommended conservative default

If the future sandbox draft is successfully created and read back, retain the unsent draft unchanged as durable test evidence. Do not automatically send, post, convert, update or delete it. Any later cleanup must receive separate operator authorization.

## Safe outcome handling

### Expected successful creation

1. Capture only sanitized request hash, response hash and returned draft identifier in the protected evidence plane.
2. Perform exactly one approved GET readback.
3. Confirm that the result remains a draft and has not been sent, posted or converted into an invoice.
4. Close the kill switch immediately and consume/revoke the one-use authorization.
5. Preserve the sandbox draft unchanged for operator inspection.
6. Do not perform cleanup unless separately authorized.

### Provider failure or indeterminate response

1. Do not retry automatically.
2. Close the kill switch and revoke the one-use authorization.
3. Use GET-only search/readback to determine whether a draft was created, using approved correlation evidence without printing business payload data.
4. Classify the outcome as `FAILED_NO_OBJECT`, `SUCCEEDED_OBJECT_OBSERVED` or `INDETERMINATE`.
5. Escalate `INDETERMINATE`; never infer that a failed HTTP response means no object exists.

### Unexpected or incorrect sandbox draft

1. Do not send, post, credit, convert, update or delete it automatically.
2. Preserve its identifier and sanitized hashes in the protected evidence plane.
3. Keep all write gates closed.
4. Require a separate operator decision for any manual sandbox cleanup through the Conta UI or any provider-side corrective call.

## Independent verification

After the future one-call window closes, the operator or an independently executed GET-only workflow should confirm:

- the draft exists only if the create call was observed as successful or reconciled as successful;
- the object remains a draft;
- no second provider mutation occurred;
- the one-use authorization has been consumed/revoked;
- the kill switch is closed;
- no production route or organization was accessed.

## Approval decision still required

The recommended policy is:

```text
SUCCESSFUL_TEST_DRAFT_DISPOSITION=RETAIN_UNSENT_AS_TEST_EVIDENCE
AUTOMATIC_CLEANUP=false
CLEANUP_REQUIRES_SEPARATE_AUTHORIZATION=true
AUTOMATIC_RETRY=false
INDETERMINATE_RESULT=ESCALATE_AND_GET_RECONCILE
```

This recommendation becomes approved only after explicit operator approval. Approval of this rectification procedure does not authorize the invoice-draft creation itself.
