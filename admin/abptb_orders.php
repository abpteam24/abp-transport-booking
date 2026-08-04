<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Orders')) {
        class ABPTB_Orders {
            public function __construct() {
                add_action('abptb_load_orders', [$this, 'load_orders']);
                add_action('wp_ajax_abptb_load_order_list', [$this, 'load_order_list']);
                add_action('wp_ajax_abptb_item_cancel', [$this, 'item_cancel']);
            }
            public function load_orders(): void {
                ?>
                <div class="abptb_orders _section_card">
                    <h4 class="_abp_title_gap_xs"><span>📋</span> <?php esc_html_e('Order Filter', 'abp-transport-booking'); ?></h4>
                    <div class="_ov_initial_mar_t_xs">
                        <form class="abp_search_form" method="post" action="">
                            <div class="_form_inline">
                                <?php
                                    ABPTB_Layout::filter_post_list();
                                    ABPTB_Layout::filter_booking_date();
                                    ABPTB_Layout::filter_booking_date_between();
                                    ABPTB_Layout::filter_bp();
                                    ABPTB_Layout::filter_dp();
                                    ABPTB_Layout::filter_order_date();
                                    ABPTB_Layout::filter_order_date_between();
                                    ABPTB_Layout::filter_user_id();
                                    ABPTB_Layout::filter_order_id();
                                    ABPTB_Layout::filter_bill_name();
                                    ABPTB_Layout::filter_bill_email();
                                    ABPTB_Layout::filter_bill_phone();
                                ?>
                            </div>
                            <div class="_form_inline_mar_t_xs">
                                <div class="_input_item">
                                    <button type="submit" class="_btn_theme_xs_w_full">
                                        <span class="_mar_r_xs">🔎</span><?php esc_html_e('Search', 'abp-transport-booking'); ?>
                                    </button>
                                </div>
                                <div class="_input_item">
                                    <button class="_btn_theme_xs _w_full" title="<?php esc_attr_e('More Options', 'abp-transport-booking'); ?>" type="button" data-collapse-target="#view_more_filter_option"
                                            data-close-text="👁️ <?php esc_attr_e('More Options', 'abp-transport-booking'); ?>" data-open-text="🙈  <?php esc_attr_e('Close Options', 'abp-transport-booking'); ?>"
                                    >
                                        <span data-text>👁️ <?php esc_html_e('More Options', 'abp-transport-booking'); ?></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="order_list">
                        <?php $this->order_lists(); ?>
                    </div>
                </div>
                <?php
            }
            public function order_lists($filter_args = []): void {
                $page_number = isset($filter_args['page_number']) && is_numeric($filter_args['page_number']) ? (int)$filter_args['page_number'] : 1;
                $limit = isset($filter_args['page_item']) && is_numeric($filter_args['page_item']) ? (int)$filter_args['page_item'] : ABPTB_Function::get_option('abptb_per_page_item', 20);
                $data_status = !empty($filter_args['status']) ? sanitize_text_field($filter_args['status']) : '';
                $si = ($page_number - 1) * $limit + 1;
                $offset = $si - 1;
                $booking_lists = ABPTB_Query::get_booking_query($filter_args, $limit, $offset);
                $total_order = ABPTB_Query::get_booking_query($filter_args, 0, 0, true);
                $filter_args['status'] = 'all';
                $label = ABPTB_Function::label();
                $brand_icon = ABPTB_Function::icon();
                $booked_status = ABPTB_Function::booking_status();
                $booked_status = $booked_status ? explode(',', $booked_status) : [];
                $_filter_args = $filter_args;
                $total_additional = 0;
                $total_sale = 0;
                // echo '<pre>';                print_r($booking_lists);                echo '</pre>';
                $count_foot_left_col = 0;
                $count_foot_right_col = 0;
                ?>
                <div class="_ov_auto_fj_between _mar_b_xs">
                    <div class="_group_content order_status_menu">
                        <button class="_btn_light_green_pale_xs_text_nowrap <?php echo esc_attr($data_status === 'all' ? 'abp_active' : ''); ?>" type="button" data-status="all" title="<?php esc_attr_e('All Booking', 'abp-transport-booking'); ?>">
                            <?php echo esc_html(__('All Booking', 'abp-transport-booking') . ' (' . ABPTB_Query::get_booking_query($filter_args, 0, 0, true) . ' )') ?>
                        </button>
                        <button class="_btn_light_green_pale_xs_text_nowrap <?php echo esc_attr(!$data_status ? 'abp_active' : ''); ?>" type="button" data-status="" title="<?php esc_attr_e('Booking Completed', 'abp-transport-booking'); ?>">
                            <?php
                                $filter_args['status'] = '';
                                echo esc_html(__('Booking Completed', 'abp-transport-booking') . ' (' . ABPTB_Query::get_booking_query($filter_args, 0, 0, true) . ' )');
                            ?>
                        </button>
                        <?php
                            $all_status = wc_get_order_statuses();
                            if (!empty($all_status) && is_array($all_status)) {
                                foreach ($all_status as $key => $status) {
                                    ?>
                                    <button class="_btn_light_green_pale_xs_text_nowrap <?php echo esc_attr($data_status === $key ? 'abp_active' : ''); ?>" type="button" data-status="<?php echo esc_attr($key); ?>">
                                        <?php
                                            $filter_args['status'] = sanitize_key($key);
                                            echo esc_html($status . ' (' . ABPTB_Query::get_booking_query($filter_args, 0, 0, true) . ')');
                                        ?>
                                    </button>
                                    <?php
                                }
                            }
                        ?>
                    </div>
                    <?php do_action('abptb_order_tab_action', $_filter_args); ?>
                </div>
                <?php if (!empty($booking_lists) && is_array($booking_lists)) { ?>
                    <table class=" _abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('Action', 'abp-transport-booking'); ?><?php $count_foot_left_col++; ?></th>
                            <th><?php esc_html_e('Order ID/ Date', 'abp-transport-booking'); ?><?php $count_foot_left_col++; ?></th>
                            <th><?php ABPTB_Layout::image_icon($brand_icon); ?><?php echo esc_html($label); ?><?php $count_foot_left_col++; ?></th>
                            <th><span class="_gap_xxs"><span class="fas fa-route"></span><?php esc_html_e('From - To', 'abp-transport-booking'); ?><?php $count_foot_left_col++; ?></span></th>
                            <th><?php esc_html_e('Ticket Info', 'abp-transport-booking'); ?><?php $count_foot_left_col++; ?></th>
                            <?php if (ABPTB_Function::on_off('additional_info')) { ?>
                                <th><?php esc_html_e('Additional Info', 'abp-transport-booking'); ?><?php $count_foot_left_col++; ?></th>
                            <?php } ?>
                            <th><?php esc_html_e('Price ', 'abp-transport-booking'); ?></th>
                            <?php if (ABPTB_Function::on_off('additional_info')) { ?>
                                <th><?php esc_html_e('Additional ', 'abp-transport-booking'); ?></th>
                            <?php } ?>
                            <th><?php esc_html_e('Total ', 'abp-transport-booking'); ?></th>
                            <th><?php esc_html_e('Status', 'abp-transport-booking'); ?><?php $count_foot_right_col++; ?></th>
                            <th><?php esc_html_e('Payment Method', 'abp-transport-booking'); ?><?php $count_foot_right_col++; ?></th>
                            <th><?php esc_html_e('Billing Info', 'abp-transport-booking'); ?><?php $count_foot_right_col++; ?></th>
                            <?php if (ABPTB_Function::on_off('client_info')) { ?>
                                <th><?php esc_html_e('Passenger Info', 'abp-transport-booking'); ?><?php $count_foot_right_col++; ?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($booking_lists as $booking_list) {
                            $post_infos['post_id'] = $booking_list['post_id'] ?? '';
                            $order_status = $booking_list['order_status'] ?? '';
                            $total_price = $booking_list['total'] ?? 0;
                            $total_sale = $total_sale + (int)($total_price);
                            $price = $booking_list['price'] ?? 0;
                            $ex_price = $booking_list['ex_price'] ?? 0;
                            $total_additional = $total_additional + (int)($ex_price);
                            $ticket_infos = json_decode($booking_list['ticket_info'] ?? '', true) ?: [];
                            $passenger_infos = json_decode($booking_list['pass_info'] ?? '', true) ?: [];
                            $additional_infos = json_decode($booking_list['ex_info'] ?? '', true) ?: [];
                            $others = json_decode($booking_list['others'] ?? '', true) ?: [];
                            ?>
                            <tr>
                                <th>
                                    <div class="_group_content">
                                        <?php do_action('abptb_order_action', ($booking_list['id'] ?? ''));
                                            if (in_array($order_status, $booked_status, true)) { ?>
                                                <button class="_btn_light_danger_xxs item_cancel" data-item_id="<?php echo esc_attr($booking_list['id'] ?? ''); ?>" title="<?php esc_attr_e('Ticket Cancel', 'abp-transport-booking'); ?>" type="button"><?php ABPTB_Layout::icon_svg('close_2'); ?></button>
                                            <?php } ?>
                                    </div>
                                </th>
                                <th class="_text_left">
                                    <p class="_abp"><?php echo esc_html($si . '. #' . ($booking_list['order_id'] ?? '')); ?></p>
                                    <p class="_abp_color_theme"><?php echo esc_html(ABPTB_Function::date_format($booking_list['created_at'] ?? '')); ?></p>
                                </th>
                                <th class="_text_left">
                                    <div class="_gap_xxs"><?php ABPTB_Layout::title($post_infos); ?></div>
                                    <p class="_abp_color_theme"><?php echo esc_html(ABPTB_Function::date_format($booking_list['start_time'] ?? '')); ?></p>
                                </th>
                                <td>
                                    <?php ABPTB_Layout::route_direction($post_infos, ($booking_list['bp_dp'] ?? ''), false, false); ?>
                                    <p class="_abp_color_theme"><?php echo esc_html(ABPTB_Function::date_format($booking_list['bp_time'] ?? '')); ?></p>
                                </td>
                                <th><?php ABPTB_Layout::ticket_info($ticket_infos); ?></th>
                                <?php if (ABPTB_Function::on_off('additional_info')) { ?>
                                    <td><?php ABPTB_Layout::additional_info($additional_infos); ?></td>
                                <?php } ?>
                                <th><?php echo $price > 0 ? wp_kses_post(wc_price($price)) : esc_html__('FREE', 'abp-transport-booking'); ?></th>
                                <?php if (ABPTB_Function::on_off('additional_info')) { ?>
                                    <th><?php echo $ex_price > 0 ? wp_kses_post(wc_price($ex_price)) : esc_html__('FREE', 'abp-transport-booking'); ?></th>
                                <?php } ?>
                                <th><?php echo $total_price > 0 ? wp_kses_post(wc_price($total_price)) : esc_html__('FREE', 'abp-transport-booking'); ?></th>
                                <th>
                                    <span class="abp_tag _text_capitalize <?php echo esc_attr($order_status); ?>"> <?php echo esc_html(ABPTB_Layout::status_text($order_status)); ?></span>
                                </th>
                                <th class="_text_capitalize"><?php echo esc_html($booking_list['payment_method'] ?? ''); ?></th>
                                <td>
                                    <div class="load_more">
                                        <?php ABPTB_Layout::billing_info($booking_list); ?>
                                        <span class="load_more_action" data-less="<?php esc_attr_e('....Less ', 'abp-transport-booking'); ?>" data-more="<?php esc_attr_e('....More', 'abp-transport-booking'); ?>"><?php esc_html_e('.... More', 'abp-transport-booking'); ?></span>
                                    </div>
                                </td>
                                <?php if (ABPTB_Function::on_off('client_info')) { ?>
                                    <td>
                                        <?php if (!empty($passenger_infos)) { ?>
                                            <div class="load_more">
                                                <?php ABPTB_Layout::client_info($passenger_infos); ?>
                                                <span class="load_more_action" data-less="<?php esc_html_e('....Less ', 'abp-transport-booking'); ?>" data-more="<?php esc_html_e('.... More', 'abp-transport-booking'); ?>"><?php esc_html_e('.... More', 'abp-transport-booking'); ?></span>
                                            </div>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                            </tr>
                            <?php $si++;
                        } ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="<?php echo esc_attr($count_foot_left_col); ?>"><?php esc_html_e('Total Summary', 'abp-transport-booking'); ?></th>
                            <th><?php echo (!empty($total_price) && $total_price > 0) ? wp_kses_post(wc_price($total_price)) : esc_html__('FREE', 'abp-transport-booking'); ?></th>
                            <?php if (ABPTB_Function::on_off('additional_info')) { ?>
                                <th><?php echo (!empty($total_additional) && $total_additional > 0) ? wp_kses_post(wc_price($total_additional)) : esc_html__('FREE', 'abp-transport-booking'); ?></th>
                            <?php } ?>
                            <th><?php echo (!empty($total_sale) && $total_sale > 0) ? wp_kses_post(wc_price($total_sale)) : esc_html__('FREE', 'abp-transport-booking'); ?></th>
                            <th colspan="<?php echo esc_attr($count_foot_right_col); ?>"></th>
                        </tr>
                        </tfoot>
                    </table>
                <?php } else {
                    ABPTB_Layout::layout_warning_info('no_order_found');
                }
                do_action('abptb_pagination', ['page_item' => $limit, 'page_number' => $page_number, 'total' => $total_order, 'style' => 'ajax']); ?>
                <?php
            }
            public function load_order_list(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                ob_start();
                $filter_args = isset($_POST) ? array_map('sanitize_text_field', wp_unslash($_POST)) : [];
                $limit = isset($filter_args['page_item']) ? (int)$filter_args['page_item'] : 20;
                $data_limit = (int)ABPTB_Function::get_option('abptb_per_page_item', 20);
                $filter_args['page_item'] = $limit > 0 ? $limit : $data_limit;
                if ($limit > 0 && $data_limit !== $limit) {
                    update_option('abptb_per_page_item', $limit);
                }
                $this->order_lists($filter_args);
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'msg' => esc_html__('Order Loaded Successfully !', 'abp-transport-booking'), 'type' => 'success']);
            }
            public function item_cancel(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $item_id = isset($_POST['item_id']) ? sanitize_text_field(wp_unslash($_POST['item_id'])) : '';
                if (!empty($item_id)) {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'abptb_orders';
                    $booking_lists = ABPTB_Query::get_booking_query(['id' => $item_id]);
                    if (!empty($booking_lists) && is_array($booking_lists)) {
                        $value = current($booking_lists);
                        $others = $value['others'] ?? '';
                        if (!empty($others)) {
                            $others = json_decode($others, true) ?: [];
                            $user_id = get_current_user_id();
                            $others['cancel_by'] = $user_id;
                            $data = [
                                'others' => wp_json_encode($others),
                                'order_status' => 'wc-cancelled',
                                'updated_at' => current_time('Y-m-d H:i:s')
                            ];
                            $where = ['id' => (int)$item_id];
                            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                            $wpdb->update($table_name, $data, $where, ['%s', '%s', '%s'], ['%d']);
                        }
                    }
                    wp_send_json_success(['html' => '', 'msg' => esc_html__('Deleted Successfully !', 'abp-transport-booking'), 'type' => 'success']);
                }
                wp_send_json_error(['html' => '', 'msg' => esc_html__('Something Error Occurred !', 'abp-transport-booking'), 'type' => 'warn']);
            }
        }
        new ABPTB_Orders();
    }