# Conta MCP agent instructions

## Governing records

- Treat `README.md`, `SECURITY.md` and `docs/rollout/` as the current repository guidance and evidence base.
- Preserve the documented fail-closed execution boundary.
- Never commit runtime credentials, organization identifiers, approval secrets, customer data or production configuration.

## Process progress reporting

- Follow `docs/rollout/PROCESS_PROGRESS_REPORTING_STANDARD.md` for every operator-facing process.
- End each discrete process result with a percentage progress bar, stated completion target, result and next evidence gate.
- Calculate progress from verified weighted milestones only.
- Failed or blocked processes do not increase the percentage.
- Recalculate explicitly when scope changes.
- Carry the current percentage into status records, transfer packs and continuation prompts.
- A progress percentage never authorizes deployment, provider calls, write enablement or mutation.

## Execution boundary

Do not deploy, enable write tools, approve release or authorization artifacts, invoke a write-capable Conta tool, or perform provider mutation without the applicable evidence and explicit operator authorization.