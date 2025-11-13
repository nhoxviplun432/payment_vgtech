<?php

use paymentvgtech\CheckoutControll;
use paymentvgtech\PaymentControll;
use paymentvgtech\AccountControll;
use paymentvgtech\WooControll;

return [
    'controllers' => [
        CheckoutControll::class,
        PaymentControll::class,
        AccountControll::class,
        WooControll::class
    ],
    'hooks' => [

        // WooControll
        ['action', 'init', [WooControll::class, 'package_products_shortcode']],
        ['filter', 'woocommerce_login_redirect', [WooControll::class, 'woocommerce_login_redirect_ai_vgtech'], 10, 2],
        ['shortcode', 'package_products', [WooControll::class, 'package_products_shortcode']],
        ['action', 'woocommerce_product_options_general_product_data', [WooControll::class, 'add_general_fields']],
        ['action', 'woocommerce_process_product_meta', [WooControll::class, 'save_product_meta']],
        ['action', 'admin_footer', [WooControll::class, 'admin_footer_script']],
        ['action', 'pre_get_posts', [WooControll::class, 'exclude_package_products']],

        ['action', 'woocommerce_before_calculate_totals', [WooControll::class, 'modify_cart_prices']],
        ['filter', 'woocommerce_add_to_cart_redirect', [WooControll::class, 'redirect_add_to_cart']],
        ['action', 'template_redirect', [WooControll::class, 'block_product_page']],
        ['action', 'woocommerce_before_cart', [WooControll::class, 'block_cart_display']],
        ['action', 'wp_ajax_woocommerce_ajax_add_to_cart', [WooControll::class, 'ajax_add_to_cart']],
        ['action', 'wp_ajax_nopriv_woocommerce_ajax_add_to_cart', [WooControll::class, 'ajax_add_to_cart']],


        // CheckoutControll
        ['action', 'woocommerce_checkout_create_order', [CheckoutControll::class, 'save_order_meta'], 10, 2],
        ['filter', 'woocommerce_checkout_fields', [CheckoutControll::class, 'custom_checkout_fields']],
        // ['action', 'woocommerce_checkout_update_order_meta', [CheckoutControll::class, 'update_order_fullname']],
        ['action', 'woocommerce_review_order_before_submit', [CheckoutControll::class, 'change_order_button']],
        ['action', 'woocommerce_after_checkout_validation', [CheckoutControll::class, 'custom_checkout_validation'], 9999, 2],
        ['action', 'woocommerce_checkout_before_order_review', [CheckoutControll::class, 'custom_checkout_summary']],
        ['action', 'woocommerce_before_thankyou', [CheckoutControll::class, 'thankyou_page_customization']],
        ['filter', 'gettext', [CheckoutControll::class, 'custom_gettext'], 20, 3],
        ['action', 'woocommerce_before_checkout_form', [CheckoutControll::class, 'before_checkout_form_vgetch']],
        // // Order action
        // ['action', 'woocommerce_order_status_changed', [AccountOrderControll::class, 'vgtech_order_status_completed']],

    
        // AccountControll
        ['shortcode', 'igeni_one_account_menu', [AccountControll::class, 'igeni_one_account_menu']],
        ['filter', 'woocommerce_account_menu_items', [AccountControll::class, 'remove_blocked_endpoints'], 10, 1],
        ['action', 'template_redirect', [AccountControll::class, 'block_restricted_account_pages']],
        ['filter', 'woocommerce_save_account_details_required_fields', [AccountControll::class, 'custom_required_fields']],
        ['filter', 'gettext', [AccountControll::class, 'translate_account_texts'], 20, 3],
        ['action', 'woocommerce_created_customer', [AccountControll::class, 'save_extra_customer_fields']],
        ['action', 'woocommerce_register_post', [AccountControll::class, 'validate_registration_fields'], 10, 3],
        ['action', 'woocommerce_save_account_details', [AccountControll::class, 'vgtech_update_account_fields'], 10, 1],

        ['filter', 'woocommerce_account_menu_items', [AccountControll::class, 'add_tracuu_tab'], 20, 1],
        ['action', 'init', [AccountControll::class, 'register_tracuu_endpoints']],
        ['action', 'woocommerce_account_tracuu_endpoint', [AccountControll::class, 'render_tracuu_history']],
        ['action', 'woocommerce_account_tracuu-view_endpoint', [AccountControll::class, 'render_tracuu_detail']],
        ['action', 'woocommerce_account_tuvan_endpoint', [AccountControll::class, 'account_tuvan_endpoint']],

            // Consult View account in admin
            ['filter', 'manage_users_columns', [AccountControll::class, 'add_consult_view_column']],
            ['filter', 'manage_users_custom_column', [AccountControll::class, 'show_consult_view_column'], 10, 3],
            ['filter', 'manage_users_sortable_columns', [AccountControll::class, 'sortable_consult_view_column']],
            ['action', 'pre_get_users', [AccountControll::class, 'sort_consult_view_query']],
             // Hiển thị field trên trang edit user
            ['action', 'show_user_profile', [AccountControll::class, 'add_consult_view_field']],
            ['action', 'edit_user_profile', [AccountControll::class, 'add_consult_view_field']],

            // Lưu field khi update user
            ['action', 'personal_options_update', [AccountControll::class, 'save_consult_view_field']],
            ['action', 'edit_user_profile_update', [AccountControll::class, 'save_consult_view_field']],

        // PaymentControll
        ['action', 'wp_head', [PaymentControll::class, 'inject_checkout_css']],
        ['action', 'wp_ajax_vgtech_get_payment_status', [PaymentControll::class, 'vgtech_get_payment_status']],
        ['action', 'wp_ajax_nopriv_vgtech_get_payment_status', [PaymentControll::class, 'vgtech_get_payment_status']],
        // AJAX endpoints
        // ['action', 'wp_ajax_handle_get_full_pdf_api', [PaymentControll::class, 'handle_get_full_pdf_api']],
        // ['action', 'wp_ajax_nopriv_handle_get_full_pdf_api', [PaymentControll::class, 'handle_get_full_pdf_api']],
        // ['action', 'wp_ajax_handle_checkPaymentPaid', [PaymentControll::class, 'handle_checkPaymentPaid']],
        // ['action', 'wp_ajax_nopriv_handle_checkPaymentPaid', [PaymentControll::class, 'handle_checkPaymentPaid']],
        // ['filter', 'manage_edit-shop_order_columns', [PaymentControll::class, 'add_order_email_column']],
        // ['action', 'manage_shop_order_posts_custom_column', [PaymentControll::class, 'render_order_email_column'], 10, 2],
        // ['filter', 'manage_edit-shop_order_sortable_columns', [PaymentControll::class, 'make_order_email_sortable']],

    ]
];