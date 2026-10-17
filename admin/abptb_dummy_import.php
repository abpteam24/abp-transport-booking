<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Dummy_Import')) {
        class ABPTB_Dummy_Import {
            public function __construct() {
                add_action('wp_ajax_abptb_create_page', array($this, 'create_page'));
                add_action('wp_ajax_abptb_delete_dummy', array($this, 'delete_dummy'));
                add_action('wp_ajax_abptb_import_dummy', array($this, 'import_dummy'));
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
                        if ($page_type == 'tf_ticket') {
                            $label = __('Ticket', 'abp-transport-booking');
                            $short_code = '[abptb-ticket]';
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
                        /* translators: %s: Transport Label */
                        $translated_format = esc_html__('%s Page Created successfully.....', 'abp-transport-booking');
                        $msg = sprintf($translated_format, $label);
                        wp_send_json_success(['type' => 'success', 'msg' => $msg]);
                    }
                    wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('Page already exists.', 'abp-transport-booking')]);
                } else {
                    wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('Something Wrong...!', 'abp-transport-booking')]);
                }
            }
            public function delete_dummy(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $dummy_posts = ABPTB_Query::dummy_ids();
                if (empty($dummy_posts)) {
                    wp_send_json_error(['type' => 'warn', 'msg' => esc_html__('No dummy data found to delete.', 'abp-transport-booking')]);
                }
                foreach ($dummy_posts as $post_id) {
                    wp_delete_post((int)$post_id, true);
                }
                flush_rewrite_rules();
                wp_send_json_success(['type' => 'success', 'msg' => esc_html__('Dummy data deleted successfully!', 'abp-transport-booking')]);
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
                        'abptb_location' => self::static_location(),
                        'abptb_category' => self::static_category(),
                        'abptb_organizer' => self::static_organizer(),
                        'abptb_brand' => self::static_brand(),
                    ],
                    'options' => [
                        'abptb_ticket' => self::static_ticket(),
                        'abptb_decor' => self::static_decoration(),
                        'abptb_additional' => ABPTB_Static::static_additional(),
                        'abptb_form' => ABPTB_Static::static_form(),
                        'abptb_faq' => self::static_faq(),
                        'abptb_tc' => self::static_tc(),
                        'abptb_feature' => self::static_feature(),
                    ],
                    'custom_post' => []
                ];
            }
            public function dummy(): array {
                $on_off = ['on', 'off'];
                $template = ["default", "light", "premium"];
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
                        'abptb_organizer' => self::get_id($all_organizer, $organizer[$i]),
                        'display_brand' => $on_off[wp_rand(0, 1)],
                        'abptb_brand' => self::get_id($all_brands, $band[$i]),
                        'display_capacity' => $on_off[wp_rand(0, 1)],
                        'display_category' => $on_off[wp_rand(0, 1)],
                        'abptb_category' => self::get_id($all_categories, $categories[$i]),
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
                        'ticket_infos' => $all_route_info[$i]['ticket_infos'] ?? [],
                        'sp_infos' => $all_route_info[$i]['sp_infos'] ?? [],
                        'all_ticket_type' => $all_route_info[$i]['all_ticket_type'] ?? [],
                        'routing_infos' => $all_route_info[$i]['routing_infos'] ?? [],
                        'return_routing_infos' => $all_route_info[$i]['return_routing_infos'] ?? [],
                        'route_data' => $all_route_info[$i]['route_data'] ?? [],
                        'route_direction' => $all_route_info[$i]['route_direction'] ?? [],
                        'return_route_direction' => $all_route_info[$i]['return_route_direction'] ?? [],
                        'price_infos' => $all_route_info[$i]['price_infos'] ?? [],
                        'return_price_infos' => $all_route_info[$i]['return_price_infos'] ?? [],
                        'price_data' => $all_route_info[$i]['price_data'] ?? [],
                    ];
                }
                return $all_data;
            }
            public function route_info($seat_type = []): array {
                self::static_sp();
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
                                $stop = self::get_id($options, ($info['stop'] ?? ''));
                                $all_data[$key]['routing_infos'][$stop]['type'] = $info['type'] ?? '';
                                $all_data[$key]['routing_infos'][$stop]['time'] = $info['time'] ?? '';
                                $all_data[$key]['route_direction'][] = $stop;
                            }
                            foreach ($prices as $info) {
                                $bp = self::get_id($options, ($info['bp'] ?? ''));
                                $dp = self::get_id($options, ($info['dp'] ?? ''));
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
                                $stop = self::get_id($options, ($info['stop'] ?? ''));
                                $all_data[$key]['return_routing_infos'][$stop]['type'] = $info['type'] ?? '';
                                $all_data[$key]['return_routing_infos'][$stop]['time'] = $info['time'] ?? '';
                                $all_data[$key]['return_route_direction'][] = $stop;
                            }
                            foreach ($prices as $info) {
                                $bp = self::get_id($options, ($info['bp'] ?? ''));
                                $dp = self::get_id($options, ($info['dp'] ?? ''));
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
            public static function static_sp(): void {
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
            public static function get_id($options = [], $name = ''): int|string|null {
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
            public static function static_location(): array {
                return [
                    0 => ['name' => 'New York City'],
                    1 => ['name' => 'Los Angeles'],
                    2 => ['name' => 'Chicago'],
                    3 => ['name' => 'Houston'],
                    4 => ['name' => 'Philadelphia'],
                    5 => ['name' => 'Dallas'],
                    6 => ['name' => 'San Jose'],
                    7 => ['name' => 'Seattle'],
                    8 => ['name' => 'Washington, D.C.'],
                    9 => ['name' => 'Boston'],
                    10 => ['name' => 'Detroit'],
                    11 => ['name' => 'Portland'],
                    12 => ['name' => 'Baltimore'],
                    13 => ['name' => 'Fresno'],
                    14 => ['name' => 'Providence'],
                    15 => ['name' => 'Tacoma'],
                    16 => ['name' => 'Olympia'],
                    17 => ['name' => 'Bakersfield'],
                    18 => ['name' => 'South Bend'],
                    19 => ['name' => 'Toledo'],
                    20 => ['name' => 'New Haven'],
                    21 => ['name' => 'Corsicana'],
                    22 => ['name' => 'Huntsville'],
                    23 => ['name' => 'San Francisco'],
                ];
            }
            public static function static_category(): array {
                return [
                    0 => ['name' => 'AC'],
                    1 => ['name' => 'Non AC'],
                    2 => ['name' => 'Economy'],
                    3 => ['name' => 'Business'],
                    4 => ['name' => 'VIP'],
                    5 => ['name' => 'Sleeper'],
                    6 => ['name' => 'Express'],
                    7 => ['name' => 'Local'],
                    8 => ['name' => 'Luxury'],
                    9 => ['name' => 'Shuttle'],
                ];
            }
            public static function static_organizer(): array {
                return [
                    0 => ['name' => 'Global Transit Group'],
                    1 => ['name' => 'Express Travel Network'],
                    2 => ['name' => 'Premium Coach Services'],
                    3 => ['name' => 'InterCity Transport'],
                    4 => ['name' => 'Continental Bus Lines'],
                    5 => ['name' => 'Smart Mobility Solutions'],
                ];
            }
            public static function static_brand(): array {
                return [
                    0 => ['name' => 'Mercedes-Benz'],
                    1 => ['name' => 'Volvo'],
                    2 => ['name' => 'Scania'],
                    3 => ['name' => 'MAN'],
                    4 => ['name' => 'IVECO'],
                    5 => ['name' => 'Setra'],
                    6 => ['name' => 'Yutong'],
                    7 => ['name' => 'King Long'],
                    8 => ['name' => 'Alexander Dennis'],
                    9 => ['name' => 'VDL Bus & Coach'],
                ];
            }
            public static function static_ticket(): array {
                return [
                    1 => ['label' => 'Ticket', 'color' => '', 'prefix' => '', 'icon' => '🎟️', 'type' => 'seat',],
                    2 => ['label' => 'VIP', 'color' => '#A78BFA', 'prefix' => 'VIP-', 'icon' => '👑', 'type' => 'seat',],
                    3 => ['label' => 'Business Class', 'color' => '#0EA5E9', 'prefix' => 'B-', 'icon' => '🛋️', 'type' => 'seat',],
                    4 => ['label' => 'Special', 'color' => '#6366F1', 'prefix' => 'S-', 'icon' => 'fas fa-couch', 'type' => 'seat',],
                    5 => ['label' => 'Couple', 'color' => '#C026D3', 'prefix' => 'C-', 'icon' => '💑', 'type' => 'seat',],
                    6 => ['label' => 'Female', 'color' => '#F472B6', 'prefix' => 'F-', 'icon' => '👩', 'type' => 'seat',],
                    7 => ['label' => 'Adult', 'color' => '#78350F', 'prefix' => 'AD-', 'icon' => 'fas fa-chair', 'type' => 'seat',],
                    8 => ['label' => 'Child', 'color' => '#F59E0B', 'prefix' => 'CH-', 'icon' => '🪑', 'type' => 'seat',],
                    9 => ['label' => 'Economy', 'color' => '#84CC16', 'prefix' => 'E-', 'icon' => '💺', 'type' => 'seat',],
                ];
            }
            public static function static_decoration(): array {
                return [
                    1 => ['label' => 'Blank Space', 'color' => '', 'icon' => '', 'type' => 'other'],
                    2 => ['label' => 'Driver Seat', 'color' => '#1E293B', 'icon' => '👨‍✈️', 'type' => 'other'],
                    3 => ['label' => 'Door Entry', 'color' => '#EAB308', 'icon' => '🚪', 'type' => 'other'],
                    4 => ['label' => 'Stairs', 'color' => '#64748B', 'icon' => '🪜', 'type' => 'other'],
                    5 => ['label' => 'Aisle/Walkway', 'color' => '#94A3B8', 'icon' => '↔', 'type' => 'other'],
                    6 => ['label' => 'Window', 'color' => '#38BDF8', 'icon' => '🪟', 'type' => 'other'],
                    7 => ['label' => 'Engine Box', 'color' => '#475569', 'icon' => '⚙️', 'type' => 'other'],
                    8 => ['label' => 'Toilet', 'color' => '#06B6D4', 'icon' => '🚽', 'type' => 'other'],
                    9 => ['label' => 'Luggage Rack', 'color' => '#F97316', 'icon' => '🧳', 'type' => 'other'],
                    10 => ['label' => 'Food/Snacks', 'color' => '#10B981', 'icon' => '🍔', 'type' => 'other'],
                    11 => ['label' => 'Emergency Exit', 'color' => '#EF4444', 'icon' => '🚨', 'type' => 'other'],
                ];
            }
            public static function static_feature(): array {
                return [
                    1 => ['icon' => '🚍', 'label' => 'Coach Bus'],
                    2 => ['icon' => '🚐', 'label' => 'Minibus'],
                    3 => ['icon' => '🏫', 'label' => 'School Bus'],
                    4 => ['icon' => '🏙️', 'label' => 'City Bus'],
                    5 => ['icon' => '🏨', 'label' => 'Shuttle Bus'],
                    6 => ['icon' => '🚇', 'label' => 'Metro'],
                    7 => ['icon' => '🚉', 'label' => 'Subway'],
                    8 => ['icon' => '🚝', 'label' => 'Monorail'],
                    9 => ['icon' => '🚈', 'label' => 'Light Rail'],
                    10 => ['icon' => '🚄', 'label' => 'High Speed Train'],
                    11 => ['icon' => '🚅', 'label' => 'Bullet Train'],
                    12 => ['icon' => '🚞', 'label' => 'Mountain Railway'],
                    13 => ['icon' => '🚠', 'label' => 'Cable Car'],
                    15 => ['icon' => '🏠', 'label' => 'Domestic Flight'],
                    16 => ['icon' => '🌍', 'label' => 'International Flight'],
                    17 => ['icon' => '🛩️', 'label' => 'Air Charter'],
                    18 => ['icon' => '🚢', 'label' => 'Passenger Ship'],
                    19 => ['icon' => '🛳️', 'label' => 'Cruise Ship'],
                    21 => ['icon' => '🚤', 'label' => 'Speed Boat'],
                    22 => ['icon' => '🛶', 'label' => 'River Boat'],
                    23 => ['icon' => '🚕', 'label' => 'Cab Service'],
                    24 => ['icon' => '🚘', 'label' => 'Ride Sharing'],
                    25 => ['icon' => '🚗', 'label' => 'Car Rental'],
                    26 => ['icon' => '🏍️', 'label' => 'Motorbike Taxi'],
                    27 => ['icon' => '🚲', 'label' => 'Bicycle Rental'],
                    28 => ['icon' => '🛺', 'label' => 'Auto Rickshaw'],
                    29 => ['icon' => '🚜', 'label' => 'Van Service'],
                    31 => ['icon' => '🎟️', 'label' => 'E-Ticket'],
                    32 => ['icon' => '📱', 'label' => 'Mobile Ticket'],
                    33 => ['icon' => '🔳', 'label' => 'QR Code Ticket'],
                    34 => ['icon' => '💺', 'label' => 'Seat Reservation'],
                    36 => ['icon' => '⭐', 'label' => 'VIP Seat'],
                    37 => ['icon' => '🛏️', 'label' => 'Sleeper Seat'],
                    38 => ['icon' => '👨‍👩‍👧‍👦', 'label' => 'Family Seat'],
                    40 => ['icon' => '🔄', 'label' => 'Round Trip Booking'],
                    41 => ['icon' => '🌐', 'label' => 'Multi City Booking'],
                    42 => ['icon' => '⚡', 'label' => 'Instant Booking'],
                    43 => ['icon' => '❌', 'label' => 'Ticket Cancellation'],
                    44 => ['icon' => '💰', 'label' => 'Ticket Refund'],
                    45 => ['icon' => '📍', 'label' => 'Route Tracking'],
                    46 => ['icon' => '🛰️', 'label' => 'GPS Tracking'],
                    47 => ['icon' => '📡', 'label' => 'Live Location'],
                    50 => ['icon' => '🌙', 'label' => 'Night Service'],
                    51 => ['icon' => '🚀', 'label' => 'Express Service'],
                    52 => ['icon' => '🌎', 'label' => 'International Routes'],
                    53 => ['icon' => '💳', 'label' => 'Online Payment'],
                    54 => ['icon' => '💵', 'label' => 'Cash Payment'],
                    55 => ['icon' => '💳', 'label' => 'Card Payment'],
                    56 => ['icon' => '📲', 'label' => 'Mobile Banking'],
                    57 => ['icon' => '🧾', 'label' => 'Invoice Support'],
                    58 => ['icon' => '❄️', 'label' => 'Air Conditioning'],
                    59 => ['icon' => '📶', 'label' => 'WiFi'],
                    60 => ['icon' => '🔌', 'label' => 'USB Charging'],
                    61 => ['icon' => '🔋', 'label' => 'Power Outlet'],
                    62 => ['icon' => '🪑', 'label' => 'Reclining Seats'],
                    63 => ['icon' => '🎬', 'label' => 'Entertainment System'],
                    64 => ['icon' => '💡', 'label' => 'Reading Light'],
                    66 => ['icon' => '🚻', 'label' => 'Toilet Facility'],
                    67 => ['icon' => '🧳', 'label' => 'Luggage Storage'],
                    68 => ['icon' => '♿', 'label' => 'Wheelchair Access'],
                    69 => ['icon' => '👴', 'label' => 'Priority Seating'],
                    70 => ['icon' => '👶', 'label' => 'Child Friendly'],
                    71 => ['icon' => '🐕', 'label' => 'Pet Friendly'],
                    72 => ['icon' => '🎥', 'label' => 'CCTV Security'],
                    73 => ['icon' => '🚪', 'label' => 'Emergency Exit'],
                    74 => ['icon' => '⛑️', 'label' => 'First Aid Kit'],
                ];
            }
            public static function static_faq(): array {
                return [
                    1 => [
                        'title' => 'How do I book a ticket?',
                        'des' => '<p>You can book a ticket by selecting your route, travel date, departure time, and preferred seat. Complete the payment process to confirm your booking.</p>',
                    ],
                    2 => [
                        'title' => 'Can I cancel my booking?',
                        'des' => '<p>Yes, you can cancel your booking according to the cancellation policy set by the transport operator. Refund eligibility may vary depending on the booking conditions.</p>',
                    ],
                    3 => [
                        'title' => 'Can I change my travel date?',
                        'des' => '<p>Travel dates can be modified only if the transport operator allows schedule changes and seats are available for the selected date.</p>',
                    ],
                    4 => [
                        'title' => 'When should I arrive at the boarding point?',
                        'des' => '<p>Passengers are advised to arrive at least 15 to 30 minutes before the scheduled departure time to avoid missing the journey.</p>',
                    ],
                    5 => [
                        'title' => 'Will I receive a booking confirmation?',
                        'des' => '<p>Yes, a booking confirmation will be sent after a successful payment. You can also review your booking details from your account dashboard.</p>',
                    ],
                    6 => [
                        'title' => 'What payment methods are supported?',
                        'des' => '<p>Available payment methods depend on the payment gateways configured by the website administrator.</p>',
                    ],
                    7 => [
                        'title' => 'Can I select my preferred seat?',
                        'des' => '<p>Yes, if seat selection is enabled by the operator, you will be able to choose your preferred seat during the booking process.</p>',
                    ],
                    8 => [
                        'title' => 'What happens if the trip is delayed?',
                        'des' => '<p>Departure times may change due to weather conditions, traffic congestion, maintenance requirements, or other unforeseen circumstances.</p>',
                    ],
                    9 => [
                        'title' => 'Are children allowed to travel?',
                        'des' => '<p>Yes, children are allowed to travel. Additional requirements and ticket policies may vary depending on the transport operator.</p>',
                    ],
                    10 => [
                        'title' => 'Who should I contact for assistance?',
                        'des' => '<p>If you need assistance, please contact the transport operator using the contact information provided on the website.</p>',
                    ],
                ];
            }
            public static function static_tc(): false|string {
                ob_start(); ?>
                <h6>1. Acceptance of Terms</h6>
                By using our transport booking service, you agree to comply with these terms and conditions. If you do not agree with any part of these terms, please do not use this service.
                <h6>2. Booking Confirmation</h6>
                A booking is considered confirmed only after successful payment and receipt of a booking confirmation. The customer is responsible for providing accurate information during the booking process.
                <h6>3. Passenger Information</h6>
                Passengers must provide valid information, including their name, contact details, and any other required information. Incorrect information may result in booking cancellation without notice.
                <h6>4. Seat Availability</h6>
                All bookings are subject to seat availability. The operator reserves the right to change or reassign seats when necessary due to operational requirements.
                <h6>5. Boarding and Arrival</h6>
                Passengers are advised to arrive at the designated boarding point at least 15 to 30 minutes before departure. Failure to arrive on time may result in the loss of the booking without any refund.
                <h6>6. Cancellation and Refund Policy</h6>
                Cancellation and refund eligibility may vary depending on the operator's policy. Any applicable processing fees, taxes, or service charges may be deducted from the refund amount.
                <h6>7. Schedule Changes</h6>
                Departure times, routes, and stops may be modified because of weather conditions, traffic, technical issues, government regulations, or other unforeseen circumstances.
                <h6>8. Passenger Conduct</h6>
                Passengers must behave appropriately during the journey. Any unlawful, abusive, or disruptive behavior may result in immediate removal from the service without compensation.
                <h6>9. Luggage Policy</h6>
                Passengers are responsible for their belongings. The transport operator will not be liable for any loss, damage, or theft of personal items.
                <h6>10. Limitation of Liability</h6>
                The transport operator shall not be held responsible for delays, cancellations, accidents, natural disasters, or circumstances beyond reasonable control.
                <h6>11. Privacy Policy</h6>
                Personal information collected during the booking process will be used only to provide and improve the service in accordance with the applicable privacy policy.
                <h6>12. Changes to Terms</h6>
                These terms and conditions may be updated at any time without prior notice. Continued use of the service indicates acceptance of the revised terms and conditions.
                <h6>13. Contact Information</h6>
                If you have any questions regarding these terms and conditions, please contact the transport operator directly.
                <?php return ob_get_clean();
            }
        }
        new ABPTB_Dummy_Import();
    }