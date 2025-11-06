<?php
namespace paymentvgtech;

defined('ABSPATH') || exit;

class PaymentControll{

    public function vgtech_update_ai_views_after_payment($order_id) {
        if (!$order_id) {
            return;
        }

        global $product_type, $product_package;

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Lấy ID user mua hàng
        $user_id = $order->get_user_id();
        if (!$user_id) {
            return;
        }

        // Duyệt qua tất cả sản phẩm trong đơn hàng
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $product_type_meta = get_post_meta($product_id, '_product_type', true);

            // Kiểm tra nếu sản phẩm là loại $product_type
            if ($product_type_meta === $product_type) {
                // Lấy giá trị meta tương ứng với gói (ví dụ: _ai_chat)
                $package_value = (int) get_post_meta($product_id, '_' . $product_package, true);

                // Lấy giá trị hiện tại của user
                $current_views = (int) get_user_meta($user_id, '_vgtech_ai_views', true);

                // Cộng thêm số lượng
                $new_views = $current_views + $package_value;

                // Cập nhật usermeta
                update_user_meta($user_id, '_vgtech_ai_views', $new_views);
            }
        }
    }

    public function inject_checkout_css(){
        global $product_type;
        $cart = WC()->cart ? WC()->cart->get_cart() : [];
        $has_special = false;

        foreach ($cart as $cart_item) {
            $product = $cart_item['data'];
            $type = get_post_meta($product->get_id(), '_product_type', true);
            if (in_array($type, [$product_type])) {
                $has_special = true;
                break;
            }
        }
        if ($has_special) {
            wp_enqueue_style('checkout-css', PAYMENT_AI_CHAT_VGTECH_URL . 'assets/checkout.css');
        }
    }

}