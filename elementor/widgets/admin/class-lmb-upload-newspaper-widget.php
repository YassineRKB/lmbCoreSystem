<?php
/**
 * Upload Newspaper Widget
 *
 * Allows admins to upload weekly newspaper PDFs with filtering and download options.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Upload_Newspaper_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_upload_newspaper';
    }

    public function get_title() {
        return __('Upload Newspaper', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-newspaper';
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
        <div class="lmb-upload-newspaper" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_upload_newspaper_nonce')); ?>">
            <form class="lmb-newspaper-form" enctype="multipart/form-data">
                <label for="lmb-newspaper-title"><?php _e('Newspaper Title', 'lmb-core'); ?></label>
                <input type="text" id="lmb-newspaper-title" name="newspaper_title" required>
                <label for="lmb-newspaper-file"><?php _e('Newspaper PDF', 'lmb-core'); ?></label>
                <input type="file" id="lmb-newspaper-file" name="newspaper_file" accept=".pdf" required>
                <button type="submit"><?php _e('Upload Newspaper', 'lmb-core'); ?></button>
            </form>
            <form class="lmb-newspaper-filter">
                <input type="text" name="search" placeholder="<?php _e('Search by Title', 'lmb-core'); ?>">
                <input type="date" name="start_date">
                <input type="date" name="end_date">
                <button type="submit"><?php _e('Filter', 'lmb-core'); ?></button>
            </form>
            <ul class="lmb-newspapers-list"></ul>
        </div>
        <?php
    }
}