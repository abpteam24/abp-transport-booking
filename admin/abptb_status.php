<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('ABPTB_Status')) {
        class ABPTB_Status {
            public function __construct() {
                add_action('abptb_load_status', array($this, 'load_status'));
                add_action('wp_ajax_abptb_wc_config', array($this, 'wc_config'));
                add_action('wp_ajax_abptb_create_page', array($this, 'create_page'));
                add_action('wp_ajax_abptb_import_dummy', array($this, 'import_dummy'));
            }
            public function load_status(): void {
                ?>
                <div class="abp_panel_max_1200_mar_auto abp_status">
                    <div class="_panel_head">
                        <h3 class="abp_gap_xs"><span>🛡️</span> <?php esc_html_e('Status  & Information', 'abp-transport-booking'); ?></h3>
                    </div>
                    <div class="_panel_body_fd_column_gap_xs">
                        <?php

                            $this->version();
                            $this->wordpress();
                            $this->php();
                            $this->wc();
                            if (ABPTB_WC > 1) {
                                do_action('abptb_add_tools');
                                $this->post_page();
                            }
                        ?>
                    </div>
                </div>
                <?php
            }
            public function version(): void {
                ?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="abp"> <?php esc_html_e('Transport Booking Version', 'abp-transport-booking') ?> </h6>
                        <button class="_btn_light_success_xs" type="button"><span class="fas fa-check"></span><?php echo esc_html(ABPTB_VERSION); ?></button>
                    </div>
                </div>
                <?php
            }
            public function wordpress(): void {
                $version = get_bloginfo('version');
                ?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="abp"> <?php esc_html_e('WordPress Version', 'abp-transport-booking'); ?> </h6>
                        <?php if ($version > 5.5) { ?>
                            <button class="_btn_light_success_xs" type="button"><span class="fas fa-check"></span><?php echo esc_html($version); ?></button>
                        <?php } else { ?>
                            <button class="_btn_light_warning_xs" type="button"><span class="fas fa-exclamation-triangle"></span><?php echo esc_html($version); ?></button>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            public function php(): void {
                $version = phpversion();
                ?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="abp"> <?php esc_html_e('Php Version', 'abp-transport-booking'); ?> </h6>
                        <?php if ($version > 7.4) { ?>
                            <button class="_btn_light_success_xs" type="button"><span class="fas fa-check"></span><?php echo esc_html($version); ?></button>
                        <?php } else { ?>
                            <button class="_btn_light_warning_xs" type="button"><span class="fas fa-exclamation-triangle"></span><?php echo esc_html($version); ?></button>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            public function wc(): void {
                $title = ABPTB_WC == 2 ? __('WooCommerce Plugin', 'abp-transport-booking') : __('WooCommerce need to install and active', 'abp-transport-booking');
                $title = ABPTB_WC == 1 ? __('WooCommerce already installed but  not  activated', 'abp-transport-booking') : $title;
                $name = get_option('woocommerce_email_from_name');
                $email = get_option('woocommerce_email_from_address');
                ?>
                <div class="_section_xs ">
                    <div class="_fa_center_fj_between abp_notice">
                        <h6 class="abp"> <?php echo esc_html($title); ?></h6>
                        <?php if (ABPTB_WC == 2) { ?>
                            <button class="_btn_light_success_xs" type="button"><span class="fas fa-check"></span><?php esc_html_e('Activated', 'abp-transport-booking'); ?></button>
                        <?php } elseif (ABPTB_WC == 1) { ?>
                            <button class="_btn_warning_xs" onclick="abptb_wc_config('wc_active')" type="button"><span class="fas fa-tasks"></span><?php esc_html_e('Active Now', 'abp-transport-booking'); ?></button>
                        <?php } else { ?>
                            <button class="_btn_warning_xs" onclick="abptb_wc_config('wc_install_active')" type="button"><span class="fas fa-file-download"></span><?php esc_html_e('Install & Active Now', 'abp-transport-booking'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php if (ABPTB_WC == 2 && defined('WC_VERSION')) { ?>
                        <div class="_fa_center_fj_between">
                            <h6 class="abp"><?php esc_html_e('WooCommerce Version', 'abp-transport-booking'); ?></h6>
                            <?php if (version_compare(WC_VERSION, '8.0', '>')) { ?>
                                <button class="_btn_light_success_xs" type="button"><span class="fas fa-check"></span><?php echo esc_html(WC_VERSION); ?></button>
                            <?php } else { ?>
                                <button class="_btn_light_warning_xs" type="button"><span class="fas fa-exclamation-triangle"></span><?php echo esc_html(WC_VERSION); ?></button>
                            <?php } ?>
                        </div>
                        <?php if (!empty($name)) { ?>
                            <div class="_divider_xs"></div>
                            <div class="_fa_center_fj_between">
                                <h6 class="abp"><?php esc_html_e('Name', 'abp-transport-booking'); ?></h6>
                                <button class="_btn_light_success_xs" type="button"><?php echo esc_html($name); ?></button>
                            </div>
                        <?php } ?>
                        <?php if (!empty($email)) { ?>
                            <div class="_divider_xs"></div>
                            <div class="_fa_center_fj_between">
                                <h6 class="abp"><?php esc_html_e('Email Address', 'abp-transport-booking'); ?></h6>
                                <button class="_btn_light_success_xs_text_inherit" type="button"><?php echo esc_html($email); ?></button>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="_color_warning"><span class="_mar_r_xxs  fas fa-exclamation-triangle"></span><?php echo esc_html(ABPTB_Static::array_info('must_wc')); ?></div>
                    <?php } ?>
                </div>
                <?php
            }
            public function post_page(): void {
                $label = ABPTB_Function::label();
                $total = sizeof(ABPTB_ids);
                ?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="abp"><?php echo esc_html($label) . ' ' . esc_html__('Booking Page', 'abp-transport-booking'); ?></h6>
                        <?php if (ABPTB_Function::get_page_by_slug('tf_booking')) { ?>
                            <button class="_btn_light_success_xs" type="button"><span class="fas fa-check"></span><?php esc_html_e('Activated', 'abp-transport-booking'); ?></button>
                        <?php } else { ?>
                            <button class="_btn_warning_xs " onclick="abptb_create_page('tf_booking')" type="button"><span class="fas fa-plus"></span><?php esc_html_e('Add Transport Booking Booking Page', 'abp-transport-booking'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="abp"><?php echo esc_html($label) . ' ' . esc_html__('Post List Page', 'abp-transport-booking'); ?></h6>
                        <?php if (ABPTB_Function::get_page_by_slug('tf_post')) { ?>
                            <button class="_btn_light_success_xs" type="button"><span class="fas fa-check"></span><?php esc_html_e('Activated', 'abp-transport-booking'); ?></button>
                        <?php } else { ?>
                            <button class="_btn_warning_xs " onclick="abptb_create_page('tf_post')" type="button"><span class="fas fa-plus"></span><?php esc_html_e('Add Transport Booking List Page', 'abp-transport-booking'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="abp"><?php esc_html_e('Gallery Page', 'abp-transport-booking'); ?></h6>
                        <?php if (ABPTB_Function::get_page_by_slug('tf_gallery')) { ?>
                            <button class="_btn_light_success_xs" type="button"><span class="fas fa-check"></span><?php esc_html_e('Activated', 'abp-transport-booking'); ?></button>
                        <?php } else { ?>
                            <button class="_btn_warning_xs" onclick="abptb_create_page('tf_gallery')" type="button"><span class="fas fa-plus"></span><?php esc_html_e('Add Gallery Page', 'abp-transport-booking'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="abp"> <?php esc_html_e('Number of Post', 'abp-transport-booking'); ?> </h6>
                        <?php if ($total > 0) { ?>
                            <button class="_btn_light_success_xs" type="button"><span class="fas fa-check"></span><?php echo esc_html($total); ?></button>
                        <?php } else { ?>
                            <button class="_btn_light_warning_xs" type="button"><span class="fas fa-exclamation-triangle"></span><?php esc_html_e('Can Not Find Post', 'abp-transport-booking'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="abp"> <?php esc_html_e('Dummy Import', 'abp-transport-booking'); ?> </h6>
                        <button class="<?php echo esc_attr($total > 0 ? '_btn_light_success_xs' : '_btn_warning_xs'); ?>" onclick="abptb_import_global('dummy')" type="button"><span class="fas fa-plus"></span><?php esc_html_e('Add New Dummy Post', 'abp-transport-booking'); ?></button>
                    </div>
                </div>
                <?php
            }
            //=============================//
            public function wc_config(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $page_type = $post_val('type');
                if ($page_type == 'wc_install_active') {
                    include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
                    include_once(ABSPATH . 'wp-admin/includes/file.php');
                    include_once(ABSPATH . 'wp-admin/includes/misc.php');
                    include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
                    $plugin = 'woocommerce';
                    $api = plugins_api('plugin_information', array(
                        'slug' => $plugin,
                        'fields' => array(
                            'short_description' => false,
                            'sections' => false,
                            'requires' => false,
                            'rating' => false,
                            'ratings' => false,
                            'downloaded' => false,
                            'last_updated' => false,
                            'added' => false,
                            'tags' => false,
                            'compatibility' => false,
                            'homepage' => false,
                            'donate_link' => false,
                        ),
                    ));
                    if (is_wp_error($api)) {
                        wp_send_json_error(['html' => '', 'msg' => $api->get_error_message()]);
                    }
                    $title = 'title';
                    $url = 'url';
                    $nonce = 'nonce';
                    $woocommerce_plugin = new Plugin_Upgrader(new Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')));
                    $installed = $woocommerce_plugin->install($api->download_link);
                    if (is_wp_error($installed)) {
                        wp_send_json_error(['msg' => $installed->get_error_message(), 'type' => 'warn']);
                    }
                    $activated = activate_plugin('woocommerce/woocommerce.php');
                    if (is_wp_error($activated)) {
                        wp_send_json_error(['msg' => $activated->get_error_message(), 'type' => 'warn']);
                    }
                    wp_send_json_success(['msg' => esc_html__('WooCommerce installed and activated successfully!', 'abp-transport-booking'), 'type' => 'success'], 200);
                }
                if ($page_type == 'wc_active') {
                    if (defined('ABPTB_WC') && ABPTB_WC == 1) {
                        $activated = activate_plugin('woocommerce/woocommerce.php');
                        if (is_wp_error($activated)) {
                            wp_send_json_error(['msg' => $activated->get_error_message(), 'type' => 'warn']);
                        }
                        wp_send_json_success(['msg' => esc_html__('WooCommerce activated successfully!', 'abp-transport-booking'), 'type' => 'success'], 200);
                    }
                }
                wp_send_json_error(['msg' => esc_html__('WooCommerce is either not installed or already active.', 'abp-transport-booking'), 'type' => 'warn'], 403);
            }
            public function create_page(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $page_type = $post_val('type');
                if (!empty($page_type)) {
                    if (!ABPTB_Function::get_page_by_slug($page_type)) {
                        $label = ABPTB_Function::label();
                        $short_code = '';
                        if ($page_type == 'tf_booking') {
                            $label = __('Booking', 'abp-transport-booking');
                            $short_code = '[abptb-booking]';
                        }
                        if ($page_type == 'tf_post') {
                            $short_code = '[abptb-post]';
                        }
                        if ($page_type == 'tf_gallery') {
                            $label = __('Gallery', 'abp-transport-booking');
                            $short_code = '[abptb-gallery]';
                        }
                        $page = array(
                            'post_type' => 'page',
                            'post_name' => $page_type,
                            'post_title' => $label,
                            'post_content' => $short_code,
                            'post_status' => 'publish',
                        );
                        $post_id = wp_insert_post($page);
                        if (is_wp_error($post_id) || 0 === $post_id) {
                            wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('Failed to create page.', 'abp-transport-booking')]);
                        }
                        flush_rewrite_rules();
                        /* translators: %s: Trnasport Label */
                        $translated_format = esc_html__('%s Page Created successfully.....', 'abp-transport-booking');
                        $msg = sprintf($translated_format, $label);
                        wp_send_json_success(['type' => 'success', 'msg' => $msg]);
                    }
                    wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('Page already exists.', 'abp-transport-booking')]);
                } else {
                    wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('Something Wrong...!', 'abp-transport-booking')]);
                }
            }
            public function import_dummy(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $dummy_infos = $this->dummy_data();
                if (isset($dummy_infos['taxonomy'])) {
                    foreach ($dummy_infos['taxonomy'] as $tax => $taxonomy_option) {
                        if (taxonomy_exists($tax)) {
                            $check_terms = get_terms(array('taxonomy' => $tax, 'hide_empty' => false));
                            if (is_string($check_terms) || sizeof($check_terms) == 0) {
                                foreach ($taxonomy_option as $taxonomy_data) {
                                    unset($term);
                                    $term = wp_insert_term($taxonomy_data['name'], $tax);
                                }
                            }
                        }
                    }
                    do_action('abptb_location_update');
                    do_action('abptb_category_update');
                    do_action('abptb_organizer_update');
                    do_action('abptb_brand_update');
                }
                if (isset($dummy_infos['options'])) {
                    foreach ($dummy_infos['options'] as $option => $dummy_option) {
                        $option_data = get_option($option);
                        if (empty($option_data)) {
                            update_option($option, $dummy_option);
                        }
                    }
                }
                if (isset($dummy_infos['custom_post'])) {
                    $dummy_posts = $this->dummy();
                    foreach ($dummy_posts as $dummy_data) {
                        $args = array();
                        if (isset($dummy_data['name'])) {
                            $args['post_title'] = $dummy_data['name'];
                        }
                        $args['post_status'] = 'publish';
                        $args['post_type'] = ABPTB_Function::get_cpt();
                        $post_id = wp_insert_post($args);
                        $post_data = $dummy_data['post_data'] ?? [];
                        if (!empty($post_data)) {
                            foreach ($post_data as $meta_key => $data) {
                                update_post_meta($post_id, $meta_key, $data);
                            }
                        }
                    }
                }
                flush_rewrite_rules();
                wp_send_json_success([
                    'msg' => esc_html__('Dummy data imported successfully!', 'abp-transport-booking')
                ]);
            }
            public function dummy_data(): array {
                return [
                    'taxonomy' => [
                        'abptb_location' => ABPTB_Static::static_location(),
                        'abptb_category' => ABPTB_Static::static_category(),
                        'abptb_organizer' => ABPTB_Static::static_organizer(),
                        'abptb_brand' => ABPTB_Static::static_brand(),
                    ],
                    'options' => [
                        'abptb_ticket' => ABPTB_Static::static_ticket(),
                        'abptb_decor' => ABPTB_Static::static_decoration(),
                        'abptb_additional' => ABPTB_Static::static_additional(),
                        'abptb_form' => ABPTB_Static::static_form(),
                        'abptb_faq' => ABPTB_Static::static_faq(),
                        'abptb_tc' => ABPTB_Static::static_tc(),
                        'abptb_feature' => ABPTB_Static::static_feature(),
                    ],
                    'custom_post' => []
                ];
            }
            public function static_sp(): void {
                $sp_data = ABPTB_Query::get_sp();
                if (empty($sp_data)) {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'abptb_sp';
                    $bus_plan_data_1 = [
                        'name' => uniqid('sp_'),
                        'total_seats' => 41,
                        'others' => '{"bg_image":"0","bg_color":"#fff","row":11,"column":5,"width":60,"height":60,"gap":5,"radius":5}',
                        'layout_data' => '[{"index":"0","type":"other","id":"1","name":"Entrance","width_ratio":"3","fs":"15"},{"index":"1","type":"other","id":"1","name":""},{"index":"2","type":"other","id":"1","name":""},{"index":"3","type":"other","id":"2","name":"Driver","width_ratio":"2","fs":"14"},{"index":"4","type":"other","id":"1","name":""},{"index":"5","type":"seat","id":"3","name":"B-1"},{"index":"6","type":"seat","id":"3","name":"B-2"},{"index":"7","type":"other","id":"1","name":"Passenger Aisle","height_ratio":"9","rotate":"90","fs":"20"},{"index":"8","type":"seat","id":"3","name":"B-3"},{"index":"9","type":"seat","id":"3","name":"B-4"},{"index":"10","type":"seat","id":"5","name":"C-1"},{"index":"11","type":"seat","id":"5","name":"C-2"},{"index":"12","type":"other","id":"1","name":""},{"index":"13","type":"seat","id":"5","name":"C-3"},{"index":"14","type":"seat","id":"5","name":"C-4"},{"index":"15","type":"seat","id":"6","name":"F-1"},{"index":"16","type":"seat","id":"6","name":"F-2"},{"index":"17","type":"other","id":"1","name":""},{"index":"18","type":"seat","id":"6","name":"F-3"},{"index":"19","type":"seat","id":"6","name":"F-4"},{"index":"20","type":"seat","id":"7","name":"AD-1"},{"index":"21","type":"seat","id":"7","name":"AD-2"},{"index":"22","type":"other","id":"1","name":""},{"index":"23","type":"seat","id":"7","name":"AD-3"},{"index":"24","type":"seat","id":"7","name":"AD-4"},{"index":"25","type":"seat","id":"8","name":"CH-1"},{"index":"26","type":"seat","id":"8","name":"CH-2"},{"index":"27","type":"other","id":"1","name":""},{"index":"28","type":"seat","id":"8","name":"CH-3"},{"index":"29","type":"seat","id":"8","name":"CH-4"},{"index":"30","type":"seat","id":"2","name":"VIP-1"},{"index":"31","type":"seat","id":"2","name":"VIP-2"},{"index":"32","type":"other","id":"1","name":""},{"index":"33","type":"seat","id":"2","name":"VIP-3"},{"index":"34","type":"seat","id":"2","name":"VIP-4"},{"index":"35","type":"seat","id":"4","name":"S-1"},{"index":"36","type":"seat","id":"4","name":"S-2"},{"index":"37","type":"other","id":"1","name":""},{"index":"38","type":"seat","id":"4","name":"S-3"},{"index":"39","type":"seat","id":"4","name":"S-4"},{"index":"40","type":"seat","id":"4","name":"S-5"},{"index":"41","type":"seat","id":"4","name":"S-6"},{"index":"42","type":"other","id":"1","name":""},{"index":"43","type":"seat","id":"4","name":"S-7"},{"index":"44","type":"seat","id":"4","name":"S-8"},{"index":"45","type":"seat","id":"3","name":"B-5"},{"index":"46","type":"seat","id":"3","name":"B-6"},{"index":"47","type":"other","id":"1","name":""},{"index":"48","type":"seat","id":"3","name":"B-7"},{"index":"49","type":"seat","id":"3","name":"B-8"},{"index":"50","type":"seat","id":"9","name":"E-1"},{"index":"51","type":"seat","id":"9","name":"E-2"},{"index":"52","type":"seat","id":"9","name":"E-3"},{"index":"53","type":"seat","id":"9","name":"E-4"},{"index":"54","type":"seat","id":"9","name":"E-5"}]',
                        'seat_info' => '{"2":"4","3":"8","4":"8","5":"4","6":"4","7":"4","8":"4","9":"5"}'
                    ];
                    $bus_plan_data_2 = [
                        'name' => uniqid('sp_'),
                        'total_seats' => 40,
                        'others' => '{"bg_image":"0","bg_color":"#fff","row":11,"column":5,"width":60,"height":60,"gap":5,"radius":5}',
                        'layout_data' => '[{"index":"0","type":"other","id":"1","name":"Entance","width_ratio":"3","fs":"15"},{"index":"1","type":"other","id":"1","name":""},{"index":"2","type":"other","id":"1","name":""},{"index":"3","type":"other","id":"2","name":"","width_ratio":"2"},{"index":"4","type":"other","id":"1","name":""},{"index":"5","type":"seat","id":"3","name":"A-1"},{"index":"6","type":"seat","id":"3","name":"A-2"},{"index":"7","type":"other","id":"1","name":"Passenger Walkway","height_ratio":"10","rotate":"90","fs":"18"},{"index":"8","type":"seat","id":"3","name":"A-3"},{"index":"9","type":"seat","id":"3","name":"A-4"},{"index":"10","type":"seat","id":"3","name":"B-1"},{"index":"11","type":"seat","id":"3","name":"B-2"},{"index":"12","type":"other","id":"1","name":""},{"index":"13","type":"seat","id":"3","name":"B-3"},{"index":"14","type":"seat","id":"3","name":"B-4"},{"index":"15","type":"seat","id":"3","name":"C-1"},{"index":"16","type":"seat","id":"3","name":"C-2"},{"index":"17","type":"other","id":"1","name":""},{"index":"18","type":"seat","id":"3","name":"C-3"},{"index":"19","type":"seat","id":"3","name":"C-4"},{"index":"20","type":"seat","id":"3","name":"D-1"},{"index":"21","type":"seat","id":"3","name":"D-2"},{"index":"22","type":"other","id":"1","name":""},{"index":"23","type":"seat","id":"3","name":"D-3"},{"index":"24","type":"seat","id":"3","name":"D-4"},{"index":"25","type":"seat","id":"3","name":"E-1"},{"index":"26","type":"seat","id":"3","name":"E-2"},{"index":"27","type":"other","id":"1","name":""},{"index":"28","type":"seat","id":"3","name":"E-3"},{"index":"29","type":"seat","id":"3","name":"E-4"},{"index":"30","type":"seat","id":"3","name":"F-1"},{"index":"31","type":"seat","id":"3","name":"F-2"},{"index":"32","type":"other","id":"1","name":""},{"index":"33","type":"seat","id":"3","name":"F-3"},{"index":"34","type":"seat","id":"3","name":"F-4"},{"index":"35","type":"seat","id":"3","name":"G-1"},{"index":"36","type":"seat","id":"3","name":"G-2"},{"index":"37","type":"other","id":"1","name":""},{"index":"38","type":"seat","id":"3","name":"G-3"},{"index":"39","type":"seat","id":"3","name":"G-4"},{"index":"40","type":"seat","id":"3","name":"H-1"},{"index":"41","type":"seat","id":"3","name":"H-2"},{"index":"42","type":"other","id":"1","name":""},{"index":"43","type":"seat","id":"3","name":"H-3"},{"index":"44","type":"seat","id":"3","name":"H-4"},{"index":"45","type":"seat","id":"3","name":"I-1"},{"index":"46","type":"seat","id":"3","name":"I-2"},{"index":"47","type":"other","id":"1","name":""},{"index":"48","type":"seat","id":"3","name":"I-3"},{"index":"49","type":"seat","id":"3","name":"I-4"},{"index":"50","type":"seat","id":"3","name":"J-1"},{"index":"51","type":"seat","id":"3","name":"J-2"},{"index":"52","type":"other","id":"1","name":""},{"index":"53","type":"seat","id":"3","name":"J-3"},{"index":"54","type":"seat","id":"3","name":"J-4"}]',
                        'seat_info' => '{"3":"40"}'
                    ];
                    $bus_plan_data_3 = [
                        'name' => uniqid('sp_'),
                        'total_seats' => 30,
                        'others' => '{"bg_image":"","bg_color":"#fff","row":11,"column":4,"width":60,"height":60,"gap":5,"radius":5}',
                        'layout_data' => '[{"index":"0","type":"other","id":"1","name":"Entrance","width_ratio":"2","fs":"16"},{"index":"1","type":"other","id":"1","name":""},{"index":"2","type":"other","id":"2","name":"","width_ratio":"2"},{"index":"3","type":"other","id":"1","name":""},{"index":"4","type":"seat","id":"1","name":"A-1"},{"index":"5","type":"other","id":"1","name":"Passenger Access Path","height_ratio":"10","rotate":"90","fs":"16"},{"index":"6","type":"seat","id":"1","name":"A-2"},{"index":"7","type":"seat","id":"1","name":"A-3"},{"index":"8","type":"seat","id":"1","name":"B-1"},{"index":"9","type":"other","id":"1","name":""},{"index":"10","type":"seat","id":"1","name":"B-2"},{"index":"11","type":"seat","id":"1","name":"B-3"},{"index":"12","type":"seat","id":"1","name":"C-1"},{"index":"13","type":"other","id":"1","name":""},{"index":"14","type":"seat","id":"1","name":"C-2"},{"index":"15","type":"seat","id":"1","name":"C-3"},{"index":"16","type":"seat","id":"1","name":"D-1"},{"index":"17","type":"other","id":"1","name":""},{"index":"18","type":"seat","id":"1","name":"D-2"},{"index":"19","type":"seat","id":"1","name":"D-3"},{"index":"20","type":"seat","id":"1","name":"E-1"},{"index":"21","type":"other","id":"1","name":""},{"index":"22","type":"seat","id":"1","name":"E-2"},{"index":"23","type":"seat","id":"1","name":"E-3"},{"index":"24","type":"seat","id":"1","name":"F-1"},{"index":"25","type":"other","id":"1","name":""},{"index":"26","type":"seat","id":"1","name":"F-2"},{"index":"27","type":"seat","id":"1","name":"F-3"},{"index":"28","type":"seat","id":"1","name":"G-1"},{"index":"29","type":"other","id":"1","name":""},{"index":"30","type":"seat","id":"1","name":"G-2"},{"index":"31","type":"seat","id":"1","name":"G-3"},{"index":"32","type":"seat","id":"1","name":"H-1"},{"index":"33","type":"other","id":"1","name":""},{"index":"34","type":"seat","id":"1","name":"H-2"},{"index":"35","type":"seat","id":"1","name":"H-3"},{"index":"36","type":"seat","id":"1","name":"I-1"},{"index":"37","type":"other","id":"1","name":""},{"index":"38","type":"seat","id":"1","name":"I-2"},{"index":"39","type":"seat","id":"1","name":"I-3"},{"index":"40","type":"seat","id":"1","name":"J-1"},{"index":"41","type":"other","id":"1","name":""},{"index":"42","type":"seat","id":"1","name":"J-2"},{"index":"43","type":"seat","id":"1","name":"J-3"}]',
                        'seat_info' => '{"1":"30"}'
                    ];
                    $bus_plan_data_4 = [
                        'name' => uniqid('sp_'),
                        'total_seats' => 15,
                        'others' => '{"bg_image":"","bg_color":"#fff","row":11,"column":4,"width":60,"height":60,"gap":5,"radius":5}',
                        'layout_data' => '[{"index":"0","type":"other","id":"1","name":"Entance","width_ratio":"2","fs":"16"},{"index":"1","type":"other","id":"1","name":""},{"index":"2","type":"other","id":"2","name":"Driver","width_ratio":"2","fs":"16"},{"index":"3","type":"other","id":"1","name":""},{"index":"4","type":"seat","id":"4","name":"S-1","height_ratio":"2","fs":"14"},{"index":"5","type":"other","id":"1","name":"Passenger Way","height_ratio":"10","rotate":"90","fs":"18"},{"index":"6","type":"seat","id":"4","name":"S-2","height_ratio":"2","fs":"14"},{"index":"7","type":"seat","id":"4","name":"S-3","height_ratio":"2","fs":"14"},{"index":"8","type":"other","id":"1","name":""},{"index":"9","type":"other","id":"1","name":""},{"index":"10","type":"other","id":"1","name":""},{"index":"11","type":"other","id":"1","name":""},{"index":"12","type":"seat","id":"4","name":"S-4","height_ratio":"2","fs":"14"},{"index":"13","type":"other","id":"1","name":""},{"index":"14","type":"seat","id":"4","name":"S-5","height_ratio":"2","fs":"14"},{"index":"15","type":"seat","id":"4","name":"S-6","height_ratio":"2","fs":"14"},{"index":"16","type":"other","id":"1","name":""},{"index":"17","type":"other","id":"1","name":""},{"index":"18","type":"other","id":"1","name":""},{"index":"19","type":"other","id":"1","name":""},{"index":"20","type":"seat","id":"4","name":"S-7","height_ratio":"2","fs":"14"},{"index":"21","type":"other","id":"1","name":""},{"index":"22","type":"seat","id":"4","name":"S-8","height_ratio":"2","fs":"14"},{"index":"23","type":"seat","id":"4","name":"S-9","height_ratio":"2","fs":"14"},{"index":"24","type":"other","id":"1","name":""},{"index":"25","type":"other","id":"1","name":""},{"index":"26","type":"other","id":"1","name":""},{"index":"27","type":"other","id":"1","name":""},{"index":"28","type":"seat","id":"4","name":"S-10","height_ratio":"2","fs":"14"},{"index":"29","type":"other","id":"1","name":""},{"index":"30","type":"seat","id":"4","name":"S-11","height_ratio":"2","fs":"14"},{"index":"31","type":"seat","id":"4","name":"S-12","height_ratio":"2","fs":"14"},{"index":"32","type":"other","id":"1","name":""},{"index":"33","type":"other","id":"1","name":""},{"index":"34","type":"other","id":"1","name":""},{"index":"35","type":"other","id":"1","name":""},{"index":"36","type":"seat","id":"4","name":"S-13","height_ratio":"2","fs":"14"},{"index":"37","type":"other","id":"1","name":""},{"index":"38","type":"seat","id":"4","name":"S-14","height_ratio":"2","fs":"14"},{"index":"39","type":"seat","id":"4","name":"S-15","height_ratio":"2","fs":"14"},{"index":"40","type":"other","id":"1","name":""},{"index":"41","type":"other","id":"1","name":""},{"index":"42","type":"other","id":"1","name":""},{"index":"43","type":"other","id":"1","name":""}]',
                        'seat_info' => '{"4":"15"}'
                    ];
                    $ticket_infos = ABPTB_Function::get_option('abptb_ticket_sp');
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->insert($table_name, $bus_plan_data_1);
                    $id_1 = $wpdb->insert_id;
                    $ticket_infos[$id_1]['type'] = json_decode($bus_plan_data_1['seat_info'], true);
                    $ticket_infos[$id_1]['total'] = 41;
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->insert($table_name, $bus_plan_data_2);
                    $id_2 = $wpdb->insert_id;
                    $ticket_infos[$id_2]['type'] = json_decode($bus_plan_data_2['seat_info'], true);
                    $ticket_infos[$id_2]['total'] = 40;
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->insert($table_name, $bus_plan_data_3);
                    $id_3 = $wpdb->insert_id;
                    $ticket_infos[$id_3]['type'] = json_decode($bus_plan_data_3['seat_info'], true);
                    $ticket_infos[$id_3]['total'] = 30;
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->insert($table_name, $bus_plan_data_4);
                    $id_4 = $wpdb->insert_id;
                    $ticket_infos[$id_4]['type'] = json_decode($bus_plan_data_4['seat_info'], true);
                    $ticket_infos[$id_4]['total'] = 15;
                    update_option('abptb_ticket_sp', $ticket_infos);
                }
            }
            public function dummy(): array {
                $on_off = ['on', 'off'];
                $template = ["default", "light"];
                $icon = ["🚌", "🚐", "🚍", "🚎", "fas fa-bus", "fas fa-bus-simple"];
                $all_organizer = ABPTB_Function::get_option('abptb_organizer');
                $organizer = ['Global Transit Group', 'Express Travel Network', 'Premium Coach Services', 'InterCity Transport', 'Continental Bus Lines', 'Smart Mobility Solutions'];
                $all_brands = ABPTB_Function::get_option('abptb_brand');
                $band = ['Mercedes-Benz', 'Volvo', 'Scania', 'IVECO', 'Alexander Dennis', 'Yutong'];
                $all_categories = ABPTB_Function::get_option('abptb_category');
                $categories = ['Express', 'Economy', 'Business', 'Luxury', 'Sleeper', 'Shuttle'];
                $features = ABPTB_Function::get_option('abptb_feature');
                $times = ['09:15', '11:30', '14:00', '18:45', '21:10', '08:15', '10:30', '12:00', '15:45', '20:10'];
                $seat_type = ['ticket', 'sp', 'ticket', 'sp', 'ticket', 'sp'];
                $names = ['Greyhound Express Intercity Bus Service', 'Megabus Affordable City-to-City Travel', 'FlixBus Modern Long Distance Transport', 'Peter Pan Premium Travel Experience', 'Jefferson Lines Regional Bus Network', 'Trailways Comfortable Shuttle Service'];
                $all_data = [];
                $all_route_info = $this->route_info($seat_type);
                for ($i = 0; $i < 6; $i++) {
                    $all_data[$i]['name'] = $names[$i];
                    $all_data[$i]['post_data'] = [
                        'sale_continue' => 'on',
                        'abptb_template' => $template[wp_rand(0, 1)],
                        'display_sku' => 'on',
                        'post_sku' => wp_rand(100, 999),
                        'post_icon' => $icon[wp_rand(0, 5)],
                        'sub_title' => 'Travel comfortably with modern vehicles and professional drivers.',
                        'post_description' => 'Experience hassle-free transportation with well-maintained vehicles, affordable fares, and excellent customer support. Whether traveling for business or leisure, our services ensure comfort, punctuality, and convenience from departure to arrival.',
                        'display_organizer' => $on_off[wp_rand(0, 1)],
                        'abptb_organizer' => $this->get_id($all_organizer, $organizer[$i]),
                        'display_brand' => $on_off[wp_rand(0, 1)],
                        'abptb_brand' => $this->get_id($all_brands, $band[$i]),
                        'display_capacity' => $on_off[wp_rand(0, 1)],
                        'display_category' => $on_off[wp_rand(0, 1)],
                        'abptb_category' => $this->get_id($all_categories, $categories[$i]),
                        'post_feature' => implode(',', array_rand($features, 5)),
                        'abptb_slider' => '10,20,30,40,50,100,60,70,80,90',
                        'active_global_dates' => 'on',
                        'time_infos' => ['time' => array_map(fn($key) => $times[$key], array_rand($times, 2))],
                        'return_time_infos' => ['time' => array_map(fn($key) => $times[$key], array_rand($times, 2))],
                        'display_additional_services' => 'on',
                        'active_global_additional' => 'on',
                        'display_client_form' => 'on',
                        'active_global_form' => 'on',
                        'display_single_form' => $on_off[wp_rand(0, 1)],
                        'display_faq' => 'on',
                        'active_global_faq' => 'on',
                        'display_tc' => 'on',
                        'active_global_tc' => 'on',
                        'dummy' => 'on',
                        'seat_type' => $seat_type[$i],
                        'display_ticket_type' => 'on',
                        'display_return' => 'on',
                        'min_qty' => wp_rand(1, 2),
                        'max_qty' => wp_rand(3, 10),
                        'ticket_infos' => $all_route_info[$i]['ticket_infos'],
                        'sp_infos' => $all_route_info[$i]['sp_infos'],
                        'all_ticket_type' => $all_route_info[$i]['all_ticket_type'],
                        'routing_infos' => $all_route_info[$i]['routing_infos'],
                        'return_routing_infos' => $all_route_info[$i]['return_routing_infos'],
                        'route_data' => $all_route_info[$i]['route_data'],
                        'route_direction' => $all_route_info[$i]['route_direction'],
                        'return_route_direction' => $all_route_info[$i]['return_route_direction'],
                        'price_infos' => $all_route_info[$i]['price_infos'],
                        'return_price_infos' => $all_route_info[$i]['return_price_infos'],
                        'price_data' => $all_route_info[$i]['price_data'],
                    ];
                }
                return $all_data;
            }
            public function route_info($seat_type = []): array {
                $this->static_sp();
                $options = ABPTB_Function::get_option('abptb_location');
                $ticket_options = ABPTB_Function::get_option('abptb_ticket');
                $random_num = sizeof($ticket_options) > 4 ? 3 : sizeof($ticket_options);
                $all_ticket_type = array_rand($ticket_options, $random_num);
                $routes = self::route_data();
                $all_sp_ticket = ABPTB_Function::get_option('abptb_ticket_sp');
                $sp_id = [];
                if (!empty($all_sp_ticket)) {
                    $sp_id = array_keys($all_sp_ticket);
                }
                $sp_select = '';
                $all_data = [];
                $all_data['sp_id'] = $sp_id[array_rand($sp_id)];
                if (!empty($routes)) {
                    foreach ($routes as $key => $data) {
                        $type = $seat_type[$key] ?? 'ticket';
                        if ($type == 'sp') {
                            $sp_select = $sp_id[array_rand($sp_id)];
                            $tickets = [];
                            $seat_infos = $all_sp_ticket[$sp_select] ?? [];
                            if (!empty($seat_infos)) {
                                $seat_info = $seat_infos['type'] ?? [];
                                if (!empty($seat_info)) {
                                    $tickets = array_merge($tickets, array_keys($seat_info));
                                }
                            }
                            $all_ticket_type = array_values(array_unique($tickets));
                        }
                        $route_info = $data['routing_infos'] ?? [];
                        $prices = $data['price_infos'] ?? [];
                        if (!empty($route_info) && !empty($prices)) {
                            foreach ($route_info as $info) {
                                $stop = $this->get_id($options, ($info['stop'] ?? ''));
                                $all_data[$key]['routing_infos'][$stop]['type'] = $info['type'] ?? '';
                                $all_data[$key]['routing_infos'][$stop]['time'] = $info['time'] ?? '';
                                $all_data[$key]['route_direction'][] = $stop;
                            }
                            foreach ($prices as $info) {
                                $bp = $this->get_id($options, ($info['bp'] ?? ''));
                                $dp = $this->get_id($options, ($info['dp'] ?? ''));
                                $price = $info['price'] ?? 0;
                                $step = 0;
                                $bp_dp = $bp . '_' . $dp;
                                $all_data[$key]['route_data'][] = $bp_dp;
                                foreach ($all_ticket_type as $type_id) {
                                    $all_data[$key]['price_infos'][$bp_dp][$type_id] = $price + $step;
                                    $all_data[$key]['price_data'][$bp_dp][$type_id] = $price + $step;
                                    $step = $step + 5;
                                    if ($type == 'ticket') {
                                        $all_data[$key]['ticket_infos'][$type_id]['qty'] = wp_rand(30, 60);
                                        $all_data[$key]['ticket_infos'][$type_id]['reserve'] = wp_rand(5, 10);
                                        $all_data[$key]['ticket_infos'][$type_id]['min_qty'] = wp_rand(1, 2);
                                        $all_data[$key]['ticket_infos'][$type_id]['max_qty'] = wp_rand(2, 5);
                                    } else {
                                        $all_data[$key]['sp_infos'][0]['id'] = $sp_select;
                                    }
                                }
                            }
                            $all_data[$key]['all_ticket_type'] = $all_ticket_type;
                        }
                        $route_info = $data['return_routing_infos'] ?? [];
                        $prices = $data['return_price_infos'] ?? [];
                        if (!empty($route_info) && !empty($prices)) {
                            foreach ($route_info as $info) {
                                $stop = $this->get_id($options, ($info['stop'] ?? ''));
                                $all_data[$key]['return_routing_infos'][$stop]['type'] = $info['type'] ?? '';
                                $all_data[$key]['return_routing_infos'][$stop]['time'] = $info['time'] ?? '';
                                $all_data[$key]['return_route_direction'][] = $stop;
                            }
                            foreach ($prices as $info) {
                                $bp = $this->get_id($options, ($info['bp'] ?? ''));
                                $dp = $this->get_id($options, ($info['dp'] ?? ''));
                                $price = $info['price'] ?? 0;
                                $step = 0;
                                $bp_dp = $bp . '_' . $dp;
                                $all_data[$key]['route_data'][] = $bp_dp;
                                foreach ($all_ticket_type as $type_id) {
                                    $all_data[$key]['return_price_infos'][$bp_dp][$type_id] = $price + $step;
                                    $all_data[$key]['price_data'][$bp_dp][$type_id] = $price + $step;
                                    $step = $step + 5;
                                }
                            }
                            $all_data[$key]['all_ticket_type'] = $all_ticket_type;
                        }
                    }
                }
                return $all_data;
            }
            public function get_id($options = [], $name = ''): int|string|null {
                if (!empty($options)) {
                    foreach ($options as $key => $option) {
                        if (isset($option['name']) && $option['name'] === $name) {
                            return $key;
                        }
                    }
                }
                return null;
            }
            public static function route_data(): array {
                return [
                    0 => [
                        'routing_infos' => [
                            0 => ['stop' => 'New York City', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Philadelphia', 'type' => 'bp', 'time' => '90'],
                            2 => ['stop' => 'Baltimore', 'type' => 'bp', 'time' => '180'],
                            3 => ['stop' => 'Washington, D.C.', 'type' => 'dp', 'time' => '240'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'New York City', 'dp' => 'Philadelphia', 'price' => '35'],
                            1 => ['bp' => 'New York City', 'dp' => 'Baltimore', 'price' => '55'],
                            2 => ['bp' => 'New York City', 'dp' => 'Washington, D.C.', 'price' => '75'],
                            3 => ['bp' => 'Philadelphia', 'dp' => 'Baltimore', 'price' => '30'],
                            4 => ['bp' => 'Philadelphia', 'dp' => 'Washington, D.C.', 'price' => '50'],
                            5 => ['bp' => 'Baltimore', 'dp' => 'Washington, D.C.', 'price' => '25'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'Washington, D.C.', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Baltimore', 'type' => 'bp', 'time' => '80'],
                            2 => ['stop' => 'Philadelphia', 'type' => 'bp', 'time' => '150'],
                            3 => ['stop' => 'New York City', 'type' => 'dp', 'time' => '280'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'Philadelphia', 'dp' => 'New York City', 'price' => '35'],
                            1 => ['bp' => 'Baltimore', 'dp' => 'New York City', 'price' => '55'],
                            2 => ['bp' => 'Washington, D.C.', 'dp' => 'New York City', 'price' => '75'],
                            4 => ['bp' => 'Washington, D.C.', 'dp' => 'Philadelphia', 'price' => '50'],
                            5 => ['bp' => 'Washington, D.C.', 'dp' => 'Baltimore', 'price' => '25'],
                        ]
                    ],
                    1 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Los Angeles', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Bakersfield', 'type' => 'bp', 'time' => '120'],
                            2 => ['stop' => 'Fresno', 'type' => 'both', 'time' => '240'],
                            3 => ['stop' => 'San Jose', 'type' => 'dp', 'time' => '330'],
                            4 => ['stop' => 'San Francisco', 'type' => 'dp', 'time' => '390'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Los Angeles', 'dp' => 'Fresno', 'price' => '40'],
                            1 => ['bp' => 'Los Angeles', 'dp' => 'San Jose', 'price' => '55'],
                            2 => ['bp' => 'Los Angeles', 'dp' => 'San Francisco', 'price' => '65'],
                            3 => ['bp' => 'Bakersfield', 'dp' => 'Fresno', 'price' => '20'],
                            4 => ['bp' => 'Bakersfield', 'dp' => 'San Jose', 'price' => '35'],
                            5 => ['bp' => 'Bakersfield', 'dp' => 'San Francisco', 'price' => '45'],
                            6 => ['bp' => 'Fresno', 'dp' => 'San Jose', 'price' => '25'],
                            7 => ['bp' => 'Fresno', 'dp' => 'San Francisco', 'price' => '35'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'San Francisco', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'San Jose', 'type' => 'bp', 'time' => '70'],
                            2 => ['stop' => 'Fresno', 'type' => 'both', 'time' => '130'],
                            3 => ['stop' => 'Bakersfield', 'type' => 'dp', 'time' => '300'],
                            4 => ['stop' => 'Los Angeles', 'type' => 'dp', 'time' => '420'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'San Francisco', 'dp' => 'Fresno', 'price' => '35'],
                            1 => ['bp' => 'San Francisco', 'dp' => 'Bakersfield', 'price' => '45'],
                            2 => ['bp' => 'San Francisco', 'dp' => 'Los Angeles', 'price' => '65'],
                            3 => ['bp' => 'San Jose', 'dp' => 'Fresno', 'price' => '25'],
                            4 => ['bp' => 'San Jose', 'dp' => 'Bakersfield', 'price' => '35'],
                            5 => ['bp' => 'San Jose', 'dp' => 'Los Angeles', 'price' => '55'],
                            6 => ['bp' => 'Fresno', 'dp' => 'Bakersfield', 'price' => '20'],
                            7 => ['bp' => 'Fresno', 'dp' => 'Los Angeles', 'price' => '40'],
                        ]
                    ],
                    2 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Chicago', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'South Bend', 'type' => 'bp', 'time' => '90'],
                            2 => ['stop' => 'Toledo', 'type' => 'both', 'time' => '210'],
                            3 => ['stop' => 'Detroit', 'type' => 'dp', 'time' => '300'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Chicago', 'dp' => 'Toledo', 'price' => '45'],
                            1 => ['bp' => 'Chicago', 'dp' => 'Detroit', 'price' => '60'],
                            2 => ['bp' => 'South Bend', 'dp' => 'Toledo', 'price' => '25'],
                            3 => ['bp' => 'South Bend', 'dp' => 'Detroit', 'price' => '40'],
                            4 => ['bp' => 'Toledo', 'dp' => 'Detroit', 'price' => '20'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'Detroit', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Toledo', 'type' => 'both', 'time' => '120'],
                            2 => ['stop' => 'South Bend', 'type' => 'dp', 'time' => '200'],
                            3 => ['stop' => 'Chicago', 'type' => 'dp', 'time' => '320'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'Detroit', 'dp' => 'Toledo', 'price' => '20'],
                            1 => ['bp' => 'Detroit', 'dp' => 'South Bend', 'price' => '40'],
                            2 => ['bp' => 'Detroit', 'dp' => 'Chicago', 'price' => '60'],
                            3 => ['bp' => 'Toledo', 'dp' => 'South Bend', 'price' => '25'],
                            4 => ['bp' => 'Toledo', 'dp' => 'Chicago', 'price' => '45'],
                        ]
                    ],
                    3 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Boston', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Providence', 'type' => 'bp', 'time' => '60'],
                            2 => ['stop' => 'New Haven', 'type' => 'both', 'time' => '150'],
                            3 => ['stop' => 'New York City', 'type' => 'dp', 'time' => '240'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Boston', 'dp' => 'New Haven', 'price' => '35'],
                            1 => ['bp' => 'Boston', 'dp' => 'New York City', 'price' => '55'],
                            2 => ['bp' => 'Providence', 'dp' => 'New Haven', 'price' => '20'],
                            3 => ['bp' => 'Providence', 'dp' => 'New York City', 'price' => '40'],
                            4 => ['bp' => 'New Haven', 'dp' => 'New York City', 'price' => '20'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'New York City', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'New Haven', 'type' => 'both', 'time' => '90'],
                            2 => ['stop' => 'Providence', 'type' => 'dp', 'time' => '150'],
                            3 => ['stop' => 'Boston', 'type' => 'dp', 'time' => '240'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'New York City', 'dp' => 'New Haven', 'price' => '20'],
                            1 => ['bp' => 'New York City', 'dp' => 'Providence', 'price' => '40'],
                            2 => ['bp' => 'New York City', 'dp' => 'Boston', 'price' => '55'],
                            3 => ['bp' => 'New Haven', 'dp' => 'Providence', 'price' => '20'],
                            4 => ['bp' => 'New Haven', 'dp' => 'Boston', 'price' => '35'],
                        ]
                    ],
                    4 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Dallas', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Corsicana', 'type' => 'bp', 'time' => '60'],
                            2 => ['stop' => 'Huntsville', 'type' => 'both', 'time' => '180'],
                            3 => ['stop' => 'Houston', 'type' => 'dp', 'time' => '240'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Dallas', 'dp' => 'Huntsville', 'price' => '35'],
                            1 => ['bp' => 'Dallas', 'dp' => 'Houston', 'price' => '50'],
                            2 => ['bp' => 'Corsicana', 'dp' => 'Huntsville', 'price' => '20'],
                            3 => ['bp' => 'Corsicana', 'dp' => 'Houston', 'price' => '35'],
                            4 => ['bp' => 'Huntsville', 'dp' => 'Houston', 'price' => '15'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'Houston', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Huntsville', 'type' => 'both', 'time' => '60'],
                            2 => ['stop' => 'Corsicana', 'type' => 'dp', 'time' => '120'],
                            3 => ['stop' => 'Dallas', 'type' => 'dp', 'time' => '240'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'Houston', 'dp' => 'Huntsville', 'price' => '15'],
                            1 => ['bp' => 'Houston', 'dp' => 'Corsicana', 'price' => '35'],
                            2 => ['bp' => 'Houston', 'dp' => 'Dallas', 'price' => '50'],
                            3 => ['bp' => 'Huntsville', 'dp' => 'Corsicana', 'price' => '20'],
                            4 => ['bp' => 'Huntsville', 'dp' => 'Dallas', 'price' => '35'],
                        ]
                    ],
                    5 => [
                        'routing_infos' => [
                            0 => ['stop' => 'Seattle', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Tacoma', 'type' => 'bp', 'time' => '45'],
                            2 => ['stop' => 'Olympia', 'type' => 'both', 'time' => '90'],
                            3 => ['stop' => 'Portland', 'type' => 'dp', 'time' => '180'],
                        ],
                        'price_infos' => [
                            0 => ['bp' => 'Seattle', 'dp' => 'Olympia', 'price' => '25'],
                            1 => ['bp' => 'Seattle', 'dp' => 'Portland', 'price' => '45'],
                            2 => ['bp' => 'Tacoma', 'dp' => 'Olympia', 'price' => '15'],
                            3 => ['bp' => 'Tacoma', 'dp' => 'Portland', 'price' => '35'],
                            4 => ['bp' => 'Olympia', 'dp' => 'Portland', 'price' => '20'],
                        ],
                        'return_routing_infos' => [
                            0 => ['stop' => 'Portland', 'type' => 'bp', 'time' => '0'],
                            1 => ['stop' => 'Olympia', 'type' => 'both', 'time' => '90'],
                            2 => ['stop' => 'Tacoma', 'type' => 'dp', 'time' => '120'],
                            3 => ['stop' => 'Seattle', 'type' => 'dp', 'time' => '180'],
                        ],
                        'return_price_infos' => [
                            0 => ['bp' => 'Portland', 'dp' => 'Olympia', 'price' => '20'],
                            1 => ['bp' => 'Portland', 'dp' => 'Tacoma', 'price' => '35'],
                            2 => ['bp' => 'Portland', 'dp' => 'Seattle', 'price' => '45'],
                            3 => ['bp' => 'Olympia', 'dp' => 'Tacoma', 'price' => '15'],
                            4 => ['bp' => 'Olympia', 'dp' => 'Seattle', 'price' => '25'],
                        ]
                    ],
                ];
            }
        }
        new ABPTB_Status();
    }