<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('search_form_template', function ($post_infos = [], $form_data = []) {
        $admin_order = $post_infos['admin_order'] ?? '';
        $global_order = $post_infos['global_order'] ?? '';
        $post_id = absint($post_infos['post_id'] ?? 0);
        $all_post_ids = $post_infos['all_post'] ?? [$post_id];
        $form_type = $form_data['form'] ?? 'inline';
        $brand_icon = ABPTF_Function::icon();
        if (isset($_SESSION['abptf_cart_success']) && empty($admin_order)) {
            ?>
            <div class="toast_notice" data-type="success">
                <?php echo esc_html(sanitize_text_field(wp_unslash($_SESSION['abptf_cart_success']))); ?>
            </div>
            <?php
            unset($_SESSION['abptf_cart_success']);
        }

       //echo '<pre>';        print_r(ABPTF_Function::get_route_info());        echo '</pre>';
        $all_dates = ABPTF_Function::date_all($all_post_ids);
        $upcoming_date = current($all_dates);
        $upcoming_date = !empty($upcoming_date) ? gmdate('Y-m-d', strtotime($upcoming_date)) : '';
        $display_return = $post_infos['display_return'] ?? 'on';
        $display_return = ABPTF_Function::on_off('return') ? $display_return : 'off';
        ?>
        <div id="abptf_search_area">
            <h5 class="_abp_mar_b_xs">
                <span class="_mar_r_xxs fas fa-ticket"></span><?php esc_html_e('BUY TICKET', 'abp-transportforge'); ?>
            </h5>
            <form class="search_form <?php echo esc_attr($form_type === 'column' ? '_form_column' : '_form_inline'); ?>" method="post" action="">
                <?php if ($post_id > 0 && empty($global_order)) { ?>
                    <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>"/>
                <?php } else {
                    ABPTF_Layout::filter_post_list();
                } ?>
                <div class="abptf_bp _input_item abp_dropdown">
                    <label>
                        <span><i class="fas fa-map-marker-alt _mar_r_xxs"></i><?php esc_html_e('From', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></span>
                        <input type="hidden" name="_bp" value=""/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php esc_attr_e('Select Boarding Point', 'abp-wc-transport-manager'); ?>" value=""/>
                    </label>
                    <div class="dropdown_list">
                        <ul class="_abp ">
                        </ul>
                    </div>
                </div>
                <div class="abptf_dp _input_item abp_dropdown">
                    <label>
                        <span><i class="fas fa-map-marker-alt _mar_r_xxs"></i><?php esc_html_e('To', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></span>
                        <input type="hidden" name="_dp" value=""/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php esc_attr_e('Select Dropping Point', 'abp-wc-transport-manager'); ?>" value=""/>
                    </label>
                    <div class="dropdown_list">
                        <ul class="_abp ">
                        </ul>
                    </div>
                </div>
                <div class="journey_date _input_item">
                    <?php ABPTF_Layout::journey_date($all_dates, $upcoming_date); ?>
                </div>
                <?php if ($display_return == 'on') { ?>
                    <div class="return_date _input_item">
                        <?php ABPTF_Layout::return_date($all_dates); ?>
                    </div>
                <?php } ?>
                <div class="_input_item_fj_between_fd_column">
                    <span></span>
                    <button type="submit" class="_btn_theme">
                        <?php ABPTF_Layout::image_icon($brand_icon); ?>
                        <?php esc_html_e('Check Availability', 'abp-transportforge'); ?>
                    </button>
                </div>
            </form>
            <div class="date_details"></div>
        </div>
        <?php
    }, 10, 2);