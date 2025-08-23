<?php
/**
 * Ad Manager
 *
 * Handles operations related to legal ads.
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

    public static function approve_ad($ad_id) {
        if (!current_user_can('manage_options')) {
            return false;
        }
        $ad = get_post($ad_id);
        if ($ad && $ad->post_type === 'lmb_legal_ad') {
            wp_update_post([
                'ID'          => $ad_id,
                'post_status' => 'publish',
            ]);
            update_post_meta($ad_id, 'lmb_approved_by', get_current_user_id());
            update_post_meta($ad_id, 'lmb_approved_at', current_time('mysql'));
            LMB_Notification_Manager::add_notification($ad->post_author, __('Your legal ad has been approved.', 'lmb-core'), 'ad_approved');
            return true;
        }
        return false;
    }

    public static function deny_ad($ad_id) {
        if (!current_user_can('manage_options')) {
            return false;
        }
        $ad = get_post($ad_id);
        if ($ad && $ad->post_type === 'lmb_legal_ad') {
            wp_update_post([
                'ID'          => $ad_id,
                'post_status' => 'trash',
            ]);
            LMB_Notification_Manager::add_notification($ad->post_author, __('Your legal ad has been denied.', 'lmb-core'), 'ad_denied');
            return true;
        }
        return false;
    }

    public static function get_pending_ads() {
        $args = [
            'post_type'   => 'lmb_legal_ad',
            'post_status' => 'pending',
            'posts_per_page' => -1,
        ];
        return get_posts($args);
    }
}