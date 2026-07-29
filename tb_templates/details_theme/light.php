<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_details_light_template', function ($post_id) {
        if ($post_id > 0) {
            $post_infos = ABPTB_Function::get_all_meta($post_id);
            $form_data['form'] = 'inline';
            $display_return = $post_infos['display_return'] ?? 'off';
            $display_return=ABPTB_Function::on_off('return')?$display_return:'off';
            //echo '<pre>';print_r(ABPTB_Function::get_route_info());echo '</pre>';
            ?>
            <div id="abptb_area" class="abptb_area default_details_page">
                <div class="abptb_container">
                    <div class="_abp_row">
                        <div class="_f_equal_f_wrap_gap _w_full">
                            <div class="_section_15_min_500"><?php ABPTB_Layout::image($post_id); ?></div>
                            <div class="_section_15_min_500">
                                <h1 class="_abp_color_theme"><?php ABPTB_Layout::title($post_infos);?></h1>
                                <?php ABPTB_Layout::sub_title($post_infos); ?>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::capacity($post_infos);
                                    ABPTB_Layout::category($post_infos);
                                    ABPTB_Layout::brand($post_infos);
                                    ABPTB_Layout::organizer($post_infos ,'publish');
                                    ABPTB_Layout::description($post_infos);    ?>
                                <div class="_divider_xxs"></div>
                                <div class="_abp_color_active_fs_label"><?php ABPTB_Layout::route_direction($post_infos); ?></div>
                                <?php if ($display_return=='on') { ?>
                                    <div class="_abp_color_burnt_orange">
                                        <?php ABPTB_Layout::route_direction($post_infos,'',true); ?>
                                    </div>
                                <?php } ?>
                                <?php if (ABPTB_Function::on_off('return')) { ?>
                                    <div class="_abp_color_burnt_orange_fs_label">
                                        <?php ABPTB_Layout::route_direction($post_infos, '',true); ?>
                                        <?php esc_html_e('(Return)', 'abp-transport-booking'); ?>
                                    </div>
                                <?php } ?>
                                <?php ABPTB_Layout::item_feature($post_infos['post_feature'] ?? ''); ?>
                            </div>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12">
                            <?php do_action('abptb_content', $post_id); ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12">
                            <?php do_action('abptb_search_form', $post_infos, $form_data); ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12 abptb_booking">
                            <?php do_action('abptb_registration', $post_infos, $form_data); ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_f_equal_f_wrap_gap _w_full_mar_t">
                            <div class="_min_500">
                                <?php do_action('abptb_faq', $post_infos); ?>
                            </div>
                            <div class="_min_500">
                                <?php do_action('abptb_term_condition', $post_infos); ?>
                            </div>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12_mar_t"><?php do_action('abptb_slider', ($post_infos['abptb_slider'] ?? [])); ?></div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12_mar_t"> <?php do_action('abptb_related_item', ($post_infos['related_item'] ?? '')); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    });
