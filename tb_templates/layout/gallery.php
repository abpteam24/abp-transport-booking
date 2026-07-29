<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptb_gallery_template', function ($img_ids =[], $abptb_slider = []) {
        if ( empty( $img_ids ) || ! is_array( $img_ids ) ) {
            return;
        }
        $popup_id = uniqid('#abptb_slider_');
        $active_popup = $abptb_slider['visible_popup'] ?? 'on';
        $image_column = absint($abptb_slider['image_column'] ?? 3);
        $show_item = $abptb_slider['show_item'] ?? '';
        $post_count = 0;
        $image_column = ($image_column > 0 && $image_column < 11) ? $image_column : 3;
        $total_images = count($img_ids);
        ?>
        <div class="abptb_gallery">
            <div class="gallery_area item_<?php echo esc_attr($image_column); ?>">
                <?php
                    foreach ($img_ids as $img_id) {
                        if (!$img_id) {
                            continue;
                        }
                        $add_class = '';
                        if (!empty($show_item)) {
                            $add_class = (absint($show_item) >= $post_count) ? 'pagination_item' : 'pagination_item abp_close';
                        }
                        $post_count++;
                        $url = ABPTB_Function::get_image_url('', $img_id) ?: ABPTB_BLANK_IMG_URL;
                        ?>
                        <div class="gallery_item <?php echo esc_attr($add_class); ?>" data-img="<?php echo esc_url($url); ?>" <?php if ($active_popup === 'on') { ?>data-target-popup="<?php echo esc_attr($popup_id); ?>"<?php } ?>>
                            <img src="#" alt="<?php echo esc_html($img_id); ?>"/>
<!--                            <div class="item_caption">-->
<!--                                <div class="caption_label">--><?php //echo esc_html($img_post); ?><!--</div>-->
<!--                                <div class="caption_title">--><?php //echo esc_html($img_label); ?><!--</div>-->
<!--                            </div>-->
                        </div>
                    <?php } ?>
            </div>
            <?php
                if (!empty($show_item)) {
                    $args = [
                        'total' => $total_images,
                        'page_item' => $show_item,
                    ];
                    do_action('abptb_pagination', $args);
                }
            ?>
        </div>
        <?php
        do_action('abptb_slider_popup', $abptb_slider, $img_ids, $popup_id);
    }, 10, 2);