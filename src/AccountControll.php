<?php
namespace paymentvgtech;

defined('ABSPATH') || exit;

class AccountControll{
    public function __construct()
    {
    }
    

    public function shortcode_buy_button()
    {
        ob_start();
        echo '<div class="button-payment-wrap" id="button-payment-tracuu"></div>';
        return ob_get_clean();
    }

    /*-----------------------------------------
     * SHORT CODE
     ------------------------------------------*/

    public function igeni_one_account_menu($atts) {
        // Lấy ID trang tài khoản WooCommerce
        $dashboard_page_id = get_option('woocommerce_myaccount_page_id');

        // Lấy tham số shortcode
        $atts = shortcode_atts(array(
            'menu' => '', // Tên hoặc slug của menu
        ), $atts);

        // Bắt đầu ghi nội dung
        ob_start();

        echo '<div class="igeni-one-account-menu-guest elementor-button-wrapper">';

        // ✅ Luôn hiển thị menu nếu có chỉ định
        if (!empty($atts['menu'])) {
            echo '<nav class="tai-khoan-menu-nav">';
            wp_nav_menu(array(
                'menu' => $atts['menu'],
                'container' => false,
                'fallback_cb' => false
            ));
            echo '</nav>';
        }

        // ✅ Kiểm tra nếu KHÔNG phải trang tài khoản thì mới hiển thị các nút
        if (!is_page($dashboard_page_id)) {

            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();

                // Hiển thị thông tin tài khoản
                echo '<a class="elementor-button elementor-button-link elementor-size-sm" href="' . esc_url(get_permalink($dashboard_page_id)) . '">';
                echo 'Xin chào, ' . esc_html($current_user->display_name);
                echo '</a>';

                // Nút đăng xuất
                echo '<a class="elementor-button button-logout button-border" href="' . esc_url(wp_logout_url(home_url())) . '">';
                    echo '<i aria-hidden="true" class="fas fa-sign-out-alt"></i>';
                echo '</a>';

            } else {
                // Nếu chưa đăng nhập
                if ($dashboard_page_id) {
                    echo '<a class="elementor-button elementor-button-link elementor-size-sm" href="' . esc_url(get_permalink($dashboard_page_id)) . '">Đăng nhập</a>';
                    echo '<a class="elementor-button elementor-button-link elementor-size-sm" href="' . esc_url(get_permalink($dashboard_page_id) . '?account=register') . '">Đăng ký</a>';
                } else {
                    echo '<p>Trang tài khoản WooCommerce chưa được cấu hình.</p>';
                }
            }
        }

        echo '</div>';

