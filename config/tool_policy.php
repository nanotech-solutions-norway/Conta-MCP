<?php
/**
 * Canonical tool classification for Conta MCP.
 * Effective tool discovery is calculated at runtime by WritePolicy.
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
    'preview_tools' => [
        'conta_preview_invoice_draft',
    ],
    'controlled_write_tools' => [
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
