<?php

/**
 * @wordpress-plugin
 * Plugin Name: Thanh toán Vgtech
 * Description: Quản lý tài khoản tra cứu, quản lý order và thanh toán qr với VGTech
 * Version: 1.0.0
 * Author: hoaigiangtf@gmail.com
 */

if (!defined('WPINC')) {
    die;
}

define('PAYMENT_VGTECH_DIR', plugin_dir_path(__FILE__));
define('PAYMENT_VGTECH_URL', plugin_dir_url(__FILE__));

require_once PAYMENT_VGTECH_DIR . 'class.php';