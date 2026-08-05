# Conta MCP Recommended Next Action — 11:11, 05.08.2026

## Decision

The next authorized work unit is:

```text
POST_MERGE_BASELINE_CLOSURE
THEN_READ_ONLY_RUNTIME_INVENTORY
```

Do not proceed directly to a sandbox mutation.

## Desktop execution sequence

### Step 1 — Synchronize and validate local `main`

```powershell
git fetch origin --prune
git checkout main
git pull --ff-only origin main
git rev-parse HEAD
git status --short
```

Required commit:

```text
689cf28d943b761e26d9d1a7ef2eaddf5b78cc07
```

The working tree must be clean.

### Step 2 — Run local source validation

```powershell
Get-ChildItem app,bin,config,public,tests -Recurse -Filter *.php |
    ForEach-Object {
        php -l $_.FullName
        if ($LASTEXITCODE -ne 0) { throw "PHP syntax validation failed: $($_.FullName)" }
    }

php tests/controlled-write-foundation.php
if ($LASTEXITCODE -ne 0) { throw "Controlled-write foundation tests failed" }

php tests/remaining-control-paths.php
if ($LASTEXITCODE -ne 0) { throw "Remaining control-path tests failed" }

git diff --exit-code
```

Record the exact command output in a sanitized local validation report.

### Step 3 — Review this closure PR

Confirm that the post-merge baseline:

- uses the canonical merge commit;
- does not claim deployment or provider execution;
- leaves all write gates closed;
- preserves historical evidence;
- marks local and runtime validation as pending where evidence is unavailable.

### Step 4 — Perform read-only Domeneshop inventory

Use `RUNTIME_INVENTORY_CHECKLIST_20260805.md`.

Do not upload, overwrite, delete, rename or change permissions. Do not modify runtime configuration. Do not call any write-capable Conta route.

### Step 5 — Produce runtime drift report

Compare:

```text
canonical repository main
vs.
active Domeneshop runtime
```

Classify every unexplained mismatch as `PENDING_REVIEW`.

### Step 6 — Prepare fail-closed deployment plan

Only after local validation and runtime inventory pass, prepare a deployment plan preserving:

```text
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
kill_switch_global_blocked=true
```

Deployment preparation is not deployment authorization.

## Stop conditions

Stop immediately if any of the following occurs:

- local HEAD differs from the canonical merge commit;
- the working tree contains unexplained changes;
- PHP lint or either control test fails;
- runtime files cannot be inventoried safely;
- runtime configuration exposes secrets;
- deployed code differs from expected source without explanation;
- write tools appear in `tools/list` unexpectedly;
- any non-GET provider request is observed;
- current provider evidence conflicts with the implemented route or payload assumptions.

## Gate after successful closure

The subsequent gate is:

```text
FAIL_CLOSED_SANDBOX_DEPLOYMENT_VALIDATION
```

It remains separate from:

```text
SANDBOX_ONE_CALL_AUTHORIZATION
```

A separate explicit operator decision is required before one provider mutation.
