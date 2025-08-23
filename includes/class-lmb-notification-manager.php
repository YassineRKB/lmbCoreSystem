<?php
/**
 * Notification Manager
 *
 * Handles creation and retrieval of notifications.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Notification_Manager {
    public static function init() {
        // No actions needed for now
    }

    public static function add_notification($user_id, $message, $type) {
        global $wpdb;
        $table = $wpdb->prefix . 'lmb_notifications';
        $wpdb->insert($table, [
            'user_id'    => absint($user_id),
            'message'    => sanitize_text_field($message),
            'type'       => sanitize_text_field($type),
            'status'     => 'unread',
            'created_at' => current_time('mysql'),
        ]);
    }

    public static function get_notifications($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lmb_notifications';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d AND status = 'unread' ORDER BY created_at DESC", $user_id));
    }

    public static function mark_notification_read($notification_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'lmb_notifications';
        $wpdb->update($table, ['status' => 'read'], ['id' => absint($notification_id)]);
    }
}