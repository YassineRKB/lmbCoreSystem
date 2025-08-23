<?php
class LMB_Access_Control {
    public static function init() {
        add_action('init', [__CLASS__, 'register_capabilities']);
    }

    public static function register_capabilities() {
        $admin_role = get_role('administrator');
        $capabilities = [
            'edit_lmb_legal_ad',
            'edit_lmb_legal_ads',
            'publish_lmb_legal_ads',
            'read_lmb_legal_ad',
            'delete_lmb_legal_ad',
            'edit_others_lmb_legal_ads',
            'delete_others_lmb_legal_ads',
            'edit_lmb_newspaper',
            'edit_lmb_newspapers',
            'publish_lmb_newspapers',
            'read_lmb_newspaper',
            'delete_lmb_newspaper',
            'edit_others_lmb_newspapers',
            'delete_others_lmb_newspapers',
            'edit_lmb_invoice',
            'edit_lmb_invoices',
            'publish_lmb_invoices',
            'read_lmb_invoice',
            'delete_lmb_invoice',
            'edit_others_lmb_invoices',
            'delete_others_lmb_invoices',
        ];

        foreach ($capabilities as $cap) {
            $admin_role->add_cap($cap);
        }

        // User role capabilities
        $user_role = get_role('subscriber');
        $user_role->add_cap('read_lmb_legal_ad');
        $user_role->add_cap('edit_lmb_legal_ad');
        $user_role->add_cap('publish_lmb_legal_ads');
        $user_role->add_cap('read_lmb_invoice');
    }
}