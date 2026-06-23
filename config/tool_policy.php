<?php
/**
 * Tool policy for Conta MCP.
 *
 * Design rule:
 * - Read-only tools are available when the MCP endpoint is authenticated and configured.
 * - Write tools are listed but disabled unless explicitly enabled server-side.
 * - Destructive/accounting-posting tools are not implemented in this initial package.
 */

return [
    'read_tools' => [
        'conta_health_check',
        'conta_list_organizations',
        'conta_list_customers',
        'conta_get_customer',
        'conta_list_invoices',
        'conta_get_invoice',
    ],
    'draft_write_tools' => [
        'conta_create_invoice_draft',
    ],
    'blocked_tools' => [
        'conta_send_invoice',
        'conta_delete_invoice',
        'conta_post_accounting_entry',
        'conta_submit_vat_return',
        'conta_modify_bank_payment',
    ],
];
