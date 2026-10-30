<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_search_form_template', function ($post_infos = [], $form_data = []) {
        $admin_order = $post_infos['admin_order'] ?? '';
        $global_order = $post_infos['global_order'] ?? '';
        $booking_item = $post_infos['booking_item'] ?? [];
        $_post_id = $booking_item['post_id'] ?? '';
        $bp = $booking_item['bp'] ?? '';
        $dp = $booking_item['dp'] ?? '';
        $post_id = absint($post_infos['post_id'] ?? 0);
        $post_id = !empty($_post_id) ? $_post_id : $post_id;
        $all_post_ids = $post_infos['all_post'] ?? [$post_id];
        $form_type = $form_data['form'] ?? 'inline';
        if (isset($_SESSION['abptb_cart_success']) && empty($admin_order)) {
            ?>
            <div class="toast_notice" data-type="success">
                <?php echo esc_html(sanitize_text_field(wp_unslash($_SESSION['abptb_cart_success']))); ?>
            </div>
            <?php
            unset($_SESSION['abptb_cart_success']);
        }
        $all_dates = ABPTB_Function::date_all($all_post_ids);
        $upcoming_date = current($all_dates);
        $upcoming_date = !empty($upcoming_date) ? gmdate('Y-m-d', strtotime($upcoming_date)) : '';
        if (!empty($booking_item)) {
            $upcoming_date = $booking_item['start_time'] ?? '';
            $upcoming_date = !empty($upcoming_date) ? gmdate('Y-m-d', strtotime($upcoming_date)) : '';
        }
        $display_return = $post_infos['display_return'] ?? 'on';
        $display_return = ABPTB_Function::on_off('return') ? $display_return : 'off';
       // echo '<pre>';        print_r($bp);        echo '</pre>';
        ?>
        <div id="abptb_search_area">
            <?php if (!empty($booking_item)) {
                do_action('abptb_before_booking_borm', $booking_item);
            } else { ?>
                <h5 class="abp_gap_xs_color_theme"><span class="fas fa-ticket"></span><?php esc_html_e('BUY TICKET', 'abp-transport-booking'); ?></h5>
                <div class="_divider_xxs"></div>
            <?php } ?>

            <form class="abp_search_form <?php echo esc_attr($form_type === 'column' ? '_form_column' : '_form_inline'); ?>" method="post" action="">
                <?php if (!empty($booking_item)) { ?>
                    <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking_item['id'] ?? ''); ?>"/>
                    <?php
                }
                    if ($post_id > 0 && empty($global_order)) { ?>
                        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>"/>
                    <?php } else {
                        ABPTB_Layout::filter_post_list($_post_id);
                    }
                    ABPTB_Layout::filter_bp($bp);
                    ABPTB_Layout::filter_dp($dp);
                ?>
                <div class="journey_date _input_item">
                    <?php ABPTB_Layout::journey_date($all_dates, $upcoming_date); ?>
                </div>
                <?php if ($display_return == 'on' && empty($booking_item)) { ?>
                    <div class="return_date _input_item">
                        <?php ABPTB_Layout::return_date($all_dates); ?>
                    </div>
                <?php } ?>
                <div class="_input_item_fj_between_fd_column">
                    <button type="submit" class="_btn_theme">
                        <?php ABPTB_Layout::image_icon(ABPTB_Function::icon()); ?>
                        <?php esc_html_e('Check Availability', 'abp-transport-booking'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }, 10, 2);