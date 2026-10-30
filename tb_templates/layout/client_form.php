<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_client_form_template', function ($post_infos, $form_data = [], $prefix = '') {
        $forms = ABPTB_Function::client_data($post_infos);
        if (!empty($forms) && is_array($forms)) {
            $booking_item = $form_data['booking_item'] ?? [];
            $passenger_infos = json_decode($booking_item['pass_info'] ?? '', true) ?: [];
            ?>
            <div class="client_info_area">
                <?php if (!empty($passenger_infos)) {
                    foreach ($passenger_infos as $passenger_info) {
                        //echo '<pre>';        print_r($passenger_info);        echo '</pre>';?>
                        <div class="item_box_1 attendee_item">
                            <h5 class=" abp_title">
                                <?php esc_html_e('Passenger Information : ', 'abp-transport-booking'); ?>&nbsp;<span class="_color_theme attendee_seat_name"></span>
                            </h5>
                            <?php foreach ($forms as $id => $form) {
                                //echo '<pre>';        print_r($id);        echo '</pre>';
                                //echo '<pre>';        print_r($form);        echo '</pre>';
                                $form['d_value'] = array_key_exists($id, $passenger_info) ? ($passenger_info[$id]['value'] ?? '') : '';
                                ABPTB_Layout::create_client_form($form, $id, $prefix);
                            } ?>
                        </div>
                    <?php }
                } else { ?>
                    <div class="item_box_1 attendee_item">
                        <h5 class=" abp_title">
                            <?php esc_html_e('Passenger Information : ', 'abp-transport-booking'); ?>&nbsp;<span class="_color_theme attendee_seat_name"></span>
                        </h5>
                        <?php foreach ($forms as $id => $form) {
                            ABPTB_Layout::create_client_form($form, $id, $prefix);
                        } ?>
                    </div>
                <?php } ?>
            </div>
            <?php
        }
    }, 10, 3);