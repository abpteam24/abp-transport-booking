<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptf_details_default_template', function ($post_id, $form_data = []) {
        if ($post_id > 0) {
            $post_infos = ABPTF_Function::get_all_meta($post_id);
            $form_data['form'] = 'inline';
            $bp_dp = $form_data['bp_dp'] ?? '';
            $display_return = $post_infos['display_return'] ?? 'off';
            $display_return = ABPTF_Function::on_off('return') ? $display_return : 'off';
            //echo '<pre>';print_r(ABPTF_Function::get_route_info());echo '</pre>';
            ?>
            <div id="abptf_area" class="abptf_area default_details_page">
                <div class="abptf_container">
                    <div class="_abp_row">
                        <div class="_f_equal_f_wrap_gap _section_15">
                            <div class="_min_500"><?php ABPTF_Layout::image($post_id); ?></div>
                            <div class="_min_500">
                                <h1 class="_abp_color_theme"><?php ABPTF_Layout::title($post_infos); ?></h1>
                                <?php ABPTF_Layout::sub_title($post_infos); ?>
                                <div class="_section_dot_xs_mar_t_xs">
                                    <div class="_abp_color_active_fs_label"><?php ABPTF_Layout::route_direction($post_infos, $bp_dp); ?></div>
                                    <?php if ($display_return == 'on') { ?>
                                        <div class="_abp_color_burnt_orange">
                                            <?php ABPTF_Layout::route_direction($post_infos, '', true); ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="_gap_xs">
                                    <?php ABPTF_Layout::capacity($post_infos);
                                        ABPTF_Layout::category($post_infos);
                                        ABPTF_Layout::brand($post_infos);
                                        ABPTF_Layout::organizer($post_infos, 'publish');
                                    ?>
                                </div>
                                <?php ABPTF_Layout::item_feature($post_infos['post_feature'] ?? '');
                                    ABPTF_Layout::description($post_infos);                          ?>
                            </div>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12">
                            <?php do_action('abptf_content', $post_id); ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12">
                            <?php do_action('abptf_search_form', $post_infos, $form_data); ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12 abptf_booking">
                            <?php do_action('abptf_registration', $post_infos, $form_data); ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_f_equal_f_wrap_gap _w_full_mar_t">
                            <div class="_min_500">
                                <?php do_action('abptf_faq', $post_infos); ?>
                            </div>
                            <div class="_min_500">
                                <?php do_action('abptf_term_condition', $post_infos); ?>
                            </div>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12_mar_t"><?php do_action('abptf_slider', ($post_infos['abptf_slider'] ?? [])); ?></div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12_mar_t"> <?php do_action('abptf_related_item', ($post_infos['related_item'] ?? '')); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    });
