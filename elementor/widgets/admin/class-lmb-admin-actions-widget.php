<?php
/**
 * Admin Actions Widget
 *
 * Displays a tabbed interface for admin actions: Feed, Quick Actions, Pending Legal Ads, Pending Payments.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Admin_Actions_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_admin_actions';
    }

    public function get_title() {
        return __('Admin Actions', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-tasks';
    }

    public function get_categories() {
        return ['lmb-admin'];
    }

    protected function _register_controls() {
        // No controls needed for display-only widget
    }

    protected function render() {
        if (!current_user_can('manage_options')) {
            echo '<p>' . esc_html__('Access denied.', 'lmb-core') . '</p>';
            return;
        }

        ?>
        <div class="lmb-admin-actions" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_admin_actions_nonce')); ?>">
            <div class="lmb-tabs">
                <button class="lmb-tab active" data-tab="feed"><?php _e('Feed', 'lmb-core'); ?></button>
                <button class="lmb-tab" data-tab="quick-actions"><?php _e('Quick Actions', 'lmb-core'); ?></button>
                <button class="lmb-tab" data-tab="pending-ads"><?php _e('Pending Legal Ads', 'lmb-core'); ?></button>
                <button class="lmb-tab" data-tab="pending-payments"><?php _e('Pending Payments', 'lmb-core'); ?></button>
            </div>
            <div class="lmb-tab-content" id="lmb-tab-feed">
                <ul class="lmb-feed-list"></ul>
            </div>
            <div class="lmb-tab-content" id="lmb-tab-quick-actions" style="display:none;">
                <button class="lmb-action-btn" data-action="approve-all-ads"><?php _e('Approve All Pending Ads', 'lmb-core'); ?></button>
                <button class="lmb-action-btn" data-action="deny-all-ads"><?php _e('Deny All Pending Ads', 'lmb-core'); ?></button>
                <button class="lmb-action-btn" data-action="approve-all-payments"><?php _e('Approve All Pending Payments', 'lmb-core'); ?></button>
                <button class="lmb-action-btn" data-action="deny-all-payments"><?php _e('Deny All Pending Payments', 'lmb-core'); ?></button>
            </div>
            <div class="lmb-tab-content" id="lmb-tab-pending-ads" style="display:none;">
                <ul class="lmb-pending-ads-list"></ul>
            </div>
            <div class="lmb-tab-content" id="lmb-tab-pending-payments" style="display:none;">
                <ul class="lmb-pending-payments-list"></ul>
            </div>
        </div>
        <?php
    }
}