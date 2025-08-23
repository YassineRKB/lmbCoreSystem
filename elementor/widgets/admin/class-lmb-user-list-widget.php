<?php
/**
 * User List Widget
 *
 * Allows admins to search for users by ID, name, or company and view profiles.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_User_List_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_user_list';
    }

    public function get_title() {
        return __('User List', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-users';
    }

    public function get_categories() {
        return ['lmb-admin'];
    }

    protected function _register_controls() {
        // No controls needed for frontend form
    }

    protected function render() {
        if (!current_user_can('manage_options')) {
            echo '<p>' . esc_html__('Access denied.', 'lmb-core') . '</p>';
            return;
        }

        ?>
        <div class="lmb-user-list" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_user_list_nonce')); ?>">
            <form class="lmb-user-search">
                <input type="text" name="search" placeholder="<?php _e('Search by ID, Name, or Company', 'lmb-core'); ?>">
                <button type="submit"><?php _e('Search', 'lmb-core'); ?></button>
            </form>
            <table class="lmb-users-table">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'lmb-core'); ?></th>
                        <th><?php _e('Name', 'lmb-core'); ?></th>
                        <th><?php _e('Email', 'lmb-core'); ?></th>
                        <th><?php _e('Company', 'lmb-core'); ?></th>
                    </tr>
                </thead>
                <tbody class="lmb-users-table-body"></tbody>
            </table>
        </div>
        <?php
    }
}