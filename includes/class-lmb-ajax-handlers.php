<?php
/**
 * AJAX Handlers
 *
 * Handles AJAX requests for LMB widgets.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Ajax_Handlers {
    public static function init() {
        add_action('wp_ajax_lmb_get_admin_stats', [__CLASS__, 'handle_get_admin_stats']);
        add_action('wp_ajax_lmb_get_admin_actions', [__CLASS__, 'handle_get_admin_actions']);
        add_action('wp_ajax_lmb_approve_ad', [__CLASS__, 'handle_approve_ad']);
        add_action('wp_ajax_lmb_deny_ad', [__CLASS__, 'handle_deny_ad']);
        add_action('wp_ajax_lmb_approve_payment', [__CLASS__, 'handle_approve_payment']);
        add_action('wp_ajax_lmb_deny_payment', [__CLASS__, 'handle_deny_payment']);
        add_action('wp_ajax_lmb_update_balance', [__CLASS__, 'handle_update_balance']);
        add_action('wp_ajax_lmb_get_legal_ads', [__CLASS__, 'handle_get_legal_ads']);
        add_action('wp_ajax_lmb_get_notifications', [__CLASS__, 'handle_get_notifications']);
        add_action('wp_ajax_lmb_mark_notification_read', [__CLASS__, 'handle_mark_notification_read']);
        add_action('wp_ajax_lmb_update_packages', [__CLASS__, 'handle_update_packages']);
        add_action('wp_ajax_lmb_upload_accuse', [__CLASS__, 'handle_upload_accuse']);
        add_action('wp_ajax_lmb_upload_newspaper', [__CLASS__, 'handle_upload_newspaper']);
        add_action('wp_ajax_lmb_get_newspapers', [__CLASS__, 'handle_get_newspapers']);
        add_action('wp_ajax_lmb_search_users', [__CLASS__, 'handle_search_users']);
        add_action('wp_ajax_lmb_get_user_stats', [__CLASS__, 'handle_get_user_stats']);
        add_action('wp_ajax_lmb_get_invoices', [__CLASS__, 'handle_get_invoices']);
        add_action('wp_ajax_lmb_upload_bank_proof', [__CLASS__, 'handle_upload_bank_proof']);
        add_action('wp_ajax_lmb_subscribe_package', [__CLASS__, 'handle_subscribe_package']);
    }

    public static function handle_get_admin_stats() {
        check_ajax_referer('lmb_admin_stats_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        global $wpdb;
        $month_start = date('Y-m-01 00:00:00');
        $month_end = date('Y-m-t 23:59:59');

        $new_clients = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->users WHERE user_registered >= %s AND user_registered <= %s",
            $month_start, $month_end
        ));

        $published_ads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'lmb_legal_ad' AND post_status = 'publish' AND post_date >= %s AND post_date <= %s",
            $month_start, $month_end
        ));

        $draft_ads = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'lmb_legal_ad' AND post_status = 'draft' AND post_date >= %s AND post_date <= %s",
            $month_start, $month_end
        ));

        $profits = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM {$wpdb->prefix}lmb_points WHERE transaction_type = 'payment_approved' AND transaction_date >= %s AND transaction_date <= %s",
            $month_start, $month_end
        ));

        wp_send_json_success([
            'new_clients'   => (int)$new_clients,
            'published_ads' => (int)$published_ads,
            'draft_ads'     => (int)$draft_ads,
            'profits'       => (int)$profits . ' MAD',
        ]);
    }

    public static function handle_get_admin_actions() {
        check_ajax_referer('lmb_admin_actions_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'feed';
        $data = [];

        if ($tab === 'feed') {
            $data['feed'] = [];
            $notifications = LMB_Notification_Manager::get_notifications(0);
            foreach ($notifications as $n) {
                $data['feed'][] = [
                    'message' => esc_html($n->message),
                    'time'    => esc_html($n->created_at),
                ];
            }
        } elseif ($tab === 'pending-ads') {
            $ads = LMB_Ad_Manager::get_pending_ads();
            foreach ($ads as $ad) {
                $data['pending_ads'][] = [
                    'id'      => $ad->ID,
                    'title'   => esc_html($ad->post_title),
                    'content' => esc_html(wp_trim_words($ad->post_content, 20)),
                ];
            }
        } elseif ($tab === 'pending-payments') {
            $payments = LMB_Payment_Verifier::get_pending_payments();
            foreach ($payments as $p) {
                $data['pending_payments'][] = [
                    'id'     => $p->id,
                    'user'   => esc_html(get_userdata($p->user_id)->display_name),
                    'points' => (int)$p->points,
                ];
            }
        }

        wp_send_json_success($data);
    }

    public static function handle_approve_ad() {
        check_ajax_referer('lmb_admin_actions_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }
        $ad_id = isset($_POST['ad_id']) ? absint($_POST['ad_id']) : 0;
        if (LMB_Ad_Manager::approve_ad($ad_id)) {
            wp_send_json_success(['message' => __('Ad approved.', 'lmb-core')]);
        } else {
            wp_send_json_error(['message' => __('Failed to approve ad.', 'lmb-core')]);
        }
    }

    public static function handle_deny_ad() {
        check_ajax_referer('lmb_admin_actions_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }
        $ad_id = isset($_POST['ad_id']) ? absint($_POST['ad_id']) : 0;
        if (LMB_Ad_Manager::deny_ad($ad_id)) {
            wp_send_json_success(['message' => __('Ad denied.', 'lmb-core')]);
        } else {
            wp_send_json_error(['message' => __('Failed to deny ad.', 'lmb-core')]);
        }
    }

    public static function handle_approve_payment() {
        check_ajax_referer('lmb_admin_actions_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }
        $payment_id = isset($_POST['payment_id']) ? absint($_POST['payment_id']) : 0;
        if (LMB_Payment_Verifier::approve_payment($payment_id)) {
            wp_send_json_success(['message' => __('Payment approved.', 'lmb-core')]);
        } else {
            wp_send_json_error(['message' => __('Failed to approve payment.', 'lmb-core')]);
        }
    }

    public static function handle_deny_payment() {
        check_ajax_referer('lmb_admin_actions_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }
        $payment_id = isset($_POST['payment_id']) ? absint($_POST['payment_id']) : 0;
        if (LMB_Payment_Verifier::deny_payment($payment_id)) {
            wp_send_json_success(['message' => __('Payment denied.', 'lmb-core')]);
        } else {
            wp_send_json_error(['message' => __('Failed to deny payment.', 'lmb-core')]);
        }
    }

    public static function handle_update_balance() {
        check_ajax_referer('lmb_balance_manipulation_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $points = isset($_POST['points']) ? intval($_POST['points']) : 0;
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';

        if (!$user_id || !$points || !in_array($action, ['add', 'subtract'])) {
            wp_send_json_error(['message' => __('Invalid input.', 'lmb-core')]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'lmb_points';
        $points = $action === 'add' ? abs($points) : -abs($points);
        $wpdb->insert($table, [
            'user_id'         => $user_id,
            'points'          => $points,
            'transaction_type' => 'admin_adjustment',
            'transaction_date' => current_time('mysql'),
        ]);

        LMB_Notification_Manager::add_notification($user_id, sprintf(__('Your balance was %s by %d points.', 'lmb-core'), $action === 'add' ? 'increased' : 'decreased', abs($points)), 'balance_change');
        wp_send_json_success(['message' => __('Balance updated.', 'lmb-core')]);
    }

    public static function handle_get_legal_ads() {
        check_ajax_referer('lmb_legal_ads_list_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $args = [
            'post_type'      => 'lmb_legal_ad',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
            $args['author'] = absint($_POST['user_id']);
        }
        if (isset($_POST['ad_type']) && !empty($_POST['ad_type'])) {
            $args['meta_query'][] = [
                'key'     => 'lmb_ad_type',
                'value'   => sanitize_text_field($_POST['ad_type']),
                'compare' => '=',
            ];
        }
        if (isset($_POST['company_name']) && !empty($_POST['company_name'])) {
            $args['meta_query'][] = [
                'key'     => 'lmb_company_name',
                'value'   => sanitize_text_field($_POST['company_name']),
                'compare' => 'LIKE',
            ];
        }

        $ads = get_posts($args);
        $data = [];
        foreach ($ads as $ad) {
            $approved_by = get_post_meta($ad->ID, 'lmb_approved_by', true);
            $data[] = [
                'id'          => $ad->ID,
                'content'     => esc_html(wp_trim_words($ad->post_content, 20)),
                'status'      => esc_html($ad->post_status),
                'approved_by'  => $approved_by ? esc_html(get_userdata($approved_by)->display_name) : '-',
                'timestamp'   => esc_html($ad->post_date),
            ];
        }
        wp_send_json_success($data);
    }

    public static function handle_get_notifications() {
        check_ajax_referer('lmb_notifications_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $user_id = current_user_can('manage_options') ? 0 : get_current_user_id();
        $notifications = LMB_Notification_Manager::get_notifications($user_id);
        $data = [];
        foreach ($notifications as $n) {
            $data[] = [
                'id'      => $n->id,
                'message' => esc_html($n->message),
                'type'    => esc_html($n->type),
                'time'    => esc_html($n->created_at),
            ];
        }
        wp_send_json_success($data);
    }

    public static function handle_mark_notification_read() {
        check_ajax_referer('lmb_notifications_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $notification_id = isset($_POST['notification_id']) ? absint($_POST['notification_id']) : 0;
        LMB_Notification_Manager::mark_notification_read($notification_id);
        wp_send_json_success(['message' => __('Notification marked as read.', 'lmb-core')]);
    }

    public static function handle_update_packages() {
        check_ajax_referer('lmb_packages_editor_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $package_id = isset($_POST['package_id']) ? absint($_POST['package_id']) : 0;
        $package_name = isset($_POST['package_name']) ? sanitize_text_field($_POST['package_name']) : '';
        $package_price = isset($_POST['package_price']) ? floatval($_POST['package_price']) : 0;
        $package_points = isset($_POST['package_points']) ? absint($_POST['package_points']) : 0;

        if (!$package_name) {
            $packages = get_option('lmb_packages', []);
            wp_send_json_success(['packages' => array_values($packages)]);
        }

        if (!$package_price || !$package_points) {
            wp_send_json_error(['message' => __('Invalid input.', 'lmb-core')]);
        }

        $packages = get_option('lmb_packages', []);
        $packages[$package_id ?: count($packages) + 1] = [
            'name'   => $package_name,
            'price'  => $package_price,
            'points' => $package_points,
        ];
        update_option('lmb_packages', $packages);
        wp_send_json_success(['message' => __('Package saved.', 'lmb-core'), 'packages' => array_values($packages)]);
    }

    public static function handle_upload_accuse() {
        check_ajax_referer('lmb_upload_accuse_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $ad_id = isset($_POST['ad_id']) ? absint($_POST['ad_id']) : 0;
        if (!$ad_id || !isset($_FILES['accuse_file'])) {
            wp_send_json_error(['message' => __('Invalid input.', 'lmb-core')]);
        }

        $upload = wp_handle_upload($_FILES['accuse_file'], ['test_form' => false, 'mimes' => ['pdf' => 'application/pdf']]);
        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
        }

        update_post_meta($ad_id, 'lmb_accuse_file', $upload['url']);
        $ad = get_post($ad_id);
        LMB_Notification_Manager::add_notification($ad->post_author, __('An accuse has been uploaded for your legal ad.', 'lmb-core'), 'accuse_uploaded');
        wp_send_json_success(['message' => __('Accuse uploaded.', 'lmb-core')]);
    }

    public static function handle_upload_newspaper() {
        check_ajax_referer('lmb_upload_newspaper_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $title = isset($_POST['newspaper_title']) ? sanitize_text_field($_POST['newspaper_title']) : '';
        if (!$title || !isset($_FILES['newspaper_file'])) {
            wp_send_json_error(['message' => __('Invalid input.', 'lmb-core')]);
        }

        $upload = wp_handle_upload($_FILES['newspaper_file'], ['test_form' => false, 'mimes' => ['pdf' => 'application/pdf']]);
        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
        }

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_type'    => 'lmb_newspaper',
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
        ]);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => __('Failed to create newspaper.', 'lmb-core')]);
        }

        update_post_meta($post_id, 'lmb_newspaper_file', $upload['url']);
        wp_send_json_success(['message' => __('Newspaper uploaded.', 'lmb-core')]);
    }

    public static function handle_get_newspapers() {
        check_ajax_referer('lmb_upload_newspaper_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $args = [
            'post_type'      => 'lmb_newspaper',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $args['s'] = sanitize_text_field($_POST['search']);
        }
        if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
            $args['date_query']['after'] = sanitize_text_field($_POST['start_date']);
        }
        if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
            $args['date_query']['before'] = sanitize_text_field($_POST['end_date']);
        }

        $newspapers = get_posts($args);
        $data = [];
        foreach ($newspapers as $n) {
            $data[] = [
                'id'    => $n->ID,
                'title' => esc_html($n->post_title),
                'url'   => esc_url(get_post_meta($n->ID, 'lmb_newspaper_file', true)),
                'date'  => esc_html($n->post_date),
            ];
        }
        wp_send_json_success($data);
    }

    public static function handle_search_users() {
        check_ajax_referer('lmb_user_list_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $args = [
            'search' => '*' . $search . '*',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key'     => 'lmb_company_name',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ],
            ],
        ];

        if (is_numeric($search)) {
            $args['include'] = [$search];
        }

        $users = get_users($args);
        $data = [];
        foreach ($users as $u) {
            $data[] = [
                'id'      => $u->ID,
                'name'    => esc_html($u->display_name),
                'email'   => esc_html($u->user_email),
                'company' => esc_html(get_user_meta($u->ID, 'lmb_company_name', true) ?: '-'),
            ];
        }
        wp_send_json_success($data);
    }

    public static function handle_get_user_stats() {
        check_ajax_referer('lmb_user_stats_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $user_id = get_current_user_id();
        global $wpdb;

        $balance = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM {$wpdb->prefix}lmb_points WHERE user_id = %d",
            $user_id
        ));

        $drafts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'lmb_legal_ad' AND post_status = 'draft' AND post_author = %d",
            $user_id
        ));

        $published = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'lmb_legal_ad' AND post_status = 'publish' AND post_author = %d",
            $user_id
        ));

        wp_send_json_success([
            'balance'   => (int)$balance,
            'drafts'    => (int)$drafts,
            'published' => (int)$published,
        ]);
    }

    public static function handle_get_invoices() {
        check_ajax_referer('lmb_invoices_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $user_id = get_current_user_id();
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'invoices';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $data = [];

        if ($tab === 'accuse') {
            $accuse = LMB_Invoice_Handler::get_user_accuse($user_id, $status);
            foreach ($accuse as $a) {
                $data['accuse'][] = [
                    'id'    => $a->ID,
                    'title' => esc_html($a->post_title),
                    'url'   => esc_url(get_post_meta($a->ID, 'lmb_accuse_file', true)),
                    'date'  => esc_html($a->post_date),
                ];
            }
        } else {
            $invoices = LMB_Invoice_Handler::get_user_invoices($user_id, $status);
            foreach ($invoices as $i) {
                $data['invoices'][] = [
                    'id'       => $i->ID,
                    'number'   => esc_html($i->post_title),
                    'status'   => esc_html(get_post_meta($i->ID, 'lmb_status', true)),
                    'price'    => floatval(get_post_meta($i->ID, 'lmb_package_price', true)) . ' MAD',
                    'package'  => esc_html(get_post_meta($i->ID, 'lmb_package_name', true)),
                    'reference' => esc_html(get_post_meta($i->ID, 'lmb_payment_reference', true)),
                    'date'     => esc_html($i->post_date),
                ];
            }
        }

        wp_send_json_success($data);
    }

    public static function handle_upload_bank_proof() {
        check_ajax_referer('lmb_upload_bank_proof_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $invoice_id = isset($_POST['invoice_id']) ? absint($_POST['invoice_id']) : 0;
        if (!$invoice_id || !isset($_FILES['bank_proof_file'])) {
            wp_send_json_error(['message' => __('Invalid input.', 'lmb-core')]);
        }

        $invoice = get_post($invoice_id);
        if (!$invoice || $invoice->post_author != get_current_user_id() || $invoice->post_type != 'lmb_invoice') {
            wp_send_json_error(['message' => __('Invalid invoice.', 'lmb-core')]);
        }

        $upload = wp_handle_upload($_FILES['bank_proof_file'], ['test_form' => false, 'mimes' => ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'png' => 'image/png']]);
        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
        }

        update_post_meta($invoice_id, 'lmb_bank_proof_file', $upload['url']);
        global $wpdb;
        $points = get_post_meta($invoice_id, 'lmb_package_points', true);
        $wpdb->insert($wpdb->prefix . 'lmb_points', [
            'user_id'         => get_current_user_id(),
            'points'          => absint($points),
            'transaction_type' => 'payment_pending',
            'transaction_date' => current_time('mysql'),
            'reference_id'    => $invoice_id,
        ]);

        LMB_Notification_Manager::add_notification(0, sprintf(__('User %s uploaded bank proof for invoice %s.', 'lmb-core'), get_userdata(get_current_user_id())->display_name, $invoice->post_title), 'payment_pending');
        wp_send_json_success(['message' => __('Bank proof uploaded.', 'lmb-core')]);
    }

    public static function handle_subscribe_package() {
        check_ajax_referer('lmb_subscribe_package_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Unauthorized', 'lmb-core')]);
        }

        $package_id = isset($_POST['package_id']) ? absint($_POST['package_id']) : 0;
        $packages = get_option('lmb_packages', []);
        if (!$package_id || !isset($packages[$package_id])) {
            wp_send_json_error(['message' => __('Invalid package.', 'lmb-core')]);
        }

        $package = $packages[$package_id];
        $invoice_id = LMB_Invoice_Handler::create_invoice(get_current_user_id(), $package_id, $package['name'], $package['price']);
        if ($invoice_id) {
            update_post_meta($invoice_id, 'lmb_package_points', $package['points']);
            wp_send_json_success(['message' => __('Invoice created. Please upload bank proof.', 'lmb-core'), 'packages' => array_values($packages)]);
        } else {
            wp_send_json_error(['message' => __('Failed to create invoice.', 'lmb-core')]);
        }
    }
}