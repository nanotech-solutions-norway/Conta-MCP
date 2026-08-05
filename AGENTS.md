# Conta MCP agent instructions

## Governing records

- Treat `README.md`, `SECURITY.md` and `docs/rollout/` as the current repository guidance and evidence base.
- Preserve the documented fail-closed execution boundary.
- Never commit runtime credentials, organization identifiers, approval secrets, customer data or production configuration.

## Process progress reporting

- Follow `docs/rollout/PROCESS_PROGRESS_REPORTING_STANDARD.md` for every operator-facing process.
- Maintain evidence-weighted progress in canonical records, but do not automatically display a status bar after ordinary processes or responses.
- Display the status bar and short completed/ongoing/remaining summary only when the user's complete trimmed message is exactly `Status`, case-insensitively, with no other text, punctuation, mention or context.
- Messages such as `Status please`, `Project status`, `What's the status?`, or `Status @GitHub` do not trigger the special status block.
- Calculate progress from verified weighted milestones only.
- Failed or blocked processes do not increase the percentage.
- Recalculate explicitly when scope changes.
- Carry the current percentage into status records, transfer packs and continuation prompts.
- A progress percentage never authorizes deployment, provider calls, write enablement or mutation.

## Execution boundary

Do not deploy, enable write tools, approve release or authorization artifacts, invoke a write-capable Conta tool, or perform provider mutation without the applicable evidence and explicit operator authorization.