<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_default_template', function ($params = []) {
        //echo '<pre>';print_r($params);echo '</pre>';
        $post_ids = $params['all_post'] ?? [];
        if (!empty($post_ids) && sizeof($post_ids) > 0) {
            $global_order = $params['global_order'] ?? '';
            $style = ($params['style'] ?? 'grid') ?: 'grid';
            $column = $params['column'] ?? 3;
            $related = $params['related'] ?? '';
            $bp_dp = $params['bp_dp'] ?? '';
            $show_post = absint(($params['show'] ?? 0) ?: ($column * 3));
            $class = $style == 'grid' && $column > 1 ? 'abptb_grid item_' . $column : 'abptb_lists item_' . $column;
            $class = !empty($related) ? 'abptb_grid ' : $class;
            $post_count = 0;
            $args['total'] = sizeof($post_ids);
            $args['page_item'] = $show_post;
            asort($post_ids);
            ?>
            <div class="<?php echo esc_attr($class); ?>">
                <?php foreach ($post_ids as $post_id) {
                    $post_count++;
                    $post_infos = ABPTB_Function::get_all_meta($post_id);
                    $cat_id = $post_infos['abptb_category'] ?? '';
                    $url = get_the_permalink($post_id);
                    $show_class = $show_post >= $post_count ? '' : 'abp_close';
                    //echo '<pre>';print_r($filter_args);echo '</pre>';
                    ?>
                    <div class="pagination_item item_box_1 <?php echo esc_attr($show_class); ?>" data-cat_id="<?php echo esc_attr($cat_id); ?>">
                        <div class="item_head">
                            <?php
                                ABPTB_Layout::image($post_id);
                                ABPTB_Layout::category($post_infos, 'ribbon');
                                ABPTB_Layout::capacity($post_infos, 'ribbon publish');
                            ?>
                            <div class="ribbon publish _ab_bottom_right_xs"><?php ABPTB_Layout::route_direction($post_infos, $bp_dp); ?></div>
                        </div>
                        <div class="item_body">
                            <div class="">
                                <a class="abp list_title" href="<?php echo esc_url($url); ?>" target="_blank">
                                    <?php ABPTB_Layout::title($post_infos); ?>
                                </a>
                                <div class="_divider_xxs"></div>
                                <?php
                                    ABPTB_Layout::brand($post_infos, 'publish');
                                    ABPTB_Layout::organizer($post_infos, 'publish');
                                    ABPTB_Layout::item_feature($post_infos['post_feature'] ?? '');
                                    ABPTB_Layout::description($post_infos); ?>
                            </div>
                            <div>
                                <div class="_divider_xxs"></div>
                                <div class="_fj_between">
                                    <?php if (!empty($bp_dp)) {
                                        $price = ABPTB_Function::get_price($post_infos, $bp_dp);
                                        $price = $price > 0 ? ABPTB_Function::tax_with_price($post_id, $price) : 0;
                                        ?>
                                        <span class="price_value">
                                                <?php
                                                    esc_html_e('Price :', 'abp-transport-booking');
                                                    echo $price > 0 ? wp_kses_post(wc_price($price)) : esc_html__('Free', 'abp-transport-booking');
                                                ?>
                                            </span>
                                    <?php } ?>
                                    <?php if (!empty($global_order)) { ?>
                                        <button type="button" class="_btn_theme_xs select_post" data-post_id="<?php echo esc_attr($post_id); ?>">
                                            <?php esc_html_e('Book Now', 'abp-transport-booking'); ?>
                                        </button>
                                    <?php } else { ?>
                                        <button type="button" class="_btn_theme_xs" data-href="<?php echo esc_url($url); ?>" data-blank="_blank">
                                            <?php esc_html_e('Book Now', 'abp-transport-booking'); ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php if (empty($related)) {
                do_action('abptb_pagination', $args);
            } ?>
            <?php
        } else {
            ABPTB_Layout::layout_warning_info('not_found');
        }
    }, 10, 2);