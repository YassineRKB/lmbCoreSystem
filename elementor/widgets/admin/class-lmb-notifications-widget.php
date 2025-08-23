<?php
/**
 * Notifications Widget
 *
 * Displays notifications for admins (pending ads/payments) and users (ad/payment status, balance changes).
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Notifications_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_notifications';
    }

    public function get_title() {
        return __('Notifications', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-bell';
    }

    public function get_categories() {
        return ['lmb-admin', 'lmb-user'];
    }

    protected function _register_controls() {
        // No controls needed for display-only widget
    }

    protected function render() {
        if (!is_user_logged_in()) {
            echo '<p>' . esc_html__('Please log in to view notifications.', 'lmb-core') . '</p>';
            return;
        }

        ?>
        <div class="lmb-notifications" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_notifications_nonce')); ?>">
            <ul class="lmb-notifications-list"></ul>
        </div>
        <?php
    }
}