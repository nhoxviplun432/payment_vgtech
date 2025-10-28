<?php

use payment_vgtech\CdnControll;
use payment_vgtech\PaymentControll;
use payment_vgtech\AdminControll;
use payment_vgtech\AccountControll;
use payment_vgtech\WooControll;

return [
    'controllers' => [
        CdnControll::class,
        PaymentControll::class,
        AdminControll::class,
        AccountControll::class
    ],
    'hooks' => [

        // Cdn
        ['action', 'wp_enqueue_scripts', [CdnControll::class, 'vgtech_lookup_wp_enqueue_scripts']],
        // ['action', 'elementor/frontend/before_render', [CdnControll::class, 'vgtech_lookup_wp_enqueue_scripts_elementor']],
        ['filter', 'gettext', [CdnControll::class, 'vgtech_gettext'], 20, 3],

        // ['action', 'admin_enqueue_scripts', [CdnControll::class, 'admin_ai_chat_vgtech_enqueue_scripts']],

        // Admin
        ['action', 'admin_footer', [AdminControll::class, 'js_admin_footer']],
        // ['action', 'wp_ajax_handle_add_new_tracuu_admin', [AdminControll::class, 'handle_add_new_tracuu_admin']],
        // ['action', 'wp_ajax_nopriv_handle_add_new_tracuu_admin', [AdminControll::class, 'handle_add_new_tracuu_admin']],
        // ['action', 'wp_ajax_handle_remove_tracuu_admin', [AdminControll::class, 'handle_remove_tracuu_admin']],
        // ['action', 'wp_ajax_nopriv_handle_remove_tracuu_admin', [AdminControll::class, 'handle_remove_tracuu_admin']],

        // Payment
        ['filter', 'woocommerce_add_to_cart_redirect', [PaymentControll::class, 'add_to_cart_redirect']],
        ['action', 'woocommerce_product_options_general_product_data', [PaymentControll::class, 'product_options_general_product_data']],
        ['action', 'woocommerce_process_product_meta', [PaymentControll::class, 'process_product_meta']],
        ['action', 'template_redirect', [PaymentControll::class, 'redirect_add_to_cart']],
        ['action', 'woocommerce_checkout_create_order', [PaymentControll::class, 'checkout_create_order'], 10, 2],
        ['action', 'woocommerce_before_cart', [PaymentControll::class, 'before_cart']],
        ['action', 'wp_ajax_woocommerce_ajax_add_to_cart', [PaymentControll::class, 'woocommerce_ajax_add_to_cart']],
        ['action', 'wp_ajax_nopriv_woocommerce_ajax_add_to_cart', [PaymentControll::class, 'woocommerce_ajax_add_to_cart']],
        ['filter', 'woocommerce_checkout_fields', [PaymentControll::class, 'checkout_fields']],
        ['action', 'woocommerce_after_checkout_form', [PaymentControll::class, 'remove_checkout_extras']],
        ['action', 'woocommerce_checkout_update_order_meta', [PaymentControll::class, 'checkout_update_order_meta']],
        ['action', 'woocommerce_review_order_before_submit', [PaymentControll::class, 'review_order_before_submit']],
        // ['action', 'woocommerce_after_checkout_validation', [PaymentControll::class, 'after_checkout_validation'], 9999, 2],
        ['action', 'woocommerce_checkout_before_order_review', [PaymentControll::class, 'checkout_before_order_review']],
        ['action', 'woocommerce_before_thankyou', [PaymentControll::class, 'before_thankyou']],
        ['action', 'woocommerce_before_calculate_totals', [PaymentControll::class, 'before_calculate_totals']],

        // Account
        ['filter', 'woocommerce_account_menu_items', [AccountControll::class, 'account_menu_items'], 10, 1],
        ['action', 'template_redirect', [AccountControll::class, 'redirect_account']],
        ['filter', 'woocommerce_save_account_details_required_fields', [AccountControll::class, 'save_account_details_required_fields']],
        ['action', 'woocommerce_register_post', [AccountControll::class, 'register_post']],
        ['action', 'woocommerce_save_account_details', [AccountControll::class, 'save_account_details']],
        ['action', 'woocommerce_created_customer', [AccountControll::class, 'created_customer']],
        ['action', 'init', [AccountControll::class, 'vgtech_init_endpoint']],
        ['action', 'woocommerce_account_tracuu_endpoint', [AccountControll::class, 'account_tracuu_endpoint']],
        ['action', 'woocommerce_account_tracuu-view_endpoint', [AccountControll::class, 'account_tracuu_vgtech']],
        ['action', 'manage_edit-shop_order_columns', [AccountControll::class, 'vgtech_order_columns']],

        // Woo
        ['action', 'wp_ajax_listing_product_tracuu', [WooControll::class, 'listing_product_tracuu']],
        ['action', 'wp_ajax_nopriv_listing_product_tracuu', [WooControll::class, 'listing_product_tracuu']],
        ['action', 'wp_head', [WooControll::class, 'wp_head_custom']],

        ['action', 'wp_ajax_handle_get_full_pdf_api', [WooControll::class, 'handle_get_full_pdf_api']],
        ['action', 'wp_ajax_nopriv_handle_get_full_pdf_api', [WooControll::class, 'handle_get_full_pdf_api']],

        // ['action', 'wp_ajax_handle_payment_nls_phone_number', [WooControll::class, 'handle_payment_nls_phone_number']],
        // ['action', 'wp_ajax_nopriv_handle_payment_nls_phone_number', [WooControll::class, 'handle_payment_nls_phone_number']]

        ['action', 'wp_ajax_handle_checkPaymentPaid', [WooControll::class, 'handle_checkPaymentPaid']],
        ['action', 'wp_ajax_nopriv_handle_checkPaymentPaid', [WooControll::class, 'handle_checkPaymentPaid']]
    
    ]
];          