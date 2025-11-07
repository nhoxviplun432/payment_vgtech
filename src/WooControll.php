<?php
namespace paymentvgtech;

defined('ABSPATH') || exit;

class WooControll {
    public function __construct() {}

    /**
     * Thêm custom fields trong trang chỉnh sửa sản phẩm
     */
     /**
     * Thêm custom fields trong trang chỉnh sửa sản phẩm
     */
    public function add_general_fields() {
        global $post, $product_type, $product_package;

        $current_type   = get_post_meta($post->ID, '_product_type', true);
        $current_value  = get_post_meta($post->ID, '_'.$product_package, true);

        // Dropdown chọn loại sản phẩm
        woocommerce_wp_select([
            'id'      => '_product_type',
            'label'   => __('Loại sản phẩm', 'woocommerce'),
            'options' => [
                'product' => __('Sản phẩm', 'woocommerce'),
                $product_type => __('Gói tư vấn', 'woocommerce'),
            ],
            'value'   => $current_type,
        ]);

        // Luôn render field AI Chat (ẩn/hiện bằng JS)
        woocommerce_wp_text_input([
            'id'          => '_'.$product_package,
            'label'       => __('Số lượng AI Chat', 'woocommerce'),
            'type'        => 'number',
            'desc_tip'    => true,
            'description' => __('Nhập số lượng AI Chat cho gói tư vấn.', 'woocommerce'),
            'value'       => $current_value,
            'custom_attributes' => [
                'min' => '0',
                'step' => '1',
            ],
        ]);
    }

    /**
     * Lưu dữ liệu meta sản phẩm
     */
    public function save_product_meta($post_id) {
        global $product_type, $product_package;

        $type = isset($_POST['_product_type']) ? sanitize_text_field($_POST['_product_type']) : '';

        if ($type === 'product') {
            update_post_meta($post_id, '_product_type', 'product');
            delete_post_meta($post_id, '_'.$product_package);
            return;
        }

        if ($type === $product_type) {
            update_post_meta($post_id, '_product_type', $product_type);

            $ai_chat_value = isset($_POST['_'.$product_package]) ? intval($_POST['_'.$product_package]) : 0;
            update_post_meta($post_id, '_'.$product_package, $ai_chat_value);
        }
    }

