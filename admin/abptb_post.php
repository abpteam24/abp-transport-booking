<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Post')) {
        class ABPTB_Post {
            public function __construct() {
                add_action('abptb_load_posts', array($this, 'load_posts'));
                add_action('add_meta_boxes', [$this, 'settings_meta']);
                add_action('save_post', array($this, 'save_settings'));
                add_action('wp_ajax_abptb_post_permanent_remove', array($this, 'post_permanent_remove'));
                add_action('wp_ajax_abptb_post_move_trash', array($this, 'post_move_trash'));
                add_action('wp_ajax_abptb_post_restore', array($this, 'post_restore'));
                add_action('wp_ajax_abptb_reload_post_list', array($this, 'reload_post_list'));
            }
            public function load_posts($abptb_info = []): void {
                $total_posts = $abptb_info['total_post'] ?? 0;
                $total_publish = $abptb_info['total_publish'] ?? 0;
                $total_draft = $abptb_info['total_draft'] ?? 0;
                $total_private = $abptb_info['total_private'] ?? 0;
                $total_trash = $abptb_info['total_trash'] ?? 0;
                $status = 'publish';
                if (isset($_GET['_abptb_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_abptb_nonce'])), 'abptb_url_action')) {
                    $status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : 'publish';
                }
                $status = $status ?? 'publish';
                $filter_args['status'] = $status;
                ?>
                <div class="abptb_posts _section_card">
                    <div class="_fj_between_f_wrap">
                        <div class="_group_content">
                            <input type="hidden" name="select_hidden_post_status" value="<?php echo esc_attr($status); ?>"/>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr($status == 'all' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTB_Function::build_url('posts', ['status' => 'all'])); ?>"><?php esc_html_e('All', 'abp-transport-booking'); ?> ( <?php echo esc_html($total_posts); ?> )</button>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr($status == 'publish' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTB_Function::build_url('posts', ['status' => 'publish'])); ?>"><?php esc_html_e('Published', 'abp-transport-booking'); ?> ( <?php echo esc_html($total_publish); ?> )</button>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr($status == 'private' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTB_Function::build_url('posts', ['status' => 'private'])); ?>"><?php esc_html_e('Private', 'abp-transport-booking'); ?> ( <?php echo esc_html($total_private); ?> )</button>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr($status == 'draft' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTB_Function::build_url('posts', ['status' => 'draft'])); ?>"><?php esc_html_e('Draft', 'abp-transport-booking'); ?> ( <?php echo esc_html($total_draft); ?> )</button>
                            <button type="button" class="_btn_light_active_xs <?php echo esc_attr($status == 'trash' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTB_Function::build_url('posts', ['status' => 'trash'])); ?>"><?php esc_html_e('Trash', 'abp-transport-booking'); ?> ( <?php echo esc_html($total_trash); ?> )</button>
                        </div>
                        <a class="_btn_navy_blue_xs" href="<?php echo esc_url(admin_url('post-new.php?post_type=' . ABPTB_Function::get_cpt())); ?>">
                            <?php ABPTB_Static::icon_svg('plus');
                                esc_html_e('Add New Transport', 'abp-transport-booking'); ?>
                        </a>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class=" post_list">
                        <?php $this->post_table($filter_args); ?>
                    </div>
                </div>
                <?php
            }
            public function settings_meta(): void {
                $label = ABPTB_Function::label();
                $brand_icon = ABPTB_Function::icon();
                $label = $label . ' ' . __('Configuration', 'abp-transport-booking') . get_the_title(get_the_id());
                add_meta_box('abptb_configuration', '<span class="' . esc_attr($brand_icon ?: '') . '"></span>' . esc_html($label), array($this, 'settings'), esc_attr(ABPTB_Function::get_cpt()), 'normal', 'high');
            }
            //=============================//
            public function post_table($filter_args): void {
                //echo '<pre>';print_r($filter_args);echo '</pre>';
                $status = $filter_args['status'] ?? '';
                if (empty($status) || $status == 'all') {
                    $status = ['publish', 'draft', 'private', 'trash'];
                }
                $page_number = absint($filter_args['page_number'] ?? 1) ?: 1;
                $limit = absint(($filter_args['page_item'] ?? 0) ?: ABPTB_Function::get_option('abptb_per_page_item', 20));
                $count = ($page_number - 1) * $limit + 1;
                $cpt = ABPTB_Function::get_cpt();
                $filters['status'] = $status;
                $filters['posts_per_page'] = $limit;
                $filters['paged'] = $page_number - 1;
                //echo '<pre>';print_r($filters);echo '</pre>';
                $post_ids = ABPTB_Query::get_post_id($filters);
                if (!empty($post_ids) && sizeof($post_ids) > 0) {
                    $total_post = sizeof(ABPTB_Query::get_post_id(['status' => $status]));
                    $new_post_url = admin_url('post-new.php?post_type=' . $cpt);
                    ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th class="_w_50"><?php esc_html_e('SI', 'abp-transport-booking'); ?></th>
                            <th class="_w_100"><?php esc_html_e('Image', 'abp-transport-booking'); ?></th>
                            <th><?php echo esc_html(ABPTB_Function::label()); ?></th>
                            <th><span class="fas fa-route _mar_r_xxs"></span><?php esc_html_e('Route', 'abp-transport-booking'); ?></th>
                            <th><?php esc_html_e('Ticket Type & Qty', 'abp-transport-booking'); ?></th>
                            <th><?php esc_html_e('Shortcode', 'abp-transport-booking'); ?></th>
                            <th><?php esc_html_e('Actions', 'abp-transport-booking'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                            foreach ($post_ids as $post_id) {
                                $post_infos = ABPTB_Function::get_all_meta($post_id);
                                $title = $post_infos['post_title'] ?? '';
                                $seat_type = $post_infos['seat_type'] ?? 'sp';
                                $seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
                                $edit_link = get_edit_post_link($post_id);
                                $sale_continue = $post_infos['sale_continue'] ?? 'on';
                                $display_return = $post_infos['display_return'] ?? 'off';
                                $display_return = ABPTB_Function::on_off('return') ? $display_return : 'off';
                                $post_status = get_post_status($post_id);
                                $new_post_url = add_query_arg(array('copy_post' => $post_id, '_abptb_nonce' => wp_create_nonce('abptb_copy_post_action'),), $new_post_url);
                                ?>
                                <tr>
                                    <th><?php echo esc_html($count); ?>.</th>
                                    <td><?php ABPTB_Layout::image($post_id); ?></td>
                                    <td>
                                        <div class="_mar_b_xxs">
                                            <?php if ($post_status == 'trash') { ?>
                                                <h6 class="_abp_color_warning"><?php ABPTB_Layout::title($post_infos); ?></h6>
                                            <?php } else { ?>
                                                <a href="<?php echo esc_url($edit_link); ?>" class="_abp_fs_h6_color_theme"><?php ABPTB_Layout::title($post_infos); ?></a>
                                            <?php } ?>
                                        </div>
                                        <div class="_gap_xxs">
                                            <span class="abp_tag"><?php echo esc_html(__('ID : ', 'abp-transport-booking') . ' ' . $post_id); ?></span>
                                            <span class=" abp_tag <?php echo esc_attr($sale_continue == 'on' ? 'publish' : 'trash'); ?>">
                                                <?php echo esc_html($sale_continue == 'on' ? __('Sale On', 'abp-transport-booking') : __('Sale Off', 'abp-transport-booking')); ?>
                                            </span>
                                            <span class="abp_tag <?php echo esc_attr($post_status); ?>"><?php echo esc_html($post_status); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="_abp_color_active"><?php ABPTB_Layout::route_direction($post_infos); ?></div>
                                        <?php if ($display_return == 'on') { ?>
                                            <div class="_abp_color_burnt_orange">
                                                <?php ABPTB_Layout::route_direction($post_infos, '', true); ?>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <th><?php echo esc_html(ABPTB_Layout::ticket_type($seat_type) . ' - ' . ABPTB_Function::get_total_qty($post_id, $post_infos)); ?></th>
                                    <th><code> [abptb-post post_id="<?php echo esc_attr($post_id); ?>"]</code></th>
                                    <th>
                                        <div class="_group_content">
                                            <button type="button" class="_btn_light_navy_blue_xxs" data-href="<?php echo esc_url($new_post_url); ?>" data-blank="_blank" title="<?php echo esc_html__('Copy/Clone : ', 'abp-transport-booking') . ' ' . esc_html($title); ?>"><?php ABPTB_Static::icon_svg('clone_1'); ?></button>
                                            <?php if ($post_status == 'trash') { ?>
                                                <button type="button" class="_btn_light_success_xxs " onclick="abptb_post_action('restore','<?php echo esc_attr($post_id); ?>')" title="<?php echo esc_html__('Restore : ', 'abp-transport-booking') . ' ' . esc_html($title); ?>">♻️</button>
                                                <button type="button" class="_btn_light_danger_xxs" onclick="abptb_post_action('permanent_remove','<?php echo esc_attr($post_id); ?>')" title="<?php echo esc_html__('Permanent Remove : ', 'abp-transport-booking') . ' ' . esc_html($title); ?>"><?php ABPTB_Static::icon_svg('close_2'); ?></button>
                                            <?php } else { ?>
                                                <button type="button" class="_btn_light_yellow_xxs" data-href="<?php echo esc_url($edit_link); ?>" data-blank="_blank" title="<?php echo esc_html__('Edit : ', 'abp-transport-booking') . ' ' . esc_html($title); ?>"><?php ABPTB_Static::icon_svg('edit'); ?></button>
                                                <button type="button" class="_btn_light_theme_xxs" data-href="<?php echo esc_url(get_permalink($post_id)); ?>" data-blank="_blank" title="<?php echo esc_html__('View : ', 'abp-transport-booking') . ' ' . esc_html($title); ?>"><?php ABPTB_Static::icon_svg('view_1'); ?></button>
                                                <button type="button" class="_btn_light_danger_xxs" onclick="abptb_post_action('move_trash','<?php echo esc_attr($post_id); ?>')" title="<?php echo esc_html__('Move to Trash : ', 'abp-transport-booking') . ' ' . esc_html($title); ?>"><?php ABPTB_Static::icon_svg('close_1'); ?></button>
                                            <?php } ?>
                                        </div>
                                    </th>
                                </tr>
                                <?php
                                $count++;
                            }
                        ?>
                        </tbody>
                    </table>
                    <?php
                    do_action('abptb_pagination', ['page_item' => $limit, 'page_number' => $page_number, 'total' => $total_post, 'style' => 'ajax']);
                } else {
                    ABPTB_Layout::layout_warning_info('not_found');
                }
            }
            public function settings(): void {
                $post_id = get_the_id();
                $copy_post_id = isset($_GET['copy_post']) ? absint($_GET['copy_post']) : '';
                if (!empty($copy_post_id) && isset($_GET['_abptb_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_abptb_nonce'])), 'abptb_copy_post_action') && current_user_can('edit_post', $copy_post_id)) {
                    ?>
                    <input type="hidden" name="abptb_copy_post" value="<?php echo esc_attr($copy_post_id); ?>"/>
                    <?php
                    $post_infos['copy_post_id'] = $copy_post_id;
                    $new_post_id = $copy_post_id;
                } else {
                    $new_post_id = $post_id;
                }
                $post_infos = ABPTB_Function::get_all_meta($new_post_id);
                wp_nonce_field('abptb_post_nonce', 'abptb_post_nonce');
                ?>
                <div class="abptb_area abptb_admin abp_post_config">
                    <input type="hidden" name="abptb_post_id" value="<?php echo esc_attr($post_id); ?>"/>
                    <div class="_abp_panel">
                        <div class="abp_tabs tab_top">
                            <div class="_panel_head">
                                <ul class="_abp tab_lists">
                                    <li data-tabs-target="#abptb_general"><span class="fas fa-rainbow"></span><?php esc_html_e('General', 'abp-transport-booking'); ?></li>
                                    <li data-tabs-target="#abptb_ticket"><span class="_mar_r_xxs">🎫 </span><?php esc_html_e('Ticket', 'abp-transport-booking'); ?></li>
                                    <li data-tabs-target="#abptb_routing"><span class="fas fa-route"></span><?php esc_html_e('Route', 'abp-transport-booking'); ?></li>
                                    <li data-tabs-target="#abptb_price" onclick="abptb_price_load()"><span class="_mar_r_xxs">💲 </span><?php esc_html_e(' Price', 'abp-transport-booking'); ?></li>
                                    <li data-tabs-target="#abptb_dates"><span class="_mar_r_xxs">🗓️</span><?php esc_html_e('Date', 'abp-transport-booking'); ?></li>
                                    <li data-tabs-target="#abptb_times"><span class="_mar_r_xxs">⏰</span><?php esc_html_e('Time', 'abp-transport-booking'); ?></li>
                                    <?php if (ABPTB_Function::on_off('additional_info')) { ?>
                                        <li data-tabs-target="#abptb_additional_service"><span class="_mar_r_xxs">💰</span><?php esc_html_e('Additional services', 'abp-transport-booking'); ?></li>
                                    <?php } ?>
                                    <?php if (ABPTB_Function::on_off('client_info')) { ?>
                                        <li data-tabs-target="#abptb_client_form"><span class="_mar_r_xxs">📋</span><?php esc_html_e('Client Form', 'abp-transport-booking'); ?></li>
                                    <?php } ?>
                                    <?php do_action('abptb_post_tab_menu', $post_infos); ?>
                                    <li data-tabs-target="#abptb_resource"><span class="_mar_r_xxs">📚</span><?php esc_html_e('Resources', 'abp-transport-booking'); ?></li>
                                </ul>
                            </div>
                            <div class="tab_content _panel_body">
                                <?php
                                    $this->general_configuration($post_infos);
                                    do_action('abptb_post_content', $post_infos);
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php ABPTB_Layout::load_admin_globally(); ?>
                </div>
                <?php
            }
            public function general_configuration($post_infos): void {
                $abptb_template = $post_infos['abptb_template'] ?? 'default';
                ?>
                <div class="tab_item" data-tabs="#abptb_general">
                    <h4 class="_abp_color_theme"><?php esc_html_e('General Configuration', 'abp-transport-booking'); ?></h4>
                    <?php ABPTB_Layout::info_text('general_config'); ?>
                    <div class="_divider_xs"></div>
                    <div class="group_setting">
                        <div class="setting_item">
                            <div class="_fa_center">
                                <?php ABPTB_Layout::switch_checkbox('sale_continue', ($post_infos['sale_continue'] ?? 'on')); ?>
                                <span class="_abp_label"><?php esc_html_e('Sale continue?', 'abp-transport-booking'); ?></span>
                            </div>
                            <div class="_divider_xxs"></div>
                            <?php ABPTB_Layout::info_text('sale_continue'); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php esc_html_e('Template', 'abp-transport-booking'); ?></span>
                                <select class="_form_control " name="abptb_template" required>
                                    <option disabled selected><?php esc_html_e('Please Select', 'abp-transport-booking'); ?></option>
                                    <option value="default" <?php echo esc_attr($abptb_template == 'default' ? 'selected' : ''); ?>><?php esc_html_e('Default Template', 'abp-transport-booking'); ?></option>
                                    <option value="light" <?php echo esc_attr($abptb_template == 'light' ? 'selected' : ''); ?>><?php esc_html_e('Light Template', 'abp-transport-booking'); ?></option>
                                </select>
                            </label>
                            <div class="_divider_xxs"></div>
                            <?php ABPTB_Layout::info_text('abptb_template'); ?>
                        </div>
                        <?php if (ABPTB_Function::on_off('sku')) { ?>
                            <div class="setting_item">
                                <div class="_fj_between">
                                    <div class="_fa_center">
                                        <?php ABPTB_Layout::switch_checkbox('display_sku', ($post_infos['display_sku'] ?? 'off')); ?>
                                        <span class="_abp_label"><?php esc_html_e('ID/SKU', 'abp-transport-booking'); ?></span>
                                    </div>
                                    <label>
                                        <input class="_form_control" name="post_sku" value="<?php echo esc_attr($post_infos['post_sku'] ?? ''); ?>" placeholder="<?php esc_attr_e('Transport ID', 'abp-transport-booking'); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('post_sku'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('post_icon')) { ?>
                            <div class="setting_item">
                                <divl class="_fj_between">
                                    <span class="_abp_label"><?php esc_html_e('Transport Icon', 'abp-transport-booking'); ?></span>
                                    <?php do_action('abptb_add_icon', 'post_icon', ($post_infos['post_icon'] ?? '')); ?>
                                </divl>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('post_icon'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('sub_title')) { ?>
                            <div class="setting_item">
                                <div class="_f_equal_f_wrap">
                                    <span class="_abp_label"><?php esc_html_e('Sub Title', 'abp-transport-booking'); ?></span>
                                    <label>
                                        <textarea class="_form_control" name="sub_title" placeholder="<?php esc_attr_e('Transport Sub Title', 'abp-transport-booking'); ?>"><?php echo esc_html($post_infos['sub_title'] ?? ''); ?></textarea>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('sub_title'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('post_des')) { ?>
                            <div class="setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span class="_abp_label"><?php esc_html_e('Short Description', 'abp-transport-booking'); ?></span>
                                    <textarea class="_form_control" name="post_description" placeholder="<?php esc_attr_e('EX: Description', 'abp-transport-booking'); ?>"><?php echo esc_html($post_infos['post_description'] ?? ''); ?></textarea>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('post_description'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('display_capacity')) { ?>
                            <div class="setting_item">
                                <div class="_fa_center">
                                    <?php ABPTB_Layout::switch_checkbox('display_capacity', ($post_infos['display_capacity'] ?? 'off')); ?>
                                    <span class="_abp_label"><?php esc_html_e('Display Capacity', 'abp-transport-booking'); ?></span>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('display_capacity'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('category')) { ?>
                            <div class="setting_item">
                                <div class="_fj_between_fa_center">
                                    <div class="_fa_center">
                                        <?php ABPTB_Layout::switch_checkbox('display_category', ($post_infos['display_category'] ?? 'on')); ?>
                                        <span class="_abp_label"><?php echo esc_html(ABPTB_Function::category_label()); ?></span>
                                    </div>
                                    <div class="tax_category _group_content">
                                        <?php ABPTB_Category::category_selection($post_infos['abptb_category'] ?? ''); ?>
                                    </div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('display_category'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('organizer')) { ?>
                            <div class="setting_item">
                                <div class="_fj_between_fa_center">
                                    <div class="_fa_center">
                                        <?php ABPTB_Layout::switch_checkbox('display_organizer', ($post_infos['display_organizer'] ?? 'off')); ?>
                                        <span class="_abp_label"><?php echo esc_html(ABPTB_Function::organizer_label()); ?></span>
                                    </div>
                                    <div class="tax_organizer _group_content"><?php ABPTB_Organizer::organizer_selection($post_infos['abptb_organizer'] ?? ''); ?></div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('display_organizer'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('brand')) { ?>
                            <div class="setting_item">
                                <div class="_fj_between_fa_center">
                                    <div class="_fa_center">
                                        <?php ABPTB_Layout::switch_checkbox('display_brand', ($post_infos['display_brand'] ?? 'off')); ?>
                                        <span class="_abp_label"><?php echo esc_html(ABPTB_Function::brand_label()); ?></span>
                                    </div>
                                    <div class="tax_brand _group_content"><?php ABPTB_Brand::brand_selection($post_infos['abptb_brand'] ?? ''); ?></div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('display_brand'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('related')) { ?>
                            <div class="setting_item related_item">
                                <div class="_fj_between_fa_center">
                                    <span class="_abp_label"><?php esc_html_e('Related Transport', 'abp-transport-booking'); ?></span>
                                    <div class="selection_area">
                                        <label>
                                            <input class="_form_control item_search" type="text" placeholder="<?php esc_attr_e('Search Related Transport ....', 'abp-transport-booking'); ?>"/>
                                        </label>
                                        <div class="selection_list"></div>
                                    </div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('related_item'); ?>
                                <div class="_divider_xxs"></div>
                                <div class="selected_area">
                                    <input type="hidden" name="related_item" value="<?php echo esc_attr($post_infos['related_item'] ?? ''); ?>"/>
                                    <div class="selected_list"></div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('feature')) { ?>
                            <div class="setting_item full_width post_feature">
                                <div class="_fj_between_fa_center">
                                    <span class="_abp_label"><?php esc_html_e('Feature', 'abp-transport-booking'); ?></span>
                                    <div class="_group_content">
                                        <div class="selection_area">
                                            <label>
                                                <input class="_form_control item_search" type="text" placeholder="<?php esc_attr_e('Search feature ....', 'abp-transport-booking'); ?>"/>
                                            </label>
                                            <div class="selection_list"></div>
                                        </div>
                                        <?php ABPTB_Layout::button_global_popup('option_feature', __('Add New', 'abp-transport-booking') . ' ' . ABPTB_Function::feature_label()); ?>
                                    </div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('post_feature'); ?>
                                <div class="_divider_xxs"></div>
                                <div class="selected_area">
                                    <input type="hidden" name="post_feature" value="<?php echo esc_attr($post_infos['post_feature'] ?? ''); ?>"/>
                                    <div class="selected_list"></div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="setting_item full_width">
                            <span class="_abp_label"><?php esc_html_e('Gallery', 'abp-transport-booking'); ?></span>
                            <div class="_divider_xxs"></div>
                            <?php ABPTB_Layout::info_text('display_slider'); ?>
                            <div class="_divider_xxs"></div>
                            <?php do_action('abptb_add_image_multiple', 'abptb_slider', ($post_infos['abptb_slider'] ?? '')); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            //====================================//
            public function save_settings($post_id): void {
                if (!isset($_POST['abptb_post_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abptb_post_nonce'])), 'abptb_post_nonce')) {
                    return;
                }
                if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                    return;
                }
                if (!current_user_can('edit_post', $post_id)) {
                    return;
                }
                if (get_post_type($post_id) == ABPTB_Function::get_cpt()) {
                    $post_int = fn($key, $default = 0) => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                    $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                    $post_textarea = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_textarea_field(wp_unslash($_POST[$key])) : $default;
                    $post_html = fn($key, $default = '') => isset($_POST[$key]) ? wp_kses_post(wp_unslash($_POST[$key])) : $default;
                    $post_int_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('absint', wp_unslash($_POST[$key])) : [];
                    $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                    $post_textarea_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_textarea_field', wp_unslash($_POST[$key])) : [];
                    $post_deep = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? map_deep(wp_unslash($_POST[$key]), 'sanitize_text_field') : [];
                    //$post_html_array     = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'wp_kses_post', wp_unslash( $_POST[ $key ] ) ) : [];
                    //$format_date         = fn( $date ) => $date ? gmdate( 'Y-m-d', strtotime( $date ) ) : '';
                    /***********************************/
                    $seat_type = $post_val('seat_type');
                    $display_ticket_type = $post_val('display_ticket_type');
                    $ticket_infos = [];
                    $sp_infos = [];
                    $all_ticket_types = [];
                    if ($display_ticket_type == 'off') {
                        $all_ticket_types[] = 'price';
                    }
                    if ($seat_type == 'ticket') {
                        $ticket_ids = $post_array('ticket_name');
                        $ticket_qty = $post_array('ticket_qty');
                        $reserve_qty = $post_array('reserve_qty');
                        $ticket_min_qty = $post_array('ticket_min_qty');
                        $ticket_max_qty = $post_array('ticket_max_qty');
                        $ticket_description = $post_textarea_array('ticket_description');
                        if (!empty($ticket_ids)) {
                            foreach ($ticket_ids as $key => $id) {
                                if (!empty($id)) {
                                    if ($display_ticket_type == 'on') {
                                        $all_ticket_types[] = $id;
                                    }
                                    $ticket_infos[$id] = [
                                        'qty' => $ticket_qty[$key] ?? 10,
                                        'reserve' => $reserve_qty[$key] ?? 0,
                                        'min_qty' => $ticket_min_qty[$key] ?? 1,
                                        'max_qty' => $ticket_max_qty[$key] ?? '',
                                        'description' => $ticket_description[$key] ?? '',
                                    ];
                                }
                            }
                        }
                    } else {
                        $sp_ids = $post_int_array('sp_id');
                        $sp_names = $post_array('sp_name');
                        $all_sp_id = [];
                        if (!empty($sp_ids)) {
                            foreach ($sp_ids as $key => $id) {
                                if (!empty($id)) {
                                    $all_sp_id[] = $id;
                                    $sp_infos[] = [
                                        'id' => $id,
                                        'name' => $sp_names[$key] ?? '',
                                    ];
                                }
                            }
                        }
                        if ($display_ticket_type == 'on') {
                            $all_ticket_types = ABPTB_Function::get_sp_ticket($all_sp_id);
                        }
                    }
                    /***********************************/
                    $route_data = [];
                    $route_infos = [];
                    $price_infos = [];
                    $route_direction = [];
                    $stops = $post_int_array('stop_name');
                    $types = $post_array('stop_type');
                    $display_pd = $post_array('display_pd');
                    $times = $post_int_array('stop_time');
                    if (!empty($stops)) {
                        foreach ($stops as $key => $stop) {
                            if (!empty($stop)) {
                                $route_infos[$stop]['type'] = $types[$key] ?? 'both';
                                $route_infos[$stop]['time'] = $times[$key] ?? '';
                                $route_infos[$stop]['pd'] = $display_pd[$key] ?? 'off';
                                $route_direction[] = $stop;
                            }
                        }
                    }
                    $count = sizeof($route_infos);
                    if ($count > 0) {
                        $route_infos[array_key_first($route_infos)]['type'] = 'bp';
                        $route_infos[array_key_first($route_infos)]['time'] = 0;
                        if ($count > 1) {
                            $route_infos[array_key_last($route_infos)]['type'] = 'dp';
                        }
                    }
                    /***********************************/
                    $route_ids = $post_array('route_id');
                    if (!empty($route_ids) && !empty($all_ticket_types)) {
                        foreach ($all_ticket_types as $ticket_type) {
                            $price_data = $post_array($ticket_type . '_price');
                            if (!empty($price_data)) {
                                foreach ($price_data as $key => $price) {
                                    $route_id = $route_ids[$key] ?? '';
                                    if (!empty($route_id)) {
                                        $price_infos[$route_id][$ticket_type] = $price;
                                        $route_data[] = $route_id;
                                    }
                                }
                            }
                        }
                    }
                    /***********************************/
                    $operation_time = [];
                    $operation_times = $post_array('operation_time');
                    if (!empty($operation_times)) {
                        $operation_time = array_values(array_unique(array_filter($operation_times)));
                        sort($operation_time);
                    }
                    $time_info['time'] = !empty($operation_time) ? $operation_time : ['00:00'];
                    $opt_time = $post_val('operation_time_optional');
                    $opt_time = !empty($opt_time) ? explode(',', $opt_time) : [];
                    if (in_array('day_wise_time', $opt_time)) {
                        foreach (ABPTB_Layout::week_day() as $key => $day) {
                            $times = $post_array($key . '_time');
                            $times = array_filter($times);
                            if (!empty($times)) {
                                sort($times);
                                $time_info['day_time'][$key] = array_values(array_unique($times));
                            }
                        }
                    }
                    if (in_array('date_wise_time', $opt_time)) {
                        $date_time_ids = $post_array('date_wise_time_id');
                        $all_dates = $post_deep('date_wise_date');
                        $all_times = $post_deep('date_wise_time');
                        if (!empty($date_time_ids)) {
                            foreach ($date_time_ids as $time_id) {
                                $date_wise_dates = $all_dates[$time_id] ?? [];
                                $date_wise_time = $all_times[$time_id] ?? [];
                                if (!empty($date_wise_dates) && !empty($date_wise_time)) {
                                    $clean_dates = array_filter($date_wise_dates);
                                    $clean_dates = reset($clean_dates);
                                    $clean_times = array_filter($date_wise_time);
                                    if (!empty($clean_dates) && !empty($clean_times)) {
                                        sort($clean_times);
                                        $time_info['date_times'][$time_id]['date'] = $clean_dates;
                                        $time_info['date_times'][$time_id]['time'] = array_values(array_unique($clean_times));
                                    }
                                }
                            }
                        }
                    }
                    /***********************************/
                    $display_return = $post_val('display_return', 'off');
                    $return_route_infos = [];
                    $return_price_infos = [];
                    $return_time_info = [];
                    $return_route_direction = [];
                    if ($display_return == 'on') {
                        $stops = $post_int_array('return_stop_name');
                        $types = $post_array('return_stop_type');
                        $display_pd = $post_array('return_display_pd');
                        $times = $post_int_array('return_stop_time');
                        if (!empty($stops)) {
                            foreach ($stops as $key => $stop) {
                                if (!empty($stop)) {
                                    $return_route_infos[$stop]['type'] = $types[$key] ?? 'both';
                                    $return_route_infos[$stop]['time'] = $times[$key] ?? '';
                                    $return_route_infos[$stop]['pd'] = $display_pd[$key] ?? 'off';
                                    $return_route_direction[] = $stop;
                                }
                            }
                        }
                        $count = sizeof($return_route_infos);
                        if ($count > 0) {
                            $return_route_infos[array_key_first($return_route_infos)]['type'] = 'bp';
                            $return_route_infos[array_key_first($return_route_infos)]['time'] = 0;
                            if ($count > 1) {
                                $return_route_infos[array_key_last($return_route_infos)]['type'] = 'dp';
                            }
                        }
                        /*****************/
                        $route_ids = $post_array('return_route_id');
                        if (!empty($route_ids) && !empty($all_ticket_types)) {
                            foreach ($all_ticket_types as $ticket_type) {
                                $price_data = $post_array('return_' . $ticket_type . '_price');
                                if (!empty($price_data)) {
                                    foreach ($price_data as $key => $price) {
                                        $route_id = $route_ids[$key] ?? '';
                                        if (!empty($route_id)) {
                                            $return_price_infos[$route_id][$ticket_type] = $price;
                                            $route_data[] = $route_id;
                                        }
                                    }
                                }
                            }
                        }
                        /*****************/
                        $operation_times = $post_array('return_operation_time');
                        $operation_time = !empty($operation_times) ? array_values(array_unique(array_filter($operation_times))) : [];
                        $operation_time = !empty($operation_time) ? $operation_time : ['00:00'];
                        sort($operation_time);
                        $return_time_info['time'] = $operation_time;
                        $opt_time = $post_val('return_operation_time_optional');
                        $opt_time = !empty($opt_time) ? explode(',', $opt_time) : [];
                        if (in_array('return_day_wise_time', $opt_time)) {
                            foreach (ABPTB_Layout::week_day() as $key => $day) {
                                $times = $post_array('return_' . $key . '_time');
                                $times = array_filter($times);
                                if (!empty($times)) {
                                    sort($times);
                                    $return_time_info['day_time'][$key] = array_values(array_unique($times));
                                }
                            }
                        }
                        if (in_array('return_date_wise_time', $opt_time)) {
                            $date_time_ids = $post_array('return_date_wise_time_id');
                            $all_dates = $post_deep('return_date_wise_date');
                            $all_times = $post_deep('return_date_wise_time');
                            if (!empty($date_time_ids)) {
                                foreach ($date_time_ids as $time_id) {
                                    $date_wise_dates = $all_dates[$time_id] ?? [];
                                    $date_wise_time = $all_times[$time_id] ?? [];
                                    if (!empty($date_wise_dates) && !empty($date_wise_time)) {
                                        $clean_dates = array_filter($date_wise_dates);
                                        $clean_times = array_filter($date_wise_time);
                                        if (!empty($clean_dates) && !empty($clean_times)) {
                                            sort($clean_times);
                                            $return_time_info['date_times'][$time_id]['date'] = reset($clean_dates);
                                            $return_time_info['date_times'][$time_id]['time'] = array_values(array_unique($clean_times));
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //echo '<pre>';print_r($return_time_info);echo '</pre>';die();
                    /***********************************/
                    $active_global_dates = $post_val('active_global_dates', 'on');
                    $abptb_dates = $active_global_dates == 'on' ? [] : apply_filters('abptb_get_date_array', []);
                    $display_additional_services = $post_val('display_additional_services', 'off');
                    $active_global_additional = $display_additional_services == 'on' ? $post_val('active_global_additional', 'on') : 'off';
                    $additional_services = ($active_global_additional == 'on' || $display_additional_services == 'off') ? [] : apply_filters('abptb_get_additional_array', []);
                    $display_client_form = $post_val('display_client_form', 'off');
                    $active_global_form = $display_client_form == 'on' ? $post_val('active_global_form', 'on') : 'off';
                    $abptb_form = ($active_global_form == 'on' || $display_client_form == 'off') ? [] : apply_filters('abptb_get_form_array', []);
                    $display_faq = $post_val('display_faq', 'off');
                    $active_global_faq = $display_faq == 'on' ? $post_val('active_global_faq', 'on') : 'off';
                    $abptb_faq = ($active_global_faq == 'on' || $display_faq == 'off') ? [] : apply_filters('abptb_get_faq_array', []);
                    $display_tc = $post_val('display_tc', 'off');
                    $active_global_tc = $display_tc == 'on' ? $post_val('active_global_tc', 'on') : 'off';
                    $abptb_tc = ($active_global_tc == 'on' || $display_tc == 'off') ? [] : $post_html('tc_content');
                    $meta_info = [
                        'sale_continue' => $post_val('sale_continue', 'on'),
                        'abptb_template' => $post_val('abptb_template', 'default'),
                        'display_sku' => $post_val('display_sku', 'off'),
                        'post_sku' => $post_val('post_sku'),
                        'post_icon' => $post_val('post_icon'),
                        'sub_title' => $post_textarea('sub_title'),
                        'post_description' => $post_textarea('post_description'),
                        'display_organizer' => $post_val('display_organizer', 'off'),
                        'abptb_organizer' => $post_val('abptb_organizer'),
                        'display_brand' => $post_val('display_brand', 'off'),
                        'abptb_brand' => $post_val('abptb_brand'),
                        'display_capacity' => $post_val('display_capacity', 'on'),
                        'display_category' => $post_val('display_category', 'on'),
                        'abptb_category' => $post_val('abptb_category'),
                        'related_item' => $post_val('related_item'),
                        'post_feature' => $post_val('post_feature'),
                        'abptb_slider' => $post_val('abptb_slider'),
                        //================//
                        'seat_type' => $seat_type,
                        'display_ticket_type' => $display_ticket_type,
                        'min_qty' => $post_int('min_qty'),
                        'max_qty' => $post_int('max_qty'),
                        'ticket_infos' => $ticket_infos,
                        'sp_infos' => $sp_infos,
                        'all_ticket_type' => $all_ticket_types,
                        //================//
                        'routing_infos' => $route_infos,
                        'display_return' => $display_return,
                        'return_routing_infos' => $return_route_infos,
                        'route_data' => $route_data,
                        //================//
                        'route_direction' => $route_direction,
                        'return_route_direction' => $return_route_direction,
                        //================//
                        'price_infos' => $price_infos,
                        'return_price_infos' => $return_price_infos,
                        'price_data' => array_merge($price_infos, $return_price_infos),
                        //================//
                        'active_global_dates' => $active_global_dates,
                        'abptb_dates' => $abptb_dates,
                        'time_infos' => $time_info,
                        'return_time_infos' => $return_time_info,
                        'display_additional_services' => $display_additional_services,
                        'active_global_additional' => $active_global_additional,
                        'additional_services' => $additional_services,
                        'display_client_form' => $display_client_form,
                        'active_global_form' => $active_global_form,
                        'display_single_form' => $post_val('display_single_form', 'on'),
                        'abptb_form' => $abptb_form,
                        'display_faq' => $display_faq,
                        'active_global_faq' => $active_global_faq,
                        'abptb_faq' => $abptb_faq,
                        'display_tc' => $display_tc,
                        'active_global_tc' => $active_global_tc,
                        'abptb_tc' => $abptb_tc,
                    ];
                    //=============tax================//
                    if (get_option('woocommerce_calc_taxes') == 'yes') {
                        $meta_info['_tax_status'] = $post_val('_tax_status', 'none');
                        $meta_info['_tax_class'] = $post_val('_tax_class');
                    }
                    //=============================//
                    $meta_info = apply_filters('abptb_meta_info_update', $meta_info, $post_id);
                    if (sizeof($meta_info) > 0) {
                        foreach ($meta_info as $key => $value) {
                            update_post_meta($post_id, sanitize_key($key), $value);
                        }
                    }
                }
            }
            public function post_permanent_remove(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
                if ($post_id <= 0) {
                    wp_send_json_error(['html' => '', 'msg' => __('Invalid ID ..... !! ', 'abp-transport-booking'), 'type' => 'warn'], 400);
                }
                $title = get_the_title($post_id);
                $link_wc_id = absint(ABPTB_Function::get_post_info($post_id, 'link_wc_id'));
                if ($link_wc_id > 0) {
                    wp_delete_post($link_wc_id, true);
                }
                wp_delete_post($post_id, true);
                wp_send_json_success(['html' => '', 'msg' => $title . ' : ' . __('Permanently removed. ..... !! ', 'abp-transport-booking'), 'type' => 'error']);
            }
            public function post_move_trash(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
                if ($post_id > 0) {
                    $title = get_the_title($post_id);
                    $link_wc_id = absint(ABPTB_Function::get_post_info($post_id, 'link_wc_id'));
                    if ($link_wc_id > 0) {
                        wp_trash_post($link_wc_id);
                    }
                    wp_trash_post($post_id);
                    wp_send_json_success(['html' => '', 'msg' => $title . ' : ' . __('Moved to trash successfully...... !! ', 'abp-transport-booking'), 'type' => 'warn']);
                }
                wp_send_json_error(['html' => '', 'msg' => __('Invalid  ID ..... !! ', 'abp-transport-booking'), 'type' => 'warn'], 400);
            }
            public function post_restore(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
                if ($post_id > 0) {
                    $link_wc_id = absint(ABPTB_Function::get_post_info($post_id, 'link_wc_id'));
                    if ($link_wc_id > 0) {
                        wp_untrash_post($link_wc_id);
                    }
                    wp_untrash_post($post_id);
                    $updated_post = [
                        'ID' => $post_id,
                        'post_status' => 'publish',
                    ];
                    wp_update_post($updated_post);
                    $title = get_the_title($post_id);
                    wp_send_json_success(['html' => '', 'msg' => $title . ' : ' . __('Restored successfully...... !! ', 'abp-transport-booking'), 'type' => 'success']);
                }
                wp_send_json_error(['html' => '', 'msg' => __('Invalid  ID ..... !! ', 'abp-transport-booking'), 'type' => 'warn'], 400);
            }
            public function reload_post_list(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                $filter_args = $post_array('filter_args');
                ob_start();
                $this->post_table($filter_args);
                $table_html = ob_get_clean();
                wp_send_json_success([
                    'html' => $table_html, 'type' => 'success',
                    'msg' => __('Post List Loaded successfully...... !! ', 'abp-transport-booking')
                ]);
            }
        }
        new ABPTB_Post();
    }