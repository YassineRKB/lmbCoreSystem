<?php
/**
 * Ad Manager
 *
 * Manages legal ad operations.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Ad_Manager {
    public static function init() {
        // No actions needed for now
    }

    public static function get_pending_ads() {
        return get_posts([
            'post_type'      => 'lmb_legal_ad',
            'post_status'    => 'draft',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => 'lmb_status',
                    'value'   => 'pending_review',
                    'compare' => '=',
                ],
            ],
        ]);
    }

    public static function approve_ad($ad_id) {
        $ad = get_post($ad_id);
        if (!$ad || $ad->post_type !== 'lmb_legal_ad') {
            return false;
        }

        wp_update_post([
            'ID'          => $ad_id,
            'post_status' => 'publish',
        ]);
        update_post_meta($ad_id, 'lmb_status', 'published');
        update_post_meta($ad_id, 'lmb_approved_by', get_current_user_id());

        LMB_Notification_Manager::add_notification($ad->post_author, __('Your legal ad has been approved.', 'lmb-core'), 'ad_approved');
        self::log_activity('Legal ad #%d approved by %s', $ad_id, wp_get_current_user()->display_name);
        return true;
    }

    public static function deny_ad($ad_id) {
        $ad = get_post($ad_id);
        if (!$ad || $ad->post_type !== 'lmb_legal_ad') {
            return false;
        }

        wp_update_post([
            'ID'          => $ad_id,
            'post_status' => 'draft',
        ]);
        update_post_meta($ad_id, 'lmb_status', 'denied');

        LMB_Notification_Manager::add_notification($ad->post_author, __('Your legal ad has been denied.', 'lmb-core'), 'ad_denied');
        self::log_activity('Legal ad #%d denied by %s', $ad_id, wp_get_current_user()->display_name);
        return true;
    }

    public static function log_activity($msg, ...$args) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'lmb_activity_log', [
            'activity' => sanitize_text_field(vsprintf($msg, $args)),
            'created_at' => current_time('mysql'),
        ]);
    }
}