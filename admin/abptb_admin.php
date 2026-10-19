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
                $hook = add_menu_page($label, $label, 'manage_options', $slug, array($this, 'load_main_page'), $icon, 50);
                add_action('load-' . $hook, array($this, 'clean_notices'));
            }
            public function load_main_page(): void {
                add_filter('wp_dependency_installer_errors', '__return_false');
                $abptb_info = ABPTB_Query::get_info();
                $label = ABPTB_Function::label();
                $icon = ABPTB_Function::icon();
                $total_post = $abptb_info['total_post'] ?? 0;
                $total_order = $abptb_info['total_order'] ?? 0;
                /* translators: %s: transport label. */
                $add_new_title = sprintf(esc_html__('Add New %s', 'abp-transport-booking'), $label);
                $allowed_tabs = ['dashboard', 'posts', 'orders', 'sp', 'global', 'configuration', 'documentation', 'admin_order', 'cancel_requests'];
                $active_tab = 'dashboard';
                if (isset($_GET['_abptb_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_abptb_nonce'])), 'abptb_url_action')) {
                    $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'dashboard';
                }
                if (!in_array($active_tab, $allowed_tabs, true)) {
                    $active_tab = 'dashboard';
                }
                ?>
                <div class="abptb_area  abptb_admin <?php echo esc_attr($active_tab == 'documentation' ? 'documentation' : ''); ?>">
                    <div class="admin_head">
                        <div class="abptb_head_brand">
                            <span class="abptb_head_logo"><?php ABPTB_Layout::image_icon($icon); ?></span>
                            <span class="abptb_head_title">
                                <b><?php echo esc_html($label); ?></b>
                                <small>v<?php echo esc_html(ABPTB_VERSION); ?></small>
                            </span>
                        </div>
                        <nav class="abptb_nav">
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('dashboard')); ?>" class="_btn_xs <?php echo esc_attr($active_tab == 'dashboard' ? 'abp_active' : ''); ?>">
                                <i class="fas fa-gauge-high"></i><?php esc_html_e('Dashboard', 'abp-transport-booking'); ?>
                            </a>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('posts')); ?>" class="_btn_xs post_tab <?php echo esc_attr($active_tab == 'posts' ? 'abp_active' : ''); ?>">
                                <i class="fas fa-bus"></i><?php esc_html_e('Lists', 'abp-transport-booking'); ?><sup><?php echo esc_html($total_post); ?></sup>
                            </a>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('orders')); ?>" class="_btn_xs <?php echo esc_attr($active_tab == 'orders' ? 'abp_active' : ''); ?>">
                                <i class="fas fa-file-invoice"></i><?php esc_html_e('Orders', 'abp-transport-booking'); ?><sup><?php echo esc_html($total_order); ?></sup>
                            </a>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('sp')); ?>" class="_btn_xs <?php echo esc_attr($active_tab == 'sp' ? 'abp_active' : ''); ?>">
                                <i class="fas fa-chair"></i><?php esc_html_e('Ticket/Seat Plan', 'abp-transport-booking'); ?>
                            </a>
                            <?php do_action('abptb_add_admin_menu_tab_middle', $active_tab); ?>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('global')); ?>" class="_btn_xs <?php echo esc_attr($active_tab == 'global' ? 'abp_active' : ''); ?>">
                                <i class="fas fa-globe"></i><?php esc_html_e('Global Data', 'abp-transport-booking'); ?>
                            </a>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('configuration')); ?>" class="_btn_xs <?php echo esc_attr($active_tab == 'configuration' ? 'abp_active' : ''); ?>">
                                <i class="fas fa-gear"></i><?php esc_html_e('Configuration', 'abp-transport-booking'); ?>
                            </a>
                            <a href="<?php echo esc_url(ABPTB_Function::build_url('documentation')); ?>" class="_btn_xs <?php echo esc_attr($active_tab == 'documentation' ? 'abp_active' : ''); ?>">
                                <i class="fas fa-book-open"></i><?php esc_html_e('Documentation', 'abp-transport-booking'); ?>
                            </a>
                            <?php do_action('abptb_add_admin_menu_tab', $active_tab); ?>
                        </nav>
                        <?php if (ABPTB_WC == 2) { ?>
                            <div class="abptb_head_actions">
                                <button type="button" class="_btn_theme_xs" title="<?php echo esc_attr($add_new_title); ?>" data-href="<?php echo esc_url(admin_url('post-new.php?post_type=' . ABPTB_Function::get_cpt())); ?>" data-blank="_blank">
                                    <i class="fas fa-plus"></i><?php esc_html_e('Add New', 'abp-transport-booking'); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="dashboard_content">
                        <?php
                            do_action('abptb_notice');
                            do_action('abptb_load_' . $active_tab, $abptb_info); ?>
                    </div>
                    <?php ABPTB_Layout::load_admin_globally(); ?>
                </div>
                <?php
            }
            public function clean_notices(): void {
                remove_all_actions('user_admin_notices');
                remove_all_actions('admin_notices');
                remove_all_actions('all_admin_notices');
                remove_all_actions('network_admin_notices');
            }
            public function load_global($abptb_info): void {
                $allowed_tabs = ['dates', 'additional', 'client_form', 'tc', 'faq', 'category', 'organizer', 'location', 'feature', 'brand', 'discount', 'partial_payment', 'cancel_request'];
                $active_tab = 'dates';
                if (isset($_GET['_abptb_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_abptb_nonce'])), 'abptb_url_action')) {
                    $active_tab = isset($_GET['global']) ? sanitize_text_field(wp_unslash($_GET['global'])) : 'dates';
                }
                if (!in_array($active_tab, $allowed_tabs, true)) {
                    $active_tab = 'dates';
                }
                ?>
                <div class="abp_tabs tab_left">
                    <ul class="abp tab_lists">
                        <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'dates'])); ?>" class="<?php echo esc_attr($active_tab == 'dates' ? 'abp_active' : ''); ?>">
                            <?php ABPTB_Static::svg('date_1');
                                esc_html_e('Dates', 'abp-transport-booking'); ?>
                        </li>
                        <?php if (ABPTB_Function::on_off('additional_info')) { ?>
                            <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'additional'])); ?>" class="<?php echo esc_attr($active_tab == 'additional' ? 'abp_active' : ''); ?>"><span>💰</span> <?php esc_html_e('Additional services', 'abp-transport-booking'); ?></li>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('client_info')) { ?>
                            <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'client_form'])); ?>" class="<?php echo esc_attr($active_tab == 'client_form' ? 'abp_active' : ''); ?>"><span>📋</span> <?php esc_html_e('Client Form', 'abp-transport-booking'); ?></li>
                        <?php } ?>
                        <?php do_action('abptb_add_admin_global_tab', $active_tab); ?>
                        <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'location'])); ?>" class="<?php echo esc_attr($active_tab == 'location' ? 'abp_active' : ''); ?>">
                            <span class="fas fa-route"></span><?php echo esc_html(ABPTB_Function::location_label()); ?>
                        </li>
                        <?php if (ABPTB_Function::on_off('category')) { ?>
                            <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'category'])); ?>" class="<?php echo esc_attr($active_tab == 'category' ? 'abp_active' : ''); ?>">
                                <?php ABPTB_Static::svg('category');
                                    echo esc_html(ABPTB_Function::category_label()); ?>
                            </li>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('organizer')) { ?>
                            <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'organizer'])); ?>" class="<?php echo esc_attr($active_tab == 'organizer' ? 'abp_active' : ''); ?>"><span>🏢</span><?php echo esc_html(ABPTB_Function::organizer_label()); ?></li>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('brand')) { ?>
                            <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'brand'])); ?>" class="<?php echo esc_attr($active_tab == 'brand' ? 'abp_active' : ''); ?>"><span>🏷️</span><?php echo esc_html(ABPTB_Function::brand_label()); ?></li>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('feature')) { ?>
                            <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'feature'])); ?>" class="<?php echo esc_attr($active_tab == 'feature' ? 'abp_active' : ''); ?>"><span>🔗</span><?php echo esc_html(ABPTB_Function::feature_label()); ?></li>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('tc')) { ?>
                            <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'tc'])); ?>" class="<?php echo esc_attr($active_tab == 'tc' ? 'abp_active' : ''); ?>"><span>🤝</span><?php esc_html_e('Term & Conditions', 'abp-transport-booking'); ?></li>
                        <?php } ?>
                        <?php if (ABPTB_Function::on_off('faq')) { ?>
                            <li data-href="<?php echo esc_url(ABPTB_Function::build_url('global', ['global' => 'faq'])); ?>" class="<?php echo esc_attr($active_tab == 'faq' ? 'abp_active' : ''); ?>"><span>❓</span><?php esc_html_e('FAQ', 'abp-transport-booking'); ?></li>
                        <?php } ?>
                    </ul>
                    <div class="tab_content">
                        <?php do_action('abptb_global_' . $active_tab, $abptb_info); ?>
                    </div>
                </div>
                <?php
            }
        }
        new ABPTB_ADMIN();
    }