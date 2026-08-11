# Conta sandbox configuration

The Conta sandbox is an independent test environment. Its account, API key and organization ID do not need to match production, but all three configured values must belong to the sandbox environment.

## Required values

| Setting | Source | Storage |
| --- | --- | --- |
| `CONTA_ENVIRONMENT` | Fixed value `sandbox` | Non-secret environment variable |
| `CONTA_API_BASE_URL` | Fixed value `https://api.gateway.conta-sandbox.no` | Non-secret GitHub environment variable |
| `CONTA_API_KEY` | API key created while signed into `https://app.conta-sandbox.no` | Protected environment secret |
| `CONTA_ORG_ID` | Organization ID returned for that sandbox identity | Protected environment secret |
| `CONTA_SANDBOX_TEST_CUSTOMER_ID` | Operator-selected synthetic customer in the configured sandbox organization | Protected environment secret |
| `CONTA_MCP_BEARER_TOKEN` | Separate MCP bridge authentication token | Protected environment secret |

Never use a production API key or production organization ID with the sandbox gateway. Never put real values in `.env.example`, repository files, pull requests, Actions logs or chat.

## GitHub protected environment

The `conta-sandbox-secrets` environment must contain:

- variables `CONTA_ENVIRONMENT=sandbox` and `CONTA_API_BASE_URL=https://api.gateway.conta-sandbox.no`;
- secrets named `CONTA_API_KEY`, `CONTA_ORG_ID`, `CONTA_SANDBOX_TEST_CUSTOMER_ID` and `CONTA_MCP_BEARER_TOKEN`;
- required reviewer approval and protected-branch restrictions.

Secret values are entered manually in GitHub. The repository and validation workflow can verify presence and access but cannot retrieve or display them.

## Safe identity validation sequence

1. Register and activate the separate Conta sandbox user.
2. Create the API key in the sandbox application.
3. Store the raw key, without a header prefix or quotes, as `CONTA_API_KEY`.
4. Run the protected read-only identity validation.
5. After authentication succeeds, use the returned sandbox organization ID as `CONTA_ORG_ID`.
6. Rerun the read-only validation to confirm organization-scoped access.

The identity workflow permits `GET` only and refuses a non-sandbox environment or gateway. It does not authorize provider writes, sandbox mutation, write-tool enablement or production access.

The separate test-customer workflow also permits `GET` only. It verifies that the protected customer ID resolves inside the configured sandbox organization without printing the customer ID, customer record or response body.

