<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_ticket_type_template', function ($post_infos, $form_data = [], $prefix = '') {
        if (!empty($post_infos)) {
            //echo '<pre>';        print_r($form_data);        echo '</pre>';
            $_ticket_infos = $post_infos['ticket_infos'] ?? [];
            if (is_array($_ticket_infos) && sizeof($_ticket_infos) > 0) {
                $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
                $display_ticket_type = ABPTB_Function::on_off('ticket_type') ? $display_ticket_type : 'off';
                $ticket_infos = [];
                if ($display_ticket_type === 'off') {
                    $key = array_key_first($_ticket_infos);
                    $ticket_infos[$key] = $_ticket_infos[$key];
                } else {
                    $ticket_infos = $_ticket_infos;
                }
                $bp_dp = $form_data['bp_dp'] ?? '';
                $journey_date = $form_data['journey_date'] ?? '';
                $bp_times = $form_data['bp_times'] ?? [];
                $double_route = $form_data['double_route'] ?? '';
                // echo '<pre>';            print_r($bp_dp);            echo '</pre>';
                ?>
                <div class="booking_item">
                    <?php do_action('abptb_type_head', $post_infos, $form_data, $prefix); ?>
                    <div class="booking_content">
                        <div class="ticket_content">
                            <?php foreach ($ticket_infos as $key => $ticket_info) {
                                $price = ABPTB_Function::get_price($post_infos, $bp_dp, $key, $journey_date);
                                ?>
                                <div class="ticket_item _section_card_xs_w_full">
                                    <div class="_fj_between">
                                        <h5 class="_abp"><?php ABPTB_Layout::image_icon(ABPTB_Function::ticket_icon($key)); ?><?php echo esc_html(ABPTB_Function::ticket_name($key)); ?></h5>
                                        <?php if (!empty($price) && !empty($bp_dp)) { ?>
                                            <div class="abp_tag price_value">
                                                <?php echo ($price > 0) ? wp_kses_post(wc_price($price)) : esc_html__('Free', 'abp-transport-booking'); ?>
                                                <sub class="_color_green_pale _fs_small"><?php esc_html_e('/Ticket', 'abp-transport-booking') ?></sub>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <?php if (ABPTB_Function::on_off('capacity')) { ?>
                                        <h6 class="_abp"><?php echo esc_html__('Capacity : ', 'abp-transport-booking') . ' ' . esc_html($ticket_info['qty'] ?? 0); ?></h6>
                                    <?php } ?>
                                    <p class="_abp"><?php echo esc_html($ticket_info['description'] ?? ''); ?></p>
                                    <?php if (!empty($bp_dp) && !empty($bp_times)) {
                                        ABPTB_Layout::item_select($post_infos, $ticket_info, $key, $price, $prefix);
                                    } ?>
                                </div>
                            <?php } ?>
                            <?php do_action('abptb_additional', $post_infos, $prefix); ?>
                        </div>
                        <div class="ticket_right">
                            <?php
                                do_action('abptb_client_form', $post_infos, $prefix);
                                if (empty($double_route)) {
                                    do_action('abptb_total_price', $post_infos, $form_data);
                                }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            } else {
                ABPTB_Layout::layout_warning_info('no_ticket_config');
            }
        }
    }, 10, 3);