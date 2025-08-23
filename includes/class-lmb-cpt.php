<?php
class LMB_CPT {
    public static function init() {
        add_action('init', [__CLASS__, 'register_cpts']);
    }

    public static function register_cpts() {
        // Legal Ad CPT
        register_post_type('lmb_legal_ad', [
            'labels' => [
                'name' => __('Legal Ads', 'lmb-core'),
                'singular_name' => __('Legal Ad', 'lmb-core'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title', 'editor', 'author'],
            'capability_type' => 'lmb_legal_ad',
            'map_meta_cap' => true,
            'rewrite' => false,
        ]);

        // Newspaper CPT
        register_post_type('lmb_newspaper', [
            'labels' => [
                'name' => __('Newspapers', 'lmb-core'),
                'singular_name' => __('Newspaper', 'lmb-core'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title', 'thumbnail'],
            'capability_type' => 'lmb_newspaper',
            'map_meta_cap' => true,
            'rewrite' => ['slug' => 'newspapers'],
        ]);

        // Invoice CPT
        register_post_type('lmb_invoice', [
            'labels' => [
                'name' => __('Invoices', 'lmb-core'),
                'singular_name' => __('Invoice', 'lmb-core'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title'],
            'capability_type' => 'lmb_invoice',
            'map_meta_cap' => true,
            'rewrite' => false,
        ]);
    }
}