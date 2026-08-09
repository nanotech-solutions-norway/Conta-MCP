# Conta MCP — GitHub Compromise and Source-Control Continuity — 02:41, 09.08.2026

Conta MCP inherits the NTSN `GITHUB_COMPROMISE_CONTINUITY_STANDARD` maintained in `nanotech-solutions-norway/ntsn-mcp-integrations`.

## Conta-specific fail-closed rule

Because Conta can affect accounting data, a suspected GitHub account/repository/Actions compromise immediately suspends trust in GitHub-originated deployment and all write-capable activation. Existing `enable_write_tools=false` / controlled-write gates remain authoritative and may not be bypassed for continuity.

## Recovery source hierarchy during a declared GitHub compromise

1. Last independently signed and verified Conta release/security checkpoint.
2. Independently administered secondary Git repository, after history/signature verification.
3. Verified immutable Git bundle/release manifest.
4. Local/offline recovery copy after verification.

The suspected GitHub head is evidence, not recovery authority.

## Required continuity architecture

- GitHub remains normal primary engineering/change-control while trusted.
- Preferred managed secondary: Azure DevOps Repos with independent Entra roles/credentials and protected branches.
- Optional provider-diverse secondary: isolated Forgejo/GitLab on a separately administered server/provider.
- Maintain immutable signed Git bundles plus an encrypted offline copy.
- Do not use a destructive real-time mirror as the only backup.
- Do not store Conta API credentials, accounting data, customer data or production secrets in the secondary Git service or backups.

## Domeneshop/private-server use

A private server, including a Domeneshop-hosted server, may be used as a tertiary/secondary Git recovery location only if it has separate administrator credentials from production hosting/DNS, restricted SSH/VPN access, encrypted storage, host hardening, off-host immutable backup and no production secrets. It should not be the sole recovery platform if it shares a provider/account blast radius with production services.

## Incident sequence

1. Freeze GitHub-originated deployments and write activation.
2. Activate Conta/MCP kill switches as applicable.
3. Revoke/rotate affected GitHub Apps, tokens, deploy keys and workload trust.
4. Identify the last known-good signed checkpoint.
5. Verify secondary/bundle history and manifests.
6. Restore into an isolated repository/control plane.
7. Run Conta security, controlled-write, dependency and CodeQL validation.
8. Keep production writes disabled until separately re-approved.
9. Record recovery evidence and explicit return-to-service approval.

This file defines continuity architecture only. It does not claim that an Azure DevOps/Forgejo secondary or immutable/offline backup is currently configured.