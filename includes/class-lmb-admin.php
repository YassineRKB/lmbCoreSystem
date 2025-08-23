<?php
/**
 * LMB Admin Class
 *
 * Handles admin-specific functionality and dashboard setup.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Admin {
    public function __construct() {
        add_action('admin_init', [$this, 'restrict_admin_page']);
        add_action('wp_ajax_lmb_get_admin_stats', [$this, 'get_admin_stats']);
        add_action('wp_ajax_lmb_approve_legal_ad', [$this, 'approve_legal_ad']);
        add_action('wp_ajax_lmb_deny_legal_ad', [$this, 'deny_legal_ad']);
        add_action('wp_ajax_lmb_approve_payment', [$this, 'approve_payment']);
        add_action('wp_ajax_lmb_deny_payment', [$this, 'deny_payment']);
        add_action('wp_ajax_lmb_update_balance', [$this, 'update_balance']);
        $this->init();
    }

    public function init() {
        add_action('admin_menu', array($this, 'admin_menu'));
        //add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function admin_menu() {
        add_menu_page(
            'LMB Core',
            'LMB Core',
            'manage_options',
            'lmb-core',
            array($this, 'admin_page'),
            'dashicons-admin-generic',
            20
        );
    }

    /**
     * Restrict admin dashboard page to administrators only.
     */
    public function restrict_admin_page() {
        if (isset($_GET['page']) && $_GET['page'] === 'lmb-admin-dashboard' && !current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'lmb-core'));
        }
    }

    /**
     * Get admin statistics for stats widget.
     */
    public function get_admin_stats() {
        check_ajax_referer('lmb_admin_stats_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        global $wpdb;
        $month_start = date('Y-m-01');
        $new_clients = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->users WHERE user_registered >= %s",
                $month_start
            )
        );
        $published_ads = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'lmb_legal_ad' AND post_status = 'publish' AND post_date >= %s",
                $month_start
            )
        );
        $draft_ads = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'lmb_legal_ad' AND post_status = 'draft' AND post_date >= %s",
                $month_start
            )
        );
        $profits = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'lmb_price' AND post_id IN (
                    SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'lmb_status' AND meta_value = 'paid'
                ) AND post_id IN (
                    SELECT ID FROM $wpdb->posts WHERE post_type = 'lmb_invoice' AND post_date >= %s
                )",
                $month_start
            )
        );

        wp_send_json_success([
            'new_clients' => $new_clients ?: 0,
            'published_ads' => $published_ads ?: 0,
            'draft_ads' => $draft_ads ?: 0,
            'profits' => $profits ?: 0,
        ]);
    }

    /**
     * Approve a legal ad.
     */
    public function approve_legal_ad() {
        check_ajax_referer('lmb_admin_actions_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        $ad_id = isset($_POST['ad_id']) ? absint($_POST['ad_id']) : 0;
        if (!$ad_id) {
            wp_send_json_error(['message' => __('Invalid ad ID.', 'lmb-core')]);
        }

        update_post_meta($ad_id, 'lmb_status', 'approved');
        update_post_meta($ad_id, 'lmb_approved_by', get_current_user_id());
        update_post_meta($ad_id, 'lmb_approved_timestamp', current_time('mysql'));

        $user_id = get_post_meta($ad_id, 'lmb_client_id', true);
        LMB_Notifications::send_notification($user_id, __('Your legal ad has been approved.', 'lmb-core'));

        wp_send_json_success(['message' => __('Ad approved successfully.', 'lmb-core')]);
    }

    /**
     * Deny a legal ad.
     */
    public function deny_legal_ad() {
        check_ajax_referer('lmb_admin_actions_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        $ad_id = isset($_POST['ad_id']) ? absint($_POST['ad_id']) : 0;
        if (!$ad_id) {
            wp_send_json_error(['message' => __('Invalid ad ID.', 'lmb-core')]);
        }

        update_post_meta($ad_id, 'lmb_status', 'denied');
        update_post_meta($ad_id, 'lmb_denied_by', get_current_user_id());
        update_post_meta($ad_id, 'lmb_denied_timestamp', current_time('mysql'));

        $user_id = get_post_meta($ad_id, 'lmb_client_id', true);
        LMB_Notifications::send_notification($user_id, __('Your legal ad has been denied.', 'lmb-core'));

        wp_send_json_success(['message' => __('Ad denied successfully.', 'lmb-core')]);
    }

    /**
     * Approve a payment.
     */
    public function approve_payment() {
        check_ajax_referer('lmb_admin_actions_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        $invoice_id = isset($_POST['invoice_id']) ? absint($_POST['invoice_id']) : 0;
        if (!$invoice_id) {
            wp_send_json_error(['message' => __('Invalid invoice ID.', 'lmb-core')]);
        }

        update_post_meta($invoice_id, 'lmb_status', 'paid');
        $user_id = get_post_meta($invoice_id, 'lmb_client_id', true);
        $points = get_post_meta($invoice_id, 'lmb_points', true);
        $current_balance = get_user_meta($user_id, 'lmb_balance', true);
        update_user_meta($user_id, 'lmb_balance', $current_balance + $points);

        LMB_Notifications::send_notification($user_id, __('Your payment has been approved.', 'lmb-core'));

        wp_send_json_success(['message' => __('Payment approved successfully.', 'lmb-core')]);
    }

    /**
     * Deny a payment.
     */
    public function deny_payment() {
        check_ajax_referer('lmb_admin_actions_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        $invoice_id = isset($_POST['invoice_id']) ? absint($_POST['invoice_id']) : 0;
        if (!$invoice_id) {
            wp_send_json_error(['message' => __('Invalid invoice ID.', 'lmb-core')]);
        }

        update_post_meta($invoice_id, 'lmb_status', 'denied');
        $user_id = get_post_meta($invoice_id, 'lmb_client_id', true);
        LMB_Notifications::send_notification($user_id, __('Your payment has been denied.', 'lmb-core'));

        wp_send_json_success(['message' => __('Payment denied successfully.', 'lmb-core')]);
    }

    /**
     * Update user balance.
     */
    public function update_balance() {
        check_ajax_referer('lmb_balance_manipulation_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'lmb-core')]);
        }

        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $points = isset($_POST['points']) ? floatval($_POST['points']) : 0;
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';

        if (!$user_id || !$points || !in_array($action, ['add', 'subtract'])) {
            wp_send_json_error(['message' => __('Invalid input.', 'lmb-core')]);
        }

        $current_balance = get_user_meta($user_id, 'lmb_balance', true) ?: 0;
        $new_balance = $action === 'add' ? $current_balance + $points : $current_balance - $points;

        if ($new_balance < 0) {
            wp_send_json_error(['message' => __('Balance cannot be negative.', 'lmb-core')]);
        }

        update_user_meta($user_id, 'lmb_balance', $new_balance);
        LMB_Notifications::send_notification($user_id, sprintf(__('Your balance has been updated by %s points.', 'lmb-core'), $points));

        wp_send_json_success(['message' => __('Balance updated successfully.', 'lmb-core')]);
    }
}