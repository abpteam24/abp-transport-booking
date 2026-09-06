<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_details_premium_template', function ($post_id, $form_data = []) {
        if (!empty($post_id) && $post_id > 0 && get_post_type($post_id) == ABPTB_Function::get_cpt() && (get_post_status($post_id) == 'publish' || is_admin())) {
            $post_infos = ABPTB_Function::get_all_meta($post_id);
            $form_data['form'] = 'inline';
            $bp_dp = $form_data['bp_dp'] ?? '';
            $display_return = $post_infos['display_return'] ?? 'off';
            $display_return = ABPTB_Function::on_off('return') ? $display_return : 'off';
            $content = get_post_field('post_content', $post_id);
            $slider_images = $post_infos['abptb_slider'] ?? '';
            ?>
            <div class="abptb_area premium details_page">
                <div class="abp_container">
                    <div class="abp_row">
                        <div class="_section_15_grid_500 premium_head">
                            <div class="premium_head_info">
                                <h1 class="details_title"><?php ABPTB_Layout::title($post_infos); ?></h1>
                                <?php ABPTB_Layout::sub_title($post_infos); ?>
                                <div class="details_route">
                                    <div class="details_route_go"><?php ABPTB_Layout::route_direction($post_infos, $bp_dp); ?></div>
                                    <?php if ($display_return == 'on') { ?>
                                        <div class="details_route_return"><?php ABPTB_Layout::route_direction($post_infos, '', true); ?></div>
                                    <?php } ?>
                                </div>
                                <div class="details_meta">
                                    <?php ABPTB_Layout::brand($post_infos);
                                        ABPTB_Layout::organizer($post_infos, 'publish'); ?>
                                </div>
                                <div class="details_facts">
                                    <?php ABPTB_Layout::item_feature($post_infos['post_feature'] ?? '');
                                        ABPTB_Layout::description($post_infos); ?>
                                </div>
                            </div>
                            <div class="premium_head_side">
                                <div class="premium_booking_form">
                                    <?php do_action('abptb_search_form', $post_infos, $form_data); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($content)) { ?>
                        <div class="abp_row">
                            <div class="_col_12">
                                <div class="the_post_content">
                                    <?php the_content(); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="abp_row">
                        <div class="_col_12 abptb_booking">
                            <?php do_action('abptb_registration', $post_infos, $form_data); ?>
                        </div>
                    </div>
                    <div class="abp_row">
                        <div class="_grid_500">
                            <div class="_fd_column_gap">
                                <?php do_action('abptb_faq', $post_infos); ?>
                                <div class="premium_hero_media">
                                    <?php if (!empty($slider_images)) {
                                        do_action('abptb_slider', $slider_images);
                                    } else {
                                        ABPTB_Layout::image($post_id);
                                    } ?>
                                </div>
                            </div>
                            <?php do_action('abptb_term_condition', $post_infos); ?>
                        </div>
                    </div>
                    <div class="abp_row">
                        <div class="_col_12"> <?php do_action('abptb_related_item', ($post_infos['related_item'] ?? '')); ?></div>
                    </div>
                </div>
            </div>
            <?php
        }
    });