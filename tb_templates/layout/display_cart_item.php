<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_display_cart_item_template', function ($booking_infos = []) {
        $booking_info = $booking_infos['booking_infos'] ?? [];
        $post_id = $booking_infos['post_id'] ?? '';
        if (!empty($booking_info) && sizeof($booking_info) > 0 && !empty($post_id) && get_post_type($post_id) == ABPTB_Function::get_cpt()) {
            $return = '';
            foreach ($booking_info as $bp_dp => $booking_item) {
                if (!empty($booking_item)) {
                    $ticket_infos = $booking_item['info'] ?? [];
                    if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
                        [$bp, $dp] = array_map('intval', explode('_', $bp_dp));
                        $additional_info = $booking_item['additional_info'] ?? [];
                        $attendee_infos = $booking_item['pass_info'] ?? [];
                        $pick_up = $booking_item['pick_up'] ?? '';
                        $drop_off = $booking_item['drop_off'] ?? '';
                        $start_point = $booking_item['start_point'] ?? '';
                        //echo '<pre>';                    print_r($start_point);                    echo '</pre>';
                        ?>
                        <div class="abptb_area">
                            <div class="_section_card_xs _fd_column_gap_xs">
                                <div class="_cart_details _w_full">
                                    <h6 class="abp _color_theme"><?php echo esc_html__('Booking Information ', 'abp-transport-booking') . ' ' . esc_html($return) . ' : '; ?></h6>
                                    <div class="_divider_xxs"></div>
                                    <ul class="abp cart_list">
                                        <?php if (intval($start_point) !== intval($bp)) { ?>
                                            <li class="_gap_xxs">
                                                <span class="fas fa-map-location"></span>
                                                <span class="_fs_label_color_burnt_orange"><?php esc_html_e('Starting Point  : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::location_value($start_point) . ' - ' . ABPTB_Function::date_format($booking_item['start_time'] ?? '')); ?>
                                            </li>
                                        <?php } ?>
                                        <li class="_gap_xxs">
                                            <span class="fas fa-route"></span>
                                            <span class="_fs_label_color_burnt_orange"><?php esc_html_e('Boarding Point : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::location_value($bp) . ' - ' . ABPTB_Function::date_format($booking_item['bp_time'] ?? '')); ?>
                                        </li>
                                        <li class="_gap_xxs">
                                            <span class="fas fa-map-marker-alt"></span>
                                            <span class="_fs_label_color_burnt_orange"><?php esc_html_e('Arrival : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::location_value($dp) . ' - ' . ABPTB_Function::date_format($booking_item['dp_time'] ?? '')); ?>
                                        </li>
                                        <?php if (intval($pick_up) !== intval($bp)) { ?>
                                            <li class="_gap_xxs">
                                                <span class="fas fa-map-location"></span>
                                                <span class="_fs_label_color_burnt_orange"><?php esc_html_e('Pick Up  : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::pd_value($pick_up) . ' - ' . ABPTB_Function::date_format($booking_item['pick_up_time'] ?? '')); ?>
                                            </li>
                                        <?php } ?>
                                        <?php if (intval($drop_off) !== intval($dp)) { ?>
                                            <li class="_gap_xxs">
                                                <span class="fas fa-map-location"></span>
                                                <span class="_fs_label_color_burnt_orange"><?php esc_html_e('Drop-Off  : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::pd_value($drop_off) . ' - ' . ABPTB_Function::date_format($booking_item['drop_off_time'] ?? '')); ?>
                                            </li>
                                        <?php } ?>
                                        <li class="_gap_xxs">
                                            ⏰ <span class="_fs_label_color_burnt_orange"><?php esc_html_e('Approximate Time  : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html($booking_item['duration'] ?? ''); ?>
                                        </li>
                                    </ul>
                                </div>
                                <div class="cart_ticket_info _w_full">
                                    <h6 class="abp _color_theme"><?php esc_html_e('Ticket Information : ', 'abp-transport-booking'); ?></h6>
                                    <div class="_divider_xxs"></div>
                                    <ul class="abp cart_list">
                                        <?php foreach ($ticket_infos as $ticket_info) {
                                            $price = $ticket_info['price'] ?? 0;
                                            $qty = $ticket_info['qty'] ?? 1;
                                            $price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-transport-booking');
                                            $price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-transport-booking');
                                            $name = ABPTB_Function::ticket_label($ticket_info, $booking_item);
                                            ?>
                                            <li class="_gap_xxs">
                                                <span class="_fs_label_color_burnt_orange"><?php echo esc_html($name . __(' : ', 'abp-transport-booking')); ?></span>
                                                <?php echo wp_kses_post($price_text) . ' X ' . esc_html($qty) . ' = ' . wp_kses_post($price); ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <?php if (ABPTB_Function::on_off('additional_info') && !empty($additional_info) && is_array($additional_info)) { ?>
                                    <div class="cart_additional _w_full">
                                        <h6 class="abp _color_theme"><?php esc_html_e('Additional Information : ', 'abp-transport-booking'); ?></h6>
                                        <div class="_divider_xxs"></div>
                                        <ul class="abp cart_list">
                                            <?php
                                                foreach ($additional_info as $additional) {
                                                    if (!is_array($additional) || empty($additional)) {
                                                        continue;
                                                    }
                                                    $icon_image = $additional['icon'] ?? '';
                                                    $name = $additional['name'] ?? '';
                                                    $qty = $additional['qty'] ?? 1;
                                                    $price = $additional['price'] ?? 0;
                                                    $price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-transport-booking');
                                                    $ex_price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-transport-booking');
                                                    ?>
                                                    <li class="_gap_xxs">
                                                        <?php ABPTB_Layout::image_icon($icon_image); ?>
                                                        <?php echo esc_html($name . __(' : ', 'abp-transport-booking')); ?>
                                                        <?php echo wp_kses_post($price_text) . ' X ' . esc_html($qty) . ' = ' . wp_kses_post($ex_price); ?>
                                                    </li>
                                                <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                                <?php if (ABPTB_Function::on_off('client_info') && !empty($attendee_infos) && is_array($attendee_infos)) { ?>
                                    <div class="cart_client_info _w_full">
                                        <h6 class="abp _color_theme"><?php esc_html_e('Client Information : ', 'abp-transport-booking'); ?></h6>
                                        <?php
                                            foreach ($attendee_infos as $attendee_info) {
                                                if (!empty($attendee_info)) { ?>
                                                    <div class="_divider_xxs"></div>
                                                    <ul class=" abp cart_list">
                                                        <?php foreach ($attendee_info as $attendee) {
                                                            $label = $attendee['label'] ?? '';
                                                            $value = $attendee['value'] ?? '';
                                                            if (!empty($label) && !empty($value)) { ?>
                                                                <li>
                                                                    <span class="abp_label"><?php echo esc_html($label . __(' : ', 'abp-transport-booking')); ?></span>
                                                                    <?php echo esc_html($value); ?>
                                                                </li>
                                                                <?php
                                                            }
                                                        } ?>
                                                    </ul>
                                                <?php }
                                            } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                        $return = __('( Return )', 'abp-transport-booking');
                    }
                }
            }
        }
    }, 10, 2);