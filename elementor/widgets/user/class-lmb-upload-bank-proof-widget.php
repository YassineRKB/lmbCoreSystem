<?php
/**
 * Upload Bank Proof Widget
 *
 * Allows users to upload payment bank proof for an unpaid invoice.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Upload_Bank_Proof_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'lmb_upload_bank_proof';
    }

    public function get_title() {
        return __('Upload Bank Proof', 'lmb-core');
    }

    public function get_icon() {
        return 'fa fa-upload';
    }

    public function get_categories() {
        return ['lmb-user'];
    }

    protected function _register_controls() {
        // No controls needed for frontend form
    }

    protected function render() {
        if (!is_user_logged_in()) {
            echo '<p>' . esc_html__('Please log in to upload bank proof.', 'lmb-core') . '</p>';
            return;
        }

        ?>
        <div class="lmb-upload-bank-proof" data-nonce="<?php echo esc_attr(wp_create_nonce('lmb_upload_bank_proof_nonce')); ?>">
            <form class="lmb-bank-proof-form" enctype="multipart/form-data">
                <label for="lmb-invoice-id"><?php _e('Select Invoice', 'lmb-core'); ?></label>
                <select id="lmb-invoice-id" name="invoice_id" required>
                    <option value=""><?php _e('Select an invoice', 'lmb-core'); ?></option>
                </select>
                <label for="lmb-bank-proof-file"><?php _e('Bank Proof File', 'lmb-core'); ?></label>
                <input type="file" id="lmb-bank-proof-file" name="bank_proof_file" accept=".pdf,.jpg,.png" required>
                <button type="submit"><?php _e('Upload Proof', 'lmb-core'); ?></button>
            </form>
            <p class="lmb-bank-proof-message"></p>
        </div>
        <?php
    }
}