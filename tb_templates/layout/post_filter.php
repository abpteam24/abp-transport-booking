<?php
    if (!defined('ABSPATH')) {
        exit;
    }
    add_action('abptb_post_filter_template', function ($params) {
        if (!is_array($params)) {
            return;
        }
        $style = $params['style'] ?? 'grid';
        $post_ids = $params['all_post'] ?? [];
        $cat_id = $params['cat_id'] ?? null;
        $brand_id = $params['brand_id'] ?? null;
        $org_id = $params['org_id'] ?? null;
        $loc_id = $params['loc_id'] ?? null;
        $bp_dp = $params['bp_dp'] ?? null;
        $display_category = empty($cat_id) && ABPTB_Function::on_off('category') ? ($params['category'] ?? null) : null;
        $display_brand = empty($brand_id) && ABPTB_Function::on_off('brand') ? ($params['brand'] ?? null) : null;
        $display_organizer = empty($org_id) && ABPTB_Function::on_off('organizer') ? ($params['organizer'] ?? null) : null;
        $display_location = empty($loc_id) ? ($params['location'] ?? null) : null;
        $categories = [];
        $brands = [];
        $organizers = [];
        $locations = [];
        if (is_array($post_ids) && !empty($post_ids)) {
            foreach ($post_ids as $post_id) {
                if ($display_category == 'on') {
                    $category = ABPTB_Function::get_post_info($post_id, 'abptb_category');
                    if (!empty($category)) {
                        $categories[] = $category;
                    }
                }
                if ($display_brand == 'on') {
                    $brand = ABPTB_Function::get_post_info($post_id, 'abptb_brand');
                    if (!empty($brand)) {
                        $brands[] = $brand;
                    }
                }
                if ($display_organizer == 'on') {
                    $organizer = ABPTB_Function::get_post_info($post_id, 'abptb_organizer');
                    if (!empty($organizer)) {
                        $organizers[] = $organizer;
                    }
                }
                if ($display_location == 'on') {
                    $route_stops = ABPTB_Function::get_post_info($post_id, 'route_direction', []);
                    if (is_array($route_stops) && !empty($route_stops)) {
                        foreach ($route_stops as $stop_id) {
                            if (!empty($stop_id)) {
                                $locations[] = $stop_id;
                            }
                        }
                    }
                }
            }
            $categories = array_unique($categories);
            $brands = array_unique($brands);
            $organizers = array_unique($organizers);
            $locations = array_unique($locations);
        }
        $cat_count = count($categories);
        $brand_count = count($brands);
        $organizer_count = count($organizers);
        $location_count = count($locations);
        if ($cat_count > 1 || $brand_count > 1 || $organizer_count > 1 || $location_count > 1 || $style === 'grid' || $style === 'list' || !empty($bp_dp)) {
            ?>
            <div class="post_top_filter">
                <?php if ($cat_count > 1) { ?>
                    <label>
                        <select class="_form_control" name="cat_id">
                            <option value="" selected><?php echo esc_html__('All ', 'abp-transport-booking') . ' ' . esc_html(ABPTB_Function::category_label()); ?></option>
                            <?php foreach ($categories as $current_cat_id) {
                                $name = ABPTB_Category[$current_cat_id]['name'] ?? '';
                                if ($name !== '') { ?>
                                    <option value="<?php echo esc_attr($current_cat_id); ?>"><?php echo esc_html($name); ?></option>
                                <?php }
                            } ?>
                        </select>
                    </label>
                <?php } ?>
                <?php if ($brand_count > 1) { ?>
                    <label>
                        <select class="_form_control" name="brand_id">
                            <option value="" selected><?php echo esc_html__('All ', 'abp-transport-booking') . ' ' . esc_html(ABPTB_Function::brand_label()); ?></option>
                            <?php
                                foreach ($brands as $current_brand_id) {
                                    $name = ABPTB_Brand[$current_brand_id]['name'] ?? '';
                                    if ($name !== '') {
                                        ?>
                                        <option value="<?php echo esc_attr($current_brand_id); ?>"><?php echo esc_html($name); ?></option>
                                        <?php
                                    }
                                }
                            ?>
                        </select>
                    </label>
                <?php } ?>
                <?php if ($organizer_count > 1) { ?>
                    <label>
                        <select class="_form_control" name="org_id">
                            <option value="" selected><?php echo esc_html__('All ', 'abp-transport-booking') . ' ' . esc_html(ABPTB_Function::organizer_label()); ?></option>
                            <?php
                                foreach ($organizers as $current_org_id) {
                                    $name = ABPTB_Organizer[$current_org_id]['name'] ?? '';
                                    if ($name !== '') {
                                        ?>
                                        <option value="<?php echo esc_attr($current_org_id); ?>"><?php echo esc_html($name); ?></option>
                                        <?php
                                    }
                                }
                            ?>
                        </select>
                    </label>
                <?php } ?>
                <?php if ($location_count > 1) { ?>
                    <label>
                        <select class="_form_control" name="loc_id">
                            <option value="" selected><?php echo esc_html__('All ', 'abp-transport-booking') . ' ' . esc_html(ABPTB_Function::location_label()); ?></option>
                            <?php
                                foreach ($locations as $current_loc_id) {
                                    $name = ABPTB_Location[$current_loc_id]['name'] ?? '';
                                    if ($name !== '') {
                                        ?>
                                        <option value="<?php echo esc_attr($current_loc_id); ?>"><?php echo esc_html($name); ?></option>
                                        <?php
                                    }
                                }
                            ?>
                        </select>
                    </label>
                <?php } ?>
                <?php if (!empty($bp_dp)) { ?>
                    <h6 class="abp_gap_xxs">
                        <?php ABPTB_Layout::route_direction($params, $bp_dp, false, false); ?>
                    </h6>
                <?php } ?>
                <?php if ($style === 'grid' || $style === 'list') { ?>
                    <div class="_group_content">
                        <button type="button" class="_btn_light_info_xs_fs_h6 grid_view <?php echo esc_attr($style === 'grid' ? 'abp_active' : ''); ?>"><span class="fas fa-table-cells"></span></button>
                        <button type="button" class="_btn_light_info_xs_fs_h6 list_view <?php echo esc_attr($style === 'list' ? 'abp_active' : ''); ?>"><span class="fas fa-list"></span></button>
                    </div>
                <?php } ?>
            </div>
            <?php
        }
    });
