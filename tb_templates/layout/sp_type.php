<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_sp_type_template', function ($post_infos, $form_data = [], $prefix = '') {
        if (!empty($post_infos)) {
            //echo '<pre>';        print_r($form_data);        echo '</pre>';
            $_ticket_infos = $post_infos['sp_infos'] ?? [];
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
                $sp_id = $form_data['sp_id'] ?? (current($_ticket_infos)['id'] ?? '');
                //echo '<pre>';            print_r($_ticket_infos);            echo '</pre>';
                //echo '<pre>';            print_r($sp_id);            echo '</pre>';
                ?>
                <div class="booking_item">
                    <?php do_action('abptb_type_head', $post_infos, $form_data, $prefix); ?>
                    <div class="booking_content">
                        <div class="ticket_content">
                            <?php if (sizeof($_ticket_infos) > 1) { ?>
                                <label class="_text_nowrap">
                                   <span class="_abp_label"> <?php esc_html_e('Label :', 'abp-transport-booking'); ?></span>
                                    <select class="_form_control" name="<?php echo esc_attr($prefix); ?>journey_time">
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
                                ABPTB_Layout::sp($sp_id); ?>
                        </div>
                        <div class="ticket_right">
                            <?php
                                do_action('abptb_additional', $post_infos, $prefix);
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
                ABPTB_Layout::layout_warning_info('no_sp_config');
            }
        }
    }, 10, 3);