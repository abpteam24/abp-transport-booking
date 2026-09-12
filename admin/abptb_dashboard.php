<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Dashboard')) {
        class ABPTB_Dashboard {
            public function __construct() {
                add_action('abptb_load_dashboard', array($this, 'load_dashboard'), 10);
                add_action('wp_ajax_abptb_journey_popup', array($this, 'journey_popup'));
                add_action('wp_ajax_abptb_wc_config', array($this, 'wc_config'));
                add_action('wp_ajax_abptb_create_page', array($this, 'create_page'));
                add_action('wp_ajax_abptb_import_dummy', array($this, 'import_dummy'));
                add_action('wp_ajax_abptb_delete_dummy', array($this, 'delete_dummy'));
            }
            public function load_dashboard($abptb_info): void {
                $label = ABPTB_Function::label();
                $kpi = self::kpi_data();
                ?>
                <div class="abptb_dashboard">
                    <?php $this->hero($label); ?>
                    <?php $this->kpi_cards($abptb_info, $kpi); ?>
                    <div class="dash_columns">
                        <div class="dash_main">
                            <?php $this->upcoming_journeys($kpi['upcoming'] ?? array()); ?>
                            <?php $this->orders_summary(); ?>
                        </div>
                        <aside class="dash_side">
                            <?php do_action('abptb_dashboard_sidebar'); ?>
                            <?php $this->quick_actions(); ?>
                            <?php $this->system_status($abptb_info, $label); ?>
                            <?php $this->content_breakdown($abptb_info); ?>
                        </aside>
                    </div>
                </div>
                <?php
            }
            //=============================//
            private function hero($label): void {
                $user = wp_get_current_user();
                $name = $user && !empty($user->display_name) ? $user->display_name : __('Admin', 'abp-transport-booking');
                ?>
                <div class="dash_hero">
                    <div class="dash_hero_main">
                        <div class="dash_hero_text">
                            <span class="dash_hero_date"><i class="far fa-calendar-alt"></i> <?php echo esc_html(ABPTB_Function::date_format(current_time('Y-m-d'))); ?></span>
                            <h2>
                                <?php
                                    /* translators: %s: current user display name. */
                                    printf(esc_html__('Welcome back, %s!', 'abp-transport-booking'), esc_html($name));
                                ?>
                            </h2>
                            <p>
                                <?php
                                    /* translators: %s: transport label. */
                                    printf(esc_html__('Here is what is happening with your %s booking business today.', 'abp-transport-booking'), esc_html($label));
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="dash_hero_side">
                        <div class="dash_hero_actions">
                            <a class="dash_btn dash_btn_solid" href="<?php echo esc_url(admin_url('post-new.php?post_type=' . ABPTB_Function::get_cpt())); ?>">
                                <i class="fas fa-plus"></i> <?php echo esc_html($label); ?>
                            </a>
                            <a class="dash_btn dash_btn_ghost" href="<?php echo esc_url(ABPTB_Function::build_url('orders')); ?>">
                                <i class="fas fa-file-invoice"></i> <?php esc_html_e('Orders', 'abp-transport-booking'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }
            private function kpi_cards($abptb_info, $kpi): void {
                $currency = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '';
                $revenue = $currency . number_format_i18n((float)($kpi['revenue'] ?? 0), 2);
                $cards = array(
                    array('theme', 'fas fa-bus', (string)($abptb_info['total_post'] ?? 0), __('Total Transport', 'abp-transport-booking'), ABPTB_Function::build_url('posts')),
                    array('navy', 'fas fa-file-invoice', (string)($abptb_info['total_order'] ?? 0), __('Total Orders', 'abp-transport-booking'), ABPTB_Function::build_url('orders')),
                    array('success', 'fas fa-ticket-alt', (string)($kpi['tickets'] ?? 0), __('Tickets Sold', 'abp-transport-booking'), ABPTB_Function::build_url('orders')),
                    array('purple', 'fas fa-money-bill-wave', $revenue, __('Total Revenue', 'abp-transport-booking'), ABPTB_Function::build_url('orders')),
                    array('warning', 'fas fa-calendar-day', (string)($kpi['today'] ?? 0), __('Booked Today', 'abp-transport-booking'), ABPTB_Function::build_url('orders')),
                );
                ?>
                <div class="dash_kpi_grid">
                    <?php foreach ($cards as $card) { ?>
                        <a class="dash_kpi" href="<?php echo esc_url($card[4]); ?>">
                            <span class="dash_kpi_icon <?php echo esc_attr($card[0]); ?>"><i class="<?php echo esc_attr($card[1]); ?>"></i></span>
                            <span class="dash_kpi_body">
                                <span class="dash_kpi_value"><?php echo esc_html($card[2]); ?></span>
                                <span class="dash_kpi_label"><?php echo esc_html($card[3]); ?></span>
                            </span>
                        </a>
                    <?php } ?>
                </div>
                <?php
            }
            private function upcoming_journeys($upcoming): void {
                ?>
                <div class="dash_card">
                    <div class="dash_card_head">
                        <h4 class="abp"><i class="fas fa-route _color_theme"></i> <?php esc_html_e('Today Trips', 'abp-transport-booking'); ?></h4>
                        <a class="_btn_light_theme_xs" href="<?php echo esc_url(ABPTB_Function::build_url('orders')); ?>"><?php esc_html_e('View All', 'abp-transport-booking'); ?> <i class="fas fa-angle-right"></i></a>
                    </div>
                    <div class="dash_card_body dash_card_body_plain">
                        <?php if (!empty($upcoming)) { ?>
                            <div class="dash_rows">
                                <?php foreach ($upcoming as $row) {
                                    $post_id = (int)($row['post_id'] ?? 0);
                                    $start_time = $row['start_time'] ?? '';
                                    $direction = ('return' === ($row['direction'] ?? 'up')) ? 'return' : 'up';
                                    $route = $row['route'] ?? '';
                                    $started = !empty($row['started']);
                                    $sold = (int)($row['sold'] ?? 0);
                                    $available = (int)($row['available'] ?? 0);
                                    $total = (int)($row['total'] ?? 0);
                                    $reserve = (int)($row['reserve'] ?? 0);
                                    ?>
                                    <div class="dash_row dash_journey_row" data-journey data-post="<?php echo esc_attr($post_id); ?>" data-start="<?php echo esc_attr($start_time); ?>" data-direction="<?php echo esc_attr($direction); ?>" title="<?php esc_attr_e('Click to view journey bookings', 'abp-transport-booking'); ?>">
                                        <div class="dash_row_main">
                                            <h6 class="abp_gap_xs"><?php ABPTB_Layout::title(array('post_id' => $post_id)); ?></h6>
                                            <?php if (!empty($route)) { ?>
                                                <small><i class="fas fa-route"></i> <?php echo esc_html($route); ?></small>
                                            <?php } ?>
                                            <small>
                                                <i class="far fa-calendar-alt"></i>
                                                <?php echo esc_html(ABPTB_Function::date_format($start_time)); ?>
                                                <span class="dash_trip_badge dash_trip_badge_<?php echo esc_attr($direction); ?>"><i class="fas fa-<?php echo esc_attr('return' === $direction ? 'reply' : 'arrow-right'); ?>"></i> <?php echo esc_html('return' === $direction ? __('Return', 'abp-transport-booking') : __('Up', 'abp-transport-booking')); ?></span>
                                                <?php if ($started) { ?>
                                                    <span class="dash_trip_state dash_trip_state_started"><i class="fas fa-play-circle"></i> <?php esc_html_e('Journey Started', 'abp-transport-booking'); ?></span>
                                                <?php } ?>
                                            </small>
                                        </div>
                                        <div class="dash_row_meta dash_journey_meta">
                                            <span class="dash_chip dash_chip_sold"><i class="fas fa-ticket-alt"></i> <?php echo esc_html(sprintf(__('Sold %d', 'abp-transport-booking'), $sold)); ?></span>
                                            <span class="dash_chip dash_chip_avail"><i class="fas fa-chair"></i> <?php echo esc_html(sprintf(__('Avail %d', 'abp-transport-booking'), $available)); ?></span>
                                            <span class="dash_chip dash_chip_total"><i class="fas fa-users"></i> <?php echo esc_html(sprintf(__('Total %d', 'abp-transport-booking'), $total)); ?></span>
                                            <?php if ($reserve > 0) { ?>
                                                <span class="dash_chip dash_chip_reserve"><i class="fas fa-lock"></i> <?php echo esc_html(sprintf(__('Reserve %d', 'abp-transport-booking'), $reserve)); ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="dash_journey_hint"><i class="fas fa-mouse-pointer"></i> <?php esc_html_e('Click a journey to see its booking details.', 'abp-transport-booking'); ?></div>
                        <?php } else { ?>
                            <div class="dash_empty">
                                <i class="fas fa-calendar-check"></i>
                                <p><?php esc_html_e('No upcoming journeys scheduled right now.', 'abp-transport-booking'); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            private function quick_actions(): void {
                $actions = array(
                    array('fas fa-toggle-on', __('ON/OFF Configuration', 'abp-transport-booking'), __('Enable & disable features', 'abp-transport-booking'), ABPTB_Function::build_url('configuration', ['configuration' => 'on_off']), 'warning'),
                    array('fas fa-calendar-days', __('Global Date', 'abp-transport-booking'), __('Manage global dates', 'abp-transport-booking'), ABPTB_Function::build_url('global', ['global' => 'dates']), 'theme'),
                );
                if (ABPTB_Function::on_off('additional_info')) {
                    $actions[] = array('fas fa-list-check', __('Additional Services', 'abp-transport-booking'), __('Manage additional services', 'abp-transport-booking'), ABPTB_Function::build_url('global', ['global' => 'additional']), 'success');
                }
                if (ABPTB_Function::on_off('client_info')) {
                    $actions[] = array('fas fa-user', __('Client Form', 'abp-transport-booking'), __('Manage client form fields', 'abp-transport-booking'), ABPTB_Function::build_url('global', ['global' => 'client_form']), 'purple');
                }
                $actions[] = array('fas fa-route', __('Stops Configuration', 'abp-transport-booking'), __('Manage stops / locations', 'abp-transport-booking'), ABPTB_Function::build_url('global', ['global' => 'location']), 'navy');
                if (defined('ABPTB_DIR_PRO')) {
                    if (ABPTB_Function::on_off('partial_payment')) {
                        $actions[] = array('fas fa-hand-holding-dollar', __('Partial Payment', 'abp-transport-booking-pro'), __('Configure partial payment', 'abp-transport-booking-pro'), ABPTB_Function::build_url('global', ['global' => 'partial_payment']), 'success');
                    }
                    if ((ABPTB_Function::on_off('seasonal') && ABPTB_Function::on_off('seasonal_global')) || (ABPTB_Function::on_off('early_bird') && ABPTB_Function::on_off('early_bird_global'))) {
                        $actions[] = array('fas fa-percent', __('Global Discount', 'abp-transport-booking-pro'), __('Configure global discounts', 'abp-transport-booking-pro'), ABPTB_Function::build_url('global', ['global' => 'discount']), 'info');
                    }
                    if (ABPTB_Function::on_off('cancel_request')) {
                        $actions[] = array('fas fa-ban', __('Cancel Request', 'abp-transport-booking-pro'), __('Manage cancellation requests', 'abp-transport-booking-pro'), ABPTB_Function::build_url('cancel_requests'), 'warning');
                    }
                }
                ?>
                <div class="dash_card">
                    <div class="dash_card_head">
                        <h4><i class="fas fa-bolt"></i> <?php esc_html_e('Quick Actions', 'abp-transport-booking'); ?></h4>
                    </div>
                    <div class="dash_card_body">
                        <div class="dash_action_grid">
                            <?php foreach ($actions as $action) { ?>
                                <a class="dash_action" href="<?php echo esc_url($action[3]); ?>">
                                    <span class="dash_action_icon <?php echo esc_attr($action[4]); ?>"><i class="<?php echo esc_attr($action[0]); ?>"></i></span>
                                    <span class="dash_action_text">
                                        <b><?php echo esc_html($action[1]); ?></b>
                                        <small><?php echo esc_html($action[2]); ?></small>
                                    </span>
                                    <i class="fas fa-angle-right dash_action_arrow"></i>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            private function orders_summary(): void {
                $last = ABPTB_Query::get_booking_query(array(), 10);
                $today_date = current_time('Y-m-d');
                $today = ABPTB_Query::get_booking_query(array('order_date' => $today_date), 10);
                $today_total = (int)ABPTB_Query::get_booking_query(array('order_date' => $today_date), 0, 0, true);
                ?>
                <div class="dash_card dash_orders_card">
                    <div class="dash_card_head ">
                        <h4 class="abp_gap_xs"><i class="fas fa-receipt"></i> <?php esc_html_e('Recent Orders', 'abp-transport-booking'); ?></h4>
                        <div class="_group_content">
                            <button type="button" class="_btn_light_theme_xs abp_active dash_order_tab" data-dtab="recent"><i class="fas fa-list-ul"></i> <?php esc_html_e('Last 10 Orders', 'abp-transport-booking'); ?></button>
                            <button type="button" class="_btn_light_theme_xs dash_order_tab" data-dtab="today"><i class="fas fa-calendar-day"></i> <?php esc_html_e("Today's Orders", 'abp-transport-booking'); ?></button>
                        </div>
                        <a class="dash_card_link" href="<?php echo esc_url(ABPTB_Function::build_url('orders')); ?>"><?php esc_html_e('All Orders', 'abp-transport-booking'); ?> <i class="fas fa-angle-right"></i></a>
                    </div>
                    <div class="dash_orders_body">
                        <div class="dash_orders_pane abp_active" data-dpane="recent">
                            <?php $this->order_table($last, false); ?>
                        </div>
                        <div class="dash_orders_pane" data-dpane="today">
                            <div class="dash_pane_content">
                                <div class="dash_orders_meta"><?php echo esc_html($today_total); ?><?php echo esc_html(_n('order', 'orders', $today_total, 'abp-transport-booking')); ?></div>
                                <?php $this->order_table($today, false); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            private function order_table($items, $with_total = true): void {
                $totals = array('total' => 0);
                ?>
                <table class="dash_order_table">
                    <thead>
                    <tr>
                        <th><?php esc_html_e('Order / Vehicle', 'abp-transport-booking'); ?></th>
                        <th><?php esc_html_e('Route', 'abp-transport-booking'); ?></th>
                        <th><?php esc_html_e('Date', 'abp-transport-booking'); ?></th>
                        <th><?php esc_html_e('Status', 'abp-transport-booking'); ?></th>
                        <th class="dash_num"><?php esc_html_e('Total', 'abp-transport-booking'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($items)) { ?>
                        <?php foreach ($items as $item) {
                            $order_id = (int)($item['order_id'] ?? 0);
                            $bp_id = (int)($item['bp'] ?? 0);
                            $dp_id = (int)($item['dp'] ?? 0);
                            $route = ($bp_id > 0 && $dp_id > 0) ? ABPTB_Function::location_value($bp_id) . ' - ' . ABPTB_Function::location_value($dp_id) : '&mdash;';
                            $total = (float)($item['total'] ?? 0);
                            $totals['total'] += $total;
                            ?>
                            <tr>
                                <td data-label="<?php esc_attr_e('Order / Vehicle', 'abp-transport-booking'); ?>">
                                    <b class="dash_oid">#<?php echo esc_html($order_id); ?></b>
                                    <?php ABPTB_Layout::title(array('post_id' => (int)($item['post_id'] ?? 0))); ?>
                                </td>
                                <td data-label="<?php esc_attr_e('Route', 'abp-transport-booking'); ?>"><span class="dash_route"><?php echo wp_kses_post($route); ?></span></td>
                                <td data-label="<?php esc_attr_e('Date', 'abp-transport-booking'); ?>"><?php echo esc_html(ABPTB_Function::date_format($item['created_at'] ?? '', 'date')); ?></td>
                                <td data-label="<?php esc_attr_e('Status', 'abp-transport-booking'); ?>"><span class="dash_pill <?php echo esc_attr($item['order_status'] ?? ''); ?>"><?php echo esc_html(ABPTB_Layout::status_text($item['order_status'] ?? '')); ?></span></td>
                                <td class="dash_num" data-label="<?php esc_attr_e('Total', 'abp-transport-booking'); ?>"><?php echo $total > 0 ? wp_kses_post(ABPTB_WC >= 2 ? wc_price($total) : number_format_i18n((float)$total, 2)) : esc_html__('FREE', 'abp-transport-booking'); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($with_total) { ?>
                            <tr class="dash_order_total_row">
                                <td colspan="4"><?php esc_html_e('Total', 'abp-transport-booking'); ?></td>
                                <td class="dash_num"><?php echo wp_kses_post(ABPTB_WC >= 2 ? wc_price($totals['total']) : number_format_i18n((float)$totals['total'], 2)); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="5" class="dash_order_empty"><?php esc_html_e('No orders found.', 'abp-transport-booking'); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <?php
            }
            //=============================//
            public function journey_popup(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
                $start_time = isset($_POST['start_time']) ? sanitize_text_field(wp_unslash($_POST['start_time'])) : '';
                $direction = isset($_POST['direction']) && 'return' === $_POST['direction'] ? 'return' : 'up';
                if ($post_id <= 0 || empty($start_time)) {
                    wp_send_json_error(['msg' => __('Invalid journey.', 'abp-transport-booking'), 'type' => 'warn'], 400);
                }
                $meta = self::journey_meta($post_id, $start_time, -1, -1, $direction);
                $seat_type = $meta['seat_type'] ?? 'ticket';
                ob_start();
                ?>
                <div class="dash_journey_popup">
                    <div class="dash_journey_pop_head">
                        <div>
                            <h4><i class="fas fa-bus"></i> <?php echo esc_html($meta['title'] ?? __('Transport', 'abp-transport-booking')); ?></h4>
                            <?php if (!empty($meta['route'])) { ?>
                                <small><i class="fas fa-route"></i> <?php echo esc_html($meta['route']); ?></small>
                            <?php } ?>
                            <small><i class="far fa-calendar-alt"></i> <?php echo esc_html(ABPTB_Function::date_format($start_time)); ?>
                                <span class="dash_trip_badge dash_trip_badge_<?php echo esc_attr($direction); ?>"><i class="fas fa-<?php echo esc_attr('return' === $direction ? 'reply' : 'arrow-right'); ?>"></i> <?php echo esc_html('return' === $direction ? __('Return Trip', 'abp-transport-booking') : __('Up Trip', 'abp-transport-booking')); ?></span>
                                <?php if (!empty($meta['started'])) { ?>
                                    <span class="dash_trip_state dash_trip_state_started"><i class="fas fa-play-circle"></i> <?php esc_html_e('Journey Started', 'abp-transport-booking'); ?></span>
                                <?php } ?>
                            </small>
                        </div>
                        <span class="dash_chip dash_chip_total"><?php echo esc_html(('sp' === $seat_type) ? __('Seat Plan', 'abp-transport-booking') : __('Ticket Type', 'abp-transport-booking')); ?></span>
                    </div>
                    <div class="dash_journey_stats">
                        <span class="dash_chip dash_chip_sold"><i class="fas fa-ticket-alt"></i> <?php echo esc_html(sprintf(__('Sold %d', 'abp-transport-booking'), $meta['sold'])); ?></span>
                        <span class="dash_chip dash_chip_avail"><i class="fas fa-chair"></i> <?php echo esc_html(sprintf(__('Available %d', 'abp-transport-booking'), $meta['available'])); ?></span>
                        <span class="dash_chip dash_chip_total"><i class="fas fa-users"></i> <?php echo esc_html(sprintf(__('Total %d', 'abp-transport-booking'), $meta['total'])); ?></span>
                        <?php if ($meta['reserve'] > 0) { ?>
                            <span class="dash_chip dash_chip_reserve"><i class="fas fa-lock"></i> <?php echo esc_html(sprintf(__('Reserve %d', 'abp-transport-booking'), $meta['reserve'])); ?></span>
                        <?php } ?>
                    </div>
                    <?php if ('sp' === $seat_type) { ?>
                        <div class="dash_journey_section">
                            <h5><i class="fas fa-chair"></i> <?php esc_html_e('Seat Map', 'abp-transport-booking'); ?></h5>
                            <?php $this->journey_seat_map($post_id, $start_time); ?>
                        </div>
                    <?php } else { ?>
                        <div class="dash_journey_section">
                            <h5><i class="fas fa-tags"></i> <?php esc_html_e('Ticket Type Breakdown', 'abp-transport-booking'); ?></h5>
                            <?php $this->journey_ticket_table($post_id, $start_time); ?>
                        </div>
                    <?php } ?>
                    <div class="dash_journey_section">
                        <h5><i class="fas fa-file-invoice"></i> <?php esc_html_e('Bookings', 'abp-transport-booking'); ?> (<?php echo esc_html($meta['order_count']); ?>)</h5>
                        <?php $this->journey_bookings($post_id, $start_time); ?>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'msg' => esc_html__('Journey loaded.', 'abp-transport-booking'), 'type' => 'success']);
            }
            //=============================//
            private function journey_ticket_table($post_id, $start_time): void {
                $post_infos = ABPTB_Function::get_all_meta($post_id);
                $ticket_infos = $post_infos['ticket_infos'] ?? array();
                $filters = array('post_id' => $post_id, 'start_time' => $start_time, 'cache' => false);
                $sold = ABPTB_Query::get_sold_ticket($filters, true);
                if (empty($ticket_infos) || !is_array($ticket_infos)) {
                    echo '<div class="dash_empty"><i class="fas fa-tag"></i><p>' . esc_html__('No ticket configuration found.', 'abp-transport-booking') . '</p></div>';
                    return;
                }
                ?>
                <table class="dash_journey_table">
                    <thead>
                    <tr>
                        <th><?php esc_html_e('Type', 'abp-transport-booking'); ?></th>
                        <th><?php esc_html_e('Sold', 'abp-transport-booking'); ?></th>
                        <th><?php esc_html_e('Available', 'abp-transport-booking'); ?></th>
                        <th><?php esc_html_e('Total', 'abp-transport-booking'); ?></th>
                        <th><?php esc_html_e('Reserve', 'abp-transport-booking'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ticket_infos as $tic_id => $ticket_info) {
                        $qty = (int)($ticket_info['qty'] ?? 0);
                        $reserve = (int)($ticket_info['reserve'] ?? 0);
                        $sold_qty = (int)($sold[$tic_id] ?? 0);
                        $avail = max(0, $qty - $sold_qty - $reserve);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html(ABPTB_Function::ticket_name($tic_id)); ?></strong>
                            </td>
                            <td><?php echo esc_html($sold_qty); ?></td>
                            <td><?php echo esc_html($avail); ?></td>
                            <td><?php echo esc_html($qty); ?></td>
                            <td><?php echo esc_html($reserve); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <?php
            }
            //=============================//
            private function journey_seat_map($post_id, $start_time): void {
                $post_infos = ABPTB_Function::get_all_meta($post_id);
                $sp_infos = $post_infos['sp_infos'] ?? array();
                if (empty($sp_infos) || !is_array($sp_infos)) {
                    echo '<div class="dash_empty"><i class="fas fa-chair"></i><p>' . esc_html__('No seat plan assigned.', 'abp-transport-booking') . '</p></div>';
                    return;
                }
                $filters = array('post_id' => $post_id, 'start_time' => $start_time, 'cache' => false);
                $sold_seat = ABPTB_Query::get_sold_seat($filters, true);
                foreach ($sp_infos as $sp_item) {
                    $sp_id = (int)($sp_item['id'] ?? 0);
                    if ($sp_id <= 0) {
                        continue;
                    }
                    $this->render_seat_plan($sp_id, $sold_seat, $post_infos);
                }
            }
            //=============================//
            private function render_seat_plan($sp_id, $sold_seat, $post_infos): void {
                $row = ABPTB_Query::get_sp($sp_id);
                $sp_info = !empty($row) ? current($row) : array();
                if (empty($sp_info)) {
                    return;
                }
                $others = json_decode($sp_info['others'] ?? '', true) ?: array();
                $cell_width = $others['width'] ?? 40;
                $cell_height = $others['height'] ?? 40;
                $gap = $others['gap'] ?? 0;
                $radius = $others['radius'] ?? 0;
                $cols = intval($others['column'] ?? 10);
                $bg_image = $others['bg_image'] ?? '';
                $img_url = !empty($bg_image) && $bg_image > 0 ? ABPTB_Function::get_image_url('', $bg_image) : '';
                $bg_color = $others['bg_color'] ?? '#fff';
                $layout = json_decode($sp_info['layout_data'] ?? '', true) ?: array();
                $sp_name = $sp_info['name'] ?? '';
                // Multi-row/column cells must hide their "occupied" grid slots.
                $hidden_cells = array();
                foreach ($layout as $index => $cell) {
                    $c_span = intval($cell['width_ratio'] ?? 1);
                    $r_span = intval($cell['height_ratio'] ?? 1);
                    if ($c_span > 1 || $r_span > 1) {
                        for ($r = 0; $r < $r_span; $r++) {
                            for ($c = 0; $c < $c_span; $c++) {
                                if ($r === 0 && $c === 0) {
                                    continue;
                                }
                                $target_idx = $index + ($r * $cols) + $c;
                                $hidden_cells[$target_idx] = true;
                            }
                        }
                    }
                }
                ?>
                <div class="dash_sp_block">
                    <?php if (!empty($sp_name)) { ?>
                        <div class="dash_sp_title"><?php echo esc_html($sp_name); ?></div>
                    <?php } ?>
                    <div class="sp_canvas dash_sp_canvas" style="grid-template-columns: repeat(<?php echo esc_attr($cols); ?>, 1fr); background-image: url('<?php echo esc_url($img_url); ?>'); background-color: <?php echo esc_attr($bg_color); ?>;gap: <?php echo esc_attr($gap); ?>px;">
                        <?php foreach ($layout as $index => $cell) {
                            if (isset($hidden_cells[$index])) {
                                continue;
                            }
                            $type_id = $cell['id'] ?? '';
                            $name = $cell['name'] ?? '';
                            $c_span = intval($cell['width_ratio'] ?? 1);
                            $r_span = intval($cell['height_ratio'] ?? 1);
                            $rotate = intval($cell['rotate'] ?? 0);
                            $fs = $cell['fs'] ?? 12;
                            $is_seat = ('seat' === ($cell['type'] ?? ''));
                            $seat_type_class = $is_seat ? 'available' : '';
                            $seat_type_class = $is_seat && in_array($name, $sold_seat, true) ? 'sold' : $seat_type_class;
                            $class = $is_seat ? 'sp_cell ' . $seat_type_class : 'sp_decor';
                            $color = $is_seat ? ABPTB_Function::ticket_color($type_id) : ABPTB_Function::decor_color($type_id);
                            $icon_image = $is_seat ? ABPTB_Function::ticket_icon($type_id) : ABPTB_Function::decor_icon($type_id);
                            $width = $cell_width * $c_span;
                            $height = $cell_height * $r_span;
                            if ($gap > 0) {
                                $width = $c_span > 1 ? $width + ($c_span - 1) * $gap : $width;
                                $height = $r_span > 1 ? $height + ($r_span - 1) * $gap : $height;
                            }
                            $style = "color: {$color}; grid-column: span {$c_span}; grid-row: span {$r_span}; width:{$width}px;height:{$height}px; border:1px solid  {$color};font-size:{$fs}px;border-radius:{$radius}px;";
                            $image = '';
                            if (!empty($icon_image) && is_numeric($icon_image)) {
                                $image = ABPTB_Function::get_image_url('', $icon_image);
                            }
                            ?>
                            <div class="<?php echo esc_attr($class); ?>" style="<?php echo esc_attr($style); ?>">
                                <div class="cell_content <?php echo esc_attr($rotate ? "rotate-{$rotate}" : ''); ?>" style="background-image: url('<?php echo esc_url($image); ?>');">
                                    <?php ABPTB_Layout::image_icon($icon_image); ?>
                                    <span class="cell_label"><?php echo esc_html($name); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            //=============================//
            private function journey_bookings($post_id, $start_time): void {
                $bookings = ABPTB_Query::get_booking_query(array(
                    'post_id' => $post_id,
                    'start_time' => $start_time,
                    'status' => ABPTB_Function::booking_status_sold(),
                    'cache' => false,
                ));
                if (empty($bookings)) {
                    echo '<div class="dash_empty"><i class="fas fa-file-invoice"></i><p>' . esc_html__('No bookings for this journey yet.', 'abp-transport-booking') . '</p></div>';
                    return;
                }
                ?>
                <div class="dash_journey_bookings">
                    <?php foreach ($bookings as $booking) {
                        $name = $booking['billing_name'] ?? '';
                        $qty = (int)($booking['qty'] ?? 0);
                        $status = $booking['order_status'] ?? '';
                        $ticket_infos = json_decode($booking['ticket_info'] ?? '', true) ?: array();
                        $ticket_text = array();
                        if (is_array($ticket_infos)) {
                            foreach ($ticket_infos as $ticket_info) {
                                $tic_qty = (int)($ticket_info['qty'] ?? 1);
                                $label = ABPTB_Function::ticket_label($ticket_info, $booking);
                                if (!empty($label)) {
                                    $ticket_text[] = esc_html($label) . ' x ' . $tic_qty;
                                }
                            }
                        }
                        ?>
                        <div class="dash_journey_booking">
                            <div class="dash_journey_booking_main">
                                <strong><?php echo esc_html($name ? $name : '#' . esc_html($booking['order_id'] ?? '')); ?></strong>
                                <?php if (!empty($ticket_text)) { ?>
                                    <small><?php echo implode(', ', $ticket_text); ?></small>
                                <?php } ?>
                            </div>
                            <div class="dash_journey_booking_side">
                                <span class="dash_pill <?php echo esc_attr($status); ?>"><?php echo esc_html(ABPTB_Layout::status_text($status)); ?></span>
                                <span class="dash_chip dash_chip_total"><?php echo esc_html('x' . $qty); ?></span>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
//=============================//
            private function system_status($abptb_info, $label): void {
                $total = (int)($abptb_info['total_post'] ?? 0);
                $dummy_total = self::dummy_count();
                ?>
                <div class="dash_card">
                    <div class="dash_card_head">
                        <h4 class="abp_gap_xs"><i class="fas fa-server"></i> <?php esc_html_e('System Status', 'abp-transport-booking'); ?></h4>
                    </div>
                    <div class="dash_card_body dash_card_body_plain">
                        <div class="dash_kv_list">
                            <div class="dash_kv">
                                <span><?php esc_html_e('Plugin', 'abp-transport-booking'); ?></span>
                                <b><?php echo esc_html(ABPTB_VERSION); ?></b>
                            </div>
                            <div class="dash_kv">
                                <span><?php esc_html_e('WordPress', 'abp-transport-booking'); ?></span>
                                <b><?php echo esc_html(get_bloginfo('version')); ?></b>
                            </div>
                            <div class="dash_kv">
                                <span><?php esc_html_e('PHP', 'abp-transport-booking'); ?></span>
                                <b><?php echo esc_html(phpversion()); ?></b>
                            </div>
                            <div class="dash_kv">
                                <span><?php esc_html_e('WooCommerce', 'abp-transport-booking'); ?></span>
                                <?php if (ABPTB_WC >= 2 && defined('WC_VERSION')) { ?>
                                    <b class="<?php echo esc_attr(version_compare(WC_VERSION, '8.0', '>') ? '' : 'dash_status_warn'); ?>"><?php echo esc_html(WC_VERSION); ?></b>
                                <?php } else { ?>
                                    <b class="dash_status_warn"><?php esc_html_e('Not active', 'abp-transport-booking'); ?></b>
                                <?php } ?>
                            </div>
                            <?php if (ABPTB_WC >= 2) {
                                $wc_name = get_option('woocommerce_email_from_name');
                                $wc_email = get_option('woocommerce_email_from_address');
                                if (!empty($wc_name)) { ?>
                                    <div class="dash_kv">
                                        <span><?php esc_html_e('WC Name', 'abp-transport-booking'); ?></span>
                                        <b><?php echo esc_html($wc_name); ?></b>
                                    </div>
                                <?php }
                                if (!empty($wc_email)) { ?>
                                    <div class="dash_kv">
                                        <span><?php esc_html_e('WC Email', 'abp-transport-booking'); ?></span>
                                        <b><?php echo esc_html($wc_email); ?></b>
                                    </div>
                                <?php }
                            } ?>
                            <div class="dash_status_checklist">
                                <?php $this->checklist_item(__('WooCommerce Plugin', 'abp-transport-booking'), ABPTB_WC >= 2, ABPTB_WC == 1 ? 'wc_active' : 'wc_install_active'); ?>
                                <?php $this->checklist_item(__('Booking Page', 'abp-transport-booking'), (bool)ABPTB_Function::get_page_by_slug('tf_booking'), 'tf_booking'); ?>
                                <?php $this->checklist_item(
                                /* translators: %s: transport label. */
                                    sprintf(esc_html__('%s List Page', 'abp-transport-booking'), esc_html($label)),
                                    (bool)ABPTB_Function::get_page_by_slug('tf_post'),
                                    'tf_post'
                                ); ?>
                                <?php $this->checklist_item(__('Gallery Page', 'abp-transport-booking'), (bool)ABPTB_Function::get_page_by_slug('tf_gallery'), 'tf_gallery'); ?>
                                <?php if (ABPTB_WC > 1) {
                                    do_action('abptb_add_page');
                                    do_action('abptb_add_tools');
                                } ?>
                                <?php $this->checklist_item(__('First Transport Added', 'abp-transport-booking'), $total > 0, 'add_new'); ?>
                                <div class="dash_kv">
                                    <span class="dash_status_item is_todo">
                                        <i class="far fa-circle"></i>
                                        <?php esc_html_e('Number of Post', 'abp-transport-booking'); ?>
                                    </span>
                                    <b class="dash_status_ready"><?php echo esc_html($total); ?></b>
                                </div>
                                <div class="dash_kv" id="abptb_dummy_row">
                                    <span class="dash_status_item is_todo">
                                        <i class="far fa-circle"></i>
                                        <?php esc_html_e('Dummy Import', 'abp-transport-booking'); ?>
                                    </span>
                                    <div class="_group_content">
                                        <button class="_btn_light_theme_xxs" onclick="abptb_import_global('dummy')" type="button"><span class="fas fa-plus"></span><?php esc_html_e('Dummy ', 'abp-transport-booking'); ?></button>
                                        <?php if ($dummy_total > 0) { ?>
                                            <button class="_btn_light_warning_xxs" onclick="abptb_delete_dummy()" type="button"><span class="fas fa-trash"></span><?php echo esc_html($dummy_total); ?> <?php esc_html_e('Dymmy', 'abp-transport-booking'); ?></button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            private function checklist_item($label, $done, $fix_type = ''): void {
                ?>
                <div class="dash_kv" <?php echo in_array($fix_type, array('wc_active', 'wc_install_active'), true) ? 'id="abptb_wc_row"' : ''; ?>>
                    <span class="dash_status_item <?php echo esc_attr($done ? 'is_done' : 'is_todo'); ?>">
                        <i class="<?php echo esc_attr($done ? 'fas fa-check-circle' : 'far fa-circle'); ?>"></i>
                        <?php echo esc_html($label); ?>
                    </span>
                    <?php if ($done) { ?>
                        <b class="dash_status_ready"><?php esc_html_e('Ready', 'abp-transport-booking'); ?></b>
                    <?php } elseif ($fix_type === 'add_new') { ?>
                        <a class="dash_status_fix" href="<?php echo esc_url(admin_url('post-new.php?post_type=' . ABPTB_Function::get_cpt())); ?>"><?php esc_html_e('Fix', 'abp-transport-booking'); ?></a>
                    <?php } elseif (in_array($fix_type, array('wc_active', 'wc_install_active'), true)) { ?>
                        <button class="dash_status_fix" onclick="abptb_wc_config('<?php echo esc_attr($fix_type); ?>', this)" type="button">
                            <?php echo esc_html($fix_type === 'wc_active' ? __('Active Now', 'abp-transport-booking') : __('Install & Activate', 'abp-transport-booking')); ?>
                        </button>
                    <?php } elseif (!empty($fix_type)) { ?>
                        <button class="dash_status_fix" onclick="abptb_create_page('<?php echo esc_attr($fix_type); ?>')" type="button"><?php esc_html_e('Fix', 'abp-transport-booking'); ?></button>
                    <?php } ?>
                </div>
                <?php
            }
            private function content_breakdown($abptb_info): void {
                $rows = array(
                    array('publish', __('Published', 'abp-transport-booking'), (int)($abptb_info['total_publish'] ?? 0)),
                    array('draft', __('Draft', 'abp-transport-booking'), (int)($abptb_info['total_draft'] ?? 0)),
                    array('private', __('Private', 'abp-transport-booking'), (int)($abptb_info['total_private'] ?? 0)),
                    array('trash', __('Trash', 'abp-transport-booking'), (int)($abptb_info['total_trash'] ?? 0)),
                );
                $sum = 0;
                foreach ($rows as $row) {
                    $sum += $row[2];
                }
                ?>
                <div class="dash_card">
                    <div class="dash_card_head">
                        <h4 class="abp_gap_xs"><i class="fas fa-layer-group"></i> <?php esc_html_e('Content Breakdown', 'abp-transport-booking'); ?></h4>
                    </div>
                    <div class="dash_card_body">
                        <div class="dash_bar">
                            <?php foreach ($rows as $row) {
                                $width = $sum > 0 ? (int)round(($row[2] / $sum) * 100) : 0;
                                if ($row[2] > 0 && $width < 1) {
                                    $width = 1;
                                }
                                ?>
                                <span class="<?php echo esc_attr($row[0]); ?>" style="width:<?php echo esc_attr($width); ?>%;" title="<?php echo esc_attr($row[1] . ': ' . $row[2]); ?>"></span>
                            <?php } ?>
                        </div>
                        <ul class="dash_legend">
                            <?php foreach ($rows as $row) { ?>
                                <li>
                                    <span class="dash_dot <?php echo esc_attr($row[0]); ?>"></span>
                                    <?php echo esc_html($row[1]); ?>
                                    <b><?php echo esc_html($row[2]); ?></b>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <?php
            }
            //=============================//
            public static function kpi_data(): array {
                $cache_key = 'abptb_dashboard_kpi';
                $cached = wp_cache_get($cache_key, 'abptb_dashboard');
                if (is_array($cached)) {
                    return $cached;
                }
                $data = array('revenue' => 0, 'tickets' => 0, 'today' => 0, 'upcoming' => array());
                $agg = ABPTB_Query::get_booking_query(array('aggregate' => array('total' => 'SUM', 'qty' => 'SUM')));
                if (is_array($agg)) {
                    $data['revenue'] = (float)($agg['total'] ?? 0);
                    $data['tickets'] = (int)($agg['qty'] ?? 0);
                }
                $data['today'] = (int)ABPTB_Query::get_booking_query(array('order_date' => current_time('Y-m-d')), 0, 0, true);
                $data['upcoming'] = self::journey_list();
                wp_cache_set($cache_key, $data, 'abptb_dashboard');
                return $data;
            }
            //=============================//
            /**
             * Build today's journey list (a journey = a transport on a specific start time).
             * Only today's date is considered; every transport scheduled today is shown,
             * including trips whose departure time has already passed.
             */
            public static function journey_list(): array {
                $post_ids = defined('ABPTB_ids') && !empty(ABPTB_ids) ? ABPTB_ids : ABPTB_Query::get_post_id();
                if (empty($post_ids) || !is_array($post_ids)) {
                    return array();
                }
                $today = current_time('Y-m-d');
                $journeys = array();
                foreach ($post_ids as $post_id) {
                    $post_id = (int)$post_id;
                    if ($post_id <= 0) {
                        continue;
                    }
                    $journeys = array_merge($journeys, self::journey_today($post_id, $today));
                }
                if (count($journeys) > 1) {
                    usort($journeys, function ($a, $b) {
                        return strtotime($a['start_time'] ?? '') <=> strtotime($b['start_time'] ?? '');
                    });
                }
                return $journeys;
            }
            //=============================//
            private static function journey_today($post_id, $today): array {
                $post_infos = ABPTB_Function::get_all_meta($post_id);
                if (empty($post_infos)) {
                    return array();
                }
                // Is today among the post's scheduled dates?
                $dates = ABPTB_Function::date($post_id, array(), $today);
                $today_is_schedule = is_array($dates) && in_array($today, $dates, true);
                // Keep listing transports whose trips have already started today.
                if (!$today_is_schedule && !self::is_schedule_day($post_infos, $today)) {
                    return array();
                }
                $journeys = array();
                // Outbound trips: every scheduled departure time today (multi schedule = several times).
                $times = ABPTB_Function::time_operation($post_id, $today);
                if (!empty($times) && is_array($times)) {
                    foreach ($times as $_time) {
                        if ('' === (string)$_time) {
                            continue;
                        }
                        $journeys[] = self::journey_meta($post_id, $today . ' ' . $_time, -1, -1, 'up');
                    }
                }
                // Return trips: separate return timetable when the transport has return active.
                if (self::return_enabled($post_infos)) {
                    $return_times = ABPTB_Function::time_operation($post_id, $today, true);
                    if (!empty($return_times) && is_array($return_times)) {
                        foreach ($return_times as $_time) {
                            if ('' === (string)$_time) {
                                continue;
                            }
                            $journeys[] = self::journey_meta($post_id, $today . ' ' . $_time, -1, -1, 'return');
                        }
                    }
                }
                return $journeys;
            }
            //=============================//
            private static function return_enabled($post_infos): bool {
                if (!ABPTB_Function::on_off('return')) {
                    return false;
                }
                $display_return = $post_infos['display_return'] ?? 'off';
                if ('on' !== $display_return) {
                    return false;
                }
                $return_price_infos = $post_infos['return_price_infos'] ?? array();
                return !empty($return_price_infos) && is_array($return_price_infos);
            }
            //=============================//
            private static function is_schedule_day($post_infos, $today): bool {
                $date_infos = $post_infos['abptb_dates'] ?? array();
                if (empty($date_infos) || !is_array($date_infos)) {
                    return false;
                }
                $date_type = $date_infos['date_type'] ?? 'periodic_date';
                if ('specific_date' === $date_type) {
                    $specific_dates = $date_infos['specific_dates'] ?? array();
                    if (is_array($specific_dates)) {
                        foreach ($specific_dates as $date_item) {
                            if (!empty($date_item) && gmdate('Y-m-d', strtotime($date_item)) === $today) {
                                return true;
                            }
                        }
                    }
                    return false;
                }
                $start_date = $date_infos['periodic_start_date'] ?? '';
                if (empty($start_date) || strtotime($today) < strtotime($start_date)) {
                    return false;
                }
                $end_date = $date_infos['periodic_end_date'] ?? '';
                $end_date = !empty($end_date) ? gmdate('Y-m-d', strtotime($end_date)) : '';
                $advance_days = (ABPTB_Date_Config['advance_date_number'] ?? null) ?: 28;
                $calc_end = gmdate('Y-m-d', strtotime($start_date . ' +' . $advance_days . ' day'));
                if (!empty($end_date) && strtotime($end_date) < strtotime($calc_end)) {
                    $calc_end = $end_date;
                }
                return in_array($today, ABPTB_Function::date_list_modify($start_date, $calc_end, $date_infos), true);
            }
            //=============================//
            /**
             * Compute the stats (total, sold, available, reserve, seat_type) for a single journey.
             *
             * @param int $post_id Transport post ID.
             * @param string $start_time Exact journey datetime.
             * @param int $sold_qty Pre-computed sold quantity (optional).
             * @param int $order_count Number of distinct orders (optional).
             * @param string $direction 'up' or 'return' trip direction.
             */
            public static function journey_meta($post_id, $start_time, $sold_qty = -1, $order_count = -1, $direction = 'up'): array {
                $post_id = (int)$post_id;
                $post_infos = ABPTB_Function::get_all_meta($post_id);
                $seat_type = $post_infos['seat_type'] ?? 'sp';
                $seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
                $total = (int)(ABPTB_Function::get_total_qty($post_id, $post_infos));
                $filters = array('post_id' => $post_id, 'start_time' => $start_time, 'cache' => false);
                // Sold quantity.
                if ($sold_qty < 0) {
                    $sold = ABPTB_Query::get_sold_ticket($filters, true);
                    $sold_qty = (int)($sold['total'] ?? 0);
                }
                if ($order_count < 0) {
                    $order_count = count(ABPTB_Query::get_booking_query(array(
                        'post_id' => $post_id,
                        'start_time' => $start_time,
                        'status' => ABPTB_Function::booking_status_sold(),
                        'cache' => false,
                    )));
                }
                // Reserve quantity (only meaningful for ticket-type seating).
                $reserve = 0;
                if ('ticket' === $seat_type) {
                    $ticket_infos = $post_infos['ticket_infos'] ?? array();
                    if (is_array($ticket_infos)) {
                        foreach ($ticket_infos as $ticket_info) {
                            $reserve += (int)($ticket_info['reserve'] ?? 0);
                        }
                    }
                }
                $available = $total - $sold_qty - $reserve;
                if ($available < 0) {
                    $available = 0;
                }
                // Route label of the trip direction (start -> end point).
                $is_return = 'return' === $direction;
                $route_infos = $is_return ? ($post_infos['return_routing_infos'] ?? array()) : ($post_infos['routing_infos'] ?? array());
                $route = '';
                if (!empty($route_infos) && is_array($route_infos)) {
                    $bp = array_key_first($route_infos);
                    $dp = array_key_last($route_infos);
                    if ($bp !== $dp && null !== $bp && null !== $dp) {
                        $route = ABPTB_Function::location_value($bp) . ' -> ' . ABPTB_Function::location_value($dp);
                    }
                }
                return array(
                    'post_id' => $post_id,
                    'start_time' => $start_time,
                    'title' => get_the_title($post_id),
                    'seat_type' => $seat_type,
                    'direction' => $is_return ? 'return' : 'up',
                    'route' => $route,
                    'started' => strtotime($start_time) <= current_time('timestamp'),
                    'sold' => (int)$sold_qty,
                    'available' => $available,
                    'total' => $total,
                    'reserve' => $reserve,
                    'order_count' => (int)$order_count,
                );
            }
            //=============================//
            public function wc_config(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $page_type = $post_val('type');
                if ($page_type == 'wc_install_active') {
                    include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
                    include_once(ABSPATH . 'wp-admin/includes/file.php');
                    include_once(ABSPATH . 'wp-admin/includes/misc.php');
                    include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
                    $plugin = 'woocommerce';
                    $api = plugins_api('plugin_information', array(
                        'slug' => $plugin,
                        'fields' => array(
                            'short_description' => false,
                            'sections' => false,
                            'requires' => false,
                            'rating' => false,
                            'ratings' => false,
                            'downloaded' => false,
                            'last_updated' => false,
                            'added' => false,
                            'tags' => false,
                            'compatibility' => false,
                            'homepage' => false,
                            'donate_link' => false,
                        ),
                    ));
                    if (is_wp_error($api)) {
                        wp_send_json_error(['html' => '', 'msg' => $api->get_error_message()]);
                    }
                    $title = 'title';
                    $url = 'url';
                    $nonce = 'nonce';
                    $woocommerce_plugin = new Plugin_Upgrader(new Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')));
                    $installed = $woocommerce_plugin->install($api->download_link);
                    if (is_wp_error($installed)) {
                        wp_send_json_error(['msg' => $installed->get_error_message(), 'type' => 'warn']);
                    }
                    $activated = activate_plugin('woocommerce/woocommerce.php');
                    if (is_wp_error($activated)) {
                        wp_send_json_error(['msg' => $activated->get_error_message(), 'type' => 'warn']);
                    }
                    wp_send_json_success(['msg' => esc_html__('WooCommerce installed and activated successfully!', 'abp-transport-booking'), 'type' => 'success'], 200);
                }
                if ($page_type == 'wc_active') {
                    if (defined('ABPTB_WC') && ABPTB_WC == 1) {
                        $activated = activate_plugin('woocommerce/woocommerce.php');
                        if (is_wp_error($activated)) {
                            wp_send_json_error(['msg' => $activated->get_error_message(), 'type' => 'warn']);
                        }
                        wp_send_json_success(['msg' => esc_html__('WooCommerce activated successfully!', 'abp-transport-booking'), 'type' => 'success'], 200);
                    }
                }
                wp_send_json_error(['msg' => esc_html__('WooCommerce is either not installed or already active.', 'abp-transport-booking'), 'type' => 'warn'], 403);
            }
            public function create_page(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $page_type = $post_val('type');
                if (!empty($page_type)) {
                    if (!ABPTB_Function::get_page_by_slug($page_type)) {
                        $label = ABPTB_Function::label();
                        $short_code = '';
                        if ($page_type == 'tf_booking') {
                            $label = __('Booking', 'abp-transport-booking');
                            $short_code = '[abptb-booking]';
                        }
                        if ($page_type == 'tf_post') {
                            $short_code = '[abptb-post]';
                        }
                        if ($page_type == 'tf_gallery') {
                            $label = __('Gallery', 'abp-transport-booking');
                            $short_code = '[abptb-gallery]';
                        }
                        if ($page_type == 'tf_ticket') {
                            $label = __('Ticket', 'abp-transport-booking');
                            $short_code = '[abptb-ticket]';
                        }
                        $page = array(
                            'post_type' => 'page',
                            'post_name' => $page_type,
                            'post_title' => $label,
                            'post_content' => $short_code,
                            'post_status' => 'publish',
                        );
                        $post_id = wp_insert_post($page);
                        if (is_wp_error($post_id) || 0 === $post_id) {
                            wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('Failed to create page.', 'abp-transport-booking')]);
                        }
                        flush_rewrite_rules();
                        /* translators: %s: Transport Label */
                        $translated_format = esc_html__('%s Page Created successfully.....', 'abp-transport-booking');
                        $msg = sprintf($translated_format, $label);
                        wp_send_json_success(['type' => 'success', 'msg' => $msg]);
                    }
                    wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('Page already exists.', 'abp-transport-booking')]);
                } else {
                    wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('Something Wrong...!', 'abp-transport-booking')]);
                }
            }
            public function import_dummy(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $dummy_infos = $this->dummy_data();
                if (isset($dummy_infos['taxonomy'])) {
                    foreach ($dummy_infos['taxonomy'] as $tax => $taxonomy_option) {
                        if (taxonomy_exists($tax)) {
                            $check_terms = get_terms(array('taxonomy' => $tax, 'hide_empty' => false));
                            if (is_string($check_terms) || sizeof($check_terms) == 0) {
                                foreach ($taxonomy_option as $taxonomy_data) {
                                    unset($term);
                                    $term = wp_insert_term($taxonomy_data['name'], $tax);
                                }
                            }
                        }
                    }
                    do_action('abptb_location_update');
                    do_action('abptb_category_update');
                    do_action('abptb_organizer_update');
                    do_action('abptb_brand_update');
                }
                if (isset($dummy_infos['options'])) {
                    foreach ($dummy_infos['options'] as $option => $dummy_option) {
                        $option_data = get_option($option);
                        if (empty($option_data)) {
                            update_option($option, $dummy_option);
                        }
                    }
                }
                if (isset($dummy_infos['custom_post'])) {
                    $dummy_posts = $this->dummy();
                    foreach ($dummy_posts as $dummy_data) {
                        $args = array();
                        if (isset($dummy_data['name'])) {
                            $args['post_title'] = $dummy_data['name'];
                        }
                        $args['post_status'] = 'publish';
                        $args['post_type'] = ABPTB_Function::get_cpt();
                        $post_id = wp_insert_post($args);
                        $post_data = $dummy_data['post_data'] ?? [];
                        if (!empty($post_data)) {
                            foreach ($post_data as $meta_key => $data) {
                                update_post_meta($post_id, $meta_key, $data);
                            }
                        }
                    }
                }
                flush_rewrite_rules();
                wp_send_json_success([
                    'msg' => esc_html__('Dummy data imported successfully!', 'abp-transport-booking')
                ]);
            }
            public function delete_dummy(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $dummy_posts = self::dummy_ids();
                if (empty($dummy_posts)) {
                    wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('No dummy data found to delete.', 'abp-transport-booking')]);
                }
                foreach ($dummy_posts as $post_id) {
                    wp_delete_post((int)$post_id, true);
                }
                flush_rewrite_rules();
                wp_send_json_success(['type' => 'success', 'msg' => esc_html__('Dummy data deleted successfully!', 'abp-transport-booking')]);
            }
            public static function dummy_count(): int {
                return count(self::dummy_ids());
            }
            private static function dummy_ids(): array {
                $args = array(
                    'post_type' => ABPTB_Function::get_cpt(),
                    'post_status' => 'any',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'meta_key' => 'dummy',
                    'meta_value' => 'on',
                );
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                $posts = get_posts($args);
                return is_array($posts) ? $posts : array();
            }
            public function dummy_data(): array {
                return [
                    'taxonomy' => [
                        'abptb_location' => ABPTB_Static::static_location(),
                        'abptb_category' => ABPTB_Static::static_category(),
                        'abptb_organizer' => ABPTB_Static::static_organizer(),
                        'abptb_brand' => ABPTB_Static::static_brand(),
                    ],
                    'options' => [
                        'abptb_ticket' => ABPTB_Static::static_ticket(),
                        'abptb_decor' => ABPTB_Static::static_decoration(),
                        'abptb_additional' => ABPTB_Static::static_additional(),
                        'abptb_form' => ABPTB_Static::static_form(),
                        'abptb_faq' => ABPTB_Static::static_faq(),
                        'abptb_tc' => ABPTB_Static::static_tc(),
                        'abptb_feature' => ABPTB_Static::static_feature(),
                    ],
                    'custom_post' => []
                ];
            }
            public function static_sp(): void {
                $sp_data = ABPTB_Query::get_sp();
                if (empty($sp_data)) {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'abptb_sp';
                    $bus_plan_data_1 = [
                        'name' => uniqid('sp_'),
                        'total_seats' => 41,
                        'others' => '{"bg_image":"0","bg_color":"#fff","row":11,"column":5,"width":60,"height":60,"gap":5,"radius":5}',
                        'layout_data' => '[{"index":"0","type":"other","id":"1","name":"Entrance","width_ratio":"3","fs":"15"},{"index":"1","type":"other","id":"1","name":""},{"index":"2","type":"other","id":"1","name":""},{"index":"3","type":"other","id":"2","name":"Driver","width_ratio":"2","fs":"14"},{"index":"4","type":"other","id":"1","name":""},{"index":"5","type":"seat","id":"3","name":"B-1"},{"index":"6","type":"seat","id":"3","name":"B-2"},{"index":"7","type":"other","id":"1","name":"Passenger Aisle","height_ratio":"9","rotate":"90","fs":"20"},{"index":"8","type":"seat","id":"3","name":"B-3"},{"index":"9","type":"seat","id":"3","name":"B-4"},{"index":"10","type":"seat","id":"5","name":"C-1"},{"index":"11","type":"seat","id":"5","name":"C-2"},{"index":"12","type":"other","id":"1","name":""},{"index":"13","type":"seat","id":"5","name":"C-3"},{"index":"14","type":"seat","id":"5","name":"C-4"},{"index":"15","type":"seat","id":"6","name":"F-1"},{"index":"16","type":"seat","id":"6","name":"F-2"},{"index":"17","type":"other","id":"1","name":""},{"index":"18","type":"seat","id":"6","name":"F-3"},{"index":"19","type":"seat","id":"6","name":"F-4"},{"index":"20","type":"seat","id":"7","name":"AD-1"},{"index":"21","type":"seat","id":"7","name":"AD-2"},{"index":"22","type":"other","id":"1","name":""},{"index":"23","type":"seat","id":"7","name":"AD-3"},{"index":"24","type":"seat","id":"7","name":"AD-4"},{"index":"25","type":"seat","id":"8","name":"CH-1"},{"index":"26","type":"seat","id":"8","name":"CH-2"},{"index":"27","type":"other","id":"1","name":""},{"index":"28","type":"seat","id":"8","name":"CH-3"},{"index":"29","type":"seat","id":"8","name":"CH-4"},{"index":"30","type":"seat","id":"2","name":"VIP-1"},{"index":"31","type":"seat","id":"2","name":"VIP-2"},{"index":"32","type":"other","id":"1","name":""},{"index":"33","type":"seat","id":"2","name":"VIP-3"},{"index":"34","type":"seat","id":"2","name":"VIP-4"},{"index":"35","type":"seat","id":"4","name":"S-1"},{"index":"36","type":"seat","id":"4","name":"S-2"},{"index":"37","type":"other","id":"1","name":""},{"index":"38","type":"seat","id":"4","name":"S-3"},{"index":"39","type":"seat","id":"4","name":"S-4"},{"index":"40","type":"seat","id":"4","name":"S-5"},{"index":"41","type":"seat","id":"4","name":"S-6"},{"index":"42","type":"other","id":"1","name":""},{"index":"43","type":"seat","id":"4","name":"S-7"},{"index":"44","type":"seat","id":"4","name":"S-8"},{"index":"45","type":"seat","id":"3","name":"B-5"},{"index":"46","type":"seat","id":"3","name":"B-6"},{"index":"47","type":"other","id":"1","name":""},{"index":"48","type":"seat","id":"3","name":"B-7"},{"index":"49","type":"seat","id":"3","name":"B-8"},{"index":"50","type":"seat","id":"9","name":"E-1"},{"index":"51","type":"seat","id":"9","name":"E-2"},{"index":"52","type":"seat","id":"9","name":"E-3"},{"index":"53","type":"seat","id":"9","name":"E-4"},{"index":"54","type":"seat","id":"9","name":"E-5"}]',
                        'seat_info' => '{"2":"4","3":"8","4":"8","5":"4","6":"4","7":"4","8":"4","9":"5"}'
                    ];
                    $bus_plan_data_2 = [
                        'name' => uniqid('sp_'),
                        'total_seats' => 40,
                        'others' => '{"bg_image":"0","bg_color":"#fff","row":11,"column":5,"width":60,"height":60,"gap":5,"radius":5}',
                        'layout_data' => '[{"index":"0","type":"other","id":"1","name":"Entance","width_ratio":"3","fs":"15"},{"index":"1","type":"other","id":"1","name":""},{"index":"2","type":"other","id":"1","name":""},{"index":"3","type":"other","id":"2","name":"","width_ratio":"2"},{"index":"4","type":"other","id":"1","name":""},{"index":"5","type":"seat","id":"3","name":"A-1"},{"index":"6","type":"seat","id":"3","name":"A-2"},{"index":"7","type":"other","id":"1","name":"Passenger Walkway","height_ratio":"10","rotate":"90","fs":"18"},{"index":"8","type":"seat","id":"3","name":"A-3"},{"index":"9","type":"seat","id":"3","name":"A-4"},{"index":"10","type":"seat","id":"3","name":"B-1"},{"index":"11","type":"seat","id":"3","name":"B-2"},{"index":"12","type":"other","id":"1","name":""},{"index":"13","type":"seat","id":"3","name":"B-3"},{"index":"14","type":"seat","id":"3","name":"B-4"},{"index":"15","type":"seat","id":"3","name":"C-1"},{"index":"16","type":"seat","id":"3","name":"C-2"},{"index":"17","type":"other","id":"1","name":""},{"index":"18","type":"seat","id":"3","name":"C-3"},{"index":"19","type":"seat","id":"3","name":"C-4"},{"index":"20","type":"seat","id":"3","name":"D-1"},{"index":"21","type":"seat","id":"3","name":"D-2"},{"index":"22","type":"other","id":"1","name":""},{"index":"23","type":"seat","id":"3","name":"D-3"},{"index":"24","type":"seat","id":"3","name":"D-4"},{"index":"25","type":"seat","id":"3","name":"E-1"},{"index":"26","type":"seat","id":"3","name":"E-2"},{"index":"27","type":"other","id":"1","name":""},{"index":"28","type":"seat","id":"3","name":"E-3"},{"index":"29","type":"seat","id":"3","name":"E-4"},{"index":"30","type":"seat","id":"3","name":"F-1"},{"index":"31","type":"seat","id":"3","name":"F-2"},{"index":"32","type":"other","id":"1","name":""},{"index":"33","type":"seat","id":"3","name":"F-3"},{"index":"34","type":"seat","id":"3","name":"F-4"},{"index":"35","type":"seat","id":"3","name":"G-1"},{"index":"36","type":"seat","id":"3","name":"G-2"},{"index":"37","type":"other","id":"1","name":""},{"index":"38","type":"seat","id":"3","name":"G-3"},{"index":"39","type":"seat","id":"3","name":"G-4"},{"index":"40","type":"seat","id":"3","name":"H-1"},{"index":"41","type":"seat","id":"3","name":"H-2"},{"index":"42","type":"other","id":"1","name":""},{"index":"43","type":"seat","id":"3","name":"H-3"},{"index":"44","type":"seat","id":"3","name":"H-4"},{"index":"45","type":"seat","id":"3","name":"I-1"},{"index":"46","type":"seat","id":"3","name":"I-2"},{"index":"47","type":"other","id":"1","name":""},{"index":"48","type":"seat","id":"3","name":"I-3"},{"index":"49","type":"seat","id":"3","name":"I-4"},{"index":"50","type":"seat","id":"3","name":"J-1"},{"index":"51","type":"seat","id":"3","name":"J-2"},{"index":"52","type":"other","id":"1","name":""},{"index":"53","type":"seat","id":"3","name":"J-3"},{"index":"54","type":"seat","id":"3","name":"J-4"}]',
                        'seat_info' => '{"3":"40"}'
                    ];
                    $bus_plan_data_3 = [
                        'name' => uniqid('sp_'),
                        'total_seats' => 30,
                        'others' => '{"bg_image":"","bg_color":"#fff","row":11,"column":4,"width":60,"height":60,"gap":5,"radius":5}',
                        'layout_data' => '[{"index":"0","type":"other","id":"1","name":"Entrance","width_ratio":"2","fs":"16"},{"index":"1","type":"other","id":"1","name":""},{"index":"2","type":"other","id":"2","name":"","width_ratio":"2"},{"index":"3","type":"other","id":"1","name":""},{"index":"4","type":"seat","id":"1","name":"A-1"},{"index":"5","type":"other","id":"1","name":"Passenger Access Path","height_ratio":"10","rotate":"90","fs":"16"},{"index":"6","type":"seat","id":"1","name":"A-2"},{"index":"7","type":"seat","id":"1","name":"A-3"},{"index":"8","type":"seat","id":"1","name":"B-1"},{"index":"9","type":"other","id":"1","name":""},{"index":"10","type":"seat","id":"1","name":"B-2"},{"index":"11","type":"seat","id":"1","name":"B-3"},{"index":"12","type":"seat","id":"1","name":"C-1"},{"index":"13","type":"other","id":"1","name":""},{"index":"14","type":"seat","id":"1","name":"C-2"},{"index":"15","type":"seat","id":"1","name":"C-3"},{"index":"16","type":"seat","id":"1","name":"D-1"},{"index":"17","type":"other","id":"1","name":""},{"index":"18","type":"seat","id":"1","name":"D-2"},{"index":"19","type":"seat","id":"1","name":"D-3"},{"index":"20","type":"seat","id":"1","name":"E-1"},{"index":"21","type":"other","id":"1","name":""},{"index":"22","type":"seat","id":"1","name":"E-2"},{"index":"23","type":"seat","id":"1","name":"E-3"},{"index":"24","type":"seat","id":"1","name":"F-1"},{"index":"25","type":"other","id":"1","name":""},{"index":"26","type":"seat","id":"1","name":"F-2"},{"index":"27","type":"seat","id":"1","name":"F-3"},{"index":"28","type":"seat","id":"1","name":"G-1"},{"index":"29","type":"other","id":"1","name":""},{"index":"30","type":"seat","id":"1","name":"G-2"},{"index":"31","type":"seat","id":"1","name":"G-3"},{"index":"32","type":"seat","id":"1","name":"H-1"},{"index":"33","type":"other","id":"1","name":""},{"index":"34","type":"seat","id":"1","name":"H-2"},{"index":"35","type":"seat","id":"1","name":"H-3"},{"index":"36","type":"seat","id":"1","name":"I-1"},{"index":"37","type":"other","id":"1","name":""},{"index":"38","type":"seat","id":"1","name":"I-2"},{"index":"39","type":"seat","id":"1","name":"I-3"},{"index":"40","type":"seat","id":"1","name":"J-1"},{"index":"41","type":"other","id":"1","name":""},{"index":"42","type":"seat","id":"1","name":"J-2"},{"index":"43","type":"seat","id":"1","name":"J-3"}]',
                        'seat_info' => '{"1":"30"}'
                    ];
                    $bus_plan_data_4 = [
                        'name' => uniqid('sp_'),
                        'total_seats' => 15,
                        'others' => '{"bg_image":"","bg_color":"#fff","row":11,"column":4,"width":60,"height":60,"gap":5,"radius":5}',
                        'layout_data' => '[{"index":"0","type":"other","id":"1","name":"Entance","width_ratio":"2","fs":"16"},{"index":"1","type":"other","id":"1","name":""},{"index":"2","type":"other","id":"2","name":"Driver","width_ratio":"2","fs":"16"},{"index":"3","type":"other","id":"1","name":""},{"index":"4","type":"seat","id":"4","name":"S-1","height_ratio":"2","fs":"14"},{"index":"5","type":"other","id":"1","name":"Passenger Way","height_ratio":"10","rotate":"90","fs":"18"},{"index":"6","type":"seat","id":"4","name":"S-2","height_ratio":"2","fs":"14"},{"index":"7","type":"seat","id":"4","name":"S-3","height_ratio":"2","fs":"14"},{"index":"8","type":"other","id":"1","name":""},{"index":"9","type":"other","id":"1","name":""},{"index":"10","type":"other","id":"1","name":""},{"index":"11","type":"other","id":"1","name":""},{"index":"12","type":"seat","id":"4","name":"S-4","height_ratio":"2","fs":"14"},{"index":"13","type":"other","id":"1","name":""},{"index":"14","type":"seat","id":"4","name":"S-5","height_ratio":"2","fs":"14"},{"index":"15","type":"seat","id":"4","name":"S-6","height_ratio":"2","fs":"14"},{"index":"16","type":"other","id":"1","name":""},{"index":"17","type":"other","id":"1","name":""},{"index":"18","type":"other","id":"1","name":""},{"index":"19","type":"other","id":"1","name":""},{"index":"20","type":"seat","id":"4","name":"S-7","height_ratio":"2","fs":"14"},{"index":"21","type":"other","id":"1","name":""},{"index":"22","type":"seat","id":"4","name":"S-8","height_ratio":"2","fs":"14"},{"index":"23","type":"seat","id":"4","name":"S-9","height_ratio":"2","fs":"14"},{"index":"24","type":"other","id":"1","name":""},{"index":"25","type":"other","id":"1","name":""},{"index":"26","type":"other","id":"1","name":""},{"index":"27","type":"other","id":"1","name":""},{"index":"28","type":"seat","id":"4","name":"S-10","height_ratio":"2","fs":"14"},{"index":"29","type":"other","id":"1","name":""},{"index":"30","type":"seat","id":"4","name":"S-11","height_ratio":"2","fs":"14"},{"index":"31","type":"seat","id":"4","name":"S-12","height_ratio":"2","fs":"14"},{"index":"32","type":"other","id":"1","name":""},{"index":"33","type":"other","id":"1","name":""},{"index":"34","type":"other","id":"1","name":""},{"index":"35","type":"other","id":"1","name":""},{"index":"36","type":"seat","id":"4","name":"S-13","height_ratio":"2","fs":"14"},{"index":"37","type":"other","id":"1","name":""},{"index":"38","type":"seat","id":"4","name":"S-14","height_ratio":"2","fs":"14"},{"index":"39","type":"seat","id":"4","name":"S-15","height_ratio":"2","fs":"14"},{"index":"40","type":"other","id":"1","name":""},{"index":"41","type":"other","id":"1","name":""},{"index":"42","type":"other","id":"1","name":""},{"index":"43","type":"other","id":"1","name":""}]',
                        'seat_info' => '{"4":"15"}'
                    ];
                    $ticket_infos = ABPTB_Function::get_option('abptb_ticket_sp');
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->insert($table_name, $bus_plan_data_1);
                    $id_1 = $wpdb->insert_id;
                    $ticket_infos[$id_1]['type'] = json_decode($bus_plan_data_1['seat_info'], true);
                    $ticket_infos[$id_1]['total'] = 41;
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->insert($table_name, $bus_plan_data_2);
                    $id_2 = $wpdb->insert_id;
                    $ticket_infos[$id_2]['type'] = json_decode($bus_plan_data_2['seat_info'], true);
                    $ticket_infos[$id_2]['total'] = 40;
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->insert($table_name, $bus_plan_data_3);
                    $id_3 = $wpdb->insert_id;
                    $ticket_infos[$id_3]['type'] = json_decode($bus_plan_data_3['seat_info'], true);
                    $ticket_infos[$id_3]['total'] = 30;
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->insert($table_name, $bus_plan_data_4);
                    $id_4 = $wpdb->insert_id;
                    $ticket_infos[$id_4]['type'] = json_decode($bus_plan_data_4['seat_info'], true);
                    $ticket_infos[$id_4]['total'] = 15;
                    update_option('abptb_ticket_sp', $ticket_infos);
                }
            }
            public function dummy(): array {
                $on_off = ['on', 'off'];
                $template = ["default", "light", "premium"];
                $icon = ["🚌", "🚐", "🚍", "🚎", "fas fa-bus", "fas fa-bus-simple"];
                $all_organizer = ABPTB_Function::get_option('abptb_organizer');
                $organizer = ['Global Transit Group', 'Express Travel Network', 'Premium Coach Services', 'InterCity Transport', 'Continental Bus Lines', 'Smart Mobility Solutions'];
                $all_brands = ABPTB_Function::get_option('abptb_brand');
                $band = ['Mercedes-Benz', 'Volvo', 'Scania', 'IVECO', 'Alexander Dennis', 'Yutong'];
                $all_categories = ABPTB_Function::get_option('abptb_category');
                $categories = ['Express', 'Economy', 'Business', 'Luxury', 'Sleeper', 'Shuttle'];
                $features = ABPTB_Function::get_option('abptb_feature');
                $times = ['09:15', '11:30', '14:00', '18:45', '21:10', '08:15', '10:30', '12:00', '15:45', '20:10'];
                $seat_type = ['ticket', 'sp', 'ticket', 'sp', 'ticket', 'sp'];
                $names = ['Greyhound Express Intercity Bus Service', 'Megabus Affordable City-to-City Travel', 'FlixBus Modern Long Distance Transport', 'Peter Pan Premium Travel Experience', 'Jefferson Lines Regional Bus Network', 'Trailways Comfortable Shuttle Service'];
                $all_data = [];
                $all_route_info = $this->route_info($seat_type);
                for ($i = 0; $i < 6; $i++) {
                    $all_data[$i]['name'] = $names[$i];
                    $all_data[$i]['post_data'] = [
                        'sale_continue' => 'on',
                        'abptb_template' => $template[wp_rand(0, 1)],
                        'display_sku' => 'on',
                        'post_sku' => wp_rand(100, 999),
                        'post_icon' => $icon[wp_rand(0, 5)],
                        'sub_title' => 'Travel comfortably with modern vehicles and professional drivers.',
                        'post_description' => 'Experience hassle-free transportation with well-maintained vehicles, affordable fares, and excellent customer support. Whether traveling for business or leisure, our services ensure comfort, punctuality, and convenience from departure to arrival.',
                        'display_organizer' => $on_off[wp_rand(0, 1)],
                        'abptb_organizer' => $this->get_id($all_organizer, $organizer[$i]),
                        'display_brand' => $on_off[wp_rand(0, 1)],
                        'abptb_brand' => $this->get_id($all_brands, $band[$i]),
                        'display_capacity' => $on_off[wp_rand(0, 1)],
                        'display_category' => $on_off[wp_rand(0, 1)],
                        'abptb_category' => $this->get_id($all_categories, $categories[$i]),
                        'post_feature' => implode(',', array_rand($features, 5)),
                        'abptb_slider' => '10,20,30,40,50,100,60,70,80,90',
                        'active_global_dates' => 'on',
                        'time_infos' => ['time' => array_map(fn($key) => $times[$key], array_rand($times, 2))],
                        'return_time_infos' => ['time' => array_map(fn($key) => $times[$key], array_rand($times, 2))],
                        'display_additional_services' => 'on',
                        'active_global_additional' => 'on',
                        'display_client_form' => 'on',
                        'active_global_form' => 'on',
                        'display_single_form' => $on_off[wp_rand(0, 1)],
                        'display_faq' => 'on',
                        'active_global_faq' => 'on',
                        'display_tc' => 'on',
                        'active_global_tc' => 'on',
                        'dummy' => 'on',
                        'seat_type' => $seat_type[$i],
                        'display_ticket_type' => 'on',
                        'display_return' => 'on',
                        'min_qty' => wp_rand(1, 2),
                        'max_qty' => wp_rand(3, 10),
                        'ticket_infos' => $all_route_info[$i]['ticket_infos'] ?? [],
                        'sp_infos' => $all_route_info[$i]['sp_infos'] ?? [],
                        'all_ticket_type' => $all_route_info[$i]['all_ticket_type'] ?? [],
                        'routing_infos' => $all_route_info[$i]['routing_infos'] ?? [],
                        'return_routing_infos' => $all_route_info[$i]['return_routing_infos'] ?? [],
                        'route_data' => $all_route_info[$i]['route_data'] ?? [],
                        'route_direction' => $all_route_info[$i]['route_direction'] ?? [],
                        'return_route_direction' => $all_route_info[$i]['return_route_direction'] ?? [],
                        'price_infos' => $all_route_info[$i]['price_infos'] ?? [],
                        'return_price_infos' => $all_route_info[$i]['return_price_infos'] ?? [],
                        'price_data' => $all_route_info[$i]['price_data'] ?? [],
                    ];
                }
                return $all_data;
            }
            public function route_info($seat_type = []): array {
                $this->static_sp();
                $options = ABPTB_Function::get_option('abptb_location');
                $ticket_options = ABPTB_Function::get_option('abptb_ticket');
                $random_num = sizeof($ticket_options) > 4 ? 3 : sizeof($ticket_options);
                $all_ticket_type = array_rand($ticket_options, $random_num);
                $routes = self::route_data();
                $all_sp_ticket = ABPTB_Function::get_option('abptb_ticket_sp');
                $sp_id = [];
                if (!empty($all_sp_ticket)) {
                    $sp_id = array_keys($all_sp_ticket);
                }
                $sp_select = '';
                $all_data = [];
                $all_data['sp_id'] = $sp_id[array_rand($sp_id)];
                if (!empty($routes)) {
                    foreach ($routes as $key => $data) {
                        $type = $seat_type[$key] ?? 'ticket';
                        if ($type == 'sp') {
                            $sp_select = $sp_id[array_rand($sp_id)];
                            $tickets = [];
                            $seat_infos = $all_sp_ticket[$sp_select] ?? [];
                            if (!empty($seat_infos)) {
                                $seat_info = $seat_infos['type'] ?? [];
                                if (!empty($seat_info)) {
                                    $tickets = array_merge($tickets, array_keys($seat_info));
                                }
                            }
                            $all_ticket_type = array_values(array_unique($tickets));
                        }
                        $route_info = $data['routing_infos'] ?? [];
                        $prices = $data['price_infos'] ?? [];
                        if (!empty($route_info) && !empty($prices)) {
                            foreach ($route_info as $info) {
                                $stop = $this->get_id($options, ($info['stop'] ?? ''));
                                $all_data[$key]['routing_infos'][$stop]['type'] = $info['type'] ?? '';
                                $all_data[$key]['routing_infos'][$stop]['time'] = $info['time'] ?? '';
                                $all_data[$key]['route_direction'][] = $stop;
                            }
                            foreach ($prices as $info) {
                                $bp = $this->get_id($options, ($info['bp'] ?? ''));
                                $dp = $this->get_id($options, ($info['dp'] ?? ''));
                                $price = $info['price'] ?? 0;
                                $step = 0;
                                $bp_dp = $bp . '_' . $dp;
                                $all_data[$key]['route_data'][] = $bp_dp;
                                foreach ($all_ticket_type as $type_id) {
                                    $all_data[$key]['price_infos'][$bp_dp][$type_id] = $price + $step;
                                    $all_data[$key]['price_data'][$bp_dp][$type_id] = $price + $step;
                                    $step = $step + 5;
                                    if ($type == 'ticket') {
                                        $all_data[$key]['ticket_infos'][$type_id]['qty'] = wp_rand(30, 60);
                                        $all_data[$key]['ticket_infos'][$type_id]['reserve'] = wp_rand(5, 10);
                                        $all_data[$key]['ticket_infos'][$type_id]['min_qty'] = wp_rand(1, 2);
                                        $all_data[$key]['ticket_infos'][$type_id]['max_qty'] = wp_rand(2, 5);
                                    } else {
                                        $all_data[$key]['sp_infos'][0]['id'] = $sp_select;
                                    }
                                }
                            }
                            $all_data[$key]['all_ticket_type'] = $all_ticket_type;
                        }
                        $route_info = $data['return_routing_infos'] ?? [];
                        $prices = $data['return_price_infos'] ?? [];
                        if (!empty($route_info) && !empty($prices)) {
                            foreach ($route_info as $info) {
                                $stop = $this->get_id($options, ($info['stop'] ?? ''));
                                $all_data[$key]['return_routing_infos'][$stop]['type'] = $info['type'] ?? '';
                                $all_data[$key]['return_routing_infos'][$stop]['time'] = $info['time'] ?? '';
                                $all_data[$key]['return_route_direction'][] = $stop;
                            }
                            foreach ($prices as $info) {
                                $bp = $this->get_id($options, ($info['bp'] ?? ''));
                                $dp = $this->get_id($options, ($info['dp'] ?? ''));
                                $price = $info['price'] ?? 0;
                                $step = 0;
                                $bp_dp = $bp . '_' . $dp;
                                $all_data[$key]['route_data'][] = $bp_dp;
                                foreach ($all_ticket_type as $type_id) {
                                    $all_data[$key]['return_price_infos'][$bp_dp][$type_id] = $price + $step;
                                    $all_data[$key]['price_data'][$bp_dp][$type_id] = $price + $step;
                                    $step = $step + 5;
                                }
                            }
                            $all_data[$key]['all_ticket_type'] = $all_ticket_type;
                        }
                    }
                }
                return $all_data;
            }
            public function get_id($options = [], $name = ''): int|string|null {
                if (!empty($options)) {
                    foreach ($options as $key => $option) {
                        if (isset($option['name']) && $option['name'] === $name) {
                            return $key;
                        }
                    }
                }
                return null;
            }
            public static function route_data(): array {
                return [
                    0 => [
                        'routing_infos' => [
                            0 => ['stop' => 'New York City', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Philadelphia', 'type' => 'bp', 'time' => '90'],
                            2 => ['stop' => 'Baltimore', 'type' => 'bp', 'time' => '180'],
                            3 => ['stop' => 'Washington, D.C.', 'type' => 'dp', 'time' => '240'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'New York City', 'dp' => 'Philadelphia', 'price' => '35'],
                            1 => ['bp' => 'New York City', 'dp' => 'Baltimore', 'price' => '55'],
                            2 => ['bp' => 'New York City', 'dp' => 'Washington, D.C.', 'price' => '75'],
                            3 => ['bp' => 'Philadelphia', 'dp' => 'Baltimore', 'price' => '30'],
                            4 => ['bp' => 'Philadelphia', 'dp' => 'Washington, D.C.', 'price' => '50'],
                            5 => ['bp' => 'Baltimore', 'dp' => 'Washington, D.C.', 'price' => '25'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'Washington, D.C.', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Baltimore', 'type' => 'bp', 'time' => '80'],
                            2 => ['stop' => 'Philadelphia', 'type' => 'bp', 'time' => '150'],
                            3 => ['stop' => 'New York City', 'type' => 'dp', 'time' => '280'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'Philadelphia', 'dp' => 'New York City', 'price' => '35'],
                            1 => ['bp' => 'Baltimore', 'dp' => 'New York City', 'price' => '55'],
                            2 => ['bp' => 'Washington, D.C.', 'dp' => 'New York City', 'price' => '75'],
                            4 => ['bp' => 'Washington, D.C.', 'dp' => 'Philadelphia', 'price' => '50'],
                            5 => ['bp' => 'Washington, D.C.', 'dp' => 'Baltimore', 'price' => '25'],
                        ]
                    ],
                    1 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Los Angeles', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Bakersfield', 'type' => 'bp', 'time' => '120'],
                            2 => ['stop' => 'Fresno', 'type' => 'both', 'time' => '240'],
                            3 => ['stop' => 'San Jose', 'type' => 'dp', 'time' => '330'],
                            4 => ['stop' => 'San Francisco', 'type' => 'dp', 'time' => '390'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Los Angeles', 'dp' => 'Fresno', 'price' => '40'],
                            1 => ['bp' => 'Los Angeles', 'dp' => 'San Jose', 'price' => '55'],
                            2 => ['bp' => 'Los Angeles', 'dp' => 'San Francisco', 'price' => '65'],
                            3 => ['bp' => 'Bakersfield', 'dp' => 'Fresno', 'price' => '20'],
                            4 => ['bp' => 'Bakersfield', 'dp' => 'San Jose', 'price' => '35'],
                            5 => ['bp' => 'Bakersfield', 'dp' => 'San Francisco', 'price' => '45'],
                            6 => ['bp' => 'Fresno', 'dp' => 'San Jose', 'price' => '25'],
                            7 => ['bp' => 'Fresno', 'dp' => 'San Francisco', 'price' => '35'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'San Francisco', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'San Jose', 'type' => 'bp', 'time' => '70'],
                            2 => ['stop' => 'Fresno', 'type' => 'both', 'time' => '130'],
                            3 => ['stop' => 'Bakersfield', 'type' => 'dp', 'time' => '300'],
                            4 => ['stop' => 'Los Angeles', 'type' => 'dp', 'time' => '420'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'San Francisco', 'dp' => 'Fresno', 'price' => '35'],
                            1 => ['bp' => 'San Francisco', 'dp' => 'Bakersfield', 'price' => '45'],
                            2 => ['bp' => 'San Francisco', 'dp' => 'Los Angeles', 'price' => '65'],
                            3 => ['bp' => 'San Jose', 'dp' => 'Fresno', 'price' => '25'],
                            4 => ['bp' => 'San Jose', 'dp' => 'Bakersfield', 'price' => '35'],
                            5 => ['bp' => 'San Jose', 'dp' => 'Los Angeles', 'price' => '55'],
                            6 => ['bp' => 'Fresno', 'dp' => 'Bakersfield', 'price' => '20'],
                            7 => ['bp' => 'Fresno', 'dp' => 'Los Angeles', 'price' => '40'],
                        ]
                    ],
                    2 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Chicago', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'South Bend', 'type' => 'bp', 'time' => '90'],
                            2 => ['stop' => 'Toledo', 'type' => 'both', 'time' => '210'],
                            3 => ['stop' => 'Detroit', 'type' => 'dp', 'time' => '300'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Chicago', 'dp' => 'Toledo', 'price' => '45'],
                            1 => ['bp' => 'Chicago', 'dp' => 'Detroit', 'price' => '60'],
                            2 => ['bp' => 'South Bend', 'dp' => 'Toledo', 'price' => '25'],
                            3 => ['bp' => 'South Bend', 'dp' => 'Detroit', 'price' => '40'],
                            4 => ['bp' => 'Toledo', 'dp' => 'Detroit', 'price' => '20'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'Detroit', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Toledo', 'type' => 'both', 'time' => '120'],
                            2 => ['stop' => 'South Bend', 'type' => 'dp', 'time' => '200'],
                            3 => ['stop' => 'Chicago', 'type' => 'dp', 'time' => '320'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'Detroit', 'dp' => 'Toledo', 'price' => '20'],
                            1 => ['bp' => 'Detroit', 'dp' => 'South Bend', 'price' => '40'],
                            2 => ['bp' => 'Detroit', 'dp' => 'Chicago', 'price' => '60'],
                            3 => ['bp' => 'Toledo', 'dp' => 'South Bend', 'price' => '25'],
                            4 => ['bp' => 'Toledo', 'dp' => 'Chicago', 'price' => '45'],
                        ]
                    ],
                    3 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Boston', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Providence', 'type' => 'bp', 'time' => '60'],
                            2 => ['stop' => 'New Haven', 'type' => 'both', 'time' => '150'],
                            3 => ['stop' => 'New York City', 'type' => 'dp', 'time' => '240'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Boston', 'dp' => 'New Haven', 'price' => '35'],
                            1 => ['bp' => 'Boston', 'dp' => 'New York City', 'price' => '55'],
                            2 => ['bp' => 'Providence', 'dp' => 'New Haven', 'price' => '20'],
                            3 => ['bp' => 'Providence', 'dp' => 'New York City', 'price' => '40'],
                            4 => ['bp' => 'New Haven', 'dp' => 'New York City', 'price' => '20'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'New York City', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'New Haven', 'type' => 'both', 'time' => '90'],
                            2 => ['stop' => 'Providence', 'type' => 'dp', 'time' => '150'],
                            3 => ['stop' => 'Boston', 'type' => 'dp', 'time' => '240'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'New York City', 'dp' => 'New Haven', 'price' => '20'],
                            1 => ['bp' => 'New York City', 'dp' => 'Providence', 'price' => '40'],
                            2 => ['bp' => 'New York City', 'dp' => 'Boston', 'price' => '55'],
                            3 => ['bp' => 'New Haven', 'dp' => 'Providence', 'price' => '20'],
                            4 => ['bp' => 'New Haven', 'dp' => 'Boston', 'price' => '35'],
                        ]
                    ],
                    4 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Dallas', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Corsicana', 'type' => 'bp', 'time' => '60'],
                            2 => ['stop' => 'Huntsville', 'type' => 'both', 'time' => '180'],
                            3 => ['stop' => 'Houston', 'type' => 'dp', 'time' => '240'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Dallas', 'dp' => 'Huntsville', 'price' => '35'],
                            1 => ['bp' => 'Dallas', 'dp' => 'Houston', 'price' => '50'],
                            2 => ['bp' => 'Corsicana', 'dp' => 'Huntsville', 'price' => '20'],
                            3 => ['bp' => 'Corsicana', 'dp' => 'Houston', 'price' => '35'],
                            4 => ['bp' => 'Huntsville', 'dp' => 'Houston', 'price' => '15'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'Houston', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Huntsville', 'type' => 'both', 'time' => '60'],
                            2 => ['stop' => 'Corsicana', 'type' => 'dp', 'time' => '120'],
                            3 => ['stop' => 'Dallas', 'type' => 'dp', 'time' => '240'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'Houston', 'dp' => 'Huntsville', 'price' => '15'],
                            1 => ['bp' => 'Houston', 'dp' => 'Corsicana', 'price' => '35'],
                            2 => ['bp' => 'Houston', 'dp' => 'Dallas', 'price' => '50'],
                            3 => ['bp' => 'Huntsville', 'dp' => 'Corsicana', 'price' => '20'],
                            4 => ['bp' => 'Huntsville', 'dp' => 'Dallas', 'price' => '35'],
                        ]
                    ],
                    5 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Seattle', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Tacoma', 'type' => 'bp', 'time' => '45'],
                            2 => ['stop' => 'Olympia', 'type' => 'both', 'time' => '90'],
                            3 => ['stop' => 'Portland', 'type' => 'dp', 'time' => '180'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Seattle', 'dp' => 'Olympia', 'price' => '25'],
                            1 => ['bp' => 'Seattle', 'dp' => 'Portland', 'price' => '45'],
                            2 => ['bp' => 'Tacoma', 'dp' => 'Olympia', 'price' => '15'],
                            3 => ['bp' => 'Tacoma', 'dp' => 'Portland', 'price' => '35'],
                            4 => ['bp' => 'Olympia', 'dp' => 'Portland', 'price' => '20'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'Portland', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Olympia', 'type' => 'both', 'time' => '90'],
                            2 => ['stop' => 'Tacoma', 'type' => 'dp', 'time' => '120'],
                            3 => ['stop' => 'Seattle', 'type' => 'dp', 'time' => '180'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'Portland', 'dp' => 'Olympia', 'price' => '20'],
                            1 => ['bp' => 'Portland', 'dp' => 'Tacoma', 'price' => '35'],
                            2 => ['bp' => 'Portland', 'dp' => 'Seattle', 'price' => '45'],
                            3 => ['bp' => 'Olympia', 'dp' => 'Tacoma', 'price' => '15'],
                            4 => ['bp' => 'Olympia', 'dp' => 'Seattle', 'price' => '25'],
                        ]
                    ],
                ];
            }
        }
        new ABPTB_Dashboard();
    }
