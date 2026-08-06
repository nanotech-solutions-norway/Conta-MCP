# Conta MCP Recommended Next Action — 00:10, 07.08.2026

## Decision

Phase 2C is complete and accepted as:

```text
RUNTIME_DRIFT_REQUIRES_REVIEW
LEGACY_SOURCE_DRIFT_CONFIRMED
```

The next authorized work unit is:

```text
PHASE_2D
FAIL_CLOSED_CANONICAL_DEPLOYMENT_PACKAGE_PREPARATION
```

This work unit prepares and validates a deployment package locally. It does not authorize an FTP upload, server change, deployment, MCP write call, provider call or sandbox mutation.

## Governing deployment candidate

```text
repository=nanotech-solutions-norway/Conta-MCP
branch=main
deployment_candidate_commit=7b0fee990cad291a5c01ee1468ba4329e0aeb543
foundation_ancestor_confirmed=true
local_validation=PASSED
phase_2c_comparison=COMPLETE
```

Do not silently substitute a later `main` commit. A later candidate requires a fresh clean-checkout validation and a new immutable package manifest.

## Phase 2D package objectives

1. Export only the deployable canonical source from the validated commit.
2. Exclude all secrets, local production configuration, audit data, ledgers and generated authorization artifacts.
3. Generate an immutable file manifest with source commit, path, size and SHA-256.
4. Validate PHP syntax and the repository control tests before packaging.
5. Verify that the package contains the canonical fail-closed controls.
6. Preserve the active server-only `config/conta_config.local.php` as external state; do not copy it into the package or read its secret values.
7. Create a rollback plan and a sanitized pre-deployment backup inventory.
8. Resolve the active document-root topology before any upload.

## Required package contents

At minimum, package validation must account for:

```text
.htaccess
app/*.php
bin/*.php
config/conta_config.example.php
config/release_manifest.example.json
config/sandbox-authorization.example.json
config/tool_policy.php
config/write-kill-switch.example.json
public/.htaccess
public/health.php
public/index.php
tests/controlled-write-foundation.php
tests/remaining-control-paths.php
```

Documentation and test files may be retained in the local evidence package, but production upload scope must be explicitly separated from evidence scope.

## Mandatory exclusions

```text
config/conta_config.local.php
storage/audit.log
storage/write-execution-ledger.jsonl
storage/*.json containing approvals or authorization
real API keys or bearer tokens
organization identifiers
customer, invoice or accounting data
.git/
```

The package process must fail if any excluded path or secret-like value is detected.

## Fail-closed runtime state required after any future deployment

A future deployment plan must preserve or establish:

```text
enable_write_tools=false
runtime_write_blocked=true
execution_allowed=false
production_write_approved=false
allowed_write_actions=[]
allowed_write_organizations=[]
kill_switch_global_blocked=true
release_manifest_status=PENDING_REVIEW
sandbox_authorization_status=ABSENT_OR_PENDING
provider_call_performed=false
```

The canonical source may expose a preview tool, but it must not expose or permit live execution unless all independent gates are later satisfied.

## Deployment-topology blocker

Phase 2C found:

```text
SERVER_DIRECTORY_ABSENT=public
```

The active runtime tree therefore does not match the repository's documented `public/` layout. Before any upload, Phase 2D must produce one explicit, reviewed mapping for the active `mcp.atlas-ai.no` document root.

Acceptable planning outcomes are limited to:

1. the host document root is mapped to the repository `public/` directory while `app`, `config`, `bin`, `tests`, `docs` and `storage` remain non-public; or
2. a reviewed root rewrite arrangement forwards only `/mcp` and `/health` to canonical public entry points while blocking direct access to protected directories.

Do not flatten the repository into the active root without a reviewed mapping. Do not rely on the superseded `www.nanoconcept.no/conta-mcp` deployment guide as current authority.

## Required pre-deployment rollback plan

Before any later upload authorization, prepare:

- a timestamped backup location for the current 11 deployed source files;
- normalized and raw SHA-256 hashes for every backed-up file;
- a preserved server-only configuration reference without secret values;
- a restoration sequence for the legacy runtime;
- a health and authenticated `initialize` verification sequence;
- an automatic stop condition if any protected path becomes publicly readable;
- an automatic stop condition if write tools become enabled or provider I/O is observed.

Package preparation may describe these steps but must not execute them on the server.

## Phase 2D acceptance gates

Phase 2D is complete only when all markers are true:

```text
DEPLOYMENT_PACKAGE_SOURCE_COMMIT_LOCKED=true
DEPLOYMENT_PACKAGE_SECRET_SCAN_PASSED=true
DEPLOYMENT_PACKAGE_PHP_LINT_PASSED=true
DEPLOYMENT_PACKAGE_CONTROL_TESTS_PASSED=true
DEPLOYMENT_PACKAGE_MANIFEST_GENERATED=true
DEPLOYMENT_PACKAGE_MANIFEST_VERIFIED=true
DEPLOYMENT_TOPOLOGY_MAPPING_REVIEWED=true
ROLLBACK_PLAN_PREPARED=true
FTP_UPLOAD_PERFORMED=false
SERVER_CONFIGURATION_CHANGED=false
PROVIDER_CALL_PERFORMED=false
SANDBOX_MUTATION_PERFORMED=false
```

## Stop conditions

Stop Phase 2D if:

- local `HEAD` differs from the locked candidate;
- the worktree is not clean;
- PHP lint or either control test fails;
- a secret or local configuration file enters the package;
- package hashes do not reproduce;
- server topology cannot be mapped without exposing protected directories;
- the package would overwrite server-only configuration;
- any step requires an FTP upload or provider call.

## Separate future authorization gates

After Phase 2D passes, the next possible gate is:

```text
FAIL_CLOSED_CANONICAL_DEPLOYMENT_AUTHORIZATION
```

That gate requires an explicit operator decision and remains separate from:

```text
SANDBOX_ONE_CALL_AUTHORIZATION
PRODUCTION_WRITE_AUTHORIZATION
```

Neither provider execution nor write enablement is implied by packaging or deployment.
