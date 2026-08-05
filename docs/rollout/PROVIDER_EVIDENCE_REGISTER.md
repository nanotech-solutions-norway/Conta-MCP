# Provider Evidence Register — Invoice Draft Create v2

| Evidence item | Status | Value / requirement |
|---|---|---|
| Static Swagger file hash | VERIFIED_OFFLINE | `B5C6493964F9895F2626730487B6230899C9979CC61B92D56ADAD44D69AF4A43` |
| Create method | VERIFIED_OFFLINE | `POST` |
| Create route | VERIFIED_OFFLINE | `/invoice/organizations/{opContextOrgId}/invoice-drafts` |
| Operation ID | VERIFIED_OFFLINE | `v1MakeInvoiceDraft` |
| Top-level required field | VERIFIED_OFFLINE | `registrationSource` |
| Required line fields | VERIFIED_OFFLINE | `description`, `vatCode`, `discount`, `price`, `quantity` |
| Current official schema freshness | PENDING_OPERATOR_VALIDATION | Refresh and compare before authorization |
| Provider scopes | PENDING_OPERATOR_VALIDATION | Must be confirmed from current provider evidence |
| Provider-native idempotency | PENDING_OPERATOR_VALIDATION | Local replay prevention is implemented; provider behaviour remains unconfirmed |
| Sandbox/test-company identity | PENDING_OPERATOR_VALIDATION | Server-side only; do not commit real IDs |
| Readback route | PENDING_OPERATOR_VALIDATION | Must support retrieval of the created draft by returned ID |
| Rectification procedure | PENDING_OPERATOR_VALIDATION | Must avoid send/post/delete and preserve evidence |
| One-call operator authorization | NOT_GRANTED | Separate explicit authorization required |

No unresolved provider item is interpreted as approval.
