# Conta MCP agent instructions

## Governing records

- Treat `README.md`, `SECURITY.md`, `docs/SECURITY_POLICY.md`, `docs/GITHUB_COMPROMISE_CONTINUITY.md`, `docs/rollout/` and the NTSN universal Atlas/MCP security standards as the current repository guidance and evidence base.
- Preserve the documented fail-closed execution boundary.
- Never commit or expose runtime credentials, approval secrets, access/refresh tokens, passwords, private keys, customer data or production configuration.
- Never ask the operator to paste a production credential into ChatGPT/model context. Use the approved secret manager/credential broker or secure out-of-band entry path.

## Source-control continuity

- GitHub is the normal primary engineering/change-control plane while trusted; it must not be the only independently recoverable source/governance copy.
- A suspected GitHub account/repository/App/Actions/platform compromise freezes GitHub-originated deployment and all write-capable activation.
- Do not treat a destructive live mirror as sufficient backup or automatic source authority.
- Recovery requires an independently verified known-good signed checkpoint, immutable/offline recovery evidence, credential rotation as applicable, independent validation and explicit break-glass approval.
- DNS/routing failover does not establish source integrity.
- A Domeneshop/private server may be an additional hardened recovery node but should not be the sole independent copy when it shares production DNS/hosting account/provider blast radius.
- Azure DevOps Repos and isolated Forgejo/GitLab remain implementation candidates until explicitly selected and validated.

## Device configuration boundary

- Office PC and laptop PC use separate machine configurations/identities.
- Do not synchronize `.env`, credential files, private keys or secret-manager bootstrap material between the machines through Drive or Git.
- Compromise/revocation of one machine must not require exposure of credentials from the other.

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

Do not deploy, enable write tools, approve release or authorization artifacts, invoke a write-capable Conta tool, or perform provider mutation without the applicable evidence and explicit operator authorization. A repository merge, outage, source-control failover or recovery event never relaxes this rule.

## State reporting

Report `DESIGNED`, `CONFIGURED`, `IMPLEMENTED`, `TESTED`, `VALIDATED`, `APPROVED`, `RELEASE_APPROVED` and `LIVE` separately. Security-policy adoption does not mean a secondary Git service, immutable backup, Credential Broker or production write path is live.