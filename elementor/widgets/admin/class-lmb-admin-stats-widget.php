<?php
class LMB_Admin_Stats_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_admin_stats';
    }

    public function get_title() {
        return __('Admin Stats', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-chart-bar';
    }

    public function get_categories() {
        return ['lmb-admin'];
    }

    protected function _register_controls() {
        // No controls needed for display-only widget
    }

    protected function render() {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="lmb-admin-stats" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_admin_stats_nonce')); ?>">
            <div class="lmb-stat-box">
                <h3><?php _e('New Clients This Month', 'lmb-core'); ?></h3>
                <p class="lmb-stat" data-type="new_clients">0</p>
            </div>
            <div class="lmb-stat-box">
                <h3><?php _e('Legal Ads Published', 'lmb-core'); ?></h3>
                <p class="lmb-stat" data-type="published_ads">0</p>
            </div>
            <div class="lmb-stat-box">
                <h3><?php _e('Drafted Legal Ads', 'lmb-core'); ?></h3>
                <p class="lmb-stat" data-type="draft_ads">0</p>
            </div>
            <div class="lmb-stat-box">
                <h3><?php _e('Profits This Month', 'lmb-core'); ?></h3>
                <p class="lmb-stat" data-type="profits">0 MAD</p>
            </div>
        </div>
        <?php
    }
}