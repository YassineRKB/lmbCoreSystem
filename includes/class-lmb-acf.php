<?php
/**
 * ACF Integration
 *
 * Registers ACF field groups for LMB post types.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_ACF {
    public static function init() {
        add_action('acf/init', [__CLASS__, 'register_field_groups']);
    }

    public static function register_field_groups() {
        if (function_exists('acf_add_local_field_group')) {
            // Legal Ad Details
            acf_add_local_field_group([
                'key' => 'group_legal_ad_data',
                'title' => __('Legal Ad Details', 'lmb-core'),
                'fields' => [
                    [
                        'key' => 'field_lmb_ad_type',
                        'label' => __('Ad Type', 'lmb-core'),
                        'name' => 'ad_type',
                        'type' => 'text',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_full_text',
                        'label' => __('Full Ad Text', 'lmb-core'),
                        'name' => 'full_text',
                        'type' => 'textarea',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_status',
                        'label' => __('Publication Status', 'lmb-core'),
                        'name' => 'lmb_status',
                        'type' => 'select',
                        'choices' => [
                            'draft' => __('Draft', 'lmb-core'),
                            'pending_review' => __('Pending Review', 'lmb-core'),
                            'published' => __('Published', 'lmb-core'),
                            'denied' => __('Denied', 'lmb-core'),
                        ],
                        'default_value' => 'draft',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_client_id',
                        'label' => __('Client ID', 'lmb-core'),
                        'name' => 'lmb_client_id',
                        'type' => 'number',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_ad_pdf_url',
                        'label' => __('Ad PDF URL', 'lmb-core'),
                        'name' => 'ad_pdf_url',
                        'type' => 'url',
                        'instructions' => __('URL of the generated ad PDF.', 'lmb-core'),
                        'required' => 0,
                    ],
                    [
                        'key' => 'field_lmb_accuse_file',
                        'label' => __('Accuse File', 'lmb-core'),
                        'name' => 'lmb_accuse_file',
                        'type' => 'file',
                        'return_format' => 'url',
                        'mime_types' => 'pdf',
                        'required' => 0,
                    ],
                ],
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'lmb_legal_ad',
                        ],
                    ],
                ],
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
            ]);

            // Newspaper Details
            acf_add_local_field_group([
                'key' => 'group_newspaper_data',
                'title' => __('Newspaper Details', 'lmb-core'),
                'fields' => [
                    [
                        'key' => 'field_lmb_newspaper_pdf',
                        'label' => __('Newspaper PDF', 'lmb-core'),
                        'name' => 'newspaper_pdf',
                        'type' => 'file',
                        'required' => 1,
                        'return_format' => 'url',
                        'mime_types' => 'pdf',
                    ],
                ],
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'lmb_newspaper',
                        ],
                    ],
                ],
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
            ]);

            // Invoice Details
            acf_add_local_field_group([
                'key' => 'group_invoice_data',
                'title' => __('Invoice Details', 'lmb-core'),
                'fields' => [
                    [
                        'key' => 'field_lmb_package_id',
                        'label' => __('Package ID', 'lmb-core'),
                        'name' => 'lmb_package_id',
                        'type' => 'number',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_package_name',
                        'label' => __('Package Name', 'lmb-core'),
                        'name' => 'lmb_package_name',
                        'type' => 'text',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_package_price',
                        'label' => __('Package Price (MAD)', 'lmb-core'),
                        'name' => 'lmb_package_price',
                        'type' => 'number',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_package_points',
                        'label' => __('Package Points', 'lmb-core'),
                        'name' => 'lmb_package_points',
                        'type' => 'number',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_payment_reference',
                        'label' => __('Payment Reference', 'lmb-core'),
                        'name' => 'lmb_payment_reference',
                        'type' => 'text',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_status',
                        'label' => __('Invoice Status', 'lmb-core'),
                        'name' => 'lmb_status',
                        'type' => 'select',
                        'choices' => [
                            'unpaid' => __('Unpaid', 'lmb-core'),
                            'paid' => __('Paid', 'lmb-core'),
                        ],
                        'default_value' => 'unpaid',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_lmb_bank_proof_file',
                        'label' => __('Bank Proof File', 'lmb-core'),
                        'name' => 'lmb_bank_proof_file',
                        'type' => 'file',
                        'return_format' => 'url',
                        'mime_types' => 'pdf,jpg,png',
                        'required' => 0,
                    ],
                ],
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'lmb_invoice',
                        ],
                    ],
                ],
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
            ]);
        }
    }
}