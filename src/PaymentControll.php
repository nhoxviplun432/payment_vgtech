<?php
namespace paymentvgtech;

defined('ABSPATH') || exit;

class PaymentControll{

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

    public function vgtech_get_payment_status()
    {
        check_ajax_referer('vgtech_payment_nonce', 'nonce'); // ✅ kiểm tra nonce

        global $wpdb, $product_type, $product_package;

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $status   = sanitize_text_field($_POST['status'] ?? '');

        $table_name = $wpdb->prefix . 'vgtech_payment_ai';

        if (!$order_id || in_array($status, ['PENDING', 'ERROR'], true)) {
            wp_send_json(['status' => 'order_not_paid']);
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json(['status' => 'order_not_found']);
        }

        if ($order->get_meta('_vgtech_ai_views_incremented') === 'yes') {
            wp_send_json([
                'status' => 'already_incremented',
                'message' => 'success',
                'button' => home_url()
            ]);
        }

        $total_added = 0;
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (!$product_id) continue;

            $current_type = get_post_meta($product_id, '_product_type', true);
            if (empty($current_type)) {
                $product_obj = wc_get_product($product_id);
                $current_type = $product_obj ? $product_obj->get_type() : '';
            }

            if ($current_type === $product_type) {
                $user_id = $order->get_user_id();
                if (!$user_id) continue;

                $add_views = (int) get_post_meta($product_id, '_' . $product_package, true);
                if ($add_views <= 0) $add_views = 1;

                $current_views = (int) get_user_meta($user_id, '_vgtech_ai_views', true);
                $new_views = $current_views + $add_views;
                update_user_meta($user_id, '_vgtech_ai_views', $new_views);

                $wpdb->insert(
                    $table_name,
                    [
                        'user_id'    => $user_id,
                        'order_id'   => $order_id,
                        'value'      => $add_views,
                        'created_at' => current_time('mysql'),
                    ],
                    ['%d', '%d', '%d', '%s']
                );

                $total_added += $add_views;
            }
        }

        if ($total_added > 0) {
            $order->update_meta_data('_vgtech_ai_views_incremented', 'yes');
            $order->save();

            wp_send_json([
                'status'      => 'success',
                'total_added' => $total_added,
                'order_id'    => $order_id,
                'button' => home_url()
            ]);
        }

        wp_send_json([
            'status' => 'no_matching_product',
            'message' => 'success'
        ]);
    }

}