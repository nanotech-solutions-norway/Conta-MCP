# Conta MCP Documentation Drift Register — 11:11, 05.08.2026

## Purpose

Track documentation and evidence records that no longer represent the current merged repository state. Historical evidence must not be deleted or silently rewritten.

## Drift entries

| ID | Record | Observed drift | Required treatment | Status |
|---|---|---|---|---|
| DRIFT-001 | Google Drive controlled-write implementation summary dated 16.07.2026 | Describes PR `#1` as draft and unmerged | Preserve as historical evidence; use the post-merge status record as the current source | RESOLVED_BY_SUPERSEDING_RECORD |
| DRIFT-002 | Google Drive controlled-write evidence folder | Previously contained pre-merge source packages and summaries but no post-merge closure evidence | Post-merge status record added with canonical merge SHA and CI result | RESOLVED |
| DRIFT-003 | Repository `README.md` | Structure and tool catalog described the earlier minimal runtime | README updated in this closure PR to document the merged control architecture and current execution boundary | RESOLVED_IN_THIS_PR |
| DRIFT-004 | `docs/rollout/REMAINING_CONTROL_PATHS_IMPLEMENTED.md` | Classification says `IMPLEMENTED_ON_DRAFT_BRANCH` | Preserve historical section; use or link to the post-merge baseline for current status | CONTROLLED_BY_SUPERSEDING_RECORD |
| DRIFT-005 | `docs/rollout/CONTROLLED_WRITE_FOUNDATION.md` | “Next gate” language predates completed merge | Preserve design evidence; use post-merge baseline as current source of truth | CONTROLLED_BY_SUPERSEDING_RECORD |
| DRIFT-006 | Prior transfer packs and phase records | May refer to locked continuation hold and unmerged candidate state | Retain for chronology; do not use as current execution authority | CONTROLLED_BY_SUPERSEDING_RECORD |

## Source-authority order after merge

For current status decisions, use:

1. current `main` commit and repository content;
2. successful checks attached to the canonical merge commit;
3. `POST_MERGE_BASELINE_20260805.md`;
4. the Google Drive post-merge status record in the controlled-write evidence folder;
5. current sanitized runtime evidence;
6. current official provider evidence;
7. historical Drive and transfer-pack records.

Where sources conflict, record the conflict as `PENDING_REVIEW`. Do not average, infer or silently reconcile execution-related values.

## Remaining closure criteria

The documentation drift is controlled, but the broader post-merge baseline remains open until:

- local Desktop validation is recorded;
- runtime inventory is completed;
- deployed commit and runtime hashes are known;
- all current records continue to state that provider execution is not authorized.

## Current register state

```text
DOCUMENTATION_STATUS_DRIFT_CONTROLLED
POST_MERGE_BASELINE_STILL_OPEN
LOCAL_VALIDATION_PENDING
RUNTIME_INVENTORY_PENDING
NO_PROVIDER_EXECUTION_AUTHORITY
```
