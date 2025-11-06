<?php
/**
 * Plugin Name: Thanh toán QR VGTech 
 * Description: Quản lý tài khoản tra cứu, quản lý order và thanh toán QR với VGTech.
 * Version: 1.0.0
 * Author: hoaigiangtf@gmail.com
 */

defined('ABSPATH') || exit;

// === Định nghĩa hằng số ===
define('PAYMENT_AI_CHAT_VGTECH_DIR', plugin_dir_path(__FILE__));
define('PAYMENT_AI_CHAT_VGTECH_URL', plugin_dir_url(__FILE__));


// === Nạp file khởi tạo ===
require_once PAYMENT_AI_CHAT_VGTECH_DIR . 'includes/stater.php';

// === Khi kích hoạt plugin: tạo bảng database ===
register_activation_hook(__FILE__, 'paymentvgtech\\run_starter_setup');