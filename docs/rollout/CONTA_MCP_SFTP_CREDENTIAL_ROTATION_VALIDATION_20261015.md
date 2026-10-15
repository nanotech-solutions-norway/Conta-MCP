# Conta MCP SFTP Credential Rotation Validation — 2026-10-15

## Result

The rotated Domeneshop webhotel/SFTP credential was successfully revalidated through the protected production acceptance workflow.

```text
SFTP_CREDENTIAL_ROTATION_VALIDATED=true
ACCEPTANCE_RUN_ID=38704199577
ACCEPTANCE_JOB_ID=116349772422
DEPLOYMENT_CONTROL_COMMIT=3920ca9f92f5db7e1917c4d6924a5a184ebdcc24
DEPLOYED_CONTA_SOURCE_COMMIT=19d8b9fd3e7aec7fec7405df2ffec0e72839c9ac
PUBLIC_CONTRACT_VALIDATED=true
REMOTE_RUNTIME_HASHES_VERIFIED=true
DEPLOYED_RELEASE_COMMIT_VERIFIED=true
KILL_SWITCH_GLOBAL_BLOCKED=true
PRODUCTION_AUTHORIZATION_PRESENT=false
APPROVED_RELEASE_MANIFEST_PRESENT=false
AUTHENTICATED_INITIALIZE_VERIFIED=true
TOOLS_LIST_CONTRACT_VERIFIED=true
EXECUTION_TOOL_VISIBLE=false
READ_ONLY_MCP_SMOKE_VERIFIED=true
GET_INVOICE_READBACK_EXERCISED=true
PREVIEW_VERIFIED=true
PREVIEW_PROVIDER_CALL_PERFORMED=false
PROVIDER_MUTATION_PERFORMED=false
CONTA_MCP_V1_ACCEPTANCE_PASSED=true
```

## Interpretation

The new protected SFTP credential authenticates successfully and can read the deployed runtime. All protected runtime hashes match the expected deployed Conta source commit. Authenticated MCP read operations and deterministic preview remain functional.

Production write controls remained closed for the entire validation:

- global kill switch blocked;
- no production authorization packet;
- no approved release manifest;
- execution tool absent;
- preview performed no provider call;
- no provider mutation occurred.

## Operational status

`CONTA_MCP_V1_OPERATIONAL=true` remains valid after the hosting credential rotation.

The rotated webhotel/SFTP credential is now the validated credential for deployment-control access. Superseded historical hosting credentials must not be reused.

A separate database-credential hygiene work unit remains outside this rotation scope and must be handled only after its consumers and rollback requirements are identified.
