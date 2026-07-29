<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptf_default_template', function ($params = []) {
        //echo '<pre>';print_r($params);echo '</pre>';
        $post_ids = $params['all_post'] ?? [];
        if (!empty($post_ids) && sizeof($post_ids) > 0) {
            $global_order = $params['global_order'] ?? '';
            $style = ($params['style'] ?? 'grid') ?: 'grid';
            $column = $params['column'] ?? 3;
            $related = $params['related'] ?? '';
            $bp_dp = $params['bp_dp'] ?? '';
            $show_post = absint(($params['show'] ?? 0) ?: ($column * 3));
            $class = $style == 'grid' && $column > 1 ? 'abptf_grid item_' . $column : 'abptf_lists item_' . $column;
            $class = !empty($related) ? 'abptf_grid ' : $class;
            $post_count = 0;
            $args['total'] = sizeof($post_ids);
            $args['page_item'] = $show_post;
            asort($post_ids);
            ?>
            <div class="<?php echo esc_attr($class); ?>">
                <?php foreach ($post_ids as $post_id) {
                    $post_count++;
                    $post_infos = ABPTF_Function::get_all_meta($post_id);
                    $cat_id = $post_infos['abptf_category'] ?? '';
                    $url = get_the_permalink($post_id);
                    $show_class = $show_post >= $post_count ? '' : 'abp_close';
                    //echo '<pre>';print_r($filter_args);echo '</pre>';
                    ?>
                    <div class="pagination_item item_box_1 <?php echo esc_attr($show_class); ?>" data-cat_id="<?php echo esc_attr($cat_id); ?>">
                        <div class="item_head">
                            <?php
                                ABPTF_Layout::image($post_id);
                                ABPTF_Layout::category($post_infos,'ribbon');
                            ?>
                            <div class="ribbon publish _ab_bottom_right_xs"><?php ABPTF_Layout::route_direction($post_infos,$bp_dp); ?></div>
                        </div>
                        <div class="item_body">
                            <div>
                                <a class="_abp list_title" href="<?php echo esc_url($url); ?>" target="_blank">
                                    <?php ABPTF_Layout::title($post_infos);?>
                                </a>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::capacity($post_infos ,'ribbon publish');
                                 ABPTF_Layout::brand($post_infos ,'publish');
                                 ABPTF_Layout::organizer($post_infos ,'publish');



                                    ABPTF_Layout::item_feature($post_infos['post_feature'] ?? '');
                                    ABPTF_Layout::description($post_infos);?>
                            </div>
                            <div>
                                <div class="_divider_xxs"></div>
                                <div class="_fj_between">
                                    <?php if (!empty($bp_dp)) {
                                        $price = ABPTF_Function::get_price($post_infos, $bp_dp);
                                        $price = $price > 0 ? ABPTF_Function::tax_with_price($post_id, $price) : 0;
                                        ?>
                                        <span class="price_value">
                                                <?php
                                                    esc_html_e('Price :', 'abp-transportforge');
                                                    echo $price > 0 ? wp_kses_post(wc_price($price)) : esc_html__('Free', 'abp-transportforge');
                                                ?>
                                            </span>
                                    <?php } ?>
                                    <?php if (!empty($global_order)) { ?>
                                        <button type="button" class="_btn_theme_xs select_post" data-post_id="<?php echo esc_attr($post_id); ?>">
                                            <?php esc_html_e('Book Now', 'abp-transportforge'); ?>
                                        </button>
                                    <?php } else { ?>
                                        <button type="button" class="_btn_theme_xs" data-href="<?php echo esc_url($url); ?>" data-blank="_blank">
                                            <?php esc_html_e('Book Now', 'abp-transportforge'); ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php if (empty($related)) {
                do_action('abptf_pagination', $args);
            } ?>
            <?php
        } else {
            ABPTF_Layout::layout_warning_info('not_found');
        }
    }, 10, 2);