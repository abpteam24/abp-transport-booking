<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_ticket_type_template', function ($post_infos, $form_data = [], $prefix = '') {
        if (!empty($post_infos)) {
            $bp_dp = $form_data['bp_dp'] ?? '';
            $journey_date = $form_data['journey_date'] ?? '';
            $bp_times = $form_data['bp_times'] ?? [];
            $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
            $display_ticket_type = ABPTB_Function::on_off('ticket_type') ? $display_ticket_type : 'off';
            $ticket_infos = [];
            // echo '<pre>';        print_r($form_data);        echo '</pre>';
            $_ticket_infos = $post_infos['ticket_infos'] ?? [];
            if (is_array($_ticket_infos) && sizeof($_ticket_infos) > 0) {
                if ($display_ticket_type === 'off') {
                    $key = array_key_first($_ticket_infos);
                    $ticket_infos[$key] = $_ticket_infos[$key];
                } else {
                    $ticket_infos = $_ticket_infos;
                }
                $sold_infos = [];
                if (!empty($bp_dp) && !empty($bp_times)) {
                    $sold_infos = ABPTB_Query::get_sold_ticket($form_data);
                }
                //echo '<pre>';        print_r($sold_infos);        echo '</pre>';
                ?>
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>start_point" value="<?php echo esc_attr($form_data['start_point'] ?? ''); ?>">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>start_time" value="<?php echo esc_attr($form_data['start_time'] ?? ''); ?>">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>bp_dp" value="<?php echo esc_attr($form_data['bp_dp'] ?? ''); ?>">
                <input type="hidden" name="prefix" value="<?php echo esc_attr($prefix); ?>">
                <?php
                foreach ($ticket_infos as $key => $ticket_info) {
                    $price = ABPTB_Function::get_price($post_infos, $bp_dp, $key, $journey_date);
                    $sold = $sold_infos[$key] ?? 0;
                    $qty = $ticket_info['qty'] ?? 0;
                    $reserve = $ticket_info['reserve'] ?? 0;
                    $available = $qty - $sold - $reserve;
                    $ticket_info['available'] = $available;
                    ?>
                    <div class="ticket_item _section_card_xs_w_full">
                        <div class="_fj_between">
                            <h5 class="abp_gap_xxs"><?php ABPTB_Layout::image_icon(ABPTB_Function::ticket_icon($key)); ?><?php echo esc_html(ABPTB_Function::ticket_name($key)); ?></h5>
                            <?php if (!empty($price) && !empty($bp_dp)) { ?>
                                <div class="abp_tag price_value">
                                    <?php echo ($price > 0) ? wp_kses_post(wc_price($price)) : esc_html__('Free', 'abp-transport-booking'); ?>
                                    <sub class="_color_green_pale _fs_small"><?php esc_html_e('/Ticket', 'abp-transport-booking') ?></sub>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if (ABPTB_Function::on_off('capacity')) { ?>
                            <h6 class="abp"><?php echo esc_html__('Available : ', 'abp-transport-booking') . ' ' . esc_html($available . '/' . $qty); ?></h6>
                        <?php } ?>
                        <p class="abp"><?php echo esc_html($ticket_info['description'] ?? ''); ?></p>
                        <?php if (!empty($bp_dp) && !empty($bp_times)) {
                            ABPTB_Layout::item_select($ticket_info, $key, $price, $prefix);
                        } ?>
                    </div>
                <?php }
            } else {
                ABPTB_Layout::layout_warning_info('no_ticket_config');
            }
        }
    }, 10, 3);