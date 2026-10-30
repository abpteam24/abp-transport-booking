<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_sp_type_template', function ($post_infos, $form_data = [], $prefix = '') {
        if (!empty($post_infos)) {
            $_ticket_infos = $post_infos['sp_infos'] ?? [];
            //echo '<pre>';            print_r($post_infos);            echo '</pre>';
            if (is_array($_ticket_infos) && sizeof($_ticket_infos) > 0) {
                $booking_item = $form_data['booking_item'] ?? [];
                $sp_id = $form_data['sp_id'] ?? (current($_ticket_infos)['id'] ?? '');
                $sp_id=$booking_item['sp_id'] ??$sp_id;
                $booking_tickets=json_decode($booking_item['ticket_info'] ?? '', true) ?: [];
                $selected_seat=[];
                $selected_type=[];
                if(!empty($booking_tickets) && is_array($booking_tickets) ){
                    foreach ($booking_tickets as $booking_ticket) {
                        if (!empty($booking_ticket) && sizeof($booking_ticket) > 0) {
                            $seat=$booking_ticket['name']??'';
                            $type=$booking_ticket['id']??'';
                            if(!empty($seat) && !empty($type)){
                                $selected_seat[]=$seat;
                                $selected_type[]=$type;
                            }
                        }
                    }
                }
                //echo '<pre>';            print_r(ABPTB_Query::get_sold_seat($form_data));            echo '</pre>';
                ?>
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>start_point" value="<?php echo esc_attr($form_data['start_point'] ?? ''); ?>">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>start_time" value="<?php echo esc_attr($form_data['start_time'] ?? ''); ?>">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>bp_dp" value="<?php echo esc_attr($form_data['bp_dp'] ?? ''); ?>">
                <input type="hidden" name="prefix" value="<?php echo esc_attr($prefix); ?>">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>sp_selected_seat" value="<?php echo esc_attr(!empty($selected_seat)?implode(',',$selected_seat):''); ?>">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>sp_selected_seat_id" value="<?php echo esc_attr(!empty($selected_type)?implode(',',$selected_type):''); ?> ">
                <?php if (sizeof($_ticket_infos) > 1) { ?>
                    <label class="_text_nowrap _min_300_mar_auto">
                        <span> <?php esc_html_e('Seat Label :', 'abp-transport-booking'); ?></span>
                        <select class="_form_control" name="<?php echo esc_attr($prefix); ?>sp_id">
                            <?php foreach ($_ticket_infos as $_ticket_info) {
                                $id = $_ticket_info['id'] ?? '';
                                if (!empty($id)) { ?>
                                    <option value="<?php echo esc_attr($id); ?>" <?php selected($id, $sp_id) ?>><?php echo esc_html($_ticket_info['name'] ?? $id); ?></option>
                                <?php }
                            } ?>
                        </select>
                    </label>
                    <?php
                } else { ?>
                    <input type="hidden" name="<?php echo esc_attr($prefix); ?>sp_id" value="<?php echo esc_attr($sp_id); ?>">
                <?php }
                if (!empty($form_data['bp_dp']) && !empty($form_data['start_time'])) { ?>
                    <div class="sp_legend">
                        <span class="sp_legend_item available"><i></i><?php esc_html_e('Available', 'abp-transport-booking'); ?></span>
                        <span class="sp_legend_item selected"><i></i><?php esc_html_e('Selected', 'abp-transport-booking'); ?></span>
                        <span class="sp_legend_item sold"><i></i><?php esc_html_e('Sold', 'abp-transport-booking'); ?></span>
                    </div>
                <?php
                }
                ABPTB_Layout::sp($sp_id, [], $post_infos, $form_data);
            } else {
                ABPTB_Layout::layout_warning_info('no_sp_config');
            }
        }
    }, 10, 3);