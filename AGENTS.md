# Conta MCP agent instructions

## Governing records

- Treat `README.md`, `SECURITY.md` and `docs/rollout/` as the current repository guidance and evidence base.
- Preserve the documented fail-closed execution boundary.
- Never commit runtime credentials, organization identifiers, approval secrets, customer data or production configuration.

## Process progress reporting

- Follow `docs/rollout/PROCESS_PROGRESS_REPORTING_STANDARD.md` for every operator-facing process.
- After each discrete process or major work step, display a compact cumulative evidence-weighted progress bar with a percentage and brief status label.
- Use `Process status: [██████░░░░] 60% — <brief status>` as the default compact format; do not emit a bar for every low-level tool call or internal substep.
- Display the compact bar after successful, partial, blocked and failed processes. Failed or blocked work does not increase the percentage unless it closes a verified weighted gate.
- The exact standalone `Status` command remains the trigger for the expanded completed/ongoing/remaining status block; it is no longer the exclusive trigger for showing progress.
- Calculate progress from verified weighted milestones only.
- Recalculate explicitly when scope changes.
- Carry the current percentage into status records, transfer packs and continuation prompts.
- A progress percentage never authorizes deployment, provider calls, write enablement or mutation.

## Execution boundary

Do not deploy, enable write tools, approve release or authorization artifacts, invoke a write-capable Conta tool, or perform provider mutation without the applicable evidence and explicit operator authorization.