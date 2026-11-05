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
- Maintain evidence-weighted progress in canonical records, but do not automatically display a status bar after ordinary processes or responses.
- Display the status bar and short completed/ongoing/remaining summary only when the user's complete trimmed message is exactly `Status`, case-insensitively, with no other text, punctuation, mention or context.
- Messages such as `Status please`, `Project status`, `What's the status?`, or `Status @GitHub` do not trigger the special status block.
- Calculate progress from verified weighted milestones only.
- Failed or blocked processes do not increase the percentage.
- Recalculate explicitly when scope changes.
- Carry the current percentage into status records, transfer packs and continuation prompts.
- A progress percentage never authorizes deployment, provider calls, write enablement or mutation.

## Execution boundary

Do not deploy, enable write tools, approve release or authorization artifacts, invoke a write-capable Conta tool, or perform provider mutation without the applicable evidence and explicit operator authorization. A repository merge, outage, source-control failover or recovery event never relaxes this rule.

## State reporting

Report `DESIGNED`, `CONFIGURED`, `IMPLEMENTED`, `TESTED`, `VALIDATED`, `APPROVED`, `RELEASE_APPROVED` and `LIVE` separately. Security-policy adoption does not mean a secondary Git service, immutable backup, Credential Broker or production write path is live.

## ChatGPT-first MCP creation — proposed greenfield route (05.11.2026)

For **new, isolated, synthetic-data** MCP server designs, consult the central draft [ChatGPT-first MCP development playbook](https://github.com/nanotech-solutions-norway/ntsn-mcp-integrations/pull/97) derived from the 04.11.2026 Clean-Sheet Deep Research report. It evaluates **ChatGPT Work + Plugin Creator + ChatGPT Sites + durable D1** as the lowest-friction first prototype, conditional on actual feature availability, and **GitHub + Codex Cloud + Cloudflare Workers/D1** for source-controlled portable deployment. A new MCP App UI and centralized gateway remain optional.

This is **documentation under review**, not proof of Sites access, a deployable code sample, a native ChatGPT command, production authorization, OAuth configuration, integration replacement or runtime activation. The source report's illustrative idempotency flow is not atomic and its SDK auth-context lookup is unverified; require implementation and negative/transaction tests before any deploy. Existing universal/2026-07-28 MCP security, actual provider permissions, local source-control authority and project-specific locks prevail. **Conta accounting/provider writes remain fail-closed and require separately approved controlled-write, audit, authorization and release gates.**
