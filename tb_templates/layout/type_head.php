<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_type_head_template', function ($post_infos, $form_data = [], $prefix = '') {
        if (!empty($post_infos)) {
            $seat_type = $post_infos['seat_type'] ?? 'sp';
            $seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
            //echo '<pre>';        print_r($form_data);        echo '</pre>';
            $ticket_infos = $post_infos[$seat_type.'_infos'] ?? [];
            if (is_array($ticket_infos) && sizeof($ticket_infos) > 0) {
                $bp_dp = $form_data['bp_dp'] ?? '';
                $journey_date = $form_data['journey_date'] ?? '';
                $journey_time = $form_data['journey_time'] ?? '';
                $bp_times = $form_data['bp_times'] ?? [];
                $double_route = $form_data['double_route'] ?? '';
                ?>
                <div class="post_top_filter">
                    <?php if(empty($double_route)){ ?>
                    <h3 class="_abp"><?php esc_html_e('Available Ticket', 'abp-transport-booking'); ?></h3>
                    <?php } ?>
                    <?php if (!empty($bp_dp)) { ?>
                        <h6 class="_abp _d_flex_fa_center ">
                            <?php ABPTB_Layout::route_direction($post_infos, $bp_dp,'',$double_route); ?>
                        </h6>
                        <?php
                        if ($journey_date && empty($bp_times)) {
                            ABPTB_Layout::layout_warning_info_xs('', __('No time available : ', 'abp-transport-booking') . ' ' . ABPTB_Function::date_format($journey_date, 'date'));
                        }
                        ?>
                        <label class="_text_nowrap">
                            <span class="_gap_xxs">⏰ <?php esc_html_e('Time :', 'abp-transport-booking'); ?></span>
                            <?php if (!empty($bp_times) && sizeof($bp_times) > 1) { ?>
                                <select class="_form_control" name="<?php echo esc_attr($prefix); ?>journey_time">
                                    <?php foreach ($bp_times as $bp_time) { ?>
                                        <option value="<?php echo esc_attr($bp_time); ?>" <?php selected($journey_time, $bp_time) ?>><?php echo esc_html(ABPTB_Function::date_format($bp_time)); ?></option>
                                    <?php } ?>
                                </select>
                            <?php } else { ?>
                                <?php echo esc_html(ABPTB_Function::date_format($journey_time)); ?>
                                <input type="hidden" name="<?php echo esc_attr($prefix); ?>journey_time" value="<?php echo esc_attr($journey_time); ?>">
                            <?php } ?>
                        </label>
                    <?php } ?>
                </div>
                <?php
            }
        }
    }, 10, 3);