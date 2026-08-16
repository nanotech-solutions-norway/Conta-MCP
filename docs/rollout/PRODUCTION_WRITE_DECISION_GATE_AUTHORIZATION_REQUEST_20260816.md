# Conta MCP Production-Write Decision-Gate Authorization Request — 2026-08-16

## Requested authorization

Authorize preparation and protected review of the decision packet defined by `PRODUCTION_WRITE_PROTECTED_DECISION_GATE_20260816.md`, using the public limits below:

```text
maximum_invoice_lines=1
maximum_line_amount=1.00 NOK
maximum_draft_total=1.00 NOK
currency=NOK
maximum_provider_mutations=1
automatic_retry=false
approval_max_ttl_seconds=900
```

This authorization permits:

- out-of-band selection and review of protected organization/customer references;
- out-of-band accounting, VAT, fiscal-period and duplicate-rule decisions;
- out-of-band role, credential-custody, retention, storage and incident decisions;
- computation of a canonical protected decision-packet SHA-256;
- preparation of a repository-safe attestation and later implementation-authorization request.

This authorization does not permit:

- application, workflow or runtime implementation;
- production credential provisioning;
- changing server configuration or write allowlists;
- creating or approving a release manifest;
- deployment or write-tool visibility;
- a Conta provider call, including GET or POST;
- approval or execution of a production mutation.

## Explicit authorization form

Authorization must reference the exact reviewed commit containing this request. The required instruction is:

```text
AUTHORIZE_CONTA_PRODUCTION_WRITE_DECISION_GATE for commit <exact-commit-sha>
```

The authorization is invalid if the commit differs, the PR changes afterward, or the instruction omits `DECISION_GATE`.

## Current state

```text
DECISION_GATE_AUTHORIZED=false
PROTECTED_DECISIONS_COMPLETE=false
IMPLEMENTATION_AUTHORIZED=false
DEPLOYMENT_AUTHORIZED=false
RELEASE_APPROVED=false
FIRST_PRODUCTION_MUTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
```