    /**
     * JS hiển thị/ẩn trường AI Chat trong admin
     */
    public function admin_footer_script() {
        global $product_type, $product_package;
        ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                const productTypeSelect = document.querySelector('#_product_type');
                const aiChatField = document.querySelector('#_' + <?php echo json_encode($product_package); ?>)?.closest('p');

                function toggleAiChatField() {
                    const type = productTypeSelect ? productTypeSelect.value : '';
                    const show = type === <?php echo json_encode($product_type); ?>;
                    if (aiChatField) aiChatField.style.display = show ? 'block' : 'none';
                }

                if (productTypeSelect) {
                    productTypeSelect.addEventListener('change', toggleAiChatField);
                    toggleAiChatField();
                }
            });
        </script>
        <?php
    }

    /**
     * Ẩn sản phẩm có type = package khỏi các truy vấn WooCommerce mặc định
     */
    public function exclude_package_products($query) {
        if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag() || is_search())) {
            $meta_query = $query->get('meta_query') ?: [];
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key'     => '_product_type',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => '_product_type',
                    'value'   => 'package',
                    'compare' => '!=',
                ],
            ];
            $query->set('meta_query', $meta_query);
        }
    }

    /**
     * Shortcode hiển thị danh sách sản phẩm package
     * [package_products limit="10"]
     */
    public function package_products_shortcode($atts) {
        global $product_type, $product_package;

        $atts = shortcode_atts([
            'limit' => 10,
        ], $atts, 'package_products');

        $args = [
            'post_type'      => 'product',
            'posts_per_page' => intval($atts['limit']),
            'meta_query'     => [
                [
                    'key'   => '_product_type',
                    'value' => $product_type,
                    'compare' => '=',
                ],
            ],
        ];

        $products = new \WP_Query($args);

        if (!$products->have_posts()) {
            return '<p>' . __('Không có sản phẩm gói tư vấn nào.', 'woocommerce') . '</p>';
        }

        ob_start();

        $product_count = $products->post_count;
        $columns_class = ($product_count >= 4) ? 'cols-4' : 'cols-3';

        echo '<div class="package-products ' . esc_attr($columns_class) . '">';
        while ($products->have_posts()) : $products->the_post();
            global $product;

            $ai_chat = get_post_meta(get_the_ID(), '_'.$product_package, true);
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium'); // Lấy URL ảnh đại diện
            ?>
            <!-- <div class="package-item" style="background-image: url('<?php echo esc_url($thumbnail_url); ?>');"> -->
            <div class="package-item">
                <!-- <a href="<?php the_permalink(); ?>">
                    <h3><?php the_title(); ?></h3>
                </a> -->

                <h3 class="text-center title-package"><?php the_title(); ?></h3>

                <!-- ✅ Thêm nội dung sản phẩm ở đây -->
                <div class="package-desc">
                    <?php the_excerpt(); ?>
                    <!-- Hoặc dùng the_content() nếu bạn muốn hiển thị toàn bộ mô tả -->
                </div>

                <p class="text-center"><?php echo $product->get_price_html(); ?></p>
                <!-- <p><strong><?php echo __('Lượt:', 'woocommerce'); ?></strong> <?php echo esc_html($ai_chat); ?></p> -->

                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="button add_to_cart_button">
                    <?php echo __('Nâng Cấp', 'woocommerce'); ?>
                </a>
            </div>
            <?php
        endwhile;
        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean();
    }

    public function init_redirect_ai_vgtech(){
        // Nếu có param ai_redirect thì lưu vào session
        if (!session_id()) {
            session_start();
        }

        if (isset($_GET['ai_redirect'])) {
            $_SESSION['ai_redirect_url'] = esc_url_raw($_GET['ai_redirect']);
        }
    }

    public function woocommerce_login_redirect_ai_vgtech($redirect, $user){
         if (!session_id()) {
            session_start();
        }

        if (!empty($_SESSION['ai_redirect_url'])) {
            $redirect_url = $_SESSION['ai_redirect_url'];
            unset($_SESSION['ai_redirect_url']); // xóa sau khi dùng
            return $redirect_url;
        }

        // Nếu không có redirect tùy chỉnh thì giữ mặc định
        return $redirect;
    }

    public function redirect_add_to_cart($url)
    {
        global $product_type;

        if (isset($_GET['add-to-cart'])) {
            $product_id = absint($_GET['add-to-cart']);
            $product    = wc_get_product($product_id);

            if (!$product) return $url;

            $type = $product->get_meta('_product_type');

            // ✅ Nếu là sản phẩm đặc biệt ($product_type)
            if ($type === $product_type) {
                WC()->cart->empty_cart(); // chỉ giữ lại 1 sản phẩm duy nhất
                wc_clear_notices();

                $added = WC()->cart->add_to_cart($product_id, 1);

                if (!$added) {
                    wc_add_notice(__('Không thể thêm sản phẩm vào giỏ hàng.', 'woocommerce'), 'error');
                    return home_url();
                }

                // ✅ Chuyển hướng thẳng sang trang thanh toán
                return wc_get_checkout_url();
            }
        }

        return $url;
    }

    public function block_product_page()
    {
        global $product_type;
        if (is_singular('product')) {
            global $post;
            $product = wc_get_product($post->ID);
            if ($product && in_array($product->get_meta('_product_type'), $product_type)) {
                wp_redirect(home_url('/404'), 301);
                exit;
            }
        }

        if (is_checkout() && !is_user_logged_in()) {
            $checkout_url = wc_get_checkout_url();
            $login_url    = wc_get_page_permalink('myaccount');

            // ✅ Redirect kèm tham số quay lại checkout
            $redirect_url = add_query_arg('ai_redirect', urlencode($checkout_url), $login_url);

            wp_redirect($redirect_url);
            exit;
        }
    }

    public function modify_cart_prices($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) return;

        foreach ($cart->get_cart() as $item) {
            if (!empty($item['custom_price'])) {
                $item['data']->set_price($item['custom_price']);
            }
            if (!empty($item['custom_name'])) {
                $item['data']->set_name((string)$item['custom_name']);
            }
        }
}

    public function block_cart_display()
    {
        global $product_type;

        $cart = WC()->cart->get_cart();
        $removed = false;

        foreach ($cart as $key => $item) {
            $product = $item['data'] ?? null;
            if ($product && $product->get_meta('_product_type') === $product_type) {
                WC()->cart->remove_cart_item($key);
                $removed = true;
            }
        }

        // ✅ Nếu toàn bộ cart trống sau khi loại bỏ -> về trang chủ
        if ($removed && WC()->cart->is_empty()) {
            wp_safe_redirect(home_url());
            exit;
        }
    }

    public function ajax_add_to_cart()
    {
        global $product_type;

        $product_id = absint($_POST['product_id'] ?? 0);
        $quantity   = max(1, intval($_POST['quantity'] ?? 1));
        $product    = wc_get_product($product_id);

        if (!$product) {
            wp_send_json(['error' => true, 'message' => 'Sản phẩm không tồn tại.']);
        }

        $type = $product->get_meta('_product_type');

        // ✅ Nếu là sản phẩm package (chỉ 1 sản phẩm/lần)
        if ($type === $product_type) {
            WC()->cart->empty_cart(); // Xoá toàn bộ sản phẩm khác
            $added = WC()->cart->add_to_cart($product_id, 1);

            if ($added) {
                wp_send_json(['success' => true, 'redirect' => wc_get_checkout_url()]);
            } else {
                wp_send_json(['error' => true, 'message' => 'Không thể thêm sản phẩm vào giỏ hàng.']);
            }
        } else {
            // ✅ Nếu là sản phẩm thường → thêm bình thường
            $added = WC()->cart->add_to_cart($product_id, $quantity);

            wp_send_json([
                $added ? 'success' : 'error' => true,
                'message' => $added ? '' : 'Không thể thêm sản phẩm vào giỏ hàng.',
            ]);
        }

        wp_die();
    }

    // public function vgtech_order_status_completed($order_id) {
    //     $order = wc_get_order($order_id);
    //     if (!$order) return;

    //     $user_id = $order->get_user_id();
    //     if (!$user_id) return;

    //     // Lấy trạng thái đơn hàng hiện tại
    //     $status = $order->get_status(); // ví dụ: "completed", "processing", "on-hold", "underpaid"...

    //     // Lấy danh sách trạng thái được PayOS chấp nhận là "đã thanh toán"
    //     $valid_statuses = $this->payos_get_order_statuses_after_paid();

    //     // Nếu trạng thái hiện tại không nằm trong danh sách hợp lệ => dừng
    //     if (!isset($valid_statuses['wc-' . $status])) return;

    //     // Tiến hành cộng lượt tư vấn
    //     foreach ($order->get_items() as $item) {
    //         $product_id = $item->get_product_id();
    //         $views = intval(get_post_meta($product_id, '_vgtech_ai_consulting_limit', true));

    //         if ($views > 0) {
    //             $current_views = intval(get_user_meta($user_id, '_vgtech_ai_views', true));
    //             update_user_meta($user_id, '_vgtech_ai_views', $current_views + $views);
    //         }
    //     }
    // }
}
