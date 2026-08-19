# Conta MCP Production Identity/Access Validation Authorization Request — 2026-08-19

## Status

```text
REQUEST_STATUS=READY_FOR_OPERATOR_AUTHORIZATION
IMPLEMENTATION_MERGE_COMMIT=19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac
CREDENTIAL_PROVISIONING_AUTHORIZED=false
PRODUCTION_GET_ONLY_VALIDATION_AUTHORIZED=false
LIVE_CONFIGURATION_AUTHORIZED=false
DEPLOYMENT_AUTHORIZED=false
RELEASE_APPROVED=false
PROVIDER_MUTATION_AUTHORIZED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

## Requested work unit

If explicitly authorized for the exact reviewed request commit, this gate may provision a **production-only Conta API credential into a dedicated reviewer-protected GitHub Environment used only for identity/access validation**, plus the protected production organization identifier/reference needed for that validation, and then perform GET-only validation against the production Conta API.

The API credential must belong to the existing dedicated Conta user selected by the operator and that user must be restricted to the intended production organization.

## Proposed protected environment

```text
ENVIRONMENT=conta-production-identity-validation
CONTA_ENVIRONMENT=production
CONTA_API_BASE_URL=https://api.gateway.conta.no
```

Required environment secrets:

```text
CONTA_API_KEY
CONTA_ORG_ID
```

The credential and organization identifier must be entered only through the protected secret boundary. They must not be committed to Git, pasted into chat, printed in workflow logs, or written to repository artifacts.

This environment is intentionally separate from the live runtime and from `conta-production-write-decisions`. GitHub Environment secrets are scoped to their environment; no workflow may infer or copy the confidential organization reference from another environment.

## Permitted provider operations

Only these methods are authorized in this gate:

```text
GET /invoice/organizations
GET /invoice/organizations/{opContextOrgId}/subscription-plan
```

The validation must:

1. enforce `CONTA_ENVIRONMENT=production` and `https://api.gateway.conta.no`;
2. authenticate using the dedicated production validation credential;
3. retrieve the organization list with GET only;
4. require exactly one accessible organization for the dedicated user;
5. require that the configured protected organization identifier matches that sole accessible organization;
6. perform one organization-scoped GET to confirm access to the intended production organization;
7. emit only safe aggregate evidence: authentication success, accessible organization count, configured-org match boolean, organization-reference SHA-256, scoped-GET success, methods used, and mutation=false;
8. delete response bodies from runner-temporary storage before completion;
9. never print API keys, raw organization IDs, organization names, company data, subscription response bodies, or other confidential account data.

## Explicit exclusions

This request does **not** authorize:

- installing the production Conta API key into `/Custom Models/conta-mcp` or any live server configuration;
- modifying `config/conta_config.local.php` on the server;
- setting live `allowed_write_organization_ids` or `allowed_write_actions`;
- enabling `enable_write_tools`, `execution_allowed`, `production_write_approved`, or opening a kill switch;
- deploying the production-write implementation;
- release approval;
- making any POST, PUT, PATCH or DELETE request;
- creating, updating, sending, posting, finalizing, crediting or deleting an invoice or invoice draft;
- creating/updating customers or products;
- any sandbox or production provider mutation;
- generating or approving a real invoice payload;
- making the production execution tool visible.

## Required safety result

A successful validation must record only repository-safe evidence equivalent to:

```text
PRODUCTION_API_AUTHENTICATED=true
ACCESSIBLE_ORGANIZATION_COUNT=1
CONFIGURED_ORGANIZATION_ACCESSIBLE=true
CONFIGURED_ORGANIZATION_IS_SOLE_ACCESSIBLE_ORGANIZATION=true
ORGANIZATION_REFERENCE_SHA256=<hash-only>
ORGANIZATION_SCOPED_GET_SUCCEEDED=true
HTTP_METHODS_USED=GET_ONLY
PROVIDER_READ_CALL_PERFORMED=true
PROVIDER_WRITE_CALL_PERFORMED=false
PRODUCTION_MUTATION_PERFORMED=false
RESPONSE_BODY_PRINTED=false
SECRET_VALUE_PRINTED=false
LIVE_CONFIGURATION_CHANGED=false
DEPLOYMENT_PERFORMED=false
PRODUCTION_WRITE_AUTHORIZED=false
```

Any organization count other than exactly 1, configured-org mismatch, authentication failure, unexpected response shape, non-200 scoped GET, or attempted non-GET method must fail closed.

## Post-validation boundary

Successful GET-only validation proves only that the dedicated credential can authenticate and that its organization access is restricted as expected. It does not authorize storing that credential in the live runtime, deploying the implementation, approving a release, preparing a real invoice draft, or executing a production write.

A later separate authorization must bind any production credential/runtime provisioning and fail-closed deployment to the exact implementation commit and validated organization-reference hash.

## Required operator authorization syntax

Only this exact instruction, referencing the exact reviewed request commit, authorizes this gate:

```text
AUTHORIZE_CONTA_PRODUCTION_IDENTITY_ACCESS_VALIDATION for commit <exact-reviewed-request-commit>
```

That instruction authorizes secure credential provisioning to the dedicated validation environment and GET-only production identity/access validation only. It authorizes no production mutation or deployment.
