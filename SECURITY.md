# Security Policy — 23:59, 27.07.2026

## Supported operating posture

This repository is a controlled Conta MCP bridge. Current read-only and write-blocking controls must remain fail-closed unless separately approved and evidenced.

## Prohibited repository content

Do not commit API keys, bearer tokens, passwords, private keys, customer accounting records, bank data, unredacted Conta payloads, production configuration, confidential customer files or sensitive personal data.

## Reporting and response

Do not report exposed credentials or confidential accounting data in a public issue. Use an established private channel to contact the owner. For exposure: disable affected workflows, revoke and rotate outside GitHub, preserve evidence, remove unsafe artifacts, inspect history and logs, assess contractual/GDPR duties, and re-enable only after validation.

Changes affecting write gates, route permissions, authentication, accounting data handling, evidence generation or deployment require controlled pull-request review. Repository transfer remains on hold.
