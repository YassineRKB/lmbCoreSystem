<?php
/**
 * Packages Editor Widget
 *
 * Allows admins to add/edit/delete packages dynamically.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Packages_Editor_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_packages_editor';
    }

    public function get_title() {
        return __('Packages Editor', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-box';
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
        <div class="lmb-packages-editor" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_packages_editor_nonce')); ?>">
            <form class="lmb-package-form">
                <input type="hidden" name="package_id" value="0">
                <label for="lmb-package-name"><?php _e('Package Name', 'lmb-core'); ?></label>
                <input type="text" id="lmb-package-name" name="package_name" required>
                <label for="lmb-package-price"><?php _e('Price (MAD)', 'lmb-core'); ?></label>
                <input type="number" id="lmb-package-price" name="package_price" required>
                <label for="lmb-package-points"><?php _e('Points', 'lmb-core'); ?></label>
                <input type="number" id="lmb-package-points" name="package_points" required>
                <button type="submit"><?php _e('Save Package', 'lmb-core'); ?></button>
            </form>
            <ul class="lmb-packages-list"></ul>
        </div>
        <?php
    }
}