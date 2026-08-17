<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_selection_details_template', function ($post_infos, $form_data = [], $prefix = '') {
        //echo '<pre>';                print_r($prefix);                echo '</pre>';
        //echo '<pre>';                print_r(ABPTB_Location);                echo '</pre>';
        //echo '<pre>';        print_r($form_data);        echo '</pre>';
        $seat_type = $post_infos['seat_type'] ?? 'sp';
        $seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
        $bp_dp = $form_data['bp_dp'] ?? '';
        if (!empty($bp_dp)) {
            [$bp, $dp] = array_map('intval', explode('_', $bp_dp));
            //$start_point = $form_data['start_point'] ?? '';
            $journey_date = $form_data['journey_date'] ?? '';
            $bp_time = $form_data['bp_time'] ?? '';
            $dp_time = $form_data['dp_time'] ?? '';
            ?>
            <div class="_section_15">
                <ul class="abp_list">
                    <li>
                        <span class="fas fa-calendar-check"></span>
                        <span class="_fs_label"><?php esc_html_e('Journey Date: ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::date_format($journey_date)); ?>
                    </li>
                    <li>
                        <span class="fas fa-route"></span>
                        <span class="_fs_label"><?php esc_html_e('Departure : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::location_value($bp) . ' - ' . ABPTB_Function::date_format($bp_time)); ?>
                    </li>
                    <li>
                        <span class="fas fa-map-marker-alt"></span>
                        <span class="_fs_label"><?php esc_html_e('Arrival : ', 'abp-transport-booking'); ?></span>&nbsp;<?php echo esc_html(ABPTB_Function::location_value($dp) . ' - ' . ABPTB_Function::date_format($dp_time)); ?>
                    </li>
                </ul>
                <?php if (ABPTB_Function::on_off('pickup')) {
                    $is_return = ABPTB_Function::return_check($post_infos, $bp_dp);
                    $key = $is_return ? 'return_routing_infos' : 'routing_infos';
                    $route_infos = $post_infos[$key] ?? [];
                    $bp_pd = $route_infos[$bp]['pd'] ?? 'on';
                    $dp_pd = $route_infos[$dp]['pd'] ?? 'on';
                    $pickup_infos = $bp_pd == 'on' ? ABPTB_Function::get_pd_info($bp, $bp_time) : [];
                    $drop_up_infos = $dp_pd == 'on' ? ABPTB_Function::get_pd_info($dp, $dp_time) : [];
                    //echo '<pre>';                    print_r($pickup_infos);                    echo '</pre>';
                    //echo '<pre>';                    print_r($drop_up_infos);                    echo '</pre>';
                    if (sizeof($pickup_infos) > 1) {
                        ?>
                        <div class="_divider_xs"></div>
                        <label class="_fj_between">
                            <span><?php esc_html_e('Pickup Point: ', 'abp-transport-booking'); ?></span>
                            <select class="_form_control _max_350" name="<?php echo esc_attr($prefix); ?>pick_up">
                                <?php foreach ($pickup_infos as $key => $pickup) {
                                    $name = $pickup['name'] ?? '';
                                    $time = $pickup['time'] ?? '';
                                    ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $bp) ?>><?php echo esc_html($name . ' - ' . ABPTB_Function::date_format($time)); ?></option>
                                <?php } ?>
                            </select>
                        </label>
                    <?php } else { ?>
                        <input type="hidden" name="<?php echo esc_attr($prefix); ?>pick_up" value="<?php echo esc_attr($bp); ?>">
                    <?php } ?>
                    <?php if (sizeof($drop_up_infos) > 1) {
                        ?>
                        <div class="_divider_xs"></div>
                        <label class="_fj_between">
                            <span><?php esc_html_e('Drop-Off Point: ', 'abp-transport-booking'); ?></span>
                            <select class="_form_control _max_350" name="<?php echo esc_attr($prefix); ?>drop_off">
                                <?php foreach ($drop_up_infos as $key => $pickup) {
                                    $name = $pickup['name'] ?? '';
                                    $time = $pickup['time'] ?? '';
                                    ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $dp) ?>><?php echo esc_html($name . ' - ' . ABPTB_Function::date_format($time)); ?></option>
                                <?php } ?>
                            </select>
                        </label>
                    <?php } else { ?>
                        <input type="hidden" name="<?php echo esc_attr($prefix); ?>drop_off" value="<?php echo esc_attr($dp); ?>">
                    <?php } ?>
                <?php } ?>
                <?php if ($seat_type === 'sp') { ?>
                    <div class="seat_selection">
                        <div class="_divider_xs"></div>
                        <table class="abp">
                            <thead>
                            <tr>
                                <th><?php esc_html_e('Seat', 'abp-transport-booking'); ?></th>
                                <th><?php esc_html_e('Price', 'abp-transport-booking'); ?></th>
                                <th class="_text_center"><?php esc_html_e('Action', 'abp-transport-booking'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insert_item ">
                            </tbody>
                            <tfoot>
                            <tr class="_fs_h5">
                                <th><?php esc_html_e('Sub-Total', 'abp-transport-booking'); ?></th>
                                <th class="_color_theme sub_total"></th>
                                <th></th>
                            </tr>
                            </tfoot>
                        </table>
                        <div class="abp_hidden">
                            <table class="abp">
                                <tbody class="hidden_content">
                                <tr class="delete_area">
                                    <th class="seat_name"></th>
                                    <th class="seat_price"></th>
                                    <th>
                                        <div class="_all_center"><?php ABPTB_Layout::button_delete('seat_remove'); ?></div>
                                    </th>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php
        }
    }, 10, 3);