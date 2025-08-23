<?php
/**
 * User Stats Widget
 *
 * Displays user statistics: balance points, number of drafts, number of published ads.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_User_Stats_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_user_stats';
    }

    public function get_title() {
        return __('User Stats', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-chart-pie';
    }

    public function get_categories() {
        return ['lmb-user'];
    }

    protected function _register_controls() {
        // No controls needed for display-only widget
    }

    protected function render() {
        if (!is_user_logged_in()) {
            echo '<p>' . esc_html__('Please log in to view your stats.', 'lmb-core') . '</p>';
            return;
        }

        ?>
        <div class="lmb-user-stats" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_user_stats_nonce')); ?>">
            <div class="lmb-stat-box">
                <h3><?php _e('Balance Points', 'lmb-core'); ?></h3>
                <p class="lmb-stat" data-type="balance">0</p>
            </div>
            <div class="lmb-stat-box">
                <h3><?php _e('Draft Ads', 'lmb-core'); ?></h3>
                <p class="lmb-stat" data-type="drafts">0</p>
            </div>
            <div class="lmb-stat-box">
                <h3><?php _e('Published Ads', 'lmb-core'); ?></h3>
                <p class="lmb-stat" data-type="published">0</p>
            </div>
        </div>
        <?php
    }
}