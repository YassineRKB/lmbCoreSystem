<?php
/**
 * Newspaper Widget
 *
 * Publicly accessible widget displaying weekly newspapers with download, filtering, and sorting.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Newspaper_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_newspaper';
    }

    public function get_title() {
        return __('Newspaper', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-newspaper';
    }

    public function get_categories() {
        return ['lmb-general'];
    }

    protected function _register_controls() {
        // No controls needed for frontend form
    }

    protected function render() {
        ?>
        <div class="lmb-newspaper" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_newspaper_nonce')); ?>">
            <form class="lmb-newspaper-filter">
                <input type="text" name="search" placeholder="<?php _e('Search by Title', 'lmb-core'); ?>">
                <select name="sort">
                    <option value="newest"><?php _e('Newest First', 'lmb-core'); ?></option>
                    <option value="oldest"><?php _e('Oldest First', 'lmb-core'); ?></option>
                </select>
                <input type="date" name="start_date" placeholder="<?php _e('Start Date', 'lmb-core'); ?>">
                <input type="date" name="end_date" placeholder="<?php _e('End Date', 'lmb-core'); ?>">
                <button type="submit"><?php _e('Filter', 'lmb-core'); ?></button>
            </form>
            <ul class="lmb-newspapers-list"></ul>
        </div>
        <?php
    }
}