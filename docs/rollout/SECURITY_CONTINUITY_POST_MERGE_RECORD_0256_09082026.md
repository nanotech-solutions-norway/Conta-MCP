# Conta MCP Security Continuity Post-Merge Record — 02:56, 09.08.2026

## Classification

```text
RECORD_STATUS=AUTO_APPROVED
UNIVERSAL_SECURITY_POLICY=MERGED_PR_24
SOURCE_CONTINUITY_POLICY=MERGED_PR_24
WRITE_ENABLEMENT=UNCHANGED_FAIL_CLOSED
SECONDARY_GIT=NOT_IMPLEMENTED
IMMUTABLE_OFFLINE_RECOVERY=NOT_IMPLEMENTED
```

## Governing outcome

Conta MCP now inherits the merged NTSN universal Atlas/MCP security baseline and the GitHub-compromise/source-control continuity rules while preserving all stricter accounting and controlled-write restrictions.

## Permanent rules

- No raw Conta/API/MCP credentials, approval secrets, access/refresh tokens, passwords or private keys in model context, Git, Drive evidence, logs, screenshots or transfer packs.
- GitHub is normal primary source/change control while trusted, but cannot be the only independently recoverable source/governance copy.
- A suspected GitHub compromise freezes GitHub-originated deployment and all write-capable activation.
- A live mirror alone is not trusted recovery; recovery requires an independently signed/verified known-good checkpoint plus immutable/offline evidence.
- Production Conta writes remain disabled unless the existing authorization, one-use approval, payload binding, replay/idempotency, audit, kill-switch, provider readback and release-manifest gates pass.
- A source-control incident never authorizes bypass of accounting controls.

## Source-recovery backlog

| ID | Requirement | State |
|---|---|---|
| CONTA-SC-01 | Select independent secondary Git provider/account boundary | `PENDING_REVIEW` |
| CONTA-SC-02 | Independent signed Git bundle/checkpoint process | `NOT_IMPLEMENTED` |
| CONTA-SC-03 | Immutable/WORM source/release recovery copy | `NOT_IMPLEMENTED` |
| CONTA-SC-04 | Encrypted offline recovery copy | `NOT_IMPLEMENTED` |
| CONTA-SC-05 | Break-glass GitHub-compromise recovery exercise | `NOT_RUN` |
| CONTA-SC-06 | Validate recovered source through full Conta repository security + controlled-write workflows | `NOT_RUN` |

## Platform candidates

Azure DevOps Repos is the preferred managed candidate under the cross-project standard. A separately administered Forgejo/GitLab service is the provider-diverse alternative. A Domeneshop/private server may be an additional recovery node only when independently hardened; it should not be the sole independent copy if it shares production DNS/hosting account/provider blast radius.

## Validation evidence

- Security adoption PR #24 merged 09.08.2026.
- Repository security, dependency, CodeQL and Controlled Write Foundation checks had passed for the security-adoption head before merge.
- This record does not claim the secondary Git, immutable/offline backup, Credential Broker or provider-auth migration is live.

## Memory / continuation rule

Future Conta MCP sessions must preserve the merged security baseline, separate Office PC/laptop PC credential configurations, fail-closed writes and independent-source-recovery requirement. Never request that the operator paste a production credential into ChatGPT.