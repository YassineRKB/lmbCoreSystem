<?php
class LMB_Elementor_Widgets {
    public function __construct() {
        add_action('elementor/elements/categories_registered', [$this, 'register_categories']);
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_categories($elements_manager) {
        $elements_manager->add_category('lmb-general', [
            'title' => __('LMB General', 'lmb-core'),
            'icon' => 'fa fa-plug',
        ]);
        $elements_manager->add_category('lmb-user', [
            'title' => __('LMB User', 'lmb-core'),
            'icon' => 'fa fa-user',
        ]);
        $elements_manager->add_category('lmb-admin', [
            'title' => __('LMB Admin', 'lmb-core'),
            'icon' => 'fa fa-user-shield',
        ]);
    }

    public function register_widgets($widgets_manager) {
        // Widgets will be registered here in later milestones
    }
}