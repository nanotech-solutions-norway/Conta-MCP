# Conta MCP Post-Success Stabilization Scope — 2026-08-16

This branch contains no provider mutation and no production-write authorization.

Scope is limited to converting the already-validated sandbox behavior from runtime patching into permanent source plus documentation/readiness updates.

Included:

- permanent numeric-string organization allowlist handling;
- permanent sandbox invoice-draft fixture `vatCode=high`, `lineNo=1`;
- permanent payload SHA-256 binding `79ae9a521fb79e1852721eb4f4f25e315d3122849bfe2b2df146e761d974cee7`;
- permanent observed Conta list-response compatibility;
- permanent redacted provider diagnostics;
- removal of temporary payload/config runtime patchers;
- CI validation of the baked harness;
- manual-only future sandbox controlled-validation workflow;
- definitive verified-success evidence;
- superseding next-action guidance.

Explicitly excluded:

- another sandbox invoice-draft mutation;
- deletion or cleanup of the existing verified sandbox draft;
- production deployment;
- production write enablement;
- production credentials or organization identifiers;
- invoice send/post/finalize/update/credit/delete functionality.

Production writes remain refused by design until a separate production-write program is implemented and approved.
