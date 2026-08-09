<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_registration_item_template', function ($post_infos, $form_data = [], $prefix = '') {
        //echo '<pre>';                print_r($prefix);                echo '</pre>';
        //echo '<pre>';                print_r($form_data);                echo '</pre>';
        $seat_type = $post_infos['seat_type'] ?? 'sp';
        $seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
        $double_route = $form_data['double_route'] ?? '';
        ?>

        <div class="booking_item">
            <?php do_action('abptb_type_head', $post_infos, $form_data, $prefix); ?>
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
                    <?php
                        if ($seat_type === 'sp') {
                            ?>
                            <div class="seat_selection">
                                <div class="_section_15_xs">
                                    <table class="_abp">
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
                                        <table class="_abp">
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
                            </div>
                            <?php
                        }
                        do_action('abptb_client_form', $post_infos, $prefix);
                        if (empty($double_route)) {
                            do_action('abptb_total_price', $post_infos, $form_data);
                        }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }, 10, 3);