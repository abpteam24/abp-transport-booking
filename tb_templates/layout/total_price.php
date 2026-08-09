<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_total_price_template', function ($post_infos = [], $form_data = []) {
        $post_id = absint($post_infos['post_id'] ?? 0);
        if ($post_id <= 0) {
            return;
        }
        $wc_link = absint($post_infos['link_wc_id'] ?? ABPTB_Function::get_post_info($post_id, 'link_wc_id', 0));
        if ($wc_link <= 0) {
            return;
        }
        $display_additional = $post_infos['display_additional_services'] ?? ABPTB_Function::get_post_info($post_id, 'display_additional_services', 'on');
        $double_route = $form_data['double_route'] ?? '';
        //echo '<pre>';                print_r($double_route);                echo '</pre>';
        //echo '<pre>';                print_r($form_data);                echo '</pre>';
        ?>
        <div class="total_continue_area">
            <div class="total_continue item_box_1">
                <div class="_fd_column_max_400">
                    <h5 class="_abp _f_equal price_up">
                        <span><?php esc_html_e('Ticket Price : ', 'abp-transport-booking'); ?>&nbsp;</span>
                        <span class="item_total _color_theme_text_right"></span>
                    </h5>
                    <?php if ($double_route) { ?>
                        <h5 class="_abp _f_equal price_down">
                            <span><?php esc_html_e('Ticket Price (Return) : ', 'abp-transport-booking'); ?>&nbsp;</span>
                            <span class="item_total _color_theme_text_right"></span>
                        </h5>
                    <?php } ?>
                    <?php if (ABPTB_Function::on_off('additional_info') && $display_additional === 'on') { ?>
                        <h5 class="_abp _f_equal ex_price_up">
                            <span><?php esc_html_e('Additional : ', 'abp-transport-booking'); ?>&nbsp;</span>
                            <span class="additional_total _color_theme_text_right"></span>
                        </h5>
                        <?php if ($double_route) { ?>
                            <h5 class="_abp _f_equal ex_price_down">
                                <span><?php esc_html_e('Additional (Return) : ', 'abp-transport-booking'); ?>&nbsp;</span>
                                <span class="additional_total _color_theme_text_right"></span>
                            </h5>
                        <?php } ?>
                    <?php } ?>
                    <div class="_divider_xs"></div>
                    <h5 class="_abp _f_equal">
                        <span><?php esc_html_e('Total : ', 'abp-transport-booking'); ?>&nbsp;</span>
                        <span class="abptb_total _color_theme_text_right"></span>
                    </h5>
                </div>
                <?php if (is_admin() && str_contains(wp_get_referer(), 'admin_order')) { ?>
                    <input type="submit" class="_d_none" name="add-admin-order" value="<?php echo esc_attr($wc_link); ?>"/>
                <?php } else { ?>
                    <input type="submit" class="_d_none" name="add-to-cart" value="<?php echo esc_attr($wc_link); ?>"/>
                <?php } ?>
                <button class="_btn_light_theme book_continue" type="button" data-alert="<?php esc_attr_e('No Ticket Selected ! Please Select Ticket', 'abp-transport-booking'); ?>" data-msg="<?php esc_attr_e('Added to Cart Successfully', 'abp-transport-booking'); ?>">
                    <?php esc_html_e('Continue', 'abp-transport-booking'); ?>
                    <span class="fas fa-angle-double-right _mar_l_xs"></span>
                </button>
            </div>
        </div>
        <?php
    }, 10, 2);