<?php
/**
 * Plugin Name: LMB CORE SYSTEM
 * Description: Elementor-first legal ads platform core (auth, CPTs, points, invoices, payments, PDFs, directories, dashboards).
 * Version: 2.0.0
 * Author: Yassine Rakibi
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: lmb-core
 */
if (!defined('ABSPATH')) {
    exit;
}

define('LMB_CORE_PATH', plugin_dir_path(__FILE__));
define('LMB_CORE_URL', plugin_dir_url(__FILE__));

class LMB_Core {
    public function __construct() {
        $this->load_dependencies();
        $this->init_classes();
        $this->enqueue_assets();
    }

    private function load_dependencies() {
        require_once LMB_CORE_PATH . 'includes/class-lmb-cpt.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-acf.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-access-control.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-database-manager.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-error-handler.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-form-handler.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-ad-manager.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-payment-verifier.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-admin.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-user.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-points.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-user-dashboard.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-invoice-handler.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-ajax-handlers.php';
        require_once LMB_CORE_PATH . 'includes/class-lmb-notification-manager.php';
        require_once LMB_CORE_PATH . 'elementor/class-lmb-elementor-widgets.php';
    }

    private function init_classes() {
        LMB_CPT::init();
        LMB_ACF::init();
        LMB_Access_Control::init();
        LMB_Database_Manager::init();
        LMB_Error_Handler::init();
        LMB_Form_Handler::init();
        LMB_Ad_Manager::init();
        LMB_Payment_Verifier::init();
        LMB_Admin::init();
        LMB_User::init();
        LMB_Points::init();
        LMB_User_Dashboard::init();
        LMB_Invoice_Handler::init();
        LMB_Ajax_Handlers::init();
        LMB_Notification_Manager::init();
        new LMB_Elementor_Widgets();
    }

    private function enqueue_assets() {
        add_action('wp_enqueue_scripts', function() {
            wp_enqueue_style('lmb-core', LMB_CORE_URL . 'assets/css/lmb-core.css', [], '1.0.0');
            wp_enqueue_script('lmb-core', LMB_CORE_URL . 'assets/js/lmb-core.js', ['jquery'], '1.0.0', true);
            wp_localize_script('lmb-core', 'lmbAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'submit_legal_ad_nonce' => wp_create_nonce('lmb_submit_legal_ad_nonce'),
            ]);

            if (current_user_can('manage_options')) {
                wp_enqueue_style('lmb-admin', LMB_CORE_URL . 'assets/css/admin.css', [], '1.0.0');
                wp_enqueue_script('lmb-admin', LMB_CORE_URL . 'assets/js/admin.js', ['jquery'], '1.0.0', true);
                wp_localize_script('lmb-admin', 'lmbAdmin', [
                    'ajaxurl' => admin_url('admin-ajax.php'),
                ]);
            }
        });
    }
}

new LMB_Core();