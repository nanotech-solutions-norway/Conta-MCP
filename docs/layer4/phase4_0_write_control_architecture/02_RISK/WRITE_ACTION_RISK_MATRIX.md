# Write Action Risk Matrix — Layer 4 / Phase 4.0

## Risk taxonomy

| Class | Meaning | Default policy | Phase 4.0 status |
|---|---|---|---|
| R0 | Read-only | Allowed under Layer 3 controls | Active already |
| R1 | Draft-only / no external effect | May be planned as future dry-run or draft concept | Planning only |
| R2 | Internal accounting mutation | Requires sandbox proof and explicit implementation approval | Not implemented |
| R3 | External communication or external business effect | Requires communication safeguards and explicit approval | Not implemented |
| R4 | Statutory/legal submission | Blocked until legal/accounting review and separate approval | Blocked |
| R5 | Payment/bank execution | Blocked | Blocked |

## Control requirements by risk class

| Control | R0 | R1 | R2 | R3 | R4 | R5 |
|---|---:|---:|---:|---:|---:|---:|
| Read-only baseline | Required | Required | Required | Required | Required | Required |
| Dry-run | Optional | Required | Required | Required | Required | Required |
| Human approval | No | Required | Required | Required | Required | Required |
| Idempotency | No | Recommended | Required | Required | Required | Required |
| Audit record | Standard | Required | Required | Required | Required | Required |
| Sandbox validation | No | Recommended | Required | Required | Required | Required |
| Legal/accounting review | No | Optional | Recommended | Recommended | Required | Required |
| Phase 4.0 implementation | No new work | No | No | No | No | No |

## Seed action classification

| Future action category | Example action | Initial class | Initial policy |
|---|---|---:|---|
| Summary/report read | Management summary | R0 | Already read-only |
| Chart of accounts read | List accounts | R0 | Already read-only if endpoint exists |
| Invoice preview | Prepare invoice preview without saving | R1 | Future dry-run only |
| Invoice draft creation | Create draft invoice in Conta | R1/R2 depending provider effect | Requires source docs |
| Invoice send | Send invoice to customer | R3 | Blocked in Phase 4.0 |
| Customer creation | Create customer | R2 | Requires sandbox proof |
| Supplier/vendor creation | Create supplier | R2 | Requires sandbox proof |
| Product/item creation | Create product/service item | R2 | Requires sandbox proof |
| Purchase registration | Register purchase | R2 | Requires sandbox proof |
| Voucher/journal posting | Post accounting entry | R2 | Requires sandbox proof and accounting review |
| Attachment upload | Upload voucher/document | R2 | Requires data handling review |
| VAT submission | Submit VAT report | R4 | Blocked |
| Annual settlement submission | Submit annual settlement | R4 | Blocked |
| Bank/payment initiation | Execute or initiate payment | R5 | Blocked |
| Delete/reverse object | Delete, void, reverse, credit | R2/R3/R4 depending object | Blocked until correction model exists |
