<?php
/**
 * LMB User Dashboard Class
 *
 * Sets up the user dashboard page and restricts access.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_User_Dashboard {
    public function __construct() {
        add_action('init', [$this, 'register_dashboard_page']);
        add_action('template_redirect', [$this, 'restrict_dashboard_access']);
    }

    /**
     * Register the user dashboard page.
     */
    public function register_dashboard_page() {
        $dashboard_page = get_option('lmb_user_dashboard_page');
        if (!$dashboard_page) {
            $page = [
                'post_title'   => __('User Dashboard', 'lmb-core'),
                'post_content' => '[elementor-template id=""]', // Placeholder for Elementor template
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1,
            ];
            $page_id = wp_insert_post($page);
            update_option('lmb_user_dashboard_page', $page_id);
        }
    }

    /**
     * Restrict dashboard page to logged-in users.
     */
    public function restrict_dashboard_access() {
        $dashboard_page = get_option('lmb_user_dashboard_page');
        if (is_page($dashboard_page) && !is_user_logged_in()) {
            wp_redirect(wp_login_url(get_permalink($dashboard_page)));
            exit;
        }
    }
}