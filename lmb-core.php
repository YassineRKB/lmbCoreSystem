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

// Define plugin constants
define('LMB_CORE_VERSION', '1.0.0');
define('LMB_CORE_FILE', __FILE__);
define('LMB_CORE_PATH', plugin_dir_path(__FILE__));
define('LMB_CORE_URL', plugin_dir_url(__FILE__));

// Load translations
add_action('init', function() {
    load_plugin_textdomain('lmb-core', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Autoloader for classes
spl_autoload_register(function($class) {
    if (strpos($class, 'LMB_') !== 0) {
        return;
    }
    $file = 'class-' . str_replace('_', '-', strtolower($class)) . '.php';
    $directories = [
        LMB_CORE_PATH . 'includes/',
        LMB_CORE_PATH . 'elementor/widgets/',
        LMB_CORE_PATH . 'elementor/widgets/admin/',
        LMB_CORE_PATH . 'elementor/widgets/user/',
        LMB_CORE_PATH . 'elementor/widgets/public/'
    ];

    foreach ($directories as $dir) {
        $path = $dir . $file;
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Activation Hook
register_activation_hook(__FILE__, function() {
    LMB_CPT::init();
    LMB_User::create_custom_roles();
    LMB_Database_Manager::create_custom_tables();
    flush_rewrite_rules();

    // Default settings
    add_option('lmb_bank_name', 'Your Bank Name');
    add_option('lmb_bank_iban', 'YOUR-IBAN-RIB-HERE');
    add_option('lmb_default_cost_per_ad', 1);
    add_option('lmb_enable_email_notifications', 1);
    add_option('lmb_invoice_template_html', '<h1>Invoice {{invoice_number}}</h1><p>Date: {{invoice_date}}</p><hr><h3>Client Details</h3><p>Name: {{user_name}}<br>Email: {{user_email}}</p><hr><h3>Item Details</h3><p><strong>Package:</strong> {{package_name}}<br><strong>Price:</strong> {{package_price}} MAD</p><p><strong>Payment Reference:</strong> {{payment_reference}}</p><hr><h3>Payment Instructions</h3><p>Please make a bank transfer to:<br><strong>Bank:</strong> {{our_bank_name}}<br><strong>IBAN/RIB:</strong> {{our_iban}}</p>');
});

// Deactivation Hook
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('lmb_daily_maintenance');
    flush_rewrite_rules();
});

// Initialize plugin components
add_action('plugins_loaded', function() {
    // Initialize error handler
    LMB_Error_Handler::init();
    
    // Log plugin initialization
    LMB_Error_Handler::log_error('LMB Core plugin initializing', [
        'version' => LMB_CORE_VERSION,
        'elementor_pro_active' => class_exists('\ElementorPro\Plugin'),
        'elementor_active' => class_exists('\Elementor\Plugin')
    ]);
    
    // Initialize components
    LMB_Access_Control::init();
    LMB_CPT::init();
    LMB_Form_Handler::init();
    LMB_Ad_Manager::init();
    LMB_Payment_Verifier::init();
    LMB_Admin::init();
    LMB_User_Dashboard::init();
    LMB_Database_Manager::init();
    LMB_Invoice_Handler::init();
    LMB_Ajax_Handlers::init();
    LMB_Notification_Manager::init();
    new LMB_User();

    // Load Elementor widgets
    require_once LMB_CORE_PATH . 'elementor/class-lmb-elementor-widgets.php';
});

// Enqueue Frontend Scripts & Styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('lmb-core', LMB_CORE_URL . 'assets/css/lmb-core.css', [], LMB_CORE_VERSION);
    wp_enqueue_script('lmb-core', LMB_CORE_URL . 'assets/js/lmb-core.js', ['jquery'], LMB_CORE_VERSION, true);

    wp_localize_script('lmb-core', 'lmbAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('lmb_frontend_ajax_nonce'),
    ]);

    wp_localize_script('lmb-core', 'lmbAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('lmb_admin_ajax_nonce'),
    ]);

    // Load admin styles for frontend admin dashboard
    wp_enqueue_style('lmb-admin-styles', LMB_CORE_URL . 'assets/css/admin.css', [], LMB_CORE_VERSION);

    // Conditionally load Chart.js for user charts
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'lmb_user_charts')) {
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.7.0', true);
    }
});

// Enqueue Admin Scripts & Styles
add_action('admin_enqueue_scripts', function($hook) {
    wp_enqueue_style('lmb-admin-styles', LMB_CORE_URL . 'assets/css/admin.css', [], LMB_CORE_VERSION);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], '5.15.4');
    wp_enqueue_script('lmb-admin-scripts', LMB_CORE_URL . 'assets/js/admin.js', ['jquery'], LMB_CORE_VERSION, true);
    wp_localize_script('lmb-admin-scripts', 'lmbAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('lmb_admin_ajax_nonce'),
    ]);
});