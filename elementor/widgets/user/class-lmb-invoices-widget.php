<?php
/**
 * Invoices Widget
 *
 * Displays tabs for Accuse (uploaded by admin) and Invoices (generated for subscriptions/points), with paid/unpaid filters.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Invoices_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_invoices';
    }

    public function get_title() {
        return __('Invoices', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-file-invoice';
    }

    public function get_categories() {
        return ['lmb-user'];
    }

    protected function _register_controls() {
        // No controls needed for frontend form
    }

    protected function render() {
        if (!is_user_logged_in()) {
            echo '<p>' . esc_html__('Please log in to view your invoices.', 'lmb-core') . '</p>';
            return;
        }

        ?>
        <div class="lmb-invoices" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_invoices_nonce')); ?>">
            <div class="lmb-tabs">
                <button class="lmb-tab active" data-tab="accuse"><?php _e('Accuse', 'lmb-core'); ?></button>
                <button class="lmb-tab" data-tab="invoices"><?php _e('Invoices', 'lmb-core'); ?></button>
            </div>
            <div class="lmb-tab-content" id="lmb-tab-accuse">
                <form class="lmb-invoices-filter">
                    <select name="status">
                        <option value="all"><?php _e('All', 'lmb-core'); ?></option>
                        <option value="published"><?php _e('Published', 'lmb-core'); ?></option>
                    </select>
                    <button type="submit"><?php _e('Filter', 'lmb-core'); ?></button>
                </form>
                <ul class="lmb-accuse-list"></ul>
            </div>
            <div class="lmb-tab-content" id="lmb-tab-invoices" style="display:none;">
                <form class="lmb-invoices-filter">
                    <select name="status">
                        <option value="all"><?php _e('All', 'lmb-core'); ?></option>
                        <option value="paid"><?php _e('Paid', 'lmb-core'); ?></option>
                        <option value="unpaid"><?php _e('Unpaid', 'lmb-core'); ?></option>
                    </select>
                    <button type="submit"><?php _e('Filter', 'lmb-core'); ?></button>
                </form>
                <ul class="lmb-invoices-list"></ul>
            </div>
        </div>
        <?php
    }
}