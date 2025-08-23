<?php
/**
 * Upload Accuse Widget
 *
 * Allows admins to upload an accuse linked to a legal ad for user download.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Upload_Accuse_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_upload_accuse';
    }

    public function get_title() {
        return __('Upload Accuse', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-upload';
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
        <div class="lmb-upload-accuse" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_upload_accuse_nonce')); ?>">
            <form class="lmb-accuse-form" enctype="multipart/form-data">
                <label for="lmb-ad-id"><?php _e('Legal Ad ID', 'lmb-core'); ?></label>
                <input type="number" id="lmb-ad-id" name="ad_id" required>
                <label for="lmb-accuse-file"><?php _e('Accuse File', 'lmb-core'); ?></label>
                <input type="file" id="lmb-accuse-file" name="accuse_file" accept=".pdf" required>
                <button type="submit"><?php _e('Upload Accuse', 'lmb-core'); ?></button>
            </form>
            <p class="lmb-accuse-message"></p>
        </div>
        <?php
    }
}