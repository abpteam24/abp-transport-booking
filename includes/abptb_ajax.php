<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Ajax')) {
        class ABPTB_Ajax {
            public function __construct() {
                add_action('wp_ajax_abptb_global_booking', [$this, 'global_booking']);
                add_action('wp_ajax_nopriv_abptb_global_booking', [$this, 'global_booking']);
                add_action('wp_ajax_abptb_load_transport_data', [$this, 'load_transport_data']);
                add_action('wp_ajax_nopriv_abptb_load_transport_data', [$this, 'load_transport_data']);
                add_action('wp_ajax_abptb_load_date', [$this, 'load_date']);
                add_action('wp_ajax_nopriv_abptb_load_date', [$this, 'load_date']);
                add_action('wp_ajax_abptb_load_return_date', [$this, 'load_return_date']);
                add_action('wp_ajax_nopriv_abptb_load_return_date', [$this, 'load_return_date']);
            }
            public function global_booking(): void {
                if (!check_ajax_referer('abptb_ajax_nonce', 'nonce', false)) {
                    wp_send_json_error(['msg' => __('Session expired. Page Reloading......', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_int = fn($key, $default = '') => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $post_id = $post_int('post_id');
                $bp = $post_val('_bp');
                $dp = $post_val('_dp');
                $bp_dp = ($bp && $dp) ? $bp . '_' . $dp : '';
                $journey_date = $post_val('journey_date');
                $return_date = $post_val('return_date');
                $form_data['post_id'] = $post_id;
                $form_data['down']['bp_dp'] = $bp_dp;
                $form_data['down']['journey_date'] = $journey_date;
                $form_data['down']['post_id'] = $post_id;
                $msg = '';
                if (ABPTB_Function::on_off('return') && !empty($return_date)) {
                    $form_data['up']['journey_date'] = $return_date;
                    $form_data['up']['bp_dp'] = ($bp && $dp) ? $dp . '_' . $bp : '';
                    $form_data['up']['post_id'] = $post_id;
                }
                ob_start();
                if (!empty($post_id) && $post_id > 0) {
                    if (get_post_type($post_id) == ABPTB_Function::get_cpt() && (get_post_status($post_id) == 'publish' || is_admin())) {
                        $post_infos = ABPTB_Function::get_all_meta($post_id);
                        $sale_continue = $post_infos['sale_continue'] ?? 'on';
                        if ($sale_continue == 'on') {
                            $all_start_time = ABPTB_Function::time_route($post_infos, $bp_dp, $journey_date);
                            $bp_times = ABPTB_Function::time_bp($post_infos, $bp_dp, $journey_date, $all_start_time);
                            $journey_time = !empty($bp_times) ? current($bp_times) : '';
                            $journey_time = !empty($journey_time) ? gmdate('Y-m-d H:i', strtotime($journey_time)) : '';
                            $start_time = '';
                            if (!empty($journey_time)) {
                                $_start_time = array_search($journey_time, $bp_times);
                                $_start_time = $_start_time ? $journey_date . ' ' . $_start_time : '';
                                $start_time = $_start_time ? gmdate('Y-m-d H:i', strtotime($_start_time)) : '';
                            }
                            $form_data['down']['start_point'] = ABPTB_Function::start_point($post_infos, $bp_dp);
                            $form_data['down']['bp_times'] = $bp_times;
                            $form_data['down']['journey_time'] = $journey_time;
                            $form_data['down']['start_time'] = $start_time;
                            $display_return = $post_infos['display_return'] ?? 'off';
                            $display_return = ABPTB_Function::on_off('return') ? $display_return : 'off';
                            if (!empty($return_date) && $display_return == 'on') {
                                $bp_dp = ($bp && $dp) ? $dp . '_' . $bp : '';
                                $key = ABPTB_Function::return_check($post_infos, $bp_dp) ? 'return_price_infos' : 'price_infos';
                                $price_infos = $post_infos[$key] ?? ABPTB_Function::get_post_info($post_id, $key, []);
                                if (array_key_exists($bp_dp, $price_infos)) {
                                    $all_start_time = ABPTB_Function::time_route($post_infos, $bp_dp, $return_date);
                                    $bp_times = ABPTB_Function::time_bp($post_infos, $bp_dp, $return_date, $all_start_time);
                                    $journey_time = !empty($bp_times) ? current($bp_times) : '';
                                    $journey_time = !empty($journey_time) ? gmdate('Y-m-d H:i', strtotime($journey_time)) : '';
                                    $start_time = '';
                                    if (!empty($journey_time)) {
                                        $_start_time = array_search($journey_time, $bp_times);
                                        $_start_time = $_start_time ? $return_date . ' ' . $_start_time : '';
                                        $start_time = $_start_time ? gmdate('Y-m-d H:i', strtotime($_start_time)) : '';
                                    }
                                    $form_data['up']['start_point'] = ABPTB_Function::start_point($post_infos, $bp_dp);
                                    $form_data['up']['bp_times'] = $bp_times;
                                    $form_data['up']['journey_time'] = $journey_time;
                                    $form_data['up']['start_time'] = $start_time;
                                    $form_data['up']['post_id'] = $post_id;
                                    $form_data['up']['double_route'] = 'double_route';
                                    $form_data['down']['double_route'] = 'double_route';
                                }
                            }
                            do_action('abptb_registration', $post_infos, $form_data);
                        } else {
                            ABPTB_Layout::layout_warning_info('sale_close_msg');
                        }
                        $msg = ($post_infos['post_title'] ?? '') . ' ' . __('data loaded....!', 'abp-transport-booking');
                    }
                } else {
                    $defaults = ABPTB_Shortcodes::default_attribute();
                    $form_data_down = $form_data['down'] ?? [];
                    $form_data_down['global_order'] = 'yes';
                    $post_ids = ABPTB_Query::get_post_id($form_data_down);
                    $form_data_down['all_post'] = $post_ids;
                    $form_data_down = array_merge($defaults, $form_data_down);
                    $style = ($form_data_down['style'] ?? 'grid') ?: 'grid';
                    $templates = ['grid' => 'list/grid.php', 'missionary' => 'list/missionary.php',];
                    $file = ABPTB_Function::template_path($templates[$style] ?? $templates['grid']);
                    //echo '<pre>';                    print_r($form_data);                    echo '</pre>';
                    ?>
                    <div class="abp_pagination _gap_fd_column">
                        <?php do_action('abptb_post_filter', $form_data_down);
                            if (is_file($file)) {
                                include_once $file;
                                do_action('abptb_' . $style . '_template', $form_data_down);
                            } else {
                                include_once ABPTB_Function::template_path('list/default.php');
                                do_action('abptb_default_template', $form_data_down);
                            } ?>
                    </div>
                    <?php
                    $form_data_up = $form_data['up'] ?? [];
                    if (!empty($form_data_up)) {
                        $form_data_up['global_order'] = 'yes';
                        $post_ids = ABPTB_Query::get_post_id($form_data_up);
                        $form_data_up['all_post'] = $post_ids;
                        $form_data_up = array_merge($defaults, $form_data_up);
                        ?>
                        <div class="abp_pagination _gap_fd_column">
                            <?php do_action('abptb_post_filter', $form_data_up);
                                if (is_file($file)) {
                                    include_once $file;
                                    do_action('abptb_' . $style . '_template', $form_data_up);
                                } else {
                                    include_once ABPTB_Function::template_path('list/default.php');
                                    do_action('abptb_default_template', $form_data_up);
                                } ?>
                        </div>
                        <?php
                    }
                    $msg = ABPTB_Function::label() . ' ' . __('List Loaded Successfully.....! ', 'abp-transport-booking');
                }
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'msg' => $msg, 'type' => 'success']);
            }
            public function load_transport_data(): void {
                if (!check_ajax_referer('abptb_ajax_nonce', 'nonce', false)) {
                    wp_send_json_error(['msg' => __('Session expired. Page Reloading......', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_int = fn($key, $default = '') => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $post_id = $post_int('post_id');
                if (!empty($post_id) && $post_id > 0 && get_post_type($post_id) == ABPTB_Function::get_cpt() && (get_post_status($post_id) == 'publish' || is_admin())) {
                    $post_infos = ABPTB_Function::get_all_meta($post_id);
                    $sale_continue = $post_infos['sale_continue'] ?? 'on';
                    ob_start();
                    if ($sale_continue == 'on') {
                        $prefix = $post_val('prefix');
                        $bp_dp = $post_val($prefix . 'bp_dp');
                        $start_time = $post_val($prefix . 'start_time');
                        $journey_date = !empty($start_time) ? gmdate('Y-m-d', strtotime($start_time)) : '';
                        $all_start_time = ABPTB_Function::time_route($post_infos, $bp_dp, $journey_date);
                        $bp_times = ABPTB_Function::time_bp($post_infos, $bp_dp, $journey_date, $all_start_time);
                        $journey_time = $post_val($prefix . 'journey_time');
                        if (!empty($journey_time)) {
                            $_start_time = array_search($journey_time, $bp_times);
                            $_start_time = $_start_time ? $journey_date . ' ' . $_start_time : '';
                            $start_time = $_start_time ? gmdate('Y-m-d H:i', strtotime($_start_time)) : '';
                        }
                        $form_data['post_id'] = $post_id;
                        $form_data['bp_dp'] = $bp_dp;
                        $form_data['start_point'] = $post_val($prefix . 'start_point');
                        $form_data['bp_times'] = $bp_times;
                        $form_data['journey_time'] = $journey_time;
                        $form_data['sp_id'] = $post_val($prefix . 'sp_id');
                        $form_data['double_route'] = $post_val('double_route');
                        $form_data['start_time'] = $start_time;
                        $seat_type = $post_infos['seat_type'] ?? 'sp';
                        $seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
                        if ($seat_type === 'ticket') {
                            do_action('abptb_ticket_type', $post_infos, $form_data, $prefix);
                        } else {
                            do_action('abptb_sp_type', $post_infos, $form_data, $prefix);
                        }
                    } else {
                        ABPTB_Layout::layout_warning_info('sale_close_msg');
                    }
                    //echo '<pre>';				print_r($form_data);				echo '</pre>';
                    $html = ob_get_clean();
                    wp_send_json_success(['html' => $html, 'msg' => (get_the_title($post_id) . ' ' . __('data loaded....!', 'abp-transport-booking')), 'type' => 'success']);
                } else {
                    wp_send_json_success(['msg' => __('Something Wrong... Reload Page....!', 'abp-transport-booking'), 'type' => 'warn']);
                }
            }
            public function load_date(): void {
                if (!check_ajax_referer('abptb_ajax_nonce', 'nonce', false)) {
                    wp_send_json_error(['msg' => esc_html__('Session expired. Please refresh the page.', 'abp-transport-booking')], 403);
                }
                $post_int = fn($key, $default = '') => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_id = $post_int('post_id');
                $all_dates = !empty($post_id) && $post_id > 0 ? ABPTB_Function::date_all([$post_id]) : ABPTB_Function::date_all();
                $upcoming_date = current($all_dates);
                $upcoming_date = !empty($upcoming_date) ? gmdate('Y-m-d', strtotime($upcoming_date)) : '';
                ob_start();
                //echo '<pre>';                    print_r($post_id);                    echo '</pre>';
                //echo '<pre>';                    print_r($all_dates);                    echo '</pre>';
                ABPTB_Layout::journey_date($all_dates, $upcoming_date);
                $html_journey = ob_get_clean();
                ob_start();
                if (ABPTB_Function::on_off('return')) {
                    ABPTB_Layout::return_date($all_dates);
                }
                $html_return = ob_get_clean();
                $new_picker_config = !empty($all_dates) ? ABPTB_Layout::create_datepicker_array($all_dates) : '';
                wp_send_json_success([
                    'html_journey' => $html_journey, 'type' => 'success',
                    'html_return' => $html_return,
                    'msg' => esc_html__('Date Loaded successfully.', 'abp-transport-booking'),
                    'picker_config' => $new_picker_config,
                    'return' => '#return_date',
                    'journey' => '#journey_date'
                ]);
            }
            public function load_return_date(): void {
                if (!check_ajax_referer('abptb_ajax_nonce', 'nonce', false)) {
                    wp_send_json_error(['msg' => esc_html__('Session expired. Please refresh the page.', 'abp-transport-booking')], 403);
                }
                $post_int = fn($key, $default = '') => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $post_id = $post_int('post_id');
                $journey_date = $post_val('journey_date');
                $journey_date = !empty($journey_date) ? gmdate('Y-m-d', strtotime($journey_date)) : '';
                ob_start();
                $all_end_dates = ABPTB_Function::date($post_id, [], $journey_date);
                ABPTB_Layout::return_date($all_end_dates);
                $new_picker_config = !empty($all_end_dates) ? ABPTB_Layout::create_datepicker_array($all_end_dates) : '';
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html, 'type' => 'success',
                    'msg' => esc_html__('Return Date Loaded successfully.', 'abp-transport-booking'),
                    'picker_config' => $new_picker_config,
                    'selector' => '#return_date'
                ]);
            }
        }
        new ABPTB_Ajax();
    }