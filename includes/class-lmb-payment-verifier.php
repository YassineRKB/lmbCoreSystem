<?php
/**
 * Payment Verifier
 *
 * Handles payment verification and processing.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Payment_Verifier {
    public static function init() {
        // No actions needed for now
    }

    public static function approve_payment($payment_id) {
        if (!current_user_can('manage_options')) {
            return false;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'lmb_points';
        $payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $payment_id));
        if ($payment && $payment->transaction_type === 'payment_pending') {
            $wpdb->update($table, ['transaction_type' => 'payment_approved'], ['id' => $payment_id]);
            LMB_Notification_Manager::add_notification($payment->user_id, __('Your payment has been approved.', 'lmb-core'), 'payment_approved');
            return true;
        }
        return false;
    }

    public static function deny_payment($payment_id) {
        if (!current_user_can('manage_options')) {
            return false;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'lmb_points';
        $payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $payment_id));
        if ($payment && $payment->transaction_type === 'payment_pending') {
            $wpdb->update($table, ['transaction_type' => 'payment_denied'], ['id' => $payment_id]);
            LMB_Notification_Manager::add_notification($payment->user_id, __('Your payment has been denied.', 'lmb-core'), 'payment_denied');
            return true;
        }
        return false;
    }

    public static function get_pending_payments() {
        global $wpdb;
        $table = $wpdb->prefix . 'lmb_points';
        return $wpdb->get_results("SELECT * FROM $table WHERE transaction_type = 'payment_pending'");
    }
}