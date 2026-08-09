<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_sp_type_template', function ($post_infos, $form_data = [], $prefix = '') {
        if (!empty($post_infos)) {
            //echo '<pre>';        print_r($form_data);        echo '</pre>';
            $_ticket_infos = $post_infos['sp_infos'] ?? [];
            if (is_array($_ticket_infos) && sizeof($_ticket_infos) > 0) {
                $sp_id = $form_data['sp_id'] ?? (current($_ticket_infos)['id'] ?? '');
                //echo '<pre>';            print_r($_ticket_infos);            echo '</pre>';
                //echo '<pre>';            print_r(ABPTB_Query::get_sold_seat($form_data));            echo '</pre>';
                ?>
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>start_point" value="<?php echo esc_attr($form_data['start_point'] ?? ''); ?>">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>start_time" value="<?php echo esc_attr($form_data['start_time'] ?? ''); ?>">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>bp_dp" value="<?php echo esc_attr($form_data['bp_dp'] ?? ''); ?>">
                <input type="hidden" name="prefix" value="<?php echo esc_attr($prefix); ?>">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>sp_selected_seat" value="">
                <input type="hidden" name="<?php echo esc_attr($prefix); ?>sp_selected_seat_id" value="">
                <?php if (sizeof($_ticket_infos) > 1) { ?>
                    <label class="_text_nowrap _min_300_mar_auto">
                        <span class="_abp_label"> <?php esc_html_e('Seat Label :', 'abp-transport-booking'); ?></span>
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
                ABPTB_Layout::sp($sp_id, [], $post_infos, $form_data);
            } else {
                ABPTB_Layout::layout_warning_info('no_sp_config');
            }
        }
    }, 10, 3);