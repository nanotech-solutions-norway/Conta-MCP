# Conta MCP Documentation Drift Register — 11:11, 05.08.2026

## Purpose

Track documentation and evidence records that no longer represent the current merged repository state. Historical evidence must not be deleted or silently rewritten.

## Drift entries

| ID | Record | Observed drift | Required treatment | Status |
|---|---|---|---|---|
| DRIFT-001 | Google Drive controlled-write implementation summary dated 16.07.2026 | Describes PR `#1` as draft and unmerged | Preserve as historical evidence; add a superseding post-merge status record | OPEN |
| DRIFT-002 | Google Drive controlled-write evidence folder | Contains pre-merge source package and summaries but no post-merge closure evidence | Add post-merge status, canonical merge SHA and CI result | OPEN |
| DRIFT-003 | Repository `README.md` | Structure and tool catalog describe the earlier minimal runtime and do not document the merged control classes, CLI tools or conditional tool visibility | Update in a separate reviewed documentation change | OPEN |
| DRIFT-004 | `docs/rollout/REMAINING_CONTROL_PATHS_IMPLEMENTED.md` | Classification says `IMPLEMENTED_ON_DRAFT_BRANCH` | Preserve historical section; append or link to post-merge baseline rather than rewriting original event evidence | OPEN |
| DRIFT-005 | `docs/rollout/CONTROLLED_WRITE_FOUNDATION.md` | “Next gate” language predates completed merge | Preserve design evidence; use post-merge baseline as current source of truth | CONTROLLED_BY_SUPERSEDING_RECORD |
| DRIFT-006 | Prior transfer packs and phase records | May refer to locked continuation hold and unmerged candidate state | Retain for chronology; do not use as current execution authority | CONTROLLED_BY_SUPERSEDING_RECORD |

## Source-authority order after merge

For current status decisions, use:

1. current `main` commit and repository content;
2. successful checks attached to the canonical merge commit;
3. `POST_MERGE_BASELINE_20260805.md`;
4. current sanitized runtime evidence;
5. current official provider evidence;
6. historical Drive and transfer-pack records.

Where sources conflict, record the conflict as `PENDING_REVIEW`. Do not average, infer or silently reconcile execution-related values.

## Closure criteria

This drift register can be closed only when:

- a post-merge Drive status record exists;
- local Desktop validation is recorded;
- README/current documentation reflects the merged architecture;
- runtime inventory is completed;
- deployed commit and runtime hashes are known;
- all current records continue to state that provider execution is not authorized.
