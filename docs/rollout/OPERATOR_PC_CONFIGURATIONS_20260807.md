# Conta MCP Operator PC Configurations — 07.08.2026

## Purpose

Maintain separate, explicit local-operator configurations for the two Windows PCs used to continue the Conta MCP rollout. GitHub remains the canonical source repository and Google Drive remains the evidence/document mirror; local PC profiles only define machine-specific paths and validation state.

No credentials, tokens, passwords, Conta organization identifiers, customer data, or production secrets belong in these profiles.

## Active profile

```text
ACTIVE_PC=OFFICE_PC
ACTIVE_PC_LABEL=Office PC
ACTIVE_PC_SELECTED_AT=2026-08-07T13:40:00+02:00
```

The operator explicitly stated that the current machine is the **Office PC**. The previously used machine is the **Laptop PC**.

## Profile: Office PC

```text
PROFILE_ID=OFFICE_PC
PROFILE_LABEL=Office PC
WINDOWS_USER=Ruben A. Meyer
REPOSITORY_ROOT=C:\Users\Ruben A. Meyer\source\repos\Conta-MCP
REPOSITORY_LOCATION_POLICY=LOCAL_NON_DRIVE_CHECKOUT
REPOSITORY=nanoTech-solutions-norway/Conta-MCP
BRANCH=main
LOCAL_VALIDATION_STATE=PENDING_CURRENT_PC_VALIDATION
```

Notes:

- This is the selected configuration for the current continuation.
- The repository root is intentionally outside Google Drive to avoid sync interference with Git operations.
- Do not claim this profile is locally validated until the Office PC has successfully verified the repository root, canonical origin, clean worktree, and `HEAD == origin/main` for the current rollout state.

## Profile: Laptop PC

```text
PROFILE_ID=LAPTOP_PC
PROFILE_LABEL=Laptop PC
WINDOWS_USER=meyer
REPOSITORY_ROOT=C:\Users\meyer\My Drive\NanoTech Solutions Norway\Prosjekter\Atlas Project\Custom ChatGPT models\Conta MCP\Conta-MCP_repo
REPOSITORY_LOCATION_POLICY=LEGACY_DRIVE_SYNCED_CHECKOUT
REPOSITORY=nanotech-solutions-norway/Conta-MCP
BRANCH=main
LAST_VALIDATED=2026-08-05
LOCAL_VALIDATION_STATE=HISTORICALLY_VALIDATED_REVALIDATE_BEFORE_RESUME
```

Notes:

- This path is the previously validated local checkout recorded in `LOCAL_DESKTOP_VALIDATION_20260805.md`.
- Because `main` can advance independently of the laptop, revalidate origin, branch, clean worktree, and current `origin/main` before resuming any local rollout operation on the Laptop PC.
- Do not silently reuse Office PC paths on the Laptop PC.

## PC switching protocol

When the operator changes machines:

1. The operator identifies the machine as **Office PC** or **Laptop PC**.
2. Load only the matching machine profile for local paths and machine-specific commands.
3. Before any path-dependent rollout operation, verify:
   - the configured repository root exists;
   - Git origin is the canonical `nanotech-solutions-norway/Conta-MCP` repository;
   - the active branch is `main` unless an explicitly approved task branch is being used;
   - `git fetch origin main` succeeds;
   - the worktree is clean;
   - local `HEAD` matches the intended canonical commit / `origin/main` for the phase being executed.
4. If validation fails, stop the local operation and classify the PC profile as requiring reconciliation. Do not substitute the other PC's path.
5. Secrets remain machine-local/operator-supplied and must never be copied into GitHub, Drive evidence, chat, or these profiles.

## Operator prompt convention

The short switching prompts are:

```text
Using Office PC
```

or

```text
Using Laptop PC
```

A machine switch changes only local operator configuration. It does **not** authorize deployment, provider calls, write-tool enablement, sandbox mutation, or production mutation.

If a future local operation depends on PC-specific state and the active PC is not explicit from current context, request the operator to identify **Office PC** or **Laptop PC** before issuing path-specific commands.

## Shared non-PC-specific canonical settings

```text
GITHUB_REPOSITORY=nanotech-solutions-norway/Conta-MCP
DEFAULT_BRANCH=main
DRIVE_PROJECT_FOLDER_ID=14G0b3Ptj__VnYhmOEPtMiUu0Yxq0T7qg
DRIVE_REPO_MIRROR_FOLDER_ID=1RSaTbfDxpjjhc3-GpjvG2q4pwSwDicxg
CONTROLLED_WRITE_EVIDENCE_FOLDER_ID=1e7bNnrEzTHsvSAjwPRx29fRRbpHG31U-
```

These shared identifiers are not changed when switching PCs.

## Safety boundary

This configuration record is documentation/operator-state only. It does not change runtime source, server configuration, deployment state, Conta provider execution, sandbox state, or write-tool state.
