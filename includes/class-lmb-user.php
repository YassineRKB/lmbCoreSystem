<?php
/**
 * LMB User Class
 *
 * Handles user-specific functionality like stats and invoices.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_User {
    public function __construct() {
        add_action('wp_ajax_lmb_get_user_stats', [$this, 'get_user_stats']);
        add_action('wp_ajax_lmb_get_invoices', [$this, 'get_invoices']);
        add_action('wp_ajax_lmb_upload_bank_proof', [$this, 'upload_bank_proof']);
        add_action('wp_ajax_lmb_subscribe_package', [$this, 'subscribe_package']);
    }

    /**
     * Get user statistics for stats widget.
     */
    public function get_user_stats() {
        check_ajax_referer('lmb_user_stats_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        $user_id = get_current_user_id();
        $balance = get_user_meta($user_id, 'lmb_balance', true) ?: 0;
        $drafts = count(LMB_Invoice_Handler::get_user_legal_ads($user_id, 'draft'));
        $published = count(LMB_Invoice_Handler::get_user_legal_ads($user_id, 'publish'));

        wp_send_json_success([
            'balance' => $balance,
            'drafts' => $drafts,
            'published' => $published,
        ]);
    }

    /**
     * Get user invoices and accuse files.
     */
    public function get_invoices() {
        check_ajax_referer('lmb_invoices_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'invoices';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $user_id = get_current_user_id();

        if ($tab === 'accuse') {
            $accuse_files = get_posts([
                'post_type' => 'lmb_accuse',
                'meta_query' => [
                    [
                        'key' => 'lmb_client_id',
                        'value' => $user_id,
                    ],
                ],
                'posts_per_page' => -1,
            ]);

            $accuse_data = array_map(function($post) {
                return [
                    'title' => esc_html($post->post_title),
                    'url' => wp_get_attachment_url(get_post_meta($post->ID, 'lmb_accuse_file', true)),
                    'date' => get_the_date('', $post),
                ];
            }, $accuse_files);

            wp_send_json_success(['accuse' => $accuse_data]);
        } else {
            $invoices = LMB_Invoice_Handler::get_user_invoices($user_id, $status);
            $invoice_data = array_map(function($post) {
                return [
                    'id' => $post->ID,
                    'number' => esc_html($post->post_title),
                    'package' => esc_html(get_post_meta($post->ID, 'lmb_package_name', true)),
                    'price' => esc_html(get_post_meta($post->ID, 'lmb_price', true)),
                    'status' => esc_html(get_post_meta($post->ID, 'lmb_status', true)),
                    'date' => get_the_date('', $post),
                ];
            }, $invoices);

            wp_send_json_success(['invoices' => $invoice_data]);
        }
    }

    /**
     * Handle bank proof upload.
     */
    public function upload_bank_proof() {
        check_ajax_referer('lmb_upload_bank_proof_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        $invoice_id = isset($_POST['invoice_id']) ? absint($_POST['invoice_id']) : 0;
        if (!$invoice_id || get_post_meta($invoice_id, 'lmb_status', true) !== 'unpaid') {
            wp_send_json_error(['message' => __('Invalid or paid invoice.', 'lmb-core')]);
        }

        if (!isset($_FILES['bank_proof_file']) || $_FILES['bank_proof_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => __('File upload error.', 'lmb-core')]);
        }

        $file = wp_handle_upload($_FILES['bank_proof_file'], ['test_form' => false]);
        if ($file && !isset($file['error'])) {
            $attachment_id = wp_insert_attachment([
                'guid' => $file['url'],
                'post_mime_type' => $file['type'],
                'post_title' => sanitize_file_name($_FILES['bank_proof_file']['name']),
                'post_content' => '',
                'post_status' => 'inherit',
            ], $file['file']);

            if ($attachment_id) {
                update_post_meta($invoice_id, 'lmb_bank_proof', $attachment_id);
                update_post_meta($invoice_id, 'lmb_status', 'pending_review');
                LMB_Notifications::send_notification(get_current_user_id(), __('Bank proof uploaded successfully.', 'lmb-core'));
                wp_send_json_success(['message' => __('Bank proof uploaded successfully.', 'lmb-core')]);
            }
        }

        wp_send_json_error(['message' => __('File upload failed.', 'lmb-core')]);
    }

    /**
     * Handle package subscription.
     */
    public function subscribe_package() {
        check_ajax_referer('lmb_subscribe_package_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        $package_id = isset($_POST['package_id']) ? sanitize_text_field($_POST['package_id']) : '';
        $packages = get_option('lmb_packages', []);
        if (!$package_id || !isset($packages[$package_id])) {
            wp_send_json_error(['message' => __('Invalid package.', 'lmb-core')]);
        }

        $package = $packages[$package_id];
        $invoice_id = wp_insert_post([
            'post_title' => sprintf(__('Invoice for %s', 'lmb-core'), $package['name']),
            'post_type' => 'lmb_invoice',
            'post_status' => 'publish',
        ]);

        if ($invoice_id) {
            update_post_meta($invoice_id, 'lmb_client_id', get_current_user_id());
            update_post_meta($invoice_id, 'lmb_package_name', $package['name']);
            update_post_meta($invoice_id, 'lmb_price', $package['price']);
            update_post_meta($invoice_id, 'lmb_points', $package['points']);
            update_post_meta($invoice_id, 'lmb_status', 'unpaid');

            LMB_Notifications::send_notification(get_current_user_id(), __('New invoice created for your package subscription.', 'lmb-core'));
            wp_send_json_success(['message' => __('Package subscribed successfully. Invoice created.', 'lmb-core')]);
        }

        wp_send_json_error(['message' => __('Failed to create invoice.', 'lmb-core')]);
    }
}