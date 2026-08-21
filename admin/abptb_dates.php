<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Dates')) {
        class ABPTB_Dates {
            public function __construct() {
                add_action('abptb_global_dates', array($this, 'global_dates'));
                add_action('abptb_post_content', array($this, 'post_content_dates'));
                add_action('wp_ajax_abptb_import_date_content', array($this, 'import_date_content'));
                add_action('wp_ajax_abptb_save_global_dates', array($this, 'save_global_dates'));
                add_filter('abptb_get_date_array', array($this, 'get_date_array'));
            }
            public function global_dates(): void {
                ?>
                <div class="global_dates">
                    <?php $this->dates_config(); ?>
                </div>
                <?php
            }
            public function dates_config(): void {
                $abptb_dates = ABPTB_Function::get_option('abptb_dates');
                $date_infos = ABPTB_Function::get_option('abptb_date_config');
                //echo '<pre>';print_r($date_infos);echo '</pre>';
                $format_array = ABPTB_Layout::array_date_format();
                $date_format = $date_infos['date_format'] ?? 'D d M , yy';
                $time_format = $date_infos['time_format'] ?? ABPTB_Time_Format;
                ?>
                <div class="abp_form">
                    <h4 class="abp_gap_xs"><?php ABPTB_Static::svg('date_2');
                            esc_html_e('Global Dates Configuration', 'abp-transport-booking'); ?></h4>
                    <?php ABPTB_Layout::info_text('abptb_dates'); ?>
                    <div class="group_setting _mar_t_xs">
                        <div class="setting_item">
                            <label class="_f_wrap_fj_between_fa_center">
                                <span class="abp_label"><?php esc_html_e('Date Format', 'abp-transport-booking'); ?></span>
                                <?php if (sizeof($format_array) > 0) { ?>
                                    <select class="_form_control " name="date_format" required>
                                        <?php foreach ($format_array as $key => $format) { ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($date_format, $key) ?>><?php echo esc_html($format); ?></option>
                                        <?php } ?>
                                    </select>
                                <?php } ?>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('date_format'); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php esc_html_e('Time Format', 'abp-transport-booking'); ?></span>
                                <input type="text" class="_form_control" name="time_format" placeholder="<?php echo esc_attr(ABPTB_Time_Format); ?>" value="<?php echo esc_attr($time_format); ?>" required/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('time_format'); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="_mar_r_xs"><?php esc_html_e('Buffer time in MIN (Optional)', 'abp-transport-booking'); ?></span>
                                <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="sale_close_before" placeholder="Ex: 15" value="<?php echo esc_attr($date_infos['sale_close_before'] ?? 0); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('sale_close_before'); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php esc_html_e('Number of advance booking date', 'abp-transport-booking'); ?></span>
                                <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="advance_date_number" placeholder="Ex: 28" value="<?php echo esc_attr($date_infos['advance_date_number'] ?? 28); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('advance_date_number'); ?>
                        </div>
                    </div>
                    <?php $this->common_part($abptb_dates); ?>
                    <div class="_divider_xs"></div>
                    <?php ABPTB_Layout::button_global_save('global_dates', __('Save Date Configuration', 'abp-transport-booking')); ?>
                </div>
                <?php
            }
            public function post_content_dates($post_infos): void {
                $date_infos = $post_infos['abptb_dates'] ?? [];
                $active_global_dates = $post_infos['active_global_dates'] ?? 'on';
                $time_infos = $post_infos['time_infos'] ?? [];
                $operation_times = $time_infos['time'] ?? [];
                $day_times = $time_infos['day_time'] ?? [];
                $date_times = $time_infos['date_times'] ?? [];
                $display_return = $post_infos['display_return'] ?? 'off';
                $display_return = ABPTB_Function::on_off('return') ? $display_return : 'off';
                // echo '<pre>';print_r($time_infos);echo '</pre>';
                //echo '<pre>';print_r($time_infos);echo '</pre>';
                ?>
                <div class="tab_item date_configuration" data-tabs="#abptb_dates">
                    <h4 class="abp_color_theme_gap_xs">🗓️<?php esc_html_e('Date Configuration', 'abp-transport-booking'); ?></h4>
                    <div class="_divider_xs"></div>
                    <div class="group_setting">
                        <div class="setting_item">
                            <div class="_f_wrap_fj_between_fa_center">
                                <div class="_gap_xs">
                                    <?php ABPTB_Layout::switch_checkbox('active_global_dates', $active_global_dates); ?>
                                    <span class="abp_label"><?php esc_html_e('Use Global Date Configuration?', 'abp-transport-booking'); ?></span>
                                </div>
                                <div data-collapse="#active_global_dates" class=" <?php echo esc_attr($active_global_dates == 'on' ? '' : 'abp_active'); ?>">
                                    <button type="button" class="_btn_active_xs" onclick="abptb_import_global('date_content')"><span class="fas fa-file-upload"></span><?php esc_html_e('Import Global Date Configuration', 'abp-transport-booking'); ?></button>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('active_global_dates'); ?>
                        </div>
                    </div>
                    <div class="date_content <?php echo esc_attr($active_global_dates == 'off' ? 'abp_active' : ''); ?>" data-collapse="#active_global_dates">
                        <?php $this->common_part($date_infos); ?>
                    </div>
                </div>
                <div class="tab_item times_configuration" data-tabs="#abptb_times">
                    <h4 class="abp_color_theme_gap_xs">⏰ <?php esc_html_e('Time Configuration', 'abp-transport-booking'); ?></h4>
                    <div class="_divider_xs"></div>
                    <div class="group_setting">
                        <div class="setting_item full_width">
                            <div class=" configuration_content">
                                <div class="_f_wrap_fj_between_fa_center_mar_b_xxs">
                                    <span class="abp_label"><?php esc_html_e('Operation Time', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></span>
                                    <?php do_action('abptb_multi_times', $post_infos); ?>
                                </div>
                                <?php ABPTB_Layout::info_text('operation_time'); ?>
                                <div class="_divider_xs"></div>
                                <div class="_f_wrap_f_equal_f_gap_xxs">
                                    <div class="insertable_area sortable_area _f_wrap_gap_xs">
                                        <?php
                                            $time_exit = 0;
                                            if (!empty($operation_times)) {
                                                foreach ($operation_times as $times) {
                                                    if (!empty($times)) {
                                                        $this->time_item('operation_time[]', $times, 'required');
                                                        $time_exit++;
                                                    }
                                                }
                                            }
                                            if ($time_exit == 0) {
                                                $this->time_item('operation_time[]', '', 'required');
                                            }
                                        ?>
                                    </div>
                                    <div class="abp_hidden">
                                        <div class="hidden_content">
                                            <?php $this->time_item('operation_time[]'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $this->day_wise_time($day_times); ?>
                            <?php $this->date_wise_time($date_times); ?>
                        </div>
                        <?php if (ABPTB_Function::on_off('return')) {
                            $return_time_infos = $post_infos['return_time_infos'] ?? [];
                            $return_operation_times = $return_time_infos['time'] ?? [];
                            $return_day_times = $return_time_infos['day_time'] ?? [];
                            $return_date_times = $return_time_infos['date_times'] ?? [];
                            //echo '<pre>';print_r($return_time_infos);echo '</pre>';
                            ?>
                            <div class="setting_item full_width <?php echo esc_attr($display_return == 'on' ? 'abp_active' : ''); ?>" data-collapse="#display_return">
                                <div class=" configuration_content">
                                    <div class="_f_wrap_fj_between_fa_center_mar_b_xxs">
                                        <span class="abp_label"><?php esc_html_e('Return Operation Time', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></span>
                                        <?php do_action('abptb_multi_times_return', $post_infos); ?>
                                    </div>
                                    <?php ABPTB_Layout::info_text('return_operation_time'); ?>
                                    <div class="_divider_xs"></div>
                                    <div class="_f_wrap_f_equal_f_gap_xxs">
                                        <div class="insertable_area sortable_area _f_wrap_gap_xs">
                                            <?php
                                                $time_exit = 0;
                                                if (!empty($return_operation_times)) {
                                                    foreach ($return_operation_times as $times) {
                                                        if (!empty($times)) {
                                                            $this->time_item('return_operation_time[]', $times, 'required');
                                                            $time_exit++;
                                                        }
                                                    }
                                                }
                                                if ($time_exit == 0) {
                                                    $this->time_item('return_operation_time[]', '', 'required');
                                                }
                                            ?>
                                        </div>
                                        <div class="abp_hidden">
                                            <div class="hidden_content">
                                                <?php $this->time_item('return_operation_time[]'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $this->day_wise_time($return_day_times, 'return_'); ?>
                                <?php $this->date_wise_time($return_date_times, 'return_'); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            public function import_date_content(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                ob_start();
                $this->common_part(ABPTB_Function::get_option('abptb_dates'));
                $html_content = ob_get_clean();
                wp_send_json_success(['html' => $html_content, 'msg' => __('Global date configuration Imported Successfully ..... !! ', 'abp-transport-booking'), 'type' => 'success'], 200);
            }
            public function save_global_dates(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $date_infos = $this->get_date_array();
                $date_config['date_format'] = $post_val('date_format');
                $date_config['time_format'] = $post_val('time_format');
                $date_config['advance_date_number'] = $post_val('advance_date_number', '28');
                $date_config['sale_close_before'] = $post_val('sale_close_before');
                update_option('abptb_dates', $date_infos);
                update_option('abptb_date_config', $date_config);
                ob_start();
                $this->dates_config();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Global Date Configuration Saved Successfully ..... !!', 'abp-transport-booking'),
                    'type' => 'success'
                ]);
            }
            public function get_date_array(array $date_infos = []): array {
                $has_post_nonce = isset($_POST['abptb_post_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abptb_post_nonce'])), 'abptb_post_nonce');
                $has_ajax_nonce = check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false);
                if (($has_post_nonce || $has_ajax_nonce) && current_user_can('manage_options')) {
                    $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                    $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                    $format_date = fn($date) => $date ? gmdate('Y-m-d', strtotime($date)) : '';
                    $date_infos['date_type'] = $post_val('date_type', 'periodic_date');
                    $date_infos['periodic_start_date'] = $format_date($post_val('periodic_start_date'));
                    $date_infos['periodic_end_date'] = $format_date($post_val('periodic_end_date'));
                    $date_infos['periodic_after'] = $post_val('periodic_after', '1');
                    $date_rule = $post_val('date_rule');
                    $date_infos['date_rule'] = $post_val('date_rule');
                    $date_rule_array = $date_rule ? explode(',', $date_rule) : [];
                    if (in_array('weekend', $date_rule_array)) {
                        $date_infos['weekend'] = $post_val('weekend');
                    }
                    if (in_array('specific_off_dates', $date_rule_array)) {
                        $specific_off_dates = array_filter($post_array('specific_off_dates'));
                        $date_infos['specific_off_dates'] = array_unique(array_map(fn($d) => gmdate('Y-m-d', strtotime($d)), $specific_off_dates));
                    }
                    if (in_array('off_date_range', $date_rule_array)) {
                        $off_schedules = [];
                        $from_dates = $post_array('abptb_off_from');
                        $to_dates = $post_array('abptb_off_to');
                        foreach ($from_dates as $key => $from_date) {
                            if ($from_date && !empty($to_dates[$key])) {
                                $off_schedules[] = ['from' => $from_date, 'to' => $to_dates[$key]];
                            }
                        }
                        $date_infos['off_date_range'] = $off_schedules;
                    }
                    if (in_array('special_on_dates', $date_rule_array)) {
                        $special_on_dates = $post_array('special_on_dates');
                        $on_start = $post_array('special_on_time_start');
                        $on_end = $post_array('special_on_time_end');
                        $specific_on = [];
                        foreach ($special_on_dates as $key => $date) {
                            if ($date) {
                                $specific_on[$key] = ['date' => $format_date($date), 'start' => $on_start[$key] ?? '', 'end' => $on_end[$key] ?? ''];
                            }
                        }
                        $date_infos['special_on_dates'] = $specific_on;
                    }
                    $off_schedules = [];
                    $from_dates = $post_array('abptb_off_from');
                    $to_dates = $post_array('abptb_off_to');
                    foreach ($from_dates as $key => $from_date) {
                        if ($from_date && !empty($to_dates[$key])) {
                            $off_schedules[] = ['from' => $from_date, 'to' => $to_dates[$key]];
                        }
                    }
                    $date_infos['off_date_range'] = $off_schedules;

                    /****************/
                    $specific_dates = $post_array('specific_dates');
                    $specific_dates = !empty($specific_dates) ? array_values(array_unique(array_filter($specific_dates))) : [];
                    sort($specific_dates);
                    $date_infos['specific_dates'] = $specific_dates;
                }
                return $date_infos;
            }
            public function common_part($date_infos): void {
                $date_type = ($date_infos['date_type'] ?? null) ?: 'periodic_date';
                $specific_dates = $date_infos['specific_dates'] ?? [];
                //echo '<pre>';print_r($date_rule_array);echo '</pre>';
                ?>
                <div class="_mar_t_xs group_setting">
                    <div class="setting_item">
                        <div class=" _fj_between">
                            <span class="abp_label"><?php esc_html_e('Operational Date Type', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></span>
                            <div class="custom_radio _group_content">
                                <input type="hidden" class="_form_control" name="date_type" value="<?php echo esc_attr($date_type); ?>"/>
                                <div class="radio_item">
                                    <button type="button" class="_btn_light_info_xs <?php echo esc_attr($date_type == 'specific_date' ? 'abp_active' : ''); ?>" data-close-target="#specific_date" data-radio="specific_date" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr($date_type == 'specific_date' ? 'far fa-check-circle' : 'far fa-circle'); ?>"></span><?php esc_html_e('Specific Dates', 'abp-transport-booking'); ?>
                                    </button>
                                </div>
                                <div class="radio_item">
                                    <button type="button" class="_btn_light_info_xs <?php echo esc_attr($date_type == 'periodic_date' ? 'abp_active' : ''); ?>" data-close-target="#periodic_date" data-radio="periodic_date" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr($date_type == 'periodic_date' ? 'far fa-check-circle' : 'far fa-circle'); ?>"></span><?php esc_html_e('Periodic Dates', 'abp-transport-booking'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTB_Layout::info_text('date_type'); ?>
                    </div>
                    <div class="setting_item <?php echo esc_attr($date_type == 'periodic_date' ? 'abp_active' : ''); ?>" data-close="#periodic_date">
                        <label class="_f_wrap_fj_between_fa_center">
                            <span class="_mar_r_xs"><?php esc_html_e('Periodic after', 'abp-transport-booking'); ?></span>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="periodic_after" placeholder="Ex: 5" value="<?php echo esc_attr($date_infos['periodic_after'] ?? 1); ?>"/>
                        </label>
                        <div class="_divider_xs"></div>
                        <?php ABPTB_Layout::info_text('periodic_after'); ?>
                    </div>
                    <div class="setting_item <?php echo esc_attr($date_type == 'periodic_date' ? 'abp_active' : ''); ?>" data-close="#periodic_date">
                        <div class="_f_wrap_fj_between_fa_center">
                            <span class="abp_label"><?php esc_html_e('Launching Date (Optional)', 'abp-transport-booking'); ?></span>
                            <?php ABPTB_Layout::input_date('periodic_start_date', ($date_infos['periodic_start_date'] ?? '')); ?>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTB_Layout::info_text('periodic_start_date'); ?>
                    </div>
                    <div class="setting_item <?php echo esc_attr($date_type == 'periodic_date' ? 'abp_active' : ''); ?>" data-close="#periodic_date">
                        <div class="_f_wrap_fj_between_fa_center">
                            <span class="abp_label"><?php esc_html_e('Terminate Date (Optional)', 'abp-transport-booking'); ?></span>
                            <?php ABPTB_Layout::input_date('periodic_end_date', ($date_infos['periodic_end_date'] ?? '')); ?>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTB_Layout::info_text('periodic_end_date'); ?>
                    </div>
                    <div class="setting_item full_width configuration_content <?php echo esc_attr($date_type == 'specific_date' ? 'abp_active' : ''); ?>" data-close="#specific_date">
                        <div class="_f_wrap_fj_between_fa_center">
                            <span class="abp_label"><?php esc_html_e('Specific Dates', 'abp-transport-booking'); ?></span>
                            <?php ABPTB_Layout::button_add(__('Add Specific Date', 'abp-transport-booking')); ?>
                        </div>
                        <div class="_divider_xs"></div>
                        <div class="insertable_area sortable_area _f_wrap_gap_xs">
                            <?php
                                if (sizeof($specific_dates)) {
                                    foreach ($specific_dates as $specific_date) {
                                        if (!empty($specific_date)) {
                                            $this->date_item('specific_dates[]', $specific_date);
                                        }
                                    }
                                }
                            ?>
                        </div>
                        <div class="abp_hidden">
                            <div class="hidden_content">
                                <?php $this->date_item('specific_dates[]'); ?>
                            </div>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTB_Layout::info_text('specific_dates'); ?>
                    </div>
                </div>
                <?php
                $this->special_on_off_dates($date_infos);
            }
            public function special_on_off_dates($date_infos = []): void {
                $date_rule = $date_infos['date_rule'] ?? '';
                $date_rule_array = $date_rule ? explode(',', $date_rule) : [];
                $date_type = ($date_infos['date_type'] ?? null) ?: 'periodic_date';
                $weekend = $date_infos['weekend'] ?? '';
                $weekend_array = $weekend ? explode(',', $weekend) : [];
                $days = ABPTB_Layout::week_day();
                $date_rules = ABPTB_Layout::date_option_rules();
                ?>
                <div class="<?php echo esc_attr($date_type == 'periodic_date' ? 'abp_active' : ''); ?>" data-close="#periodic_date">
                    <div class="group_setting _mar_t_xs">
                        <div class="setting_item full_width">
                            <div class="_fj_between _mar_t_xs">
                                <span class="abp_label"><?php esc_html_e('Special On/Off Date(optional)', 'abp-transport-booking'); ?></span>
                                <div class="custom_checkbox _group_content">
                                    <input type="hidden" name="date_rule" value="<?php echo esc_attr($date_rule); ?>"/>
                                    <?php foreach ($date_rules as $key => $rule) { ?>
                                        <div class="checkbox_item _min_100">
                                            <button type="button" class="_btn_light_info_xs <?php echo esc_attr(in_array($key, $date_rule_array, true) ? 'abp_active' : ''); ?>" data-collapse-target="#<?php echo esc_attr($key); ?>" data-checked="<?php echo esc_attr($key); ?>" data-open-icon="fa-check-square" data-close-icon="fa-square">
                                                <span data-icon class="far <?php echo esc_attr(in_array($key, $date_rule_array, true) ? 'far fa-check-square' : 'fa-square'); ?>"></span><?php echo esc_html($rule); ?>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('date_rule'); ?>
                        </div>
                        <div class="setting_item full_width <?php echo esc_attr(in_array('weekend', $date_rule_array, true) ? 'abp_active' : ''); ?> " data-collapse="#weekend">
                            <div class="_f_wrap_fj_between_fa_center">
                                <span class="abp_label"><?php esc_html_e('Weekend(optional)', 'abp-transport-booking'); ?></span>
                                <div class="custom_checkbox _group_content">
                                    <input type="hidden" name="weekend" value="<?php echo esc_attr($weekend); ?>"/>
                                    <?php foreach ($days as $key => $day) { ?>
                                        <div class="checkbox_item _min_100">
                                            <button type="button" class="_btn_light_info_xs <?php echo esc_attr(in_array($key, $weekend_array) ? 'abp_active' : ''); ?>" data-checked="<?php echo esc_attr($key); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                                <span data-icon class="<?php echo esc_attr(in_array($key, $weekend_array) ? 'far fa-check-square' : 'far fa-square'); ?>"></span><?php echo esc_html($day); ?>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('weekend'); ?>
                        </div>
                        <div class="setting_item configuration_content <?php echo esc_attr(in_array('specific_off_dates', $date_rule_array, true) ? 'abp_active' : ''); ?>" data-collapse="#specific_off_dates">
                            <div class="_fj_between_fa_center">
                                <span class="abp_label"><?php esc_html_e('Specific Off Dates(optional)', 'abp-transport-booking'); ?></span>
                                <?php ABPTB_Layout::button_add(__('Add Specific Off Date', 'abp-transport-booking')); ?>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('specific_off_dates'); ?>
                            <div class="insertable_area sortable_area _f_wrap_gap_xs_mar_t_xs">
                                <?php $specific_off_dates = $date_infos['specific_off_dates'] ?? [];
                                    if (sizeof($specific_off_dates)) {
                                        foreach ($specific_off_dates as $specific_date) {
                                            if ($specific_date) {
                                                $this->date_item('specific_off_dates[]', $specific_date);
                                            }
                                        }
                                    }
                                ?>
                            </div>
                            <div class="abp_hidden">
                                <div class="hidden_content">
                                    <?php $this->date_item('specific_off_dates[]'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="setting_item configuration_content  <?php echo esc_attr(in_array('special_on_dates', $date_rule_array, true) ? 'abp_active' : ''); ?>" data-collapse="#special_on_dates">
                            <div class="_fj_between_fa_center">
                                <span class="abp_label"><?php esc_html_e('Special On Dates (optional)', 'abp-transport-booking'); ?></span>
                                <?php ABPTB_Layout::button_add(__('Add Special On Dates', 'abp-transport-booking')); ?>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('special_on_dates'); ?>
                            <div class="insertable_area sortable_area _f_wrap_gap_xs_mar_t_xs">
                                <?php $special_dates = $date_infos['special_on_dates'] ?? [];
                                    if (sizeof($special_dates)) {
                                        foreach ($special_dates as $specific_date) {
                                            if (!empty($specific_date)) {
                                                $this->date_item('special_on_dates[]', $specific_date);
                                            }
                                        }
                                    }
                                ?>
                            </div>
                            <div class="abp_hidden">
                                <div class="hidden_content">
                                    <?php $this->date_item('special_on_dates[]'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="setting_item configuration_content <?php echo esc_attr(in_array('off_date_range', $date_rule_array, true) ? 'abp_active' : ''); ?>" data-collapse="#off_date_range">
                            <div class="_fj_between_fa_center">
                                <span class="abp_label"><?php esc_html_e('Off Date Range(optional)', 'abp-transport-booking'); ?></span>
                                <?php ABPTB_Layout::button_add(__('Add Off Date Range', 'abp-transport-booking')); ?>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('off_date_range'); ?>
                            <div class="insertable_area sortable_area _f_wrap_gap_xs_mar_t_xs">
                                <?php $off_date_range = $date_infos['off_date_range'] ?? [];
                                    if (sizeof($off_date_range)) {
                                        foreach ($off_date_range as $specific_date) {
                                            if (sizeof($specific_date) > 0 && $specific_date['from'] && $specific_date['to']) {
                                                $this->off_day_range($specific_date['from'], $specific_date['to']);
                                            }
                                        }
                                    }
                                ?>
                            </div>
                            <div class="abp_hidden">
                                <div class="hidden_content">
                                    <?php $this->off_day_range(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            public function day_wise_time($day_times = [], $prefix = ''): void {
                $days = ABPTB_Layout::week_day();
                ?>
                <div class="full_width  <?php echo esc_attr(!empty($day_times) ? 'abp_active' : ''); ?>" data-collapse="#<?php echo esc_attr($prefix); ?>day_wise_time">
                    <div class="_divider_xxs"></div>
                    <div class="_fj_between _fa_center">
                        <span class="abp_label"><?php esc_html_e('Day Wise Operation Time (Optional) ', 'abp-transport-booking'); ?></span>
                        <div class="_group_content custom_checkbox">
                            <?php foreach ($days as $key => $day) { ?>
                                <div class="checkbox_item">
                                    <button type="button" class="_btn_light_info_xs <?php echo esc_attr(array_key_exists($key, $day_times) ? 'abp_active' : ''); ?>" data-collapse-target="#<?php echo esc_attr($prefix . $key); ?>" data-checked="<?php echo esc_attr($key); ?>" data-open-icon="fa-check-square" data-close-icon="fa-square">
                                        <span data-icon class="far <?php echo esc_attr(array_key_exists($key, $day_times) ? 'far fa-check-square' : 'fa-square'); ?>"></span><?php echo esc_html($day); ?>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php ABPTB_Layout::info_text('day_wise_time'); ?>
                    <?php foreach ($days as $key => $day) {
                        $operation_times = $day_times[$key] ?? [];
                        ?>
                        <div class="configuration_content <?php echo esc_attr(array_key_exists($key, $day_times) ? 'abp_active' : ''); ?>" data-collapse="#<?php echo esc_attr($prefix . $key); ?>">
                            <div class="_divider_xs"></div>
                            <div class="insertable_area sortable_area _f_wrap_gap_xs">
                                <?php ABPTB_Layout::button_add(__('Operation Time : ', 'abp-transport-booking') . $day); ?>
                                <?php
                                    if (!empty($operation_times)) {
                                        foreach ($operation_times as $times) {
                                            if (!empty($times)) {
                                                $this->time_item($prefix . $key . '_time[]', $times);
                                            }
                                        }
                                    }
                                ?>
                            </div>
                            <div class="abp_hidden">
                                <div class="hidden_content">
                                    <?php $this->time_item($prefix . $key . '_time[]'); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            public function date_wise_time($date_times = [], $prefix = ''): void {
                ?>
                <div class="full_width configuration_content   <?php echo esc_attr(!empty($date_times) ? 'abp_active' : ''); ?>" data-collapse="#<?php echo esc_attr($prefix); ?>date_wise_time">
                    <div class="_divider_xxs"></div>
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="abp_label"><?php esc_html_e('Date Wise Operation Time (Optional) ', 'abp-transport-booking'); ?></span>
                        <?php ABPTB_Layout::button_add(__('Add New Date Wise Operation Time', 'abp-transport-booking')); ?>
                    </div>
                    <?php ABPTB_Layout::info_text('date_wise_time'); ?>
                    <div class="insertable_area sortable_area">
                        <?php if (!empty($date_times)) {
                            foreach ($date_times as $key => $date_time) {
                                $this->date_wise_time_item($date_time, $key, $prefix);
                            }
                        } ?>
                    </div>
                    <div class="abp_hidden" data-hidden_id>
                        <div class="hidden_content">
                            <?php $this->date_wise_time_item([], uniqid('abp_'), $prefix); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            //=============================//
            public function off_day_range($from_date = '', $to_date = ''): void {
                ?>
                <div class="delete_area _group_content">
                    <?php
                        ABPTB_Layout::button_sort();
                        ABPTB_Layout::input_date('abptb_off_from[]', $from_date);
                        ABPTB_Layout::input_date('abptb_off_to[]', $to_date);
                        ABPTB_Layout::button_delete();
                    ?>
                </div>
                <?php
            }
            public function date_item($name, $date = ''): void {
                ?>
                <div class="delete_area _group_content">
                    <?php
                        ABPTB_Layout::button_sort();
                        ABPTB_Layout::input_date($name, $date);
                        ABPTB_Layout::button_delete();
                    ?>
                </div>
                <?php
            }
            public function time_item($name, $time = '', $required = ''): void {
                ?>
                <div class="delete_area _group_content">
                    <?php
                        ABPTB_Layout::button_sort();
                        ABPTB_Layout::input_time($name, $time, '', $required);
                        ABPTB_Layout::button_delete();
                    ?>
                </div>
                <?php
            }
            public function date_wise_time_item($date_time = [], $key = '', $prefix = ''): void {
                $times = $date_time['time'] ?? [];
                ?>
                <div class="configuration_content delete_area">
                    <input type="hidden" name="<?php echo esc_attr($prefix); ?>date_wise_time_id[]" class="hidden_id" value="<?php echo esc_attr($key); ?>">
                    <div class="_divider_xs"></div>
                    <div class="_fa_start_gap_xs">
                        <div class="_group_content">
                            <?php
                                ABPTB_Layout::button_sort();
                                ABPTB_Layout::input_date($prefix . 'date_wise_date[' . $key . '][]', ($date_time['date'] ?? ''));
                                ABPTB_Layout::button_delete();
                            ?>
                        </div>
                        <?php ABPTB_Layout::button_add(__('Add Operation Time ', 'abp-transport-booking')); ?>
                        <div class="insertable_area sortable_area _f_wrap_fa_gap_xs">
                            <?php if (!empty($times)) {
                                foreach ($times as $time) {
                                    if (!empty($time)) {
                                        $this->time_item($prefix . 'date_wise_time[' . $key . '][]', $time);
                                    }
                                }
                            } ?>
                        </div>
                    </div>
                    <div class="abp_hidden">
                        <div class="hidden_content">
                            <?php $this->time_item($prefix . 'date_wise_time[' . $key . '][]'); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        new ABPTB_Dates();
    }