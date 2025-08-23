<?php
/**
 * Elementor Widgets Registration
 *
 * Registers all LMB widgets under Elementor categories.
 *
 * @package LMB Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMB_Elementor_Widgets {
    public function __construct() {
        add_action('elementor/elements/categories_registered', [$this, 'register_categories']);
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_categories($elements_manager) {
        $elements_manager->add_category('lmb-general', [
            'title' => __('LMB General', 'lmb-core'),
            'icon'  => 'fa fa-plug',
        ]);
        $elements_manager->add_category('lmb-user', [
            'title' => __('LMB User', 'lmb-core'),
            'icon'  => 'fa fa-user',
        ]);
        $elements_manager->add_category('lmb-admin', [
            'title' => __('LMB Admin', 'lmb-core'),
            'icon'  => 'fa fa-user-shield',
        ]);
    }

    public function register_widgets($widgets_manager) {
        // Admin Widgets
        require_once LMB_CORE_PATH . 'elementor/widgets/admin/class-lmb-admin-stats-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/admin/class-lmb-admin-actions-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/admin/class-lmb-balance-manipulation-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/admin/class-lmb-legal-ads-list-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/admin/class-lmb-notifications-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/admin/class-lmb-packages-editor-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/admin/class-lmb-upload-accuse-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/admin/class-lmb-upload-newspaper-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/admin/class-lmb-user-list-widget.php';

        $widgets_manager->register(new LMB_Admin_Stats_Widget());
        $widgets_manager->register(new LMB_Admin_Actions_Widget());
        $widgets_manager->register(new LMB_Balance_Manipulation_Widget());
        $widgets_manager->register(new LMB_Legal_Ads_List_Widget());
        $widgets_manager->register(new LMB_Notifications_Widget());
        $widgets_manager->register(new LMB_Packages_Editor_Widget());
        $widgets_manager->register(new LMB_Upload_Accuse_Widget());
        $widgets_manager->register(new LMB_Upload_Newspaper_Widget());
        $widgets_manager->register(new LMB_User_List_Widget());

        // User Widgets
        require_once LMB_CORE_PATH . 'elementor/widgets/user/class-lmb-user-stats-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/user/class-lmb-invoices-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/user/class-lmb-upload-bank-proof-widget.php';
        require_once LMB_CORE_PATH . 'elementor/widgets/user/class-lmb-subscribe-package-widget.php';

        $widgets_manager->register(new LMB_User_Stats_Widget());
        $widgets_manager->register(new LMB_Invoices_Widget());
        $widgets_manager->register(new LMB_Upload_Bank_Proof_Widget());
        $widgets_manager->register(new LMB_Subscribe_Package_Widget());
    }
}