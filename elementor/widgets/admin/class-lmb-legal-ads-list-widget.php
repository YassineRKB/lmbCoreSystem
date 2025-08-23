<?php
/**
 * Legal Ads List Widget
 *
 * Lists all legal ads with filtering by user ID, ad type, company name, sorted newest to oldest.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Legal_Ads_List_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_legal_ads_list';
    }

    public function get_title() {
        return __('Legal Ads List', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-list';
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
        <div class="lmb-legal-ads-list" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_legal_ads_list_nonce')); ?>">
            <form class="lmb-ads-filter">
                <input type="number" name="user_id" placeholder="<?php _e('User ID', 'lmb-core'); ?>">
                <input type="text" name="ad_type" placeholder="<?php _e('Ad Type', 'lmb-core'); ?>">
                <input type="text" name="company_name" placeholder="<?php _e('Company Name', 'lmb-core'); ?>">
                <button type="submit"><?php _e('Filter', 'lmb-core'); ?></button>
            </form>
            <table class="lmb-ads-table">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'lmb-core'); ?></th>
                        <th><?php _e('Content', 'lmb-core'); ?></th>
                        <th><?php _e('Status', 'lmb-core'); ?></th>
                        <th><?php _e('Approved By', 'lmb-core'); ?></th>
                        <th><?php _e('Timestamp', 'lmb-core'); ?></th>
                    </tr>
                </thead>
                <tbody class="lmb-ads-table-body"></tbody>
            </table>
        </div>
        <?php
    }
}