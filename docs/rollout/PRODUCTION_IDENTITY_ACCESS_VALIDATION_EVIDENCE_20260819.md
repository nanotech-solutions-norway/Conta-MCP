# Conta MCP Production Identity/Access Validation Evidence — 2026-08-19

## Result

```text
VALIDATION_RUN_ID=32235276641
VALIDATION_JOB_ID=96013730712
VALIDATION_RESULT=SUCCESS
IMPLEMENTATION_MERGE_COMMIT=19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac
VALIDATION_WORKFLOW_MERGE_COMMIT=966a49d09f657794d27b3c093f8dc15d2e207bf1
PRODUCTION_API_AUTHENTICATED=true
ACCESSIBLE_ORGANIZATION_COUNT=1
CONFIGURED_ORGANIZATION_ACCESSIBLE=true
CONFIGURED_ORGANIZATION_IS_SOLE_ACCESSIBLE_ORGANIZATION=true
ORGANIZATION_REFERENCE_SHA256=9ee050155b0c35066a2ea426c72a65e5cdd2806f18a3cf9829fb132bd66634ab
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

## Evidence basis

GitHub Actions run `32235276641`, job `96013730712`, completed successfully. The workflow enforced production environment `https://api.gateway.conta.no`, required the protected `CONTA_API_KEY` and `CONTA_ORG_ID` inputs, and performed only:

- `GET /invoice/organizations`
- `GET /invoice/organizations/{opContextOrgId}/subscription-plan`

The first GET returned exactly one accessible organization, and the protected configured organization identifier matched that sole accessible organization. The organization reference was recorded only as SHA-256 above. The second organization-scoped GET returned HTTP 200 and a non-empty response body.

The workflow deleted response bodies from runner temporary storage on exit and did not print the API key, raw organization ID, organization name, subscription data, or other account data.

## Interpretation

This evidence confirms that the dedicated production Conta credential can authenticate and is restricted to exactly one accessible production organization, which is the intended Conta MCP organization. It does not prove operation-level API-key scopes because Conta API keys inherit the creating user's access.

This evidence does **not** authorize:

- installation of the production credential into the live Conta MCP runtime;
- live server configuration changes;
- deployment of the production-write implementation;
- release approval;
- enabling the production execution tool;
- opening the production kill switch;
- generating or approving a real invoice payload;
- any POST, PUT, PATCH or DELETE provider request;
- any production mutation.

## Next gate

A separate commit-bound authorization is required before the validated credential and organization reference may be provisioned into the live runtime and before the fail-closed implementation may be deployed. Any such deployment must preserve all production execution gates closed after deployment and must bind the production organization to:

```text
9ee050155b0c35066a2ea426c72a65e5cdd2806f18a3cf9829fb132bd66634ab
```
