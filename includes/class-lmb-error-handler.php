<?php
class LMB_Error_Handler {
    public static function init() {
        // No actions needed on init
    }

    public static function log_error($message, $data = []) {
        error_log('LMB Error: ' . $message . ' | Data: ' . print_r($data, true));
    }
}