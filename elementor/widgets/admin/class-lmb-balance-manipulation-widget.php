<?php
/**
 * Balance Manipulation Widget
 *
 * Allows admins to add or subtract points for a user by ID.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Balance_Manipulation_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_balance_manipulation';
    }

    public function get_title() {
        return __('Balance Manipulation', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-coins';
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
        <div class="lmb-balance-manipulation" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_balance_manipulation_nonce')); ?>">
            <form class="lmb-balance-form">
                <label for="lmb-user-id"><?php _e('User ID', 'lmb-core'); ?></label>
                <input type="number" id="lmb-user-id" name="user_id" required>
                <label for="lmb-points"><?php _e('Points', 'lmb-core'); ?></label>
                <input type="number" id="lmb-points" name="points" required>
                <button type="submit" data-action="add"><?php _e('Add Points', 'lmb-core'); ?></button>
                <button type="submit" data-action="subtract"><?php _e('Subtract Points', 'lmb-core'); ?></button>
            </form>
            <p class="lmb-balance-message"></p>
        </div>
        <?php
    }
}