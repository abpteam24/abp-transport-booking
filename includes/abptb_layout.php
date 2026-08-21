<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Layout')) {
        class ABPTB_Layout {
            public function __construct() {
                add_action('abptb_load_date_picker', [$this, 'load_date_picker'], 10, 2);
                //==============================//
                add_action('abptb_add_icon', array($this, 'load_icon'), 10, 2);
                add_action('abptb_add_image_multiple', array($this, 'add_image_multi'), 10, 2);
                add_action('abptb_add_image_icon', array($this, 'selection_icon_image'), 10, 3);
                add_action('abptb_image_selection', array($this, 'image_selection'), 10, 3);
            }
            public function load_date_picker($selector, $dates): void {
                if (empty($dates) || !is_array($dates)) {
                    return;
                }
                $picker_data = self::create_datepicker_array($dates);
                $json_selector = wp_json_encode(sanitize_text_field($selector));
                $json_data = wp_json_encode($picker_data);
                $inline_js = "window.abptb_picker_data = window.abptb_picker_data || {}; window.abptb_picker_data[{$json_selector}] = {$json_data};";
                wp_add_inline_script('jquery-ui-datepicker', $inline_js);
            }
            public static function create_datepicker_array($dates): array {
                $start_date = current($dates);
                $start_year = (int)gmdate('Y', strtotime($start_date));
                $start_month = (int)(gmdate('n', strtotime($start_date)) - 1);
                $start_day = (int)gmdate('j', strtotime($start_date));
                $end_date = end($dates);
                $end_year = (int)gmdate('Y', strtotime($end_date));
                $end_month = (int)(gmdate('n', strtotime($end_date)) - 1);
                $end_day = (int)gmdate('j', strtotime($end_date));
                $all_dates = [];
                foreach ($dates as $date) {
                    $all_dates[] = gmdate('j-n-Y', strtotime($date));
                }
                return [
                    'minYear' => $start_year,
                    'minMonth' => $start_month,
                    'minDay' => $start_day,
                    'maxYear' => $end_year,
                    'maxMonth' => $end_month,
                    'maxDay' => $end_day,
                    'activeDates' => $all_dates,
                    'txtAvail' => esc_js(__('Available', 'abp-transport-booking')),
                    'txtUnavail' => esc_js(__('Unavailable', 'abp-transport-booking'))
                ];
            }
            //==============================//
            public static function load_admin_globally(): void {
                ?>
                <div class="abp_popup " data-popup="#abptb_global_popup">
                    <div class="popup_area">
                        <span class="close_icon" onclick="abptb_popup_close_global()"><i class="fas fa-times"></i></span>
                        <div class="popup_body"></div>
                    </div>
                </div>
                <div class="popup_icon abp_popup" data-popup="#abptb_popup_icon">
                    <div class="popup_area">
                        <div class="popup_head _all_center">
                            <div class="abp_dropdown _max_400">
                                <label class="abp_all_center">
                                    <input type="hidden" class="abp_icon_search_hidden" name="abp_icon_search" value=""/>
                                    <input type="text" class="_form_control_text_center validation_name abptb_allow abp_icon_search" name="" placeholder="<?php esc_attr_e('Search  icon', 'abp-transport-booking'); ?>" value=""/>
                                </label>
                                <div class="dropdown_list"></div>
                            </div>
                            <span class="popup_close"><i class="fas fa-times"></i></span>
                        </div>
                        <div class="popup_body">
                            <h4 class="abp_text_center item_icon_title"></h4>
                            <div class="item_icon_area"></div>
                        </div>
                    </div>
                </div>
                <?php
            }
            //==============================//
            public static function button_add($button_text, $class = '', $button_class = ''): void {
                $class = $class ?: 'add_new_hook';
                $button_class = $button_class ?: '_btn_light_active_xs';
                $button_text = $button_text ?: __('Add New', 'abp-transport-booking');
                ?>
                <button class="<?php echo esc_attr($button_class . ' ' . $class); ?>" type="button">
                    <?php ABPTB_Static::svg('plus');
                        echo esc_html($button_text); ?>
                </button>
                <?php
            }
            public static function button_edit($class_edit = 'edit_hook'): void {
                ?>
                <button class="_btn_light_navy_blue_xs <?php echo esc_attr($class_edit); ?>" type="button" title="<?php esc_attr_e('Edit This Item', 'abp-transport-booking'); ?>">
                    <?php ABPTB_Static::svg('edit'); ?>
                </button>
                <?php
            }
            public static function button_delete($class = 'delete_hook'): void {
                ?>
                <button class="_btn_light_danger_xxs <?php echo esc_attr($class); ?>" type="button" title="<?php esc_attr_e('Delete This Item', 'abp-transport-booking'); ?>"><?php ABPTB_Static::svg('close_1'); ?></button>
                <?php
            }
            public static function button_save($text = '', $class = ''): void {
                $class = $class ?: '_btn_green_pale_xs';
                $text = $text ?: __('save', 'abp-transport-booking');
                ?>
                <button class="<?php echo esc_attr($class); ?>" type="submit">
                    <?php ABPTB_Static::svg('save');
                        echo esc_html($text); ?>
                </button>
                <?php
            }
            public static function button_sort(): void {
                ?>
                <div class="_btn_light_info_xxs sortable_handle" type="button" title="<?php esc_attr_e('Move This Item', 'abp-transport-booking'); ?>">
                    <?php ABPTB_Static::svg('drag'); ?>
                </div>
                <?php
            }
            public static function button_delete_sort_edit(): void {
                ?>
                <div class="_all_center">
                    <div class="_group_content">
                        <?php
                            self::button_edit();
                            self::button_sort();
                            self::button_delete();
                        ?>
                    </div>
                </div>
                <?php
            }
            public static function button_delete_sort(): void {
                ?>
                <div class="_all_center">
                    <div class="_group_content">
                        <?php
                            self::button_sort();
                            self::button_delete();
                        ?>
                    </div>
                </div>
                <?php
            }
            public static function button_global_save($action, $text = '', $class = ''): void {
                if (!empty($action)) {
                    $class = $class ?: '_btn_green_pale_xs';
                    $text = $text ?: __('save', 'abp-transport-booking');
                    ?>
                    <button class="<?php echo esc_attr($class); ?>" type="button" onclick="abptb_save_global('<?php echo esc_attr($action); ?>',this)">
                        <?php ABPTB_Static::svg('save');
                            echo esc_html($text); ?>
                    </button>
                    <?php
                }
            }
            public static function button_global_popup($action, $text = '', $class = ''): void {
                if (!empty($action)) {
                    $class = $class ?: '_btn_light_active_xs';
                    $text = $text ?: __('Add New', 'abp-transport-booking');
                    ?>
                    <button type="button" class="<?php echo esc_attr($class) ?>" onclick="abptb_popup_open_global('<?php echo esc_attr($action); ?>')">
                        <?php ABPTB_Static::svg('plus');
                            echo esc_html($text); ?>
                    </button>
                    <?php
                }
            }
            //=============================//
            public static function info_text($key = '', $data = ''): void {
                $data = empty($data) ? ABPTB_Static::array_info($key) : $data;
                if ($data) {
                    ?>
                    <div class="info_text load_more">
                        ℹ️ &nbsp;<?php echo wp_kses_post($data); ?>
                        <span class="load_more_action" data-less="<?php esc_html_e('.... Less ', 'abp-transport-booking'); ?>" data-more="<?php esc_html_e('.... More', 'abp-transport-booking'); ?>"><?php esc_html_e('.... More', 'abp-transport-booking'); ?></span>
                    </div>
                    <?php
                }
            }
            public static function load_more($data = ''): void {
                if ($data) {
                    ?>
                    <div class="load_more">
                        <?php echo wp_kses_post($data); ?>
                        <span class="load_more_action" data-less="<?php esc_html_e('.... Less ', 'abp-transport-booking'); ?>" data-more="<?php esc_html_e('.... More', 'abp-transport-booking'); ?>"><?php esc_html_e('.... More', 'abp-transport-booking'); ?></span>
                    </div>
                    <?php
                }
            }
            public static function layout_warning_info($key): void {
                $data = ABPTB_Static::array_info($key);
                if ($data) {
                    echo '<div class="_section_bg_warning_mar_zero"><h4 class="abp_text_center_color_white">' . esc_html($data) . '</h4></div>';
                }
            }
            public static function layout_warning_info_xs($key, $data = ''): void {
                $data = empty($data) ? ABPTB_Static::array_info($key) : $data;
                if ($data) {
                    echo '<div class="abp_text_center_color_white_bg_warning_padding_xxs_fs_label">' . esc_html($data) . '</div>';
                }
            }
            public static function layout_info_xs($key, $data = ''): void {
                $data = empty($data) ? ABPTB_Static::array_info($key) : $data;
                if ($data) {
                    echo '<div class="abp_bg_info_padding_xxs _all_center _color_5">' . esc_html($data) . '</div>';
                }
            }
            public static function on(): bool|string {
                ob_start();
                ?>
                <strong class="abp_color_theme"> <?php esc_html_e('ON', 'abp-transport-booking'); ?></strong>
                <?php
                return ob_get_clean();
            }
            public static function off(): bool|string {
                ob_start();
                ?>
                <strong class="abp_color_theme"> <?php esc_html_e('OFF', 'abp-transport-booking'); ?></strong>
                <?php
                return ob_get_clean();
            }
            //==============Input field===============//
            public static function quantity_input($input_info = []): void {
                $name = $input_info['name'] ?? '';
                $price = floatval($input_info['price'] ?? 0);
                $min_qty = absint($input_info['min_qty'] ?? 1);
                $max_qty = absint($input_info['max_qty'] ?? 1);
                $class = $input_info['class'] ?? '';
                $collapse_id = $input_info['collapse_id'] ?? '';
                if ($name && $max_qty >= $min_qty) {
                    if (!empty($collapse_id)) {
                        ?> <div data-collapse="<?php echo esc_attr($collapse_id); ?>"><?php
                    }
                    ?>
                    <div class="_group_content qty_input">
                        <div class="qty_decrease _ag_content"><?php ABPTB_Static::svg('minus_1'); ?></div>
                        <label>
                            <input type="text" class="_form_control  validation_number <?php echo esc_attr($class); ?>"
                                   name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($min_qty); ?>"
                                   data-price="<?php echo esc_attr($price); ?>" data-min="<?php echo esc_attr($min_qty); ?>" data-max="<?php echo esc_attr($max_qty); ?>"
                            />
                        </label>
                        <div class="qty_increase _ag_content"><?php ABPTB_Static::svg('plus'); ?></div>
                    </div>
                    <?php
                    if (!empty($collapse_id)) {
                        ?></div><?php
                    }
                }
            }
            public static function switch_checkbox($name, $value = ''): void {
                $value = in_array($value, ['on', 'off', ''], true) ? $value : '';
                ?>
                <div class="<?php echo esc_attr($value === 'on' ? 'abp_active' : ''); ?>" data-switch data-collapse-target="#<?php echo esc_attr($name); ?>">
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
                <?php
            }
            public static function input_title($label = '', $required = ''): void {
                if ($label) { ?>
                    <span class="_mar_b_xxs">
							<?php echo esc_html($label); ?>
                        <?php if ($required) { ?>
                            <sup class="_color_required">*</sup>
                        <?php } ?>
						</span>
                    <?php
                }
            }
            public static function input_date($name, $date = '', $label = '', $required = ''): void {
                $date_format = ABPTB_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                $hidden_date = $date ? gmdate('Y-m-d', strtotime($date)) : '';
                $visible_date = $date ? date_i18n($date_format, strtotime($date)) : '';
                ?>
                <label class="_input_item">
                    <?php self::input_title($label, $required); ?>
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($hidden_date); ?>" <?php echo esc_attr($required); ?>/>
                    <input type="text" name="" class="_form_control abp_datepicker" value="<?php echo esc_attr($visible_date); ?>" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                    <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transport-booking'); ?>"></span>
                </label>
                <?php
            }
            public static function input_time($name, $time = '', $label = '', $required = ''): void {
                ?>
                <label class="_input_item">
                    <?php self::input_title($label, $required); ?>
                    <input type="time" class="_form_control" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($time); ?>" <?php echo esc_attr($required); ?>/>
                    <span class="fas fa-times time_close_icon" title="<?php esc_attr_e('Clear Time', 'abp-transport-booking'); ?>"></span>
                </label>
                <?php
            }
            public static function textarea($name, $value = '', $label = '', $required = ''): void {
                ?>
                <label class="abptb_textarea _input_item">
                    <?php self::input_title($label, $required); ?>
                    <textarea name="<?php echo esc_attr($name); ?>" rows="3" class="_form_control" placeholder="<?php echo esc_attr($label); ?>" title="<?php echo esc_attr($label); ?>"  <?php echo esc_attr($required); ?>><?php echo esc_textarea($value); ?></textarea>
                </label>
                <?php
            }
            public static function select($name, $value = '', $label = '', $required = '', $options = []): void {
                if (is_array($options) && sizeof($options) > 0) {
                    ?>
                    <label class="_input_item">
                        <?php self::input_title($label, $required); ?>
                        <select name="<?php echo esc_attr($name); ?>" class="_form_control" title="<?php echo esc_attr($label); ?>" <?php echo esc_attr($required); ?>>
                            <option value="" disabled selected><?php echo esc_html__('Please Select', 'abp-transport-booking') . ' ' . esc_html($label); ?></option>
                            <?php foreach ($options as $option) { ?>
                                <option value="<?php echo esc_attr($option); ?>" <?php echo esc_attr($option == $value ? 'selected' : ''); ?>><?php echo esc_html($option); ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <?php
                }
            }
            public static function checkbox($name, $value = '', $label = '', $required = '', $options = []): void {
                if (is_array($options) && sizeof($options) > 0) {
                    ?>
                    <div class=" _input_item">
                        <span class="_fs_label"> <?php self::input_title($label, $required); ?></span>
                        <div class="custom_checkbox">
                            <input type="hidden" class="_form_control" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"/>
                            <?php foreach ($options as $option) { ?>
                                <div class="checkbox_item">
                                    <button type="button" class="_btn_white_xs <?php echo esc_attr($option == $value ? 'abp_active' : ''); ?>" data-checked="<?php echo esc_attr($option); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr($option == $value ? 'far fa-check-square' : 'far fa-square'); ?>"></span><?php echo esc_html($option); ?>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
            }
            public static function radio($name, $value = '', $label = '', $required = '', $options = []): void {
                if (is_array($options) && sizeof($options) > 0) {
                    ?>
                    <div class=" _input_item">
                        <span class="_fs_label"> <?php self::input_title($label, $required); ?></span>
                        <div class="custom_radio">
                            <input type="hidden" class="_form_control" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"/>
                            <?php foreach ($options as $option) { ?>
                                <div class="radio_item">
                                    <button type="button" class="_btn_white_xs <?php echo esc_attr($option == $value ? 'abp_active' : ''); ?>" data-radio="<?php echo esc_attr($option); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr($option == $value ? 'far fa-check-circle' : 'far fa-circle'); ?>"></span><?php echo esc_html($option); ?>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
            }
            //=============Add  Image / Icon================//
            public static function image($post_id = '', $image_id = '', $url = '', $class = ''): void {
                $image_url = ($post_id > 0 || $image_id) ? ABPTB_Function::get_image_url($post_id, $image_id) : $url;
                $post_url = $post_id > 0 ? get_the_permalink($post_id) : '';
                $image_url = $image_url ?: ABPTB_BLANK_IMG_URL;
                if ($image_url) {
                    ?>
                    <div class="abp_image <?php echo esc_attr($class); ?>" data-image-href="<?php echo esc_url($image_url); ?>" <?php if (!empty($post_url)) { ?> data-href="<?php echo esc_url($post_url); ?>" <?php } ?> >
                        <img class="_img_control" src="#" alt="<?php echo esc_attr(max($post_id, $image_id)); ?>">
                    </div>
                    <?php
                }
            }
            public static function image_icon($icon_image, $class = ''): void {
                if (!empty($icon_image)) {
                    $icon = $image = $emoji = '';
                    if (is_numeric($icon_image)) {
                        $image = $icon_image;
                    } elseif (preg_match('/\s/', $icon_image)) {
                        $icon = $icon_image;
                    } else {
                        $emoji = $icon_image;
                    }
                    if ($image) {
                        ABPTB_Layout::image('', $image);
                    } else { ?>
                        <span class="<?php echo esc_attr($icon . ' ' . $class); ?>"><?php echo esc_html($emoji); ?></span>
                    <?php }
                }
            }
            public function load_icon($name, $value = ''): void {
                $button_active_class = $value ? '_d_none' : '';
                $icon = $emoji = '';
                if (preg_match('/\s/', $value)) {
                    $icon = $value;
                } else {
                    $emoji = $value;
                }
                $icon_class = ($icon || $emoji) ? '' : '_d_none';
                ?>
                <div class="icon_image_selection_area">
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"/>
                    <div class="icon_item  <?php echo esc_attr($icon_class); ?>">
                        <div class="_all_center"><span class="<?php echo esc_attr($icon); ?>" data-add-icon><?php echo esc_html($emoji); ?></span></div>
                        <span class="fas fa-times icon_close icon_delete" title="<?php esc_html_e('Remove Icon', 'abp-transport-booking'); ?>"></span>
                    </div>
                    <div class="image_icon_select_area <?php echo esc_attr($button_active_class); ?>">
                        <button class="_btn_info_xs icon_add" type="button" data-target-popup="#abptb_popup_icon"><span class="fas fa-icons _fs_h6"></span></button>
                    </div>
                </div>
                <?php
            }
            public function image_selection($name, $image_id = '', $target = ''): void {
                ?>
                <div class="image_selection">
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($image_id); ?>" data-target="<?php echo esc_attr($target); ?>"/>
                    <div class="image_item <?php echo esc_attr(empty($image_id) ? '_d_none' : ''); ?>" data-image-id="<?php echo esc_attr($image_id); ?>'">
                        <span class="image_remove" onclick="abptb_image_remove(this)">❌</span>
                        <img class="_img_control" src="<?php echo esc_url(wp_get_attachment_image_url($image_id, 'medium')); ?>" alt="<?php echo esc_attr($image_id); ?>"/>
                    </div>
                    <button type="button" class="_btn_light_active_xs <?php echo esc_attr($image_id ? '_d_none' : ''); ?>" onclick="abptb_image_selection(this)">
                        <span class="fas fa-image _mar_r_xs"></span><?php esc_html_e('Select Image', 'abp-transport-booking'); ?>
                    </button>
                </div>
                <?php
            }
            public function add_image_multi($name, $images): void {
                $images = is_array($images) ? ABPTB_Function::array_to_string($images) : $images;
                ?>
                <div class="multiple_image_area">
                    <input type="hidden" class="multiple_image_ids" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($images); ?>"/>
                    <div class="multiple_image">
                        <?php
                            $all_images = explode(',', $images);
                            if ($images && sizeof($all_images) > 0) {
                                foreach ($all_images as $image) {
                                    $img_url = ABPTB_Function::get_image_url('', $image, 'medium') ?: ABPTB_BLANK_IMG_URL;
                                    ?>
                                    <div class="multiple_image_item" data-image-id="<?php echo esc_attr($image); ?>">
                                        <span class="fas fa-times _circle_icon_xs remove_image_multi"></span>
                                        <img class="_img_control" src="<?php echo esc_attr($img_url); ?>" alt="<?php echo esc_attr($image); ?>"/>
                                    </div>
                                    <?php
                                }
                            }
                        ?>
                    </div>
                    <?php ABPTB_Layout::button_add(__('Add  Image', 'abp-transport-booking'), 'add_image_multi _mar_t_xs'); ?>
                </div>
                <?php
            }
            public function selection_icon_image($name, $value = ''): void {
                $icon = $image = $emoji = '';
                if (is_numeric($value)) {
                    $image = $value;
                } elseif (preg_match('/\s/', $value)) {
                    $icon = $value;
                } else {
                    $emoji = $value;
                }
                $icon_class = ($icon || $emoji) ? '' : '_d_none';
                $image_class = $image ? '' : '_d_none';
                $button_active_class = ($icon || $image || $emoji) ? '_d_none' : '';
                ?>
                <div class="icon_image_selection_area _fd_column">
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"/>
                    <div class="icon_item <?php echo esc_attr($icon_class); ?>">
                        <div class="_all_center"><span class="<?php echo esc_attr($icon); ?>" data-add-icon><?php echo esc_html($emoji); ?></span></div>
                        <span class="fas fa-times icon_close icon_delete" title="<?php esc_html_e('Remove Icon', 'abp-transport-booking'); ?>"></span>
                    </div>
                    <div class="image_item <?php echo esc_attr($image_class); ?>">
                        <img class="_img_control" src="<?php echo esc_url(ABPTB_Function::get_image_url('', $image, 'medium')); ?>" alt="image">
                        <span class="fas fa-times icon_close image_delete" title="<?php esc_html_e('Remove Image', 'abp-transport-booking'); ?>"></span>
                    </div>
                    <div class="image_icon_select_area <?php echo esc_attr($button_active_class); ?>">
                        <div class="_group_content_f_equal_w_full">
                            <button class="_btn_light_info_xs image_select" type="button"><span class="fas fa-image _fs_h6"></span></button>
                            <button class="_btn_light_info_xs icon_add" type="button" data-target-popup="#abptb_popup_icon"><span class="fas fa-icons _fs_h6"></span></button>
                        </div>
                    </div>
                </div>
                <?php
            }
            //=============static array================//
            public static function ticket_type($key = '') {
                $types = [
                    'sp' => __('Seat Plan', 'abp-transport-booking'),
                    'ticket' => __('Ticket', 'abp-transport-booking'),
                ];
                return !empty($key) ? ($types[$key] ?? '') : $types;
            }
            public static function status_text($status): string {
                if (!is_string($status) && !is_int($status)) {
                    return '';
                }
                $status_array = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [];
                return is_array($status_array) ? ($status_array[$status] ?? '') : '';
            }
            public static function week_day(): array {
                return [
                    'monday' => __('Monday', 'abp-transport-booking'),
                    'tuesday' => __('Tuesday', 'abp-transport-booking'),
                    'wednesday' => __('Wednesday', 'abp-transport-booking'),
                    'thursday' => __('Thursday', 'abp-transport-booking'),
                    'friday' => __('Friday', 'abp-transport-booking'),
                    'saturday' => __('Saturday', 'abp-transport-booking'),
                    'sunday' => __('Sunday', 'abp-transport-booking'),
                ];
            }
            public static function date_option_rules(): array {
                $rules = [
                    'weekend' => __('Weekend', 'abp-transport-booking'),
                    'specific_off_dates' => __('Specific Off Dates', 'abp-transport-booking'),
                    'special_on_dates' => __('Special On Dates', 'abp-transport-booking'),
                    'off_date_range' => __('Off Dates Range', 'abp-transport-booking'),
                ];
                return apply_filters('abptb_filter_date_rule', $rules);
            }
            public static function array_date_format(): array {
                $current_date = current_time('Y-m-d');
                return [
                    'yy-mm-dd' => $current_date,
                    'yy/mm/dd' => date_i18n('Y/m/d', strtotime($current_date)),
                    'yy-dd-mm' => date_i18n('Y-d-m', strtotime($current_date)),
                    'yy/dd/mm' => date_i18n('Y/d/m', strtotime($current_date)),
                    'dd-mm-yy' => date_i18n('d-m-Y', strtotime($current_date)),
                    'dd/mm/yy' => date_i18n('d/m/Y', strtotime($current_date)),
                    'mm-dd-yy' => date_i18n('m-d-Y', strtotime($current_date)),
                    'mm/dd/yy' => date_i18n('m/d/Y', strtotime($current_date)),
                    'd M , yy' => date_i18n('j M , Y', strtotime($current_date)),
                    'D d M , yy' => date_i18n('D j M , Y', strtotime($current_date)),
                    'M d , yy' => date_i18n('M  j, Y', strtotime($current_date)),
                    'D M d , yy' => date_i18n('D M  j, Y', strtotime($current_date)),
                ];
            }
            //=============================//
            public static function title($post_infos = []): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (!empty($post_id) && $post_id > 0) {
                    $display_sku = $post_infos['display_sku'] ?? ABPTB_Function::get_post_info($post_id, 'display_sku', 'off');
                    $post_sku = $post_infos['post_sku'] ?? ABPTB_Function::get_post_info($post_id, 'post_sku');
                    if (ABPTB_Function::on_off('post_icon')) {
                        ABPTB_Layout::image_icon(($post_infos['post_icon'] ?? ABPTB_Function::get_post_info($post_id, 'post_icon')));
                    }
                    echo esc_html(get_the_title($post_id)); ?>
                    <?php if (!empty($post_sku) && $display_sku == 'on' && ABPTB_Function::on_off('sku')) { ?>
                        <small class="abp_color_gray">&nbsp;(<?php echo esc_html($post_sku); ?>)</small>
                    <?php }
                }
            }
            public static function sub_title($post_infos = [], $class = 'sub_title'): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (ABPTB_Function::on_off('sub_title') && $post_id > 0) {
                    $value = $post_infos['sub_title'] ?? ABPTB_Function::get_post_info($post_id, 'sub_title');
                    if (!empty($value)) { ?>
                        <p class="abp <?php echo esc_attr($class); ?>">
                            <?php echo esc_html($value); ?>
                        </p>
                        <?php
                    }
                }
            }
            public static function capacity($post_infos = [], $class = 'publish', $list = false): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (ABPTB_Function::on_off('display_capacity') && $post_id > 0) {
                    $display = $post_infos['display_capacity'] ?? ABPTB_Function::get_post_info($post_id, 'display_capacity', 'on');
                    $capacity = $post_infos['capacity'] ?? ABPTB_Function::get_total_qty($post_id, $post_infos);
                    if (!empty($capacity) && $display === 'on') {
                        if ($list) {
                            ?>
                            <div class="<?php echo esc_attr($class); ?> abp_gap_xxs">
                                <?php ABPTB_Static::svg('user_group_2');
                                    echo esc_html(__('Capacity : ', 'abp-transport-booking') . ' ' . $capacity . ' ' . __('Passengers   ', 'abp-transport-booking')); ?>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="abp_tag <?php echo esc_attr($class); ?>">
                                <?php ABPTB_Static::svg('user_group_2');
                                    echo esc_html($capacity . ' ' . __('Passengers   ', 'abp-transport-booking')); ?>
                            </div>
                            <?php
                        }
                    }
                }
            }
            public static function category($post_infos = [], $class = '', $list = false): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (ABPTB_Function::on_off('category') && $post_id > 0) {
                    $display = $post_infos['display_category'] ?? ABPTB_Function::get_post_info($post_id, 'display_category', 'on');
                    $value = $post_infos['abptb_category'] ?? ABPTB_Function::get_post_info($post_id, 'abptb_category');
                    if (!empty($value) && $display === 'on') {
                        $value = ABPTB_Function::category_value($value);
                        if ($list) {
                            ?>
                            <div class="<?php echo esc_attr($class); ?> abp_gap_xxs">
                                <?php ABPTB_Static::svg('category');
                                    echo esc_html(ABPTB_Function::category_label() . ' : ' . $value); ?>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="abp_tag <?php echo esc_attr($class); ?>" title="<?php echo esc_attr(ABPTB_Function::category_label() . ' : ' . $value); ?>">
                                <?php ABPTB_Static::svg('category');
                                    echo esc_html($value); ?>
                            </div>
                            <?php
                        }
                    }
                }
            }
            public static function brand($post_infos = [], $class = ''): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (ABPTB_Function::on_off('brand') && $post_id > 0) {
                    $display = $post_infos['display_brand'] ?? ABPTB_Function::get_post_info($post_id, 'display_brand', 'off');
                    $value = $post_infos['abptb_brand'] ?? ABPTB_Function::get_post_info($post_id, 'abptb_brand');
                    if (!empty($value) && $display === 'on') {
                        $value = ABPTB_Function::brand_value($value); ?>
                        <span class="abp_tag <?php echo esc_attr($class); ?>" title="<?php echo esc_attr(ABPTB_Function::brand_label() . ' : ' . $value); ?>">
                                    <?php ABPTB_Static::svg('brand_2');
                                        echo esc_html($value); ?>
                                </span>
                        <?php
                    }
                }
            }
            public static function organizer($post_infos = [], $class = ''): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (ABPTB_Function::on_off('organizer') && $post_id > 0) {
                    $display = $post_infos['display_organizer'] ?? ABPTB_Function::get_post_info($post_id, 'display_organizer', 'off');
                    $value = $post_infos['abptb_organizer'] ?? ABPTB_Function::get_post_info($post_id, 'abptb_organizer');
                    if (!empty($value) && $display === 'on') {
                        $value = ABPTB_Function::organizer_value($value);
                        ?>
                        <div class="abp_tag <?php echo esc_attr($class); ?>" title="<?php echo esc_attr(ABPTB_Function::organizer_label() . ' : ' . $value); ?>">
                            <?php ABPTB_Static::svg('organizer_2');
                                echo esc_html($value); ?>
                        </div>
                        <?php
                    }
                }
            }
            public static function description($post_infos = [], $class = ''): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (ABPTB_Function::on_off('post_des') && $post_id > 0) {
                    $value = $post_infos['post_description'] ?? ABPTB_Function::get_post_info($post_id, 'post_description');
                    if (!empty($value)) { ?>
                        <div class="_padding_xs <?php echo esc_attr($class); ?>">
                            <?php self::load_more($value); ?>
                        </div>
                        <?php
                    }
                }
            }
            public static function route_direction($post_infos = [], $bp_dp = '', $return = false, $details = true): void {
                if (!empty($post_infos)) {
                    $post_id = absint($post_infos['post_id'] ?? 0);
                    if (!empty($bp_dp)) {
                        [$bp, $dp] = array_map('intval', explode('_', $bp_dp));
                    } else {
                        $key = $return ? 'return_routing_infos' : 'routing_infos';
                        $route = $post_infos[$key] ?? ABPTB_Function::get_post_info($post_id, $key, []);
                        $bp = array_key_first($route);
                        $dp = array_key_last($route);
                    }
                    if (!empty($bp) && !empty($dp)) {
                        ?>
                        <span class="fas fa-route _mar_r_xxs _color_theme"></span>
                        <span class="_color_active_mar_r_xxs"><?php echo esc_html(ABPTB_Function::location_value($bp)); ?></span>
                        <span class="fas fa-arrow-right _color_green_pale_mar_r_xxs"></span>
                        <span class="_color_burnt_orange_mar_r_xxs"> <?php echo esc_html(ABPTB_Function::location_value($dp)); ?></span>
                        <?php if ($details) {
                            if (empty($route)) {
                                $key = ABPTB_Function::return_check($post_infos, $bp_dp) ? 'return_routing_infos' : 'routing_infos';
                                $route = $post_infos[$key] ?? ABPTB_Function::get_post_info($post_id, $key, []);
                            }
                            $start_time = $route[$bp]['time'] ?? 0;
                            $end_time = $route[$dp]['time'] ?? 0;
                            $keys = array_keys($route);
                            $difference = abs(array_search($dp, $keys) - array_search($bp, $keys)) + 1;
                            ?>
                            <span class="_color_theme_mar_r_xxs">(<?php echo esc_html(ABPTB_Function::time_difference($start_time, $end_time)); ?>)</span>
                            <?php
                            if ($difference > 1) {
                                echo esc_html(' - ' . $difference . ' ' . __('Stops', 'abp-transport-booking'));
                            }
                        }
                    }
                }
            }
            public static function journey_date($all_dates, $date = ''): void {
                //echo '<pre>';print_r($all_dates);					echo '</pre>';
                if (sizeof($all_dates) > 0) {
                    $date_format = ABPTB_Function::date_format_php();
                    $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                    $date = $date ?: current($all_dates);
                    //if ( sizeof( $all_dates ) > 10 ) {
                    $hidden_date = !empty($date) ? gmdate('Y-m-d', strtotime($date)) : '';
                    $visible_date = !empty($date) ? date_i18n($date_format, strtotime($date)) : '';
                    ?>
                    <label>
                        <span class="_gap_xxs"><?php ABPTB_Static::svg('date_1'); ?><?php esc_html_e('Journey Date', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></span>
                        <input type="hidden" name="journey_date" value="<?php echo esc_attr($hidden_date); ?>" required/>
                        <input id="journey_date" type="text" value="<?php echo esc_attr($visible_date); ?>" class="_form_control" placeholder="<?php echo esc_attr($now); ?>" data-alert="<?php esc_attr_e('Please Select Journey Date', 'abp-transport-booking'); ?>" readonly required/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transport-booking'); ?>"></span>
                    </label>
                    <?php
                    do_action('abptb_load_date_picker', '#journey_date', $all_dates);
                    //}
                } else {
                    ABPTB_Layout::layout_warning_info_xs('not_date');
                }
            }
            public static function return_date($all_dates, $date = ''): void {
                $date_format = ABPTB_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                if (sizeof($all_dates) > 0) {
                    //if ( sizeof( $all_dates ) > 10 ) {
                    $hidden_date = !empty($date) ? gmdate('Y-m-d', strtotime($date)) : '';
                    $visible_date = !empty($date) ? date_i18n($date_format, strtotime($date)) : '';
                    ?>
                    <label>
                        <span class="_gap_xxs"><?php ABPTB_Static::svg('date_2'); ?><?php esc_html_e('Return Date (optional)', 'abp-transport-booking'); ?></span>
                        <input type="hidden" name="return_date" value="<?php echo esc_attr($hidden_date); ?>"/>
                        <input id="return_date" type="text" value="<?php echo esc_attr($visible_date); ?>" class="_form_control" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transport-booking'); ?>"></span>
                    </label>
                    <?php
                    do_action('abptb_load_date_picker', '#return_date', $all_dates);
                    //}
                } else {
                    ?><span></span><?php
                    ABPTB_Layout::layout_warning_info_xs('not_date');
                }
            }
            public static function item_feature($features = ''): void {
                if (ABPTB_Function::on_off('feature')) {
                    if (!is_string($features) || $features === '') {
                        return;
                    }
                    $feature_ids = explode(',', $features);
                    $abptb_feature = defined('ABPTB_Feature') ? ABPTB_Feature : [];
                    if (empty($feature_ids) || !is_array($abptb_feature)) {
                        return;
                    }
                    ?>
                    <div class="item_spec load_more">
                        <div class="_f_wrap_gap_xxs">
                            <?php
                                foreach ($feature_ids as $fec_id) {
                                    $feature = $abptb_feature[$fec_id] ?? null;
                                    if (!is_array($feature)) {
                                        continue;
                                    }
                                    $label = $feature['label'] ?? '';
                                    $value = $feature['value'] ?? '';
                                    $icon = $feature['icon'] ?? '';
                                    if ($label || $value) {
                                        echo '<span class="abp_tag" title="' . esc_attr($label) . '">';
                                        ABPTB_Layout::image_icon($icon);
                                        $output = implode(' - ', array_filter([$label, $value]));
                                        echo esc_html($output);
                                        echo '</span>';
                                    }
                                } ?>
                        </div>
                        <span class="load_more_action" data-less="<?php esc_html_e('....Less ', 'abp-transport-booking'); ?>" data-more="<?php esc_html_e('.... More', 'abp-transport-booking'); ?>"><?php esc_html_e('.... More', 'abp-transport-booking'); ?></span>
                    </div>
                    <?php
                }
            }
            public static function item_select($ticket_info, $key, $price = 0, $prefix = ''): void {
                //echo '<pre>';print_r($bp_dp);echo '</pre>';
                if (!is_array($ticket_info) || empty($key)) {
                    return;
                }
                $total_qty = intval($ticket_info['qty'] ?? 0);
                $max_qty = $ticket_info['max_qty'] ?? '';
                $available_qty = $ticket_info['available'] ?? $total_qty;
                $max_qty = ($max_qty !== '' && intval($max_qty) <= $available_qty) ? intval($max_qty) : $available_qty;
                $min_qty = intval($ticket_info['min_qty'] ?? 1);
                //echo '<pre>';print_r($min_qty);echo '</pre>';
                if ($max_qty >= $min_qty) {
                    $collapse_id = '#ticket_' . $key . $prefix;
                    ?>
                    <div class="_divider_xxs"></div>
                    <div class="item_select">
                        <div class="custom_checkbox">
                            <input type="hidden" name="<?php echo esc_attr($prefix); ?>item_check[]" value="" data-id="<?php echo esc_attr($collapse_id); ?>"/>
                            <div class="checkbox_item" data-checked="<?php echo esc_attr($key); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                <h3 class="abp"><span data-icon class="far fa-square"></span></h3>
                                <?php echo esc_html__('Select ', 'abp-transport-booking') . ' ' . esc_html(ABPTB_Function::ticket_name($key)); ?>
                            </div>
                        </div>
                        <?php
                            if ($max_qty > $min_qty) {
                                $input_info = [
                                    'name' => $prefix . 'item_qty[]',
                                    'price' => $price,
                                    'available' => $available_qty,
                                    'min_qty' => $min_qty,
                                    'max_qty' => $max_qty,
                                    'collapse_id' => $collapse_id,
                                ];
                                ABPTB_Layout::quantity_input($input_info);
                            } else { ?>
                                <input type="hidden" name="<?php echo esc_attr($prefix . 'item_qty[]'); ?>" value="<?php echo esc_attr($min_qty); ?>" data-price="<?php echo esc_attr($price); ?>" data-min="<?php echo esc_attr($min_qty); ?>"/>
                            <?php } ?>
                    </div>
                <?php } else { ?>
                    <span class="trash abp_tag"><?php esc_html_e('Sold Out !', 'abp-transport-booking'); ?></span>
                    <?php
                }
            }
            public static function list_check_in($id, $status, $checkin): void {
                $booked_status = ABPTB_Function::booking_status();
                $booked_status = $booked_status ? explode(',', $booked_status) : [];
                if (!empty($id) && in_array($status, $booked_status)) {
                    $user_id = get_current_user_id();
                    if ($checkin > 0) { ?>
                        <button class="_btn_light_success_xxs" type="button" title="<?php echo esc_attr(__('Already Checked By', 'abp-transport-booking') . ' : ' . get_user_by('id', $user_id)->display_name); ?>">
                            <?php ABPTB_Static::svg('check_1'); ?><?php esc_html_e('Checked', 'abp-transport-booking'); ?>
                        </button>
                    <?php } else { ?>
                        <button class="_btn_light_warning_xxs check_in" data-id="<?php echo esc_attr($id); ?>" title="<?php esc_attr_e('Click to Check In', 'abp-transport-booking'); ?>" type="button">
                            <?php esc_html_e('Not Checked !', 'abp-transport-booking'); ?>
                        </button>
                    <?php }
                }
            }
            public static function create_client_form($form, $name, $prefix = ''): void {
                if (!is_array($form)) {
                    return;
                }
                $name = is_string($name) ? $prefix . $name . '[]' : '';
                $type = $form['type'] ?? '';
                $required = (($form['required'] ?? '') === 'on') ? 'data-req' : '';
                $label = $form['label'] ?? '';
                $d_value = $form['d_value'] ?? '';
                if ($type === 'text' || $type === 'number' || $type === 'email') {
                    $validation_class = match ($type) {
                        'text' => 'validation_name',
                        'number' => 'validation_number',
                        default => '',
                    };
                    ?>
                    <label class="_input_item">
                        <?php ABPTB_Layout::input_title($label, $required); ?>
                        <input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($d_value); ?>" class="_form_control <?php echo esc_attr($validation_class); ?>" placeholder="<?php echo esc_attr($label); ?>" title="<?php echo esc_attr($label); ?>" <?php echo esc_attr($required); ?> />
                    </label>
                    <?php
                    return;
                }
                if ($type === 'date') {
                    ABPTB_Layout::input_date($name, $d_value, $label, $required);
                    return;
                }
                if ($type === 'textarea') {
                    ABPTB_Layout::textarea($name, $d_value, $label, $required);
                    return;
                }
                // Options bound input layouts (Select, Checkbox, Radio)
                if ($type === 'select' || $type === 'checkbox' || $type === 'radio') {
                    $options_str = $form['option'] ?? '';
                    $options = ($options_str !== '') ? explode(',', $options_str) : [];
                    match ($type) {
                        'select' => ABPTB_Layout::select($name, $d_value, $label, $required, $options),
                        'checkbox' => ABPTB_Layout::checkbox($name, $d_value, $label, $required, $options),
                        'radio' => ABPTB_Layout::radio($name, $d_value, $label, $required, $options),
                        default => null,
                    };
                }
            }
            public static function sp($id = '', $sp_info = [], $post_infos = [], $form_data = []): void {
                if (empty($sp_info)) {
                    if (!empty($id)) {
                        $row = ABPTB_Query::get_sp($id);
                        if (!empty($row)) {
                            $sp_info = current($row);
                        }
                    }
                }
                if (!empty($sp_info)) {
                    $others = json_decode($sp_info['others'] ?? '', true) ?: [];
                    $cell_width = $others['width'] ?? 50;
                    $cell_height = $others['height'] ?? 50;
                    $gap = $others['gap'] ?? 0;
                    $bg_image = $others['bg_image'] ?? '';
                    $img_url = !empty($bg_image) && $bg_image > 0 ? ABPTB_Function::get_image_url('', $bg_image) : '';
                    $bg_color = $others['bg_color'] ?? '#fff';
                    $radius = $others['radius'] ?? 0;
                    $layout = json_decode($sp_info['layout_data'] ?? '', true) ?: [];
                    //echo '<pre>';                print_r($ticket_types);                echo '</pre>';
                    $cols = intval($others['column'] ?? 10);
                    $meta_info = json_decode($sp_info['seat_info'] ?? '', true) ?: [];
                    $hidden_cells = [];
                    foreach ($layout as $index => $cell) {
                        $c_span = intval($cell['width_ratio'] ?? 1);
                        $r_span = intval($cell['height_ratio'] ?? 1);
                        if ($c_span > 1 || $r_span > 1) {
                            for ($r = 0; $r < $r_span; $r++) {
                                for ($c = 0; $c < $c_span; $c++) {
                                    if ($r === 0 && $c === 0)
                                        continue;
                                    $target_idx = $index + ($r * $cols) + $c;
                                    $hidden_cells[$target_idx] = true;
                                }
                            }
                        }
                    }
                    $bp_dp = $form_data['bp_dp'] ?? '';
                    $post_id = $form_data['post_id'] ?? '';
                    $start_time = $form_data['start_time'] ?? '';
                    $price_info = [];
                    $sold_seat = [];
                    $reserved_seat = [];
                    $sale = false;
                    if (!empty($bp_dp) && !empty($start_time) && !empty($post_id) && $post_id > 0) {
                        foreach ($meta_info as $tic_id => $ticket_num) {
                            $price_info[$tic_id] = ABPTB_Function::get_price($post_infos, $bp_dp, $tic_id, $start_time);
                        }
                        $form_data['sp_id'] = $id;
                        $sold_seat = ABPTB_Query::get_sold_seat($form_data);
                        $sale = true;
                    }
                    //echo '<pre>';                    print_r($form_data);                    echo '</pre>';
                    // echo '<pre>';                    print_r($sold_seat);                    echo '</pre>';
                    ?>
                    <div class="sp_canvas _section_15_xs_mar_auto" style="grid-template-columns: repeat(<?php echo esc_attr($cols); ?>, 1fr); background-image: url('<?php echo esc_url($img_url); ?>'); background-color: <?php echo esc_attr($bg_color); ?>;gap: <?php echo esc_attr($gap); ?>px;">
                        <?php foreach ($layout as $index => $cell) {
                            if (isset($hidden_cells[$index]))
                                continue;
                            $type_id = $cell['id'] ?? '';
                            $name = $cell['name'] ?? '';
                            $c_span = intval($cell['width_ratio'] ?? 1);
                            $r_span = intval($cell['height_ratio'] ?? 1);
                            $rotate = intval($cell['rotate'] ?? 0);
                            $fs = $cell['fs'] ?? 12;
                            $is_seat = ($cell['type'] === 'seat');
                            $seat_type_class = $sale && $is_seat ? 'available' : '';
                            $seat_type_class = $sale && in_array($name, $sold_seat) ? 'sold' : $seat_type_class;
                            $title = $sale && in_array($name, $sold_seat) ? __('Sold !', 'abp-transport-booking') : '';
                            $class = $is_seat ? "sp_cell " . $seat_type_class : "sp_decor";
                            $tag_attr = $sale && $is_seat && $seat_type_class == 'available' ? 'data-name="' . $name . '" data-id="' . $type_id . '" data-price="' . ($price_info[$type_id] ?? 0) . '" data-label="' . ABPTB_Function::ticket_name($type_id) . '"' : '';
                            if (!empty($title)) {
                                $tag_attr = $tag_attr . '  title="' . $title . '"';
                            }
                            $color = $is_seat ? ABPTB_Function::ticket_color($type_id) : ABPTB_Function::decor_color($type_id);
                            $icon_image = $is_seat ? ABPTB_Function::ticket_icon($type_id) : ABPTB_Function::decor_icon($type_id);
                            $width = $cell_width * $c_span;
                            $height = $cell_height * $r_span;
                            if ($gap > 0) {
                                $width = $c_span > 1 ? $width + ($c_span - 1) * $gap : $width;
                                $height = $r_span > 1 ? $height + ($r_span - 1) * $gap : $height;
                            }
                            $style = "color: {$color}; grid-column: span {$c_span}; grid-row: span {$r_span}; width:{$width}px;height:{$height}px; border:1px solid  {$color};font-size:{$fs}px;border-radius:{$radius}px;";
                            $image = '';
                            if (!empty($icon_image)) {
                                if (is_numeric($icon_image)) {
                                    $image = ABPTB_Function::get_image_url('', $icon_image);
                                }
                            }
                            ?>
                            <div class="<?php echo esc_attr($class); ?>" style="<?php echo esc_attr($style); ?>" <?php echo wp_kses_post($tag_attr); ?>>
                                <div class="cell_content <?php echo esc_attr($rotate ? "rotate-{$rotate}" : ""); ?>" style="background-image: url('<?php echo esc_url($image); ?>');">
                                    <?php ABPTB_Layout::image_icon($icon_image); ?>
                                    <span class="cell_label"><?php echo esc_html($name); ?></span>
                                </div>
                                <?php if ($sale && $is_seat) {
                                    $price = $price_info[$type_id] ?? 0;
                                    ?>
                                    <div class="sp_tooltip" style="color: <?php echo esc_url($color); ?>">
                                        <?php echo esc_html(ABPTB_Function::ticket_name($type_id) . ' : ');
                                            echo $price > 0 ? wp_kses_post(wc_price($price)) : esc_html__('Free', 'abp-transport-booking'); ?>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                } else {
                    ABPTB_Layout::layout_warning_info('no_sp_config');
                }
            }
            //=============================//
            public static function ticket_info($booking_item): void {
                $ticket_infos = json_decode($booking_item['ticket_info'] ?? '', true) ?: [];
                $post_id = $booking_item['post_id'] ?? '';
                if (!empty($ticket_infos) && is_array($ticket_infos) && !empty($post_id) && $post_id > 0) { ?>
                    <ul class=" abp">
                        <?php foreach ($ticket_infos as $ticket_info) {
                            if (!empty($ticket_info) && sizeof($ticket_info) > 0) {
                                $qty = $ticket_info['qty'] ?? 1;
                                $price = $ticket_info['price'] ?? 0;
                                $total = $price * $qty;
                                $name = ABPTB_Function::ticket_label($ticket_info, $booking_item);
                                ?>
                                <li>
                                    <strong><?php echo esc_html($name); ?></strong>
                                    <?php echo esc_html(' X ' . $qty . ' = ') . ' ' . (!empty($price) && $total > 0 ? wp_kses_post(wc_price($total)) : esc_html__('FREE', 'abp-transport-booking')); ?>
                                </li>
                                <?php
                            }
                        } ?>
                    </ul>
                <?php }
            }
            public static function additional_info($additional_infos): void {
                if (!empty($additional_infos) && is_array($additional_infos)) { ?>
                    <ul class=" abp">
                        <?php foreach ($additional_infos as $ex_info) {
                            if (!empty($ex_info) && sizeof($ex_info) > 0) {
                                $name = $ex_info['name'] ?? '';
                                $qty = $ex_info['qty'] ?? 1;
                                $price = $ex_info['price'] ?? 0;
                                $total = $price * $qty;
                                $returnable = $ex_info['returnable'] ?? 'no';
                                if (!empty($name)) { ?>
                                    <li>
                                        <strong><?php echo esc_html($name); ?></strong>
                                        <?php echo esc_html(' X ' . $qty . ' = ') . ' ' . (!empty($price) && $total > 0 ? wp_kses_post(wc_price($total)) : esc_html__('FREE', 'abp-transport-booking')); ?>
                                        <?php
                                            if ($returnable == 'yes') {
                                                ?> <span class="_color_required"> - <?php esc_html_e('Returnable', 'abp-transport-booking'); ?></span><?php
                                            } ?>
                                    </li>
                                    <?php
                                }
                            }
                        } ?>
                    </ul>
                <?php }
            }
            public static function client_info($passenger_infos): void {
                if (!empty($passenger_infos) && is_array($passenger_infos)) { ?>
                    <ul class=" abp">
                        <?php foreach ($passenger_infos as $pas_form) {
                            if (!empty($pas_form) && sizeof($pas_form) > 0) {
                                foreach ($pas_form as $info) {
                                    $label = $info['label'] ?? '';
                                    $value = $info['value'] ?? '';
                                    if (!empty($label) && !empty($value)) { ?>
                                        <li>
                                            <strong><?php echo esc_html($label); ?></strong> : <?php echo esc_html($value); ?>
                                        </li>
                                        <?php
                                    }
                                }
                            }
                        } ?>
                    </ul>
                <?php }
            }
            public static function billing_info($booking_item): void {
                if (!empty($booking_item)) {
                    $billing_name = $booking_item['billing_name'] ?? '';
                    $billing_email = $booking_item['billing_email'] ?? '';
                    $billing_phone = $booking_item['billing_phone'] ?? '';
                    $billing_address = $booking_item['billing_address'] ?? '';
                    ?>
                    <ul class=" abp">
                        <?php if (!empty($billing_name)) { ?>
                            <li><strong><?php esc_html_e('Name :', 'abp-transport-booking'); ?></strong>&nbsp;<?php echo esc_html($billing_name); ?></li>
                        <?php } ?>
                        <?php if (!empty($billing_email)) { ?>
                            <li><strong><?php esc_html_e('E-Mail :', 'abp-transport-booking'); ?></strong>&nbsp;<?php echo esc_html($billing_email); ?></li>
                        <?php } ?>
                        <?php if (!empty($billing_phone)) { ?>
                            <li><strong><?php esc_html_e('Phone :', 'abp-transport-booking'); ?></strong>&nbsp;<?php echo esc_html($billing_phone); ?></li>
                        <?php } ?>
                        <?php if (!empty($billing_address)) { ?>
                            <li><strong><?php esc_html_e('Address :', 'abp-transport-booking'); ?></strong>&nbsp;<?php echo esc_html($billing_address); ?></li>
                        <?php } ?>
                    </ul>
                    <?php
                }
            }
            //=============================//
            public static function filter_post_list(): void {
                $label = ABPTB_Function::label();
                $brand_icon = ABPTB_Function::icon();
                // echo '<pre>';print_r($configuration);echo '</pre>';
                ?>
                <div class="_input_item abp_dropdown post_selection">
                    <label>
                        <span class="_gap_xxs"><?php ABPTB_Layout::image_icon($brand_icon); ?><?php echo esc_html($label); ?></span>
                        <input type="hidden" name="post_id" value=""/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php echo esc_attr($label); ?>" value=""/>
                    </label>
                    <?php if (sizeof(ABPTB_ids) > 0) { ?>
                        <div class="dropdown_list">
                            <ul class="abp ">
                                <?php foreach (ABPTB_ids as $all_post_id) {
                                    $sku = ABPTB_Function::get_post_info($all_post_id, 'post_sku');
                                    $category = ABPTB_Function::get_post_info($all_post_id, 'category');
                                    $category = !empty($category) ? get_term($category)->name : '';
                                    $title = get_the_title($all_post_id);
                                    ?>
                                    <li class="_gap_xxs" data-value="<?php echo esc_attr($all_post_id); ?>" data-text="<?php echo esc_attr($title); ?>">
                                        <?php if (ABPTB_Function::on_off('post_icon')) {
                                            ABPTB_Layout::image_icon(ABPTB_Function::get_post_info($all_post_id, 'post_icon'));
                                        } ?>
                                        <span class="_fs_label"><?php echo esc_html($title); ?></span>
                                        <?php if (!empty($category) && ABPTB_Function::on_off('category')) { ?>
                                            <sub class="abp_color_gray"> - <?php echo esc_html($category); ?></sub>
                                        <?php } ?>
                                        <?php if (!empty($sku) && ABPTB_Function::on_off('sku')) { ?>
                                            <sub class="abp_color_info"> - <?php echo esc_html($sku); ?></sub>
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            public static function filter_booking_date(): void {
                $date_format = ABPTB_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                ?>
                <div class="_input_item">
                    <label>
                        <span class="_gap_xs"><?php ABPTB_Static::svg('date_1'); ?><?php esc_html_e('Journey Date', 'abp-transport-booking') ?></span>
                        <input type="hidden" name="_start_time" value=""/>
                        <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transport-booking'); ?>"></span>
                    </label>
                </div>
                <?php
            }
            public static function filter_booking_date_between(): void {
                $date_format = ABPTB_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                ?>
                <div class="_g_input_input_item_fd_column" data-collapse="#view_more_filter_option">
                    <label><span class="_gap_xs"><?php ABPTB_Static::svg('date_2'); ?><?php esc_html_e('Journey Date Between', 'abp-transport-booking'); ?></span></label>
                    <div class="_f_equal">
                        <label>
                            <input type="hidden" name="start_time_from" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transport-booking'); ?>"></span>
                        </label>
                        <label>
                            <input type="hidden" name="start_time_to" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transport-booking'); ?>"></span>
                        </label>
                    </div>
                </div>
                <?php
            }
            public static function filter_bp(): void {
                ?>
                <div class="abptb_bp _input_item abp_dropdown">
                    <label>
                        <span class="_gap_xxs"><i class="fas fa-map-marker-alt"></i><?php esc_html_e('From', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></span>
                        <input type="hidden" name="_bp" value=""/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php esc_attr_e('Select Boarding Point', 'abp-transport-booking'); ?>" value=""/>
                    </label>
                    <div class="dropdown_list">
                        <ul class="abp ">
                        </ul>
                    </div>
                </div>
                <?php
            }
            public static function filter_dp(): void {
                ?>
                <div class="abptb_dp _input_item abp_dropdown">
                    <label>
                        <span class="_gap_xxs"><i class="fas fa-map-marker-alt"></i><?php esc_html_e('To', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></span>
                        <input type="hidden" name="_dp" value=""/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php esc_attr_e('Select Dropping Point', 'abp-transport-booking'); ?>" value=""/>
                    </label>
                    <div class="dropdown_list">
                        <ul class="abp ">
                        </ul>
                    </div>
                </div>
                <?php
            }
            public static function filter_order_date(): void {
                $date_format = ABPTB_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                ?>
                <div class="_input_item">
                    <label>
                        <span class="_gap_xs">🗓️ <?php esc_html_e('Order Date', 'abp-transport-booking') ?></span>
                        <input type="hidden" name="order_date" value=""/>
                        <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transport-booking'); ?>"></span>
                    </label>
                </div>
                <?php
            }
            public static function filter_order_date_between(): void {
                $date_format = ABPTB_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                ?>
                <div class="_g_input_input_item_fd_column" data-collapse="#view_more_filter_option">
                    <label class="_mar_b_xxs"><span class="_gap_xs">⏰ <?php esc_html_e('Order Date Between', 'abp-transport-booking'); ?></span></label>
                    <div class="_f_equal">
                        <label>
                            <input type="hidden" name="order_date_from" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transport-booking'); ?>"></span>
                        </label>
                        <label>
                            <input type="hidden" name="order_date_to" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transport-booking'); ?>"></span>
                        </label>
                    </div>
                </div>
                <?php
            }
            public static function filter_user_id(): void {
                $all_users = get_users(array(
                    'fields' => array('ID', 'display_name'),
                ));
                ?>
                <div class="_input_item abp_dropdown " data-collapse="#view_more_filter_option">
                    <label>
                        <span class="_gap_xs">👨‍💼  <?php esc_html_e('User Name', 'abp-transport-booking'); ?></span>
                        <input type="hidden" name="user_id" value=""/>
                        <input type="text" class="_form_control_w_full" placeholder="<?php esc_attr_e('User Name', 'abp-transport-booking'); ?>" value=""/>
                    </label>
                    <?php if (!empty($all_users)) { ?>
                        <div class="dropdown_list">
                            <ul class="abp ">
                                <?php foreach ($all_users as $user) { ?>
                                    <li data-value="<?php echo esc_attr($user->ID); ?>" data-text="<?php echo esc_attr($user->display_name); ?>">
                                        <span class="_fs_label"><?php echo esc_html($user->display_name); ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            public static function filter_order_id(): void {
                ?>
                <div class="_input_item ">
                    <label>
                        <span class="_gap_xs">📦 <?php esc_html_e('Order ID', 'abp-transport-booking'); ?></span>
                        <input type="number" class="_form_control_w_full validation_number" name="order_id" placeholder="<?php esc_attr_e('Order ID', 'abp-transport-booking'); ?>" value=""/>
                    </label>
                </div>
                <?php
            }
            public static function filter_bill_name(): void {
                ?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label>
                        <span class="_gap_xs">👤 <?php esc_html_e('Billing Name', 'abp-transport-booking'); ?></span>
                        <input type="text" class="_form_control_w_full " name="billing_name" placeholder="<?php esc_attr_e('Billing Name', 'abp-transport-booking'); ?>" value=""/>
                    </label>
                </div>
                <?php
            }
            public static function filter_bill_email(): void {
                ?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label>
                        <span class="_gap_xs">✉️ <?php esc_html_e('Billing Email', 'abp-transport-booking'); ?></span>
                        <input type="email" class="_form_control_w_full " name="billing_email" placeholder="<?php esc_attr_e('Billing Email', 'abp-transport-booking'); ?>" value=""/>
                    </label>
                </div>
                <?php
            }
            public static function filter_bill_phone(): void {
                ?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label>
                        <span class="_gap_xs">☎️ <?php esc_html_e('Billing phone', 'abp-transport-booking'); ?></span>
                        <input type="text" class="_form_control_w_full " name="billing_phone" placeholder="<?php esc_attr_e('Billing phone', 'abp-transport-booking'); ?>" value=""/>
                    </label>
                </div>
                <?php
            }
        }
        new ABPTB_Layout();
    }