<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_registration_item_template', function ($post_infos, $form_data = [], $prefix = '') {
        if (!empty($post_infos)) {
            //echo '<pre>';                print_r($prefix);                echo '</pre>';
            //echo '<pre>';                print_r($form_data);                echo '</pre>';
            $seat_type = $post_infos['seat_type'] ?? 'sp';
            $seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
            $double_route = $form_data['double_route'] ?? '';
            $ticket_infos = $post_infos[$seat_type . '_infos'] ?? [];
            ?>
            <div class="booking_item">
                <?php if (is_array($ticket_infos) && sizeof($ticket_infos) > 0) {
                    $bp_dp = $form_data['bp_dp'] ?? '';
                    $journey_date = $form_data['journey_date'] ?? '';
                    $bp_time = $form_data['bp_time'] ?? '';
                    $bp_times = $form_data['bp_times'] ?? [];
                    $double_route = $form_data['double_route'] ?? '';
                    //echo '<pre>';        print_r($form_data);        echo '</pre>';
                    ?>
                    <div class="post_top_filter">
                        <?php if (empty($double_route)) { ?>
                            <h3 class="abp"><?php esc_html_e('Available Ticket', 'abp-transport-booking'); ?></h3>
                        <?php } ?>
                        <?php if (!empty($bp_dp)) { ?>
                            <h6 class="abp _d_flex_fa_center ">
                                <?php ABPTB_Layout::route_direction($post_infos, $bp_dp, '', $double_route); ?>
                            </h6>
                            <?php if ($journey_date && empty($bp_times)) {
                                ABPTB_Layout::layout_warning_info_xs('', __('No time available : ', 'abp-transport-booking') . ' ' . ABPTB_Function::date_format($journey_date, 'date'));
                            } ?>
                            <label class="_text_nowrap">
                                <span class="_gap_xxs">⏰ <?php esc_html_e('Time :', 'abp-transport-booking'); ?></span>
                                <?php if (!empty($bp_times) && sizeof($bp_times) > 1) { ?>
                                    <select class="_form_control" name="<?php echo esc_attr($prefix); ?>bp_time">
                                        <?php foreach ($bp_times as $times) { ?>
                                            <option value="<?php echo esc_attr($times); ?>" <?php selected($times, $bp_time) ?>><?php echo esc_html(ABPTB_Function::date_format($times)); ?></option>
                                        <?php } ?>
                                    </select>
                                <?php } else { ?>
                                    <?php echo esc_html(ABPTB_Function::date_format($bp_time)); ?>
                                    <input type="hidden" name="<?php echo esc_attr($prefix); ?>bp_time" value="<?php echo esc_attr($bp_time); ?>">
                                <?php } ?>
                                <input type="hidden" name="<?php echo esc_attr($prefix); ?>dp_time" value="<?php echo esc_attr($form_data['dp_time'] ?? ''); ?>">
                            </label>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="booking_content">
                    <div class="ticket_left">
                        <div class="ticket_content">
                            <?php if ($seat_type === 'ticket') {
                                do_action('abptb_ticket_type', $post_infos, $form_data, $prefix);
                            } else {
                                do_action('abptb_sp_type', $post_infos, $form_data, $prefix);
                            } ?>
                        </div>
                        <?php do_action('abptb_additional', $post_infos, $prefix); ?>
                    </div>
                    <div class="ticket_right">

                        <?php do_action('abptb_selection_details', $post_infos, $form_data, $prefix);
                            do_action('abptb_client_form', $post_infos, $prefix);
                            if (empty($double_route)) {
                                do_action('abptb_total_price', $post_infos, $form_data);
                            } ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }, 10, 3);