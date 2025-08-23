<?php
/**
 * Elementor Pro Forms Action: Save as Legal Ad
 *
 * Saves form submissions as legal ads.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Save_Ad_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base {
    public function get_name() {
        return 'save_legal_ad';
    }

    public function get_label() {
        return __('Save as Legal Ad', 'lmb-core');
    }

    public function run($record, $ajax_handler) {
        try {
            $raw_fields = $record->get('fields');
            if (empty($raw_fields) || !is_array($raw_fields)) {
                LMB_Error_Handler::log_error('Save Ad Action: empty or invalid fields payload');
                $ajax_handler->add_error_message(__('No form data received.', 'lmb-core'));
                return;
            }

            $form_data = [];
            foreach ($raw_fields as $key => $field) {
                $form_data[$key] = is_array($field['value']) ? implode(', ', $field['value']) : $field['value'];
            }

            LMB_Error_Handler::log_error('Save Ad Action: parsed form data', ['data' => $form_data]);

            $post_id = LMB_Form_Handler::create_legal_ad($form_data);
            LMB_Notification_Manager::add_notification(get_current_user_id(), __('Your legal ad has been submitted as a draft.', 'lmb-core'), 'ad_submitted');
            LMB_Notification_Manager::add_notification(0, sprintf(__('New legal ad #%d submitted by %s.', 'lmb-core'), $post_id, wp_get_current_user()->display_name), 'ad_submitted');

            $ajax_handler->add_response_data('post_id', (int) $post_id);
            $ajax_handler->add_success_message(__('Legal Ad saved as draft.', 'lmb-core'));

        } catch (\Throwable $e) {
            LMB_Error_Handler::log_error('Save Ad Action: exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $ajax_handler->add_error_message(__('An error occurred while creating the Legal Ad.', 'lmb-core'));
        }
    }

    public function register_settings_section($form) {
        // No custom settings needed
    }

    public function on_export($element) {
        return $element;
    }
}