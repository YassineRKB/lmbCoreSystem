<?php
/**
 * Subscribe Package Widget
 *
 * Allows users to subscribe/update packages or buy points, generating an invoice.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Subscribe_Package_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_subscribe_package';
    }

    public function get_title() {
        return __('Subscribe Package', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-shopping-cart';
    }

    public function get_categories() {
        return ['lmb-user'];
    }

    protected function _register_controls() {
        // No controls needed for frontend form
    }

    protected function render() {
        if (!is_user_logged_in()) {
            echo '<p>' . esc_html__('Please log in to subscribe to a package.', 'lmb-core') . '</p>';
            return;
        }

        ?>
        <div class="lmb-subscribe-package" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_subscribe_package_nonce')); ?>">
            <form class="lmb-package-form">
                <label for="lmb-package-id"><?php _e('Select Package', 'lmb-core'); ?></label>
                <select id="lmb-package-id" name="package_id" required>
                    <option value=""><?php _e('Select a package', 'lmb-core'); ?></option>
                </select>
                <button type="submit"><?php _e('Subscribe', 'lmb-core'); ?></button>
            </form>
            <p class="lmb-package-message"></p>
        </div>
        <?php
    }
}