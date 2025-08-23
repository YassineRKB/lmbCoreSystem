<?php
class LMB_Database_Manager {
    public static function init() {
        // No actions needed on init for now
    }

    public static function create_custom_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Points table
        $points_table = $wpdb->prefix . 'lmb_points';
        $sql = "CREATE TABLE $points_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            points INT NOT NULL DEFAULT 0,
            transaction_type VARCHAR(50) NOT NULL,
            transaction_date DATETIME NOT NULL,
            reference_id BIGINT(20) UNSIGNED,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Notifications table
        $notifications_table = $wpdb->prefix . 'lmb_notifications';
        $sql = "CREATE TABLE $notifications_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'unread',
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql);
    }
}