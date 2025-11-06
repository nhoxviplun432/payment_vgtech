<?php
namespace paymentvgtech;

defined('ABSPATH') || exit;

require_once PAYMENT_AI_CHAT_VGTECH_DIR . 'includes/class.php';

// === Kiểm tra WooCommerce ===
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>VGTech Payment</strong> yêu cầu cài và kích hoạt WooCommerce để hoạt động.</p></div>';
    });
    return;
}

// === Override plugins_url cho payos.php ===
add_filter('plugins_url', function ($url, $path, $plugin) {
    if (strpos($plugin, 'payos.php') !== false) {
        return PAYMENT_AI_CHAT_VGTECH_URL . ltrim($path, '/');
    }
    return $url;
}, 10, 3);

// === Nạp PayOS plugin nội bộ ===
$payos_file = PAYMENT_AI_CHAT_VGTECH_DIR . 'payos/payos.php';
if (file_exists($payos_file)) {
    include_once $payos_file;
} else {
    error_log('⚠️ PayOS module missing: ' . $payos_file);
}

// === Global variables ===
global $product_type, $product_package;
$product_type    = 'package_ai';
$product_package = 'ai_chat';

// === Autoload Composer hoặc class fallback ===
if (file_exists(PAYMENT_AI_CHAT_VGTECH_DIR . 'vendor/autoload.php')) {
    require_once PAYMENT_AI_CHAT_VGTECH_DIR . 'vendor/autoload.php';
} else {
    require_once PAYMENT_AI_CHAT_VGTECH_DIR . 'includes/class.php';
}

// === Khởi tạo plugin ===
add_action('plugins_loaded', function () {
    if (class_exists('\paymentvgtech\PaymentVgtech')) {
        $plugin = new \paymentvgtech\PaymentVgtech();
        $plugin->register();
        $plugin->run();
    } else {
        error_log('⚠️ Class PaymentVgtech không tồn tại.');
    }
});

// === Hàm tạo bảng database ===
function run_starter_setup()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'vgtech_payment_ai';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        order_id BIGINT UNSIGNED NOT NULL,
        value BIGINT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
