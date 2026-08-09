<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_display_cart_item_template', function ($booking_infos = []) {
        $booking_info = $booking_infos['booking_infos'] ?? [];
        $post_id = $booking_infos['post_id'] ?? '';
        if (!empty($booking_info) && sizeof($booking_info) > 0 && !empty($post_id) && get_post_type($post_id) == ABPTB_Function::get_cpt()) {
            $return = '';
            foreach ($booking_info as $bp_dp => $cart_item) {
                if (!empty($cart_item)) {
                    $ticket_infos = $cart_item['info'] ?? [];
                    $seat_type = $cart_item['seat_type'] ?? '';
                    if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
                        $journey_time = $cart_item['journey_time'] ?? '';
                        [$bp, $dp] = array_map('intval', explode('_', $bp_dp));
                        $additional_info = $cart_item['additional_info'] ?? [];
                        $attendee_infos = $cart_item['pass_info'] ?? [];
                        ?>
                        <div class="abptb_area">
                            <div class="_section_card_xs _fd_column_gap_xs">
                                <div class="_cart_details _w_full">
                                    <h6 class="_abp _color_theme"><?php echo esc_html__('Booking Information ', 'abp-transport-booking') . ' ' . esc_html($return) . ' : '; ?></h6>
                                    <div class="_divider_xxs"></div>
                                    <ul class="_abp cart_list">
                                        <li class="_gap_xxs">
                                            <span class="fas fa-location"></span>
                                            <span class="_fs_label"><?php esc_html_e('Departure : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::location_value($bp)); ?>
                                        </li>
                                        <li class="_gap_xxs">
                                            <span class="fas fa-calendar-check"></span>
                                            <span class="_fs_label"><?php esc_html_e('Departure Time: ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::date_format($journey_time)); ?>
                                        </li>
                                        <li class="_gap_xxs">
                                            <span class="fas fa-location"></span>
                                            <span class="_fs_label"><?php esc_html_e('Arrival : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::location_value($dp)); ?>
                                        </li>
                                    </ul>
                                </div>
                                <div class="cart_ticket_info _w_full">
                                    <h6 class="_abp _color_theme"><?php esc_html_e('Ticket Information : ', 'abp-transport-booking'); ?></h6>
                                    <div class="_divider_xxs"></div>
                                    <ul class="_abp cart_list">
                                        <?php foreach ($ticket_infos as $ticket_info) {
                                            $price = $ticket_info['price'] ?? 0;
                                            $qty = $ticket_info['qty'] ?? 1;
                                            $price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-transport-booking');
                                            $price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-transport-booking');
                                            $name = $ticket_info['name'] ?? '';
                                            if ($seat_type == 'sp') {
                                                $name = $name . ' - ' . ABPTB_Function::sp_label($post_id, ($ticket_info['sp_id'] ?? ''));
                                            } ?>
                                            <li class="_gap_xxs">
                                                <?php echo esc_html($name . __(' : ', 'abp-transport-booking')); ?>
                                                <?php echo wp_kses_post($price_text) . ' X ' . esc_html($qty) . ' = ' . wp_kses_post($price); ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <?php if (ABPTB_Function::on_off('additional_info') && !empty($additional_info) && is_array($additional_info)) { ?>
                                    <div class="cart_additional _w_full">
                                        <h6 class="_abp _color_theme"><?php esc_html_e('Additional Information : ', 'abp-transport-booking'); ?></h6>
                                        <div class="_divider_xxs"></div>
                                        <ul class="_abp cart_list">
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
                                        <h6 class="_abp _color_theme"><?php esc_html_e('Client Information : ', 'abp-transport-booking'); ?></h6>
                                        <?php
                                            foreach ($attendee_infos as $attendee_info) {
                                                if (!empty($attendee_info)) { ?>
                                                    <div class="_divider_xxs"></div>
                                                    <ul class=" _abp cart_list">
                                                        <?php foreach ($attendee_info as $attendee) {
                                                            $label = $attendee['label'] ?? '';
                                                            $value = $attendee['value'] ?? '';
                                                            if (!empty($label) && !empty($value)) { ?>
                                                                <li>
                                                                    <span class="_abp_label"><?php echo esc_html($label . __(' : ', 'abp-transport-booking')); ?></span>
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