        return ob_get_clean();
    }

    /*-----------------------------------------
     * ACCOUNT CUSTOMIZATION
     ------------------------------------------*/
    public static function activate() {
        add_rewrite_endpoint('tracuu', EP_ROOT | EP_PAGES);
        // add_rewrite_endpoint('tracuu-view', EP_ROOT | EP_PAGES);
        flush_rewrite_rules();
    }

    private function get_blocked_endpoints()
    {
        return ['downloads', 'edit-address'];
    }

    public function remove_blocked_endpoints($items)
    {
        foreach ($this->get_blocked_endpoints() as $endpoint) {
            unset($items[$endpoint]);
        }
        return $items;
    }

    public function block_restricted_account_pages()
    {
        if (!is_account_page()) return;
        foreach ($this->get_blocked_endpoints() as $endpoint) {
            if (get_query_var($endpoint) !== '') {
                wp_redirect(home_url());
                exit;
            }
        }
    }

    public function custom_required_fields($required_fields)
    {
        unset($required_fields['account_display_name']);
        return $required_fields;
    }

    public function translate_account_texts($translated, $text, $domain)
    {
        if ($domain === 'woocommerce') {
            if ($text === 'Account details') {
                $translated = 'Chỉnh sửa tài khoản';
            }
            if ($text === 'Log out') {
                $translated = 'Đăng xuất';
            }
        }
        return $translated;
    }

    /*-----------------------------------------
     * REGISTER / PROFILE UPDATE HANDLERS
     ------------------------------------------*/
    public function save_extra_customer_fields($user_id)
    {
        if (isset($_POST['account_first_name'])) {
            $first = sanitize_text_field($_POST['account_first_name']);
            update_user_meta($user_id, 'first_name', $first);
            update_user_meta($user_id, 'billing_first_name', $first);
        }

        if (isset($_POST['account_last_name'])) {
            $last = sanitize_text_field($_POST['account_last_name']);
            update_user_meta($user_id, 'last_name', $last);
            update_user_meta($user_id, 'billing_last_name', $last);
        }

        if (isset($_POST['account_birthday'])) {
            $birthday = sanitize_text_field($_POST['account_birthday']);
            if (preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/', $birthday)) {
                update_user_meta($user_id, 'account_birthday', $birthday);
                update_user_meta($user_id, 'account_sochudao', $this->tinhConSoChuDao($birthday));
                update_user_meta($user_id, 'account_cunghoangdao', $this->xacDinhCungHoangDao($birthday));
            }
        }

        if (isset($_POST['account_phone'])) {
            $phone = preg_replace('/[^0-9]/', '', sanitize_text_field($_POST['account_phone']));
            if (preg_match('/^0[0-9]{8,11}$/', $phone)) {
                update_user_meta($user_id, 'account_phone', $phone);
                update_user_meta($user_id, 'billing_phone', $phone);
            }
        }
    }

    public function validate_registration_fields($username, $email, $errors)
    {
        if (!empty($_POST['account_phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $_POST['account_phone']);
            if (!preg_match('/^0[0-9]{8,11}$/', $phone)) {
                $errors->add('account_phone_invalid', __('Số điện thoại không hợp lệ. Phải bắt đầu bằng 0 và tối đa 12 chữ số.', 'woocommerce'));
            }
        }
    }

    public function vgtech_update_account_fields($user_id)
    {
        if (isset($_POST['account_first_name'])) {
            $first = sanitize_text_field($_POST['account_first_name']);
            update_user_meta($user_id, 'first_name', $first);
            update_user_meta($user_id, 'billing_first_name', $first);
        }

        if (isset($_POST['account_last_name'])) {
            $last = sanitize_text_field($_POST['account_last_name']);
            update_user_meta($user_id, 'last_name', $last);
            update_user_meta($user_id, 'billing_last_name', $last);
        }

        if (isset($_POST['account_birthday'])) {
            $birthday = sanitize_text_field($_POST['account_birthday']);
            if (preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/', $birthday)) {
                update_user_meta($user_id, 'account_birthday', $birthday);
                update_user_meta($user_id, 'account_sochudao', $this->tinhConSoChuDao($birthday));
                update_user_meta($user_id, 'account_cunghoangdao', $this->xacDinhCungHoangDao($birthday));
            } else {
                wc_add_notice(__('Ngày sinh không hợp lệ. Định dạng đúng: dd/mm/yyyy', 'woocommerce'), 'error');
            }
        }

        if (isset($_POST['account_phone'])) {
            $phone = preg_replace('/[^0-9]/', '', sanitize_text_field($_POST['account_phone']));
            if (preg_match('/^0[0-9]{8,11}$/', $phone)) {
                update_user_meta($user_id, 'account_phone', $phone);
                update_user_meta($user_id, 'billing_phone', $phone);
            } else {
                wc_add_notice(__('Số điện thoại không hợp lệ. Phải bắt đầu bằng số 0 và có tối đa 12 chữ số.', 'woocommerce'), 'error');
            }
        }
    }

    /*-----------------------------------------
     * tư vấn TAB + ENDPOINTS
     ------------------------------------------*/
    public function add_tracuu_tab($items)
    {
        return array_slice($items, 0, 1, true)
            + ['tuvan' => 'Lượt tư vấn']
            + array_slice($items, 1, null, true);
    }

    public function register_tracuu_endpoints()
    {
        add_rewrite_endpoint('tuvan', EP_ROOT | EP_PAGES);
        // add_rewrite_endpoint('tuvan-view', EP_ROOT | EP_PAGES);
    }

    public function account_tuvan_endpoint() {
        global $wpdb;

        $user_id = get_current_user_id();
        if (!$user_id) {
            echo '<p>Bạn cần đăng nhập để xem dữ liệu.</p>';
            return;
        }

        $table = $wpdb->prefix . 'vgtech_payment_ai';

        // Lấy dữ liệu theo user
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));

        if (empty($results)) {
            echo '<p>Chưa có dữ liệu thanh toán nào.</p>';
            return;
        }

        // Hiển thị bảng
        echo '<table style="width:100%; border-collapse:collapse;">';
        echo '<thead>
                <tr style="background-color:var(--e-global-color-primary);">
                    <th style="padding:10px; text-align:left;">#</th>
                    <th style="padding:10px; text-align:left;">Mã đơn hàng</th>
                    <th style="padding:10px; text-align:left;">Số lượt cộng</th>
                    <th style="padding:10px; text-align:left;">Ngày thanh toán</th>
                    <th style="padding:10px; text-align:center;"></th>
                </tr>
            </thead>';
        echo '<tbody>';

        $index = 1;
        foreach ($results as $row) {
            $bg_color = $index % 2 === 0 ? '#00000038' : 'tranparent';
            $order_id = intval($row->order_id);

            echo '<tr style="background-color:' . $bg_color . ';">';    
            echo '<td style="padding:10px;">' . $index . '</td>';
            echo '<td style="padding:10px;">#' . esc_html($order_id) . '</td>';
            echo '<td style="padding:10px;">+ ' . esc_html($row->value) . '</td>';
            echo '<td style="padding:10px;">' . date('d/m/Y H:i', strtotime($row->created_at)) . '</td>';
            echo '<td style="padding:10px; text-align:center;">';

            // Nếu có order → liên kết xem đơn hàng WooCommerce
            if ($order_id > 0) {
                // 🔹 Link đến chi tiết đơn hàng WooCommerce
                $order_url = wc_get_account_endpoint_url('view-order/' . $order_id);
                echo '<a href="' . esc_url($order_url) . '" title="Xem đơn hàng"><i class="fas fa-shopping-cart"></i></a>';
            } else {
                // 🔸 Link đến chi tiết cộng thủ công (custom endpoint)
                echo 'Gift';
            }

            echo '</td>';
            echo '</tr>';   

            $index++;
        }

        echo '</tbody>';
        echo '</table>';
    }


    public function render_tracuu_history()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $table = $wpdb->prefix . 'tracuu';
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY date DESC", $user_id)
        );

        if (!$results) {
            echo '<p>Bạn chưa có dữ liệu tư vấn nào.</p>';
            return;
        }

        echo '<table style="width:100%; border-collapse:collapse;">';
        $index = 1;
        foreach ($results as $row) {
            $bg = $index % 2 === 0 ? '#ededed' : '#ffffff';
            $is_full = ($row->type === 'full');
            $type_text = $is_full ? 'Bản Đầy Đủ' : 'Bản tư vấn';

            echo '<tr style="background-color:' . esc_attr($bg) . '">';
            echo '<td style="padding:10px;width:5%;">' . $index . '</td>';
            echo '<td style="padding:10px;width:35%;">' . esc_html($row->title) . '</td>';
            echo '<td style="padding:10px;width:20%;">' . date('d/m/Y', strtotime($row->date)) . '</td>';
            echo '<td style="padding:10px;width:20%;">' . esc_html($type_text) . '</td>';
            echo '<td class="action table-tracuu-action" style="padding:10px;width:20%;">';

            // if ($is_full && $row->id_order) {
            //     $detail_url = wc_get_account_endpoint_url('tracuu-view') . $row->id_order;
            //     echo '<a href="' . esc_url($detail_url) . '" title="Xem chi tiết"><i class="fas fa-info-circle"></i></a>';
            //     echo '<a href="#" title="Tải về"><i class="fas fa-download"></i></a>';
            // }

            echo '<a href="#" title="Xem lại"><i class="fas fa-circle-notch"></i></a>';
            echo '<a href="#" title="Xóa"><i class="fas fa-trash"></i></a>';
            echo '</td></tr>';
            $index++;
        }
        echo '</table>';
    }

    public function render_tracuu_detail($order_id)
    {
        $order_id = absint($order_id);
        if (!$order_id || get_post_type($order_id) !== 'shop_order') {
            echo '<p>Đơn hàng không hợp lệ.</p>';
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            echo '<p>Không tìm thấy đơn hàng.</p>';
            return;
        }

        echo '<h4>Chi tiết tư vấn đầy đủ</h4>';
        echo '<p><strong>Ngày thanh toán:</strong> ' . wc_format_datetime($order->get_date_created()) . '</p>';

        $status = $order->get_status();
        echo '<p><strong>Trạng thái:</strong> ' . ($status === 'completed' ? 'Hoàn tất' : wc_get_order_status_name($status)) . '</p>';

        echo '<p><strong>Phương thức thanh toán:</strong> ' . esc_html($order->get_payment_method_title()) . '</p>';
        echo '<p><strong>Email người nhận:</strong> ' . esc_html($order->get_billing_email()) . '</p>';
        echo '<p><strong>Tổng thanh toán:</strong> ' . $order->get_formatted_order_total() . '</p>';
    }

    private function tinhConSoChuDao($ngay_sinh) {
        $so = preg_replace('/[^0-9]/', '', $ngay_sinh);

        $tong = array_sum(str_split($so));
        if ($tong === 11 || $tong === 22) {
            return $tong;
        }
        while ($tong > 9) {
            $tong = array_sum(str_split($tong));
        }

        return $tong;
    }

    private function xacDinhCungHoangDao($ngay_sinh) {
        $parts = explode('/', $ngay_sinh);
        if (count($parts) !== 3) return 'Định dạng không hợp lệ';

        $day = (int)$parts[0];
        $month = (int)$parts[1];

        $cung = [
            ['name' => 'Bạch Dương',  'start' => [21, 3],  'end' => [19, 4]],
            ['name' => 'Kim Ngưu',    'start' => [20, 4],  'end' => [20, 5]],
            ['name' => 'Song Tử',     'start' => [21, 5],  'end' => [20, 6]],
            ['name' => 'Cự Giải',     'start' => [21, 6],  'end' => [22, 7]],
            ['name' => 'Sư Tử',       'start' => [23, 7],  'end' => [22, 8]],
            ['name' => 'Xử Nữ',       'start' => [23, 8],  'end' => [22, 9]],
            ['name' => 'Thiên Bình',  'start' => [23, 9],  'end' => [22, 10]],
            ['name' => 'Bọ Cạp',      'start' => [23, 10], 'end' => [21, 11]],
            ['name' => 'Nhân Mã',     'start' => [22, 11], 'end' => [21, 12]],
            ['name' => 'Ma Kết',      'start' => [22, 12], 'end' => [19, 1]],
            ['name' => 'Bảo Bình',    'start' => [20, 1],  'end' => [18, 2]],
            ['name' => 'Song Ngư',    'start' => [19, 2],  'end' => [20, 3]],
        ];

        foreach ($cung as $item) {
            [$startDay, $startMonth] = $item['start'];
            [$endDay, $endMonth] = $item['end'];

            if (
                ($month == $startMonth && $day >= $startDay) ||
                ($month == $endMonth && $day <= $endDay)
            ) {
                return $item['name'];
            }
        }

        return 'Không xác định';
    }


    // Consult View AI 
    public static function add_consult_view_column($columns) {
        $columns['consult_view'] = __('Consult View', 'vgtech');
        return $columns;
    }

    public function show_consult_view_column($value, $column_name, $user_id) {
        if ($column_name === 'consult_view') {
            $views = (int) get_user_meta($user_id, '_vgtech_ai_views', true);
            return '<strong>' . $views . '</strong>';
        }
        return $value;
    }

    public function sortable_consult_view_column($columns) {
        $columns['consult_view'] = 'consult_view';
        return $columns;
    }

    public function sort_consult_view_query($query) {
        if (isset($query->query_vars['orderby']) && $query->query_vars['orderby'] === 'consult_view') {
            $query->query_vars['meta_key'] = '_vgtech_ai_views';
            $query->query_vars['orderby'] = 'meta_value_num';
        }
    }


    public function add_consult_view_field($user)
    {
        $views = (int) get_user_meta($user->ID, '_vgtech_ai_views', true);
        ?>
        <h2><?php esc_html_e('Consult View AI', 'vgtech'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="vgtech_ai_views"><?php esc_html_e('Consult View Count', 'vgtech'); ?></label></th>
                <td>
                    <input type="number" name="vgtech_ai_views" id="vgtech_ai_views"
                        value="<?php echo esc_attr($views); ?>" class="regular-text" min="0" />
                    <p class="description">
                        <?php esc_html_e('Number of consult views for this user. Updating will also log to history table.', 'vgtech'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }


    public function save_consult_view_field($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'vgtech_payment_ai';

        // Lấy giá trị mới
        $new_views = isset($_POST['vgtech_ai_views']) ? (int) $_POST['vgtech_ai_views'] : 0;

        // Lấy giá trị cũ
        $old_views = (int) get_user_meta($user_id, '_vgtech_ai_views', true);

        // Tính số lượng thêm vào
        $add_views = $new_views - $old_views;

        // Cập nhật usermeta
        update_user_meta($user_id, '_vgtech_ai_views', $new_views);

        // Nếu có tăng thêm -> ghi log
        if ($add_views > 0) {
            $wpdb->insert(
                $table_name,
                [
                    'user_id'    => $user_id,
                    'order_id'   => 0,
                    'value'      => $add_views,
                    'created_at' => current_time('mysql'),
                ],
                ['%d', '%d', '%d', '%s']
            );
        }
    }
}