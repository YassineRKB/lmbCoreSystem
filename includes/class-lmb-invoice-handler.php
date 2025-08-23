<?php
/**
 * Invoice Handler
 *
 * Manages invoice creation and retrieval.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Invoice_Handler {
    public static function init() {
        // No actions needed for now
    }

    public static function create_invoice($user_id, $package_id, $package_name, $package_price) {
        $user = get_userdata($user_id);
        $invoice_number = 'INV-' . time();
        $post_id = wp_insert_post([
            'post_title'   => $invoice_number,
            'post_type'    => 'lmb_invoice',
            'post_status'  => 'publish',
            'post_author'  => $user_id,
        ]);

        if (is_wp_error($post_id)) {
            return false;
        }

        update_post_meta($post_id, 'lmb_package_id', absint($package_id));
        update_post_meta($post_id, 'lmb_package_name', sanitize_text_field($package_name));
        update_post_meta($post_id, 'lmb_package_price', floatval($package_price));
        update_post_meta($post_id, 'lmb_status', 'unpaid');
        update_post_meta($post_id, 'lmb_payment_reference', 'REF-' . $post_id . '-' . time());

        LMB_Notification_Manager::add_notification($user_id, sprintf(__('New invoice %s created.', 'lmb-core'), $invoice_number), 'invoice_created');
        return $post_id;
    }

    public static function get_user_invoices($user_id, $status = 'all') {
        $args = [
            'post_type'      => 'lmb_invoice',
            'posts_per_page' => -1,
            'author'         => $user_id,
        ];
        if ($status !== 'all') {
            $args['meta_query'] = [
                [
                    'key'     => 'lmb_status',
                    'value'   => $status,
                    'compare' => '=',
                ],
            ];
        }
        return get_posts($args);
    }

    public static function get_user_accuse($user_id, $status = 'all') {
        $args = [
            'post_type'      => 'lmb_legal_ad',
            'posts_per_page' => -1,
            'author'         => $user_id,
            'meta_query'     => [
                [
                    'key'     => 'lmb_accuse_file',
                    'compare' => 'EXISTS',
                ],
            ],
        ];
        if ($status !== 'all') {
            $args['meta_query'][] = [
                'key'     => 'lmb_status',
                'value'   => $status,
                'compare' => '=',
            ];
        }
        return get_posts($args);
    }
}