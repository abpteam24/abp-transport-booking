<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_ADMIN')) {
        class ABPTB_ADMIN {
            public function __construct() {
                add_action('admin_menu', array($this, 'admin_menu'));
                add_action('abptb_load_global', array($this, 'load_global'));
            }
            public function admin_menu(): void {
                $label = ABPTB_Function::label();
                $slug = ABPTB_Function::slug();
                $icon = ABPTB_Function::icon_wp();
                add_menu_page($label, $label, 'manage_options', $slug, array($this, 'load_main_page'), $icon, 50);
            }
            public function load_main_page(): void {
                remove_all_actions('user_admin_notices');
                remove_all_actions('admin_notices');
                remove_all_actions('all_admin_notices');
                remove_all_actions('network_admin_notices');
                add_filter('wp_dependency_installer_errors', '__return_false');
                $abptb_info = ABPTB_Query::get_info();
                $label = ABPTB_Function::label();
                $icon = ABPTB_Function::icon();
                $total_post = $abptb_info['total_post'] ?? 0;
                $total_order = $abptb_info['total_order'] ?? 0;
                $allowed_tabs = ['dashboard', 'posts', 'orders', 'sp', 'global', 'configuration', 'status', 'documentation', 'admin_order'];
                $active_tab = 'posts';
                if (isset($_GET['_abptb_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_abptb_nonce'])), 'abptb_url_action')) {
                    $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'posts';
                }
                if (!in_array($active_tab, $allowed_tabs, true)) {
                    $active_tab = 'posts';
                }
                if (ABPTB_WC < 2) {
                    $active_tab = 'status';
                }
                ?>
                <div class="abptb_area  abptb_admin">
                    <div class="admin_head ">
                        <div class="head_brand">
                            <div class="brand_icon _all_center"><?php ABPTB_Layout::image_icon($icon); ?></div>
                            <div class="_fd_column">
                                <h4 class="_abp"><?php echo esc_html($label); ?></h4>
                                <span class="brand_version"><?php echo esc_html(ABPTB_VERSION); ?></span>
                            </div>
                        </div>
                        <div class="_group_content">
                            <!--                            <a href="--><?php //echo esc_url( add_query_arg( 'tab', 'dashboard' ) ); ?><!--" class="_btn_light_info --><?php //echo esc_attr( $active_tab == 'dashboard' ? 'abp_active' : '' ); ?><!--"><span class="_mar_r_xs">📊</span>--><?php //esc_html_e( 'Dashboard', 'abp-transport-booking' ); ?><!--</a>-->
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('posts')); ?>" class="_btn_white_xs post_tab <?php echo esc_attr($active_tab == 'posts' ? 'abp_active' : ''); ?>">
                                <?php ABPTB_Layout::image_icon($icon);
                                    echo esc_html($label) . ' ' . esc_html__('Lists', 'abp-transport-booking'); ?>
                                <sup class="_color_theme">( <?php echo esc_html($total_post); ?> )</sup>
                            </a>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('orders')); ?>" class="_btn_white_xs <?php echo esc_attr($active_tab == 'orders' ? 'abp_active' : ''); ?>">
                                <?php ABPTB_Layout::icon_svg('order');
                                    esc_html_e('Orders', 'abp-transport-booking'); ?>
                                <sup class="_color_theme">( <?php echo esc_html($total_order); ?> )</sup>
                            </a>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('sp')); ?>" class="_btn_white_xs  <?php echo esc_attr($active_tab == 'sp' ? 'abp_active' : ''); ?>">
                                <?php ABPTB_Layout::icon_svg('seat');
                                    esc_html_e('Ticket/Seat Plan', 'abp-transport-booking'); ?>
                            </a>
                            <?php do_action('abptb_add_admin_menu_tab_middle', $active_tab); ?>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('global')); ?>" class="_btn_white_xs <?php echo esc_attr($active_tab == 'global' ? 'abp_active' : ''); ?>">
                                <?php ABPTB_Layout::icon_svg('globe');
                                    esc_html_e('Global Data', 'abp-transport-booking'); ?>
                            </a>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('configuration')); ?>" class="_btn_white_xs <?php echo esc_attr($active_tab == 'configuration' ? 'abp_active' : ''); ?>">
                                <?php ABPTB_Layout::icon_svg('setting');
                                    esc_html_e('Configuration', 'abp-transport-booking'); ?>
                            </a>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('status')); ?>" class="_btn_white_xs <?php echo esc_attr($active_tab == 'status' ? 'abp_active' : ''); ?>">
                                <?php ABPTB_Layout::icon_svg('status');
                                    esc_html_e('Status', 'abp-transport-booking'); ?>
                            </a>
                            <?php do_action('abptb_add_admin_menu_tab', $active_tab); ?>
                        </div>
                        <?php if (ABPTB_WC == 2) { ?>
                            <div class="_group_content">
                                <button type="button" class="_btn_white_xs" data-href="<?php echo esc_url(admin_url('post-new.php?post_type=' . ABPTB_Function::get_cpt())); ?>" data-blank="_blank">
                                    <?php ABPTB_Layout::icon_svg('plus');
                                        echo esc_html($label); ?>
                                </button>
                                <?php ABPTB_Layout::button_global_popup('tax_location', ABPTB_Function::location_label(), '_btn_white_xs');
                                    if (ABPTB_Function::on_off('category')) {
                                        ABPTB_Layout::button_global_popup('tax_category', ABPTB_Function::category_label(), '_btn_white_xs');
                                    } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="dashboard_content">

                            <?php do_action('abptb_load_' . $active_tab, $abptb_info); ?>
                    </div>
                    <?php ABPTB_Layout::load_admin_globally(); ?>
                </div>
                <?php
            }
            public function load_global($abptb_info): void {
                $allowed_tabs = ['dates', 'additional', 'client_form', 'resource', 'category', 'organizer', 'location', 'feature', 'brand', 'discount'];
                $active_tab = 'dates';
                if (isset($_GET['_abptb_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_abptb_nonce'])), 'abptb_url_action')) {
                    $active_tab = isset($_GET['global']) ? sanitize_text_field(wp_unslash($_GET['global'])) : 'dates';
                }
                if (!in_array($active_tab, $allowed_tabs, true)) {
                    $active_tab = 'dates';
                }
                ?>
                <div class="_max_1200_mar_auto">
                    <div class="_section_card_w_full">
                        <div class="_group_content_w_full_f_equal_f_wrap">
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'dates'])); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr($active_tab == 'dates' ? 'abp_active' : ''); ?>">
                                <?php ABPTB_Layout::icon_svg('date_1');
                                    esc_html_e('Dates', 'abp-transport-booking'); ?>
                            </a>
                            <?php if (ABPTB_Function::on_off('additional_info')) { ?>
                                <a href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'additional'])); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr($active_tab == 'additional' ? 'abp_active' : ''); ?>"><span>💰</span> <?php esc_html_e('Additional services', 'abp-transport-booking'); ?></a>
                            <?php } ?>
                            <?php if (ABPTB_Function::on_off('client_info')) { ?>
                                <a href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'client_form'])); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr($active_tab == 'client_form' ? 'abp_active' : ''); ?>"><span>📋</span> <?php esc_html_e('Client Form', 'abp-transport-booking'); ?></a>
                            <?php } ?>
                            <?php do_action('abptb_add_admin_global_tab', $active_tab); ?>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'location'])); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr($active_tab == 'location' ? 'abp_active' : ''); ?>">
                                <span class="fas fa-route"></span><?php echo esc_html(ABPTB_Function::location_label()); ?>
                            </a>
                            <?php if (ABPTB_Function::on_off('category')) { ?>
                                <a href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'category'])); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr($active_tab == 'category' ? 'abp_active' : ''); ?>">
                                    <?php ABPTB_Layout::icon_svg('category');
                                        echo esc_html(ABPTB_Function::category_label()); ?>
                                </a>
                            <?php } ?>
                            <?php if (ABPTB_Function::on_off('organizer')) { ?>
                                <a href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'organizer'])); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr($active_tab == 'organizer' ? 'abp_active' : ''); ?>"><span>🏢</span><?php echo esc_html(ABPTB_Function::organizer_label()); ?></a>
                            <?php } ?>
                            <?php if (ABPTB_Function::on_off('brand')) { ?>
                                <a href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'brand'])); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr($active_tab == 'brand' ? 'abp_active' : ''); ?>"><span>🏷️</span><?php echo esc_html(ABPTB_Function::brand_label()); ?></a>
                            <?php } ?>
                            <?php if (ABPTB_Function::on_off('feature')) { ?>
                                <a href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'feature'])); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr($active_tab == 'feature' ? 'abp_active' : ''); ?>"><span>🔗</span><?php echo esc_html(ABPTB_Function::feature_label()); ?></a>
                            <?php } ?>
                            <?php if (ABPTB_Function::on_off('tc') || ABPTB_Function::on_off('faq')) { ?>
                                <a href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'resource'])); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr($active_tab == 'resource' ? 'abp_active' : ''); ?>"><span>📚</span><?php esc_html_e('Resources', 'abp-transport-booking'); ?></a>
                            <?php } ?>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php do_action('abptb_global_' . $active_tab, $abptb_info); ?>
                    </div>
                </div>
                <?php
            }
        }
        new ABPTB_ADMIN();
    }