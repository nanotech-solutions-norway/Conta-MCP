# Required Source Documents and Files Before Future Implementation — Layer 4 / Phase 4.0

## Status

This register identifies required material before any future implementation phase. It does not approve implementation.

## A. Conta API source documentation

Required current source material:

1. Current official Conta API documentation for all intended write operations.
2. Endpoint-specific request and response schemas.
3. Authentication and permission-scope documentation.
4. Error-code documentation.
5. Rate-limit documentation.
6. Idempotency documentation, if supported by Conta.
7. Sandbox/test-company documentation.
8. Webhook/callback documentation, if relevant.
9. Attachment/document upload documentation, if relevant.
10. Statutory/VAT/annual-settlement documentation, if relevant.

## B. Current project files to preserve

Required before future implementation:

1. Current production `api_v2.php`.
2. Current Layer 3 read-only Custom GPT OpenAPI schema.
3. Current route map output.
4. Current capability matrix output.
5. Current write-status response evidence.
6. Layer 3.10 release closure/archive package.
7. Phase 3.8 and 3.9 operator acceptance logs.
8. Phase 3.10 final release evidence.
9. Current PowerShell validation scripts.
10. Current fallback/backup inventory.

## C. Accounting/business rules

Required:

1. Chart of Accounts mapping.
2. VAT code mapping.
3. Customer/vendor matching and duplicate rules.
4. Product/service item mapping rules.
5. Currency handling rules.
6. Fiscal year and period-locking rules.
7. Voucher numbering rules.
8. Invoice numbering rules.
9. Credit note rules.
10. Purchase-registration rules.
11. Attachment/document-handling rules.
12. Duplicate detection rules.
13. Correction/reversal policy.
14. Approval policy by action type and risk class.

## D. Governance material

Required:

1. Operator identity source.
2. Approval authority matrix.
3. Risk-class approval requirements.
4. Audit retention policy.
5. Incident response process.
6. Rollback/correction process.
7. Data minimization and secrecy rules.
8. Legal/accounting review requirements.
9. Statutory submission policy.
10. Production change-control checklist.

## E. Sandbox validation material

Required:

1. Conta sandbox or test-company access confirmation.
2. Test company/tenant reference.
3. Test customers.
4. Test suppliers.
5. Test chart of accounts.
6. Test VAT codes.
7. Test products/services.
8. Test invoice scenarios.
9. Test purchase scenarios.
10. Test voucher scenarios.
11. Negative test cases.
12. Duplicate write test cases.
13. Permission failure test cases.
14. Correction/reversal test cases.

## Implementation blocker

If any source material needed for a selected write action is missing, that write action must remain blocked.
