<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptf_registration_template', function ($post_infos, $form_data = []) {
        if (!empty($post_infos)) {
            $sale_continue = $post_infos['sale_continue'] ?? 'on';
            if ($sale_continue == 'on') {
                //echo '<pre>';                print_r($form_data);                echo '</pre>';
                $seat_type = $post_infos['seat_type'] ?? 'sp';
                $seat_type = ABPTF_Function::on_off('sp') ? $seat_type : 'ticket';
                $post_id = absint($post_infos['post_id'] ?? 0);
                $double_route = $form_data['double_route'] ?? '';
                $return_data = $form_data['return'] ?? [];
                ?>
                <form action="" method="post" class="<?php echo esc_attr($double_route); ?>">
                    <input type="hidden" name="double_route" value="<?php echo esc_attr($form_data['double_route'] ?? ''); ?>">
                    <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                    <input type="hidden" name="seat_type" value="<?php echo esc_attr($seat_type); ?>">
                    <input type="hidden" name="same_attendee" value="<?php echo esc_attr($post_infos['display_single_form'] ?? 'on'); ?>">
                    <input type="hidden" name="min_qty" value="<?php echo esc_attr($post_infos['min_qty'] ?? 1); ?>">
                    <input type="hidden" name="max_qty" value="<?php echo esc_attr($post_infos['max_qty'] ?? ''); ?>" data-msg="<?php echo esc_attr__('You can buy max ticket :', 'abp-transportforge') . ' ' . esc_attr(($post_infos['max_qty'] ?? '')); ?>">
                    <?php wp_nonce_field('abptf_registration_nonce');
                        do_action('abptf_admin_order', $post_id); ?>
                    <div class="booking_area">
                        <input type="hidden" name="start_point" value="<?php echo esc_attr($form_data['start_point'] ?? ''); ?>">
                        <input type="hidden" name="start_time" value="<?php echo esc_attr($form_data['start_time'] ?? ''); ?>">
                        <input type="hidden" name="bp_dp" value="<?php echo esc_attr($form_data['bp_dp'] ?? ''); ?>">
                        <?php
                            if ($seat_type === 'ticket') {
                                do_action('abptf_ticket_type', $post_infos, $form_data);
                            } else {
                                do_action('abptf_sp_type', $post_infos, $form_data);
                            } ?>
                    </div>
                    <?php if (!empty($double_route)) { ?>
                        <div class="booking_area return">
                            <input type="hidden" name="return_start_point" value="<?php echo esc_attr($return_data['start_point'] ?? ''); ?>">
                            <input type="hidden" name="return_start_time" value="<?php echo esc_attr($return_data['start_time'] ?? ''); ?>">
                            <input type="hidden" name="return_bp_dp" value="<?php echo esc_attr($return_data['bp_dp'] ?? ''); ?>">
                            <?php
                                if ($seat_type === 'ticket') {
                                    do_action('abptf_ticket_type', $post_infos, $return_data, 'return_');
                                } else {
                                    do_action('abptf_sp_type', $post_infos, $return_data, 'return_');
                                } ?>
                        </div>
                    <?php }
                        if (!empty($double_route)) {
                            do_action('abptf_total_price', $post_infos, $form_data);
                        } ?>
                </form>
                <?php
            } else {
                ABPTF_Layout::layout_warning_info('sale_close_msg');
            }
        }
    }, 10, 2);