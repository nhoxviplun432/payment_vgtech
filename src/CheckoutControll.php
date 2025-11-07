<?php
namespace paymentvgtech;

defined('ABSPATH') || exit;

class CheckoutControll{
    
    public function save_order_meta($order, $data)
    {
        if (WC()->session->get('tracuu_order_data')) {
            $tracuu_data = WC()->session->get('tracuu_order_data');
            $order->update_meta_data('_tracuu_data_info', $tracuu_data);
            WC()->session->__unset('tracuu_order_data');
        } else {
            wc_add_notice('Có lỗi xảy ra trong quá trình đặt hàng. Vui lòng thử lại.', 'error');
            return false;
        }
    }

    public function custom_checkout_fields($fields)
    {
        global $product_type;

        // ✅ 1. Nếu user chưa đăng nhập → chuyển hướng sang trang My Account
        if (!is_user_logged_in()) {
            $checkout_url = wc_get_checkout_url();
            $login_url = wc_get_page_permalink('myaccount');

            // ✅ Redirect kèm tham số quay lại checkout sau login
            $redirect_url = add_query_arg('redirect_to', urlencode($checkout_url), $login_url);

            wp_safe_redirect($redirect_url);
            exit;
        }

        // ✅ 2. Kiểm tra xem trong giỏ có sản phẩm thuộc loại $product_type không
        $cart = WC()->cart->get_cart();
        $has_tracuu = false;

        foreach ($cart as $item) {
            $type = get_post_meta($item['data']->get_id(), '_product_type', true);
            if ($type === $product_type) {
                $has_tracuu = true;
                break;
            }
        }

        // ✅ 3. Nếu là sản phẩm gói (package/tracuu) → loại bỏ toàn bộ field checkout
        if ($has_tracuu) {
            $fields['billing']  = [];
            $fields['shipping'] = [];
            $fields['order']    = [];
            $fields['account']  = [];
        }

        return $fields;
    }


    // public function update_order_fullname($order_id)
    // {
    //     if (isset($_POST['billing_full_name'])) {
    //         update_post_meta($order_id, '_billing_full_name', sanitize_text_field($_POST['billing_full_name']));
    //     }
    // }

    public function change_order_button(){
        global $product_type;
        $cart = WC()->cart->get_cart();
        $has_tracuu = false;

        // ✅ Đảm bảo $product_type là mảng
        if (!is_array($product_type)) {
            $product_type = array($product_type);
        }

        foreach ($cart as $item) {
            $type = get_post_meta($item['data']->get_id(), '_product_type', true);
            if (in_array($type, $product_type, true)) {
                $has_tracuu = true;
            }
        }

        $text = $has_tracuu ? 'Đồng ý và tiếp tục' : 'Tiếp tục';

        add_filter('woocommerce_order_button_html', function () use ($text) {
            return '<button type="submit" class="button alt" id="place_order">' . esc_html($text) . '</button>';
        });
    }


    public function custom_checkout_validation($fields, $errors)
    {
        if (!empty($errors->get_error_codes())) {
            foreach ($errors->get_error_codes() as $code) {
                $errors->remove($code);
            }
            $errors->add('validation', 'Vui lòng nhập đầy đủ thông tin');
        }
    }

    public function custom_checkout_summary() {
        global $product_type;
        $cart = WC()->cart->get_cart();
        $last_product = null;

        foreach ($cart as $item) {
            $type = get_post_meta($item['data']->get_id(), '_product_type', true);
            if (in_array($type, (array)$product_type, true)) {
                $last_product = $item['data'];
                break;
            }
        }



        if ($last_product) {
            $product_id = $last_product->get_id();
            $thumbnail  = wp_get_attachment_image_url($last_product->get_image_id(), 'medium');
            $title      = $last_product->get_name();

            // Ưu tiên lấy mô tả chi tiết, fallback sang mô tả ngắn
            $content = get_post_field('post_content', $product_id);
            if (empty(trim($content))) {
                $content = get_post_field('post_excerpt', $product_id);
            }
            $content = apply_filters('the_content', $content);

            $price_html = $last_product->get_price_html();

            echo '<div class="custom-checkout-product">';
                echo '<div class="custom-checkout-product-inner">';

                    // Thông tin sản phẩm
                    echo '<div class="product-info">';
                        echo '<h3 class="product-title text-center">' . esc_html($title) . '</h3>';
                         // Ảnh sản phẩm
                        if ($thumbnail) {
                            echo '<div class="product-image">';
                            echo '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '" />';
                            echo '</div>';
                        }
                        echo '<div class="product-content">' . wp_kses_post($content) . '</div>';
                        echo '<p class="product-price text-center"><strong>Giá:</strong> ' . wp_kses_post($price_html) . '</p>';
                    echo '</div>';

                echo '</div>';
            echo '</div>';
        }
    }

    public function thankyou_page_customization($order_id)
    {
        $product_type = 'tracuu';
        $order = wc_get_order($order_id);
        $has_tracuu = false;

        foreach ($order->get_items() as $item) {
            $type = get_post_meta($item->get_product_id(), '_product_type', true);
            if ($type === $product_type) $has_tracuu = true;
        }

        wp_enqueue_style('checkout-css', PAYMENT_AI_CHAT_VGTECH_URL . 'assets/checkout.css');

        if ($has_tracuu) {
            wp_enqueue_script('full-pdf-script', PAYMENT_AI_CHAT_VGTECH_URL . 'assets/full_pdf.js', ['jquery'], null, true);
            wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', [], null, true);
            wp_localize_script('full-pdf-script', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('secure_nonce_action')]);
        }

    }

    public function custom_gettext($translated_text, $text, $domain)
    {
        if ($domain === 'woocommerce' && $text === 'Sorry, your session has expired. Return to shop') {
            $translated_text = 'Phiên của bạn đã hết hạn. <a href="' . home_url() . '">Quay lại trang chủ</a>';
        }
        return $translated_text;
    }

    public function before_checkout_form_vgetch(){
        global $product_type;
        $cart = WC()->cart->get_cart();

        $count_package = 0;
        foreach ($cart as $item) {
            if ($item['data']->get_meta('_product_type') === $product_type) {
                $count_package++;
            }
        }

        // ✅ Nếu có nhiều hơn 1 sản phẩm gói -> chỉ giữ lại sản phẩm đầu tiên
        if ($count_package > 1) {
            WC()->cart->empty_cart();
            wc_add_notice('Chỉ được phép mua 1 gói tư vấn trong mỗi lần thanh toán.', 'error');
            wp_safe_redirect(home_url());
            exit;
        }
    }

    public function vgtech_payment_complete_payos($order_id){
        $order = wc_get_order($order_id);
        if (!$order) return;

        // Tránh cộng lặp nếu webhook retry
        if ($order->get_meta('_vgtech_ai_views_incremented')) {
            return;
        }

        $user_id = $order->get_user_id();
        if ($user_id) {
            $views = (int) get_user_meta($user_id, '_vgtech_ai_views', true);
            update_user_meta($user_id, '_vgtech_ai_views', $views + 1);
            $order->update_meta_data('_vgtech_ai_views_incremented', 1);
            $order->save();
        }
    }


}