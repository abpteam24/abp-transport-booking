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
                                <label class="_abp_all_center">
                                    <input type="hidden" class="abp_icon_search_hidden" name="abp_icon_search" value=""/>
                                    <input type="text" class="_form_control_text_center validation_name abptb_allow abp_icon_search" name="" placeholder="<?php esc_attr_e('Search  icon', 'abp-transport-booking'); ?>" value=""/>
                                </label>
                                <div class="dropdown_list"></div>
                            </div>
                            <span class="popup_close"><i class="fas fa-times"></i></span>
                        </div>
                        <div class="popup_body">
                            <h4 class="_abp_text_center item_icon_title"></h4>
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
                    <?php ABPTB_Layout::icon_svg('plus');
                        echo esc_html($button_text); ?>
                </button>
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
            public static function button_edit($class_edit = 'edit_hook'): void {
                ?>
                <button class="_btn_light_navy_blue_xs <?php echo esc_attr($class_edit); ?>" type="button" title="<?php esc_attr_e('Edit This Item', 'abp-transport-booking'); ?>">
                    <?php ABPTB_Layout::icon_svg('edit'); ?>
                </button>
                <?php
            }
            public static function button_delete($class = 'delete_hook'): void {
                ?>
                <button class="_btn_light_danger_xxs <?php echo esc_attr($class); ?>" type="button" title="<?php esc_attr_e('Delete This Item', 'abp-transport-booking'); ?>"><?php ABPTB_Layout::icon_svg('close_1'); ?></button>
                <?php
            }
            public static function button_sort(): void {
                ?>
                <div class="_btn_light_info_xxs sortable_handle" type="button" title="<?php esc_attr_e('Move This Item', 'abp-transport-booking'); ?>">
                    <?php ABPTB_Layout::icon_svg('drag'); ?>
                </div>
                <?php
            }
            public static function button_global_save($action, $text = '', $class = ''): void {
                if (!empty($action)) {
                    $class = $class ?: '_btn_theme_xs';
                    $text = $text ?: __('save', 'abp-transport-booking');
                    ?>
                    <button class="<?php echo esc_attr($class); ?>" type="button" onclick="abptb_save_global('<?php echo esc_attr($action); ?>',this)">
                        <?php ABPTB_Layout::icon_svg('save');
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
                        <?php ABPTB_Layout::icon_svg('plus');
                            echo esc_html($text); ?>
                    </button>
                    <?php
                }
            }
            //=============================//
            public static function session_notice($key = '', $type = 'success'): void {
                if (!empty($key) && isset($_SESSION[$key])) {
                    ?>
                    <div class="toast_notice" data-type="<?php echo esc_attr($type) ?>">
                        <?php echo esc_html(sanitize_text_field(wp_unslash($_SESSION[$key]))); ?>
                    </div>
                    <?php
                    unset($_SESSION[$key]);
                }
            }
            public static function info_text($key = '', $data = ''): void {
                $data = empty($data) ? self::array_info($key) : $data;
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
                $data = self::array_info($key);
                if ($data) {
                    echo '<div class="_section_bg_warning_mar_zero"><h4 class="_abp_text_center_color_white">' . esc_html($data) . '</h4></div>';
                }
            }
            public static function layout_warning_info_xs($key, $data = ''): void {
                $data = empty($data) ? self::array_info($key) : $data;
                if ($data) {
                    echo '<div class="_abp_text_center_color_white_bg_warning_padding_xxs_fs_label">' . esc_html($data) . '</div>';
                }
            }
            public static function layout_info_xs($key, $data = ''): void {
                $data = empty($data) ? self::array_info($key) : $data;
                if ($data) {
                    echo '<div class="_abp_bg_info_padding_xxs _all_center _color_5">' . esc_html($data) . '</div>';
                }
            }
            public static function on(): bool|string {
                ob_start();
                ?>
                <strong class="_abp_color_theme"> <?php esc_html_e('ON', 'abp-transport-booking'); ?></strong>
                <?php
                return ob_get_clean();
            }
            public static function off(): bool|string {
                ob_start();
                ?>
                <strong class="_abp_color_theme"> <?php esc_html_e('OFF', 'abp-transport-booking'); ?></strong>
                <?php
                return ob_get_clean();
            }
            //==============Input field===============//
            public static function input_dropdown($infos, $icon = ''): void {
                if (is_array($infos) && sizeof($infos) > 0) {
                    asort($infos);
                    ?>
                    <div class="dropdown_list">
                        <ul class="_abp">
                            <?php foreach ($infos as $info) { ?>
                                <li data-value="<?php echo esc_attr($info); ?>"><span class="<?php echo esc_attr($icon); ?> _mar_r_xxs"></span><span data-text><?php echo esc_html($info); ?></span></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php
                }
            }
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
                        <div class="qty_decrease _ag_content"><?php self::icon_svg('minus_1'); ?></div>
                        <label>
                            <input type="text" class="_form_control  validation_number <?php echo esc_attr($class); ?>"
                                   name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($min_qty); ?>"
                                   data-price="<?php echo esc_attr($price); ?>" data-min="<?php echo esc_attr($min_qty); ?>" data-max="<?php echo esc_attr($max_qty); ?>"
                            />
                        </label>
                        <div class="qty_increase _ag_content"><?php self::icon_svg('plus'); ?></div>
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
                return apply_filters('abptb_filter_rent_rule', $rules);
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
                        <small class="_abp_color_gray">&nbsp;(<?php echo esc_html($post_sku); ?>)</small>
                    <?php }
                }
            }
            public static function sub_title($post_infos = [], $class = 'sub_title'): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (ABPTB_Function::on_off('sub_title') && $post_id > 0) {
                    $value = $post_infos['sub_title'] ?? ABPTB_Function::get_post_info($post_id, 'sub_title');
                    if (!empty($value)) { ?>
                        <p class="_abp <?php echo esc_attr($class); ?>">
                            <?php echo esc_html($value); ?>
                        </p>
                        <?php
                    }
                }
            }
            public static function capacity($post_infos = [], $class = 'publish'): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (ABPTB_Function::on_off('display_capacity') && $post_id > 0) {
                    $display = $post_infos['display_capacity'] ?? ABPTB_Function::get_post_info($post_id, 'display_capacity', 'on');
                    $capacity = $post_infos['capacity'] ?? ABPTB_Function::get_total_qty($post_id, $post_infos);
                    if (!empty($capacity) && $display === 'on') { ?>
                        <div class="abp_tag <?php echo esc_attr($class); ?>">
                            <?php ABPTB_Layout::icon_svg('user_group_2');
                                echo esc_html($capacity . ' ' . __('Passengers   ', 'abp-transport-booking')); ?>
                        </div>
                        <?php
                    }
                }
            }
            public static function category($post_infos = [], $class = ''): void {
                $post_id = absint($post_infos['post_id'] ?? 0);
                if (ABPTB_Function::on_off('category') && $post_id > 0) {
                    $display = $post_infos['display_category'] ?? ABPTB_Function::get_post_info($post_id, 'display_category', 'on');
                    $value = $post_infos['abptb_category'] ?? ABPTB_Function::get_post_info($post_id, 'abptb_category');
                    if (!empty($value) && $display === 'on') {
                        $value = ABPTB_Function::category_value($value); ?>
                        <div class="abp_tag <?php echo esc_attr($class); ?>" title="<?php echo esc_attr(ABPTB_Function::category_label() . ' : ' . $value); ?>">
                            <?php ABPTB_Layout::icon_svg('category');
                                echo esc_html($value); ?>
                        </div>
                        <?php
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
                                    <?php ABPTB_Layout::icon_svg('brand_2');
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
                            <?php ABPTB_Layout::icon_svg('organizer_2');
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
                        <span class="_gap_xxs"><?php ABPTB_Layout::icon_svg('date_1'); ?><?php esc_html_e('Journey Date', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></span>
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
                        <span class="_gap_xxs"><?php ABPTB_Layout::icon_svg('date_2'); ?><?php esc_html_e('Return Date (optional)', 'abp-transport-booking'); ?></span>
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
                        <div class="_f_wrap_gap_xs">
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
                                        echo '<span class="abp_tag spec_badge" title="' . esc_attr($label) . '">';
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
            public static function item_select($post_infos, $ticket_info, $key, $price = 0, $prefix = ''): void {
                //echo '<pre>';print_r($bp_dp);echo '</pre>';
                if (!is_array($ticket_info) || empty($key)) {
                    return;
                }
                $total_qty = intval($ticket_info['qty'] ?? 0);
                $reserve_qty = intval($ticket_info['reserve'] ?? 0);
                $max_qty = $ticket_info['max_qty'] ?? '';
                $sold_qty = intval(ABPTB_Query::get_sold_qty($post_infos));
                $available_qty = $total_qty - $reserve_qty - $sold_qty;
                $max_qty = ($max_qty !== '' && intval($max_qty) <= $available_qty) ? intval($max_qty) : $available_qty;
                $min_qty = intval($ticket_info['min_qty'] ?? 1);
                //echo '<pre>';print_r($min_qty);echo '</pre>';
                if ($max_qty >= $min_qty) {
                    $collapse_id = '#ticket_' . $key;
                    ?>
                    <div class="_divider_xxs"></div>
                    <div class="item_select">
                        <div class="custom_checkbox">
                            <input type="hidden" name="<?php echo esc_attr($prefix); ?>item_check[]" value="" data-id="<?php echo esc_attr($collapse_id); ?>"/>
                            <div class="checkbox_item" data-checked="<?php echo esc_attr($key); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                <h3 class="_abp"><span data-icon class="far fa-square"></span></h3>
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
            public static function create_client_form($form, $name, $prefix = ''): void {
                if (!is_array($form)) {
                    return;
                }
                $name = is_string($name) ? $prefix . $name . '[]' : '';
                $type = $form['type'] ?? '';
                $required = (($form['required'] ?? '') === 'on') ? 'required' : '';
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
                        <input type="<?php echo esc_attr($type); ?>"
                               name="<?php echo esc_attr($name); ?>"
                               value="<?php echo esc_attr($d_value); ?>"
                               class="_form_control <?php echo esc_attr($validation_class); ?>"
                               placeholder="<?php echo esc_attr($label); ?>"
                               title="<?php echo esc_attr($label); ?>"
                            <?php echo esc_attr($required); ?> />
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
            public static function sp($id = '', $sp_info = []): void {
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
                    //$ticket_types= ABPTB_Function::get_option('abptb_ticket');
                    //echo '<pre>';                print_r(ABPTB_Function::get_option('abptb_decor'));                echo '</pre>';
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
                    ?>
                    <div class="sp_canvas sp_section_15_xs" style="grid-template-columns: repeat(<?php echo esc_attr($cols); ?>, 1fr); background-image: url('<?php echo esc_url($img_url); ?>'); background-color: <?php echo esc_attr($bg_color); ?>;gap: <?php echo esc_attr($gap); ?>px;">
                        <?php foreach ($layout as $index => $cell) {
                            if (isset($hidden_cells[$index]))
                                continue;
                            $type_id = $cell['id'] ?? '';
                            $c_span = intval($cell['width_ratio'] ?? 1);
                            $r_span = intval($cell['height_ratio'] ?? 1);
                            $rotate = intval($cell['rotate'] ?? 0);
                            $fs = $cell['fs'] ?? 12;
                            $is_seat = ($cell['type'] === 'seat');
                            $class = $is_seat ? "sp_cell available" : "sp_decor";
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
                            <div class="<?php echo esc_attr($class); ?>" style="<?php echo esc_attr($style); ?>" data-name="<?php echo esc_attr($cell['name'] ?? ''); ?>">
                                <div class="cell_content <?php echo esc_attr($rotate ? "rotate-{$rotate}" : ""); ?>" style="background-image: url('<?php echo esc_url($image); ?>');">
                                    <?php ABPTB_Layout::image_icon($icon_image); ?>
                                    <span class="cell_label"><?php echo esc_html($cell['name'] ?? ''); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                } else {
                    ABPTB_Layout::layout_warning_info('no_sp_config');
                }
            }
            //=============================//
            public static function ticket_info($ticket_infos): void {
                if (!empty($ticket_infos) && is_array($ticket_infos)) { ?>
                    <ul class=" _abp">
                        <?php foreach ($ticket_infos as $tic_id => $ticket_info) {
                            if (!empty($ticket_info) && sizeof($ticket_info) > 0) {
                                $qty = $ticket_info['qty'] ?? 1;
                                $price = $ticket_info['price'] ?? ''; ?>
                                <li>
                                    <strong><?php echo esc_html(ABPTB_Function::ticket_name($tic_id)); ?></strong>
                                    <?php echo esc_html(' X ' . $qty . ' = ') . ' ' . (!empty($price) && $price > 0 ? wp_kses_post(wc_price($price)) : esc_html__('FREE', 'abp-transport-booking')); ?>
                                </li>
                                <?php
                            }
                        } ?>
                    </ul>
                <?php }
            }
            public static function additional_info($additional_infos): void {
                if (!empty($additional_infos) && is_array($additional_infos)) { ?>
                    <ul class=" _abp">
                        <?php foreach ($additional_infos as $ex_info) {
                            if (!empty($ex_info) && sizeof($ex_info) > 0) {
                                $name = $ex_info['name'] ?? '';
                                $qty = $ex_info['qty'] ?? 1;
                                $price = $ex_info['price'] ?? '';
                                $returnable = $ex_info['returnable'] ?? 'no';
                                if (!empty($name)) { ?>
                                    <li>
                                        <strong><?php echo esc_html($name); ?></strong>
                                        <?php echo esc_html(' X ' . $qty . ' = ') . ' ' . (!empty($price) && $price > 0 ? wp_kses_post(wc_price($price)) : esc_html__('FREE', 'abp-transport-booking')); ?>
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
                    <ul class=" _abp">
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
            public static function billing_info($booking_list): void {
                if (!empty($booking_list)) {
                    $billing_name = $booking_list['billing_name'] ?? '';
                    $billing_email = $booking_list['billing_email'] ?? '';
                    $billing_phone = $booking_list['billing_phone'] ?? '';
                    $billing_address = $booking_list['billing_address'] ?? '';
                    ?>
                    <ul class=" _abp">
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
                            <ul class="_abp ">
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
                                            <sub class="_abp_color_gray"> - <?php echo esc_html($category); ?></sub>
                                        <?php } ?>
                                        <?php if (!empty($sku) && ABPTB_Function::on_off('sku')) { ?>
                                            <sub class="_abp_color_info"> - <?php echo esc_html($sku); ?></sub>
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
                        <span class="_gap_xs"><?php ABPTB_Layout::icon_svg('date_1'); ?><?php esc_html_e('Journey Date', 'abp-transport-booking') ?></span>
                        <input type="hidden" name="start_time" value=""/>
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
                <div class="_g_input_input_item_fd_column">
                    <label><span class="_gap_xs"><?php ABPTB_Layout::icon_svg('date_2'); ?><?php esc_html_e('Journey Date Between', 'abp-transport-booking'); ?></span></label>
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
                        <span class="_gap_xxs"><i class="fas fa-map-marker-alt"></i><?php esc_html_e('From', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></span>
                        <input type="hidden" name="_bp" value=""/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php esc_attr_e('Select Boarding Point', 'abp-wc-transport-manager'); ?>" value=""/>
                    </label>
                    <div class="dropdown_list">
                        <ul class="_abp ">
                        </ul>
                    </div>
                </div>
                <?php
            }
            public static function filter_dp(): void {
                ?>
                <div class="abptb_dp _input_item abp_dropdown">
                    <label>
                        <span class="_gap_xxs"><i class="fas fa-map-marker-alt"></i><?php esc_html_e('To', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></span>
                        <input type="hidden" name="_dp" value=""/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php esc_attr_e('Select Dropping Point', 'abp-wc-transport-manager'); ?>" value=""/>
                    </label>
                    <div class="dropdown_list">
                        <ul class="_abp ">
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
                            <ul class="_abp ">
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
                <div class="_input_item " data-collapse="#view_more_filter_option">
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
            //=============================//
            public static function array_info($key) {
                $current_date = current_time('Y-m-d H:i');
                $des = array(
                    'general_config' => __('Note: Configure the general settings for this transport here. If you do not want to use any specific feature, you can enable or disable it from Main Configuration → On/Off Sections. Disabling a feature will remove it from the entire site.', 'abp-transport-booking'),
                    'sale_continue' => __('Note: This switch indicate Transport Ticket sale close/continue . You can  sale close/continue  by this switch. By default sale will be  continue', 'abp-transport-booking'),
                    'abptb_template' => __('Note: Here You can change your details page template.', 'abp-transport-booking'),
                    'post_sku' => __('Note: Here you can add an SKU for this post. You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transport-booking'),
                    'post_icon' => __('Note: Set a custom icon or emoji for this post. The selected icon/emoji will be displayed alongside the post title wherever the title appears across the website, helping it stand out and improve visual recognition.', 'abp-transport-booking'),
                    'sub_title' => __('Note: Add a Sub-title to enable the Post sub-tile. Leave this blank if you dont want to show any Sub-title information for this Post.', 'abp-transport-booking'),
                    'post_description' => __('Note: Add short description about this Transport . Leave this blank if you dont want to show any  description for this Transport.', 'abp-transport-booking'),
                    'display_capacity' => __('Note : Enable this option to display the capacity for this transport on the frontend. This setting only works when the global Transport Capacity Display option is enabled.', 'abp-transport-booking'),
                    'display_organizer' => __('Note : This switch indicate Transport Organizer . You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transport-booking'),
                    'display_brand' => __('Note : This switch indicate Transport Brand name . You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transport-booking'),
                    'display_category' => __('Note : This switch indicate Transport Category . You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transport-booking'),
                    'related_item' => __('Note: Select related items to display on the details page. Leave this option empty or disabled if you do not want to show related items.', 'abp-transport-booking'),
                    'post_feature' => __('Note: If you want to add feature for this Transport, you can add Here. These feature will be show with this Transport . You may leave this section empty if you do not want to show frontend. ', 'abp-transport-booking'),
                    'display_slider' => __('Note: If you want to add an image gallery for this transport, you can upload images below.  You may leave this section empty if you do not want to show images. ', 'abp-transport-booking'),
                    //=============================//
                    'route_config' => __('Note: Configure the transport route by selecting the required stops and their types. You can also add new stops while configuring the route. Boarding stops allow passengers to board only, Dropping stops allow passengers to get off only, and Both stops support both boarding and dropping. The first stop must always be Boarding, the last stop must always be Dropping, and the first stop time must be 0 minutes. All remaining stop times represent the travel time in minutes from the first stop and are applied according to your Transport Time Configuration. Enable Multiple Pickup/Drop-off Points to allow passengers to select from multiple pickup and drop-off locations. The available options are based on the configured route stops: Boarding stops become Pickup Points, Dropping stops become Drop-off Points, and Both stops are available for both. If the return journey uses the same transport, enable Same Transport Return to automatically use the same transport configuration for the return trip. ', 'abp-transport-booking'),
                    'seat_type' => __('Note: Please select your Transport seat type . Default is Seat Plan', 'abp-wc-transport-manager'),
                    'ticket_type' => __('Note: You have disabled the Seat Plan System from the Global On/Off Settings, so your ticket types will function as regular tickets only.If you want to use a seat plan, enable Seat Plan System from the Global On/Off Settings. Once enabled, you can turn the Seat Plan feature on or off for each transport individually, allowing you to use either a seat plan or regular tickets as needed.', 'abp-wc-transport-manager'),
                    'single_ticket_type' => __('Note: If the Global Multiple Ticket System is disabled, you cannot use multiple ticket types for any transport.To enable this feature, turn on Multiple Ticket System from the Global On/Off Settings. Once enabled, you can configure multiple ticket types and prices for each transport. You can also choose to use a single ticket for specific transports whenever needed.', 'abp-wc-transport-manager'),
                    'display_ticket_type' => __('Note : This switch indicate Transport ticket type. if your all ticket/seat same type then switch will be off. if you want to multiple type please switch on', 'abp-wc-transport-manager'),
                    'min_qty' => __('Note : Set the minimum quantity customers must select per order. This global setting applies across the entire booking system.', 'abp-wc-transport-manager'),
                    'max_qty' => __('Note : Set the maximum quantity a customer can select per order. This global setting helps control the maximum number of tickets or seats a customer can book in a single order.', 'abp-wc-transport-manager'),
                    //=============================//
                    //=============================//
                    'no_category' => __('No Category Found !', 'abp-transport-booking'),
                    'cat_name' => __('Note: Please enter a category name — the field cannot be empty. ', 'abp-transport-booking'),
                    'cat_slug' => __('Note: Category slug is optional — leave it blank to auto-generate from the name. ', 'abp-transport-booking'),
                    'cat_des' => __('Note: Category description is optional — you can add details to better explain this category. ', 'abp-transport-booking'),
                    //=============================//
                    'no_organizer' => __('No Organizer Found !', 'abp-transport-booking'),
                    'org_name' => __('Note: Please enter a Organizer name — the field cannot be empty. ', 'abp-transport-booking'),
                    'org_slug' => __('Note: Organizer slug is optional — leave it blank to auto-generate from the name. ', 'abp-transport-booking'),
                    'org_des' => __('Note: Organizer description is optional — you can add details to better explain this Organizer. ', 'abp-transport-booking'),
                    //=============================//
                    'no_location' => __('No Location Found ! ', 'abp-transport-booking'),
                    'loc_name' => __('Note: Please enter a Location name — the field cannot be empty. ', 'abp-transport-booking'),
                    'loc_slug' => __('Note: Location slug is optional — leave it blank to auto-generate from the name. ', 'abp-transport-booking'),
                    'loc_des' => __('Note: Location Address is optional — you can add details to better explain this Location Full  Address. ', 'abp-transport-booking'),
                    'display_pd' => __('You can add multiple pickup/drop-off  points for a single location. For each pickup/drop-off  point, set the travel time relative to the main location. Use a negative value (in minutes) if the pickup/drop-off  point is before the main location, or a positive value (in minutes) if it is after the main location. For example, use -15 for 15 minutes before the main location, or 20 for 20 minutes after it.', 'abp-transport-booking'),
                    //=============================//
                    'no_brand' => __('No Brand Found ! ', 'abp-transport-booking'),
                    'brand_name' => __('Note: Please enter a Brand name — the field cannot be empty. ', 'abp-transport-booking'),
                    'brand_slug' => __('Note: Brand slug is optional — leave it blank to auto-generate from the name. ', 'abp-transport-booking'),
                    'brand_des' => __('Note: Brand description  is optional — you can add details to better explain this Brand. ', 'abp-transport-booking'),
                    //=============================//
                    'no_feature' => __('No Feature Found ! ', 'abp-transport-booking'),
                    'feature_value' => __('Note: Please enter a Feature Value  — the field optional ', 'abp-transport-booking'),
                    'feature_icon' => __('Note: You can add an icon, or emoji for this Feature(optional).', 'abp-transport-booking'),
                    'feature_name' => __('Note: Please enter a Feature Name  — the field cannot be empty.', 'abp-transport-booking'),
                    //=============================//
                    'date_format' => __('Note:  If you want to change the Date  Format, simply choose a different format. The default date is: ', 'abp-transport-booking') . ' ' . date_i18n('D j M , Y', strtotime($current_date)),
                    'time_format' => __('Note : If you want to change the Time Format, simply choose a different format. The default Time Format is: ', 'abp-transport-booking') . ' ' . date_i18n(get_option('time_format'), strtotime($current_date)),
                    'sale_close_before' => __('Note:  Enter the time in minutes to close ticket sales before the transport starts. If not specified, it will default to 0 (e.g. 1 hour equals 60 minutes). ', 'abp-transport-booking'),
                    'advance_date_number' => __('Note: Kindly provide the number of days in advance for booking. By default, the advance booking period is set to 28 days.(optional) ', 'abp-transport-booking'),
                    'active_global_dates' => __('Note: Keep this switch ON to apply the global date settings.Switch it OFF if you want to set special date rules for this transport.Date configuration options will open when turned OFF. ', 'abp-transport-booking'),
                    'date_type' => __('Note: Please Select your Transport operational date type. Default operational date will be Periodic', 'abp-transport-booking'),
                    'specific_dates' => __('Note: Please add your Transport operational Specific Date lists  .', 'abp-transport-booking'),
                    'operation_time' => __('Note: Operation Time is required. If you do not specify any operation time, it will automatically be set to 12:00 AM (00:00). You can add multiple operation times for the same transport within a single day if needed. However, at least one operation time is required.', 'abp-transport-booking'),
                    'periodic_start_date' => __('Note: Please add your Transport Launching Date otherwise it will be start today ', 'abp-transport-booking'),
                    'periodic_end_date' => __('Note: Please add your Transport Terminate  Date otherwise it will be Continuously running periodically', 'abp-transport-booking'),
                    'periodic_after' => __('Note: Please add your periodically after days. if  your Transport operation day everyday this will be one(1).(optional)', 'abp-transport-booking'),
                    'date_rule' => __('Note: Enable this checkbox to configure special on/off date  settings. This option is optional. If you set a date/time in the special “On” date, that date will remain active even if it falls within an “Off” date range or on weekends.', 'abp-transport-booking'),
                    'special_on_dates' => __('Note: If you add any date  in Special On Dates, it will always remain active—even if that date falls within an off date range or on weekends.', 'abp-transport-booking'),
                    'weekend' => __('Note: Please select your weekend.Default all days open(optional)', 'abp-transport-booking'),
                    'day_wise_time' => __('Note:Add Day-wise Time if your transport operates on different schedules throughout the week. You can assign multiple departure times for each day, and only the configured times for the selected day will be available to passengers. ', 'abp-transport-booking'),
                    'specific_off_dates' => __('Note: please add your specific Operation off dates.(optional)', 'abp-transport-booking'),
                    'date_wise_time' => __('Note: Set the transport operation time for specific dates. A date will only be saved if it has at least one operation time. If a date is not saved, the regular day-wise schedule or the default operation time will be applied. You can add multiple operation times for the same date.(optional)', 'abp-transport-booking'),
                    'off_date_range' => __('Note: If you have off days between two dates which can add here.(optional)', 'abp-transport-booking'),
                    //=============================//
                    'qty_reserve_min_max' => __('Note: Set the total stock quantity available for sale. This field is required to save the transport. You can also set reserve, minimum, and maximum quantity limits for customer bookings. Reserve quantity keeps specific items unavailable, minimum quantity defaults to 1, and maximum quantity will follow the available stock if left empty.', 'abp-transport-booking'),
                    //=============================//
                    '_tax_class' => __('Note: If you want to add any new tax class , Please go to WooCommerce ->configuration->Tax Area', 'abp-transport-booking'),
                    'enable_tax_msg' => __('Note: Your Woo-commerce Tax setting already disable. If you want to enable tax please enable woo-commerce tax.', 'abp-transport-booking'),
                    //=============================//
                    'display_additional_services' => __('Note: If you want sale additional product/equipment with this  transport then active this button and add additional service. Additional item not depends on  operation time.', 'abp-transport-booking'),
                    'active_global_additional' => __('Note: Keep this switch ON to apply the global additional settings.Switch it OFF if you want to set special additional rules for this transport.additional configuration options will open when turned OFF. ', 'abp-transport-booking'),
                    //=============================//
                    'attendee_off' => __('Note: Globally, the Attendee Form feature is currently disabled. To add an attendee form for this transport, please enable the feature from the Global Settings, then reload this page.', 'abp-transport-booking'),
                    'client_form_option' => __('Use comma( , ) to separate option.', 'abp-transport-booking'),
                    'display_client_form' => __('Note: If you want to get Client information then active this button and add form/import global form or use global form as a client form', 'abp-transport-booking'),
                    'active_global_form' => __('Note: Keep this switch ON to apply the global Client Form settings.Switch it OFF if you want to set special  Client Form rules for this transport. Client Form configuration options will open when turned OFF. ', 'abp-transport-booking'),
                    'display_single_form' => __('If you want to get single traveller/attendee info for multiple ticket  then active this button .Default is on', 'abp-wc-transport-manager'),
                    //=============================//
                    'display_tc' => __('Use this switch to control whether the Term & Condition is displayed on the frontend. Turn the switch ON to show the Term & Condition, and OFF to hide it. By default, this option is set to ON.', 'abp-transport-booking'),
                    'active_global_tc' => __('Enable this switch to apply the global Term & Condition to this post. If you want to add custom Term & Condition specifically for this post, turn the switch OFF and add your custom Term & Condition below.You can also use the Import button to bring in global Term & Condition, which you can then edit or delete based on your needs.', 'abp-transport-booking'),
                    //=============================//
                    'faq_item' => __('Both the Title and Description fields are required. If either field is left empty, this FAQ item will not be displayed on the frontend.', 'abp-transport-booking'),
                    'display_faq' => __('Use this switch to control whether the FAQ is displayed on the frontend. Turn the switch ON to show the FAQ, and OFF to hide it. By default, this option is set to ON.', 'abp-transport-booking'),
                    'active_global_faq' => __('Enable this switch to apply the global FAQ to this post. If you want to add custom FAQs specifically for this post, turn the switch OFF and add your custom FAQs below.You can also use the Import button to bring in global FAQs, which you can then edit or delete based on your needs.', 'abp-transport-booking'),
                    //=============================//
                    'search_get_wrong_data_info' => __('Somethings went Wrong ! Please Try again', 'abp-transport-booking'),
                    'sale_close_msg' => __('This transport sale close shortly. please try another transport.', 'abp-transport-booking'),
                    'not_date' => __('No Dates Found !', 'abp-transport-booking'),
                    'not_match' => __('No Results Found !', 'abp-transport-booking'),
                    'not_found' => __('Nothing Found !', 'abp-transport-booking'),
                    'no_sp' => __('No Seat Plan Found. Click Add New to create one.', 'abp-transport-booking'),
                    'transport_not_available' => __('The transport is not available for the selected date and time. Please choose a different schedule.', 'abp-transport-booking'),
                    //=============================//
                    'no_ticket_type' => __('No Ticket Type Found ! Please add Ticket Type to use Multiple Ticket Type', 'abp-transport-booking'),
                    'no_ticket_config' => __('No ticket configuration is available for this transport. Please contact the administrator.', 'abp-transport-booking'),
                    'no_sp_config' => __('No Seat Plan configuration is available for this transport. Please contact the administrator.', 'abp-transport-booking'),
                    'ticket_settings' => __('Configure the ticket or seat type with a name, color, prefix, and optional image, icon, or emoji. These settings will be applied automatically to all assigned seats in the Seat Plan and used throughout the booking process for consistent identification.', 'abp-transport-booking'), //=============================//
                    'no_decor_item' => __('No Decor Item Found ! Please add Decor item to use Multiple Decor item', 'abp-transport-booking'),
                    'decor_setting' => __('Note: Choose an image, icon, or emoji to represent this decoration item within the Seat Plan layout. Enter a name to identify the item while designing the layout. Decoration items are used only for creating and organizing the Seat Plan and are not considered bookable seats, so they do not affect pricing, availability, or the booking process. The item name will not be displayed to customers, but you can add custom text, change the font size, or modify individual items directly from the Seat Plan editor by double-clicking on them. You can also choose a background color to make different layout elements easier to identify while designing the seat arrangement. Once configured, the selected settings will automatically be applied wherever this decoration item is used.', 'abp-transport-booking'),
                    //=============================//
                    'must_wc' => __('Transport Booking is entirely dependent on the WooCommerce plugin. Please install and activate the WooCommerce plugin otherwise the plugin will not work. Installing this tool may take some time', 'abp-transport-booking'),
                    //=============================//
                    'abptb_ticket' => __('Here you can create and manage dynamic ticket types that can be used across all transports. Examples include Economy, Business Class, VIP, Sleeper, Cabin, and more. Each ticket type can have its own price, capacity, color, icon, image, and other configurations. If a seat plan is enabled, you can create seats based on the selected ticket type, and every seat will automatically inherit the corresponding ticket settings. Every ticket type also has a unique ID . You can edit or delete ticket types at any time, but removing a ticket type that is already assigned to a transport may affect the existing configuration.', 'abp-transport-booking'),
                    'abptb_decor' => __('Note : Here you can create and manage decorative items for the seat plan layout. These items are used only for designing and organizing the seating arrangement and are not considered actual seats. Therefore, they cannot be booked, reserved, or sold. Examples include doors, exits, driver areas, engines, restrooms, food storage, baggage storage, waiting areas, lobbies, walkways, stairs, tables, partitions, and other decorative elements. If needed, you can also add custom text, icons, images, or emojis to create a more realistic seat layout. Every decoration item has its own unique ID and can be reused across multiple seat plans.', 'abp-transport-booking'),
                    'abptb_sp' => __('Note : Here you can create and manage reusable seat plans that can be assigned to any transport multiple times. Instead of designing the same layout repeatedly, you can create a seat plan once and reuse it whenever needed. The system will automatically calculate the total number of available seats and detect all assigned ticket types from the selected seat plan. If the transport is configured to use a single ticket type, all seats in the selected seat plan will automatically be converted into that ticket type, even if the original layout contains multiple ticket types. You can also use decoration items such as doors, walkways, engines, exits, luggage storage areas, tables, and other custom elements to create a more realistic layout. Every seat plan has a unique ID', 'abp-transport-booking'),
                    'abptb_sp_design' => __('Note : Here you can design and customize the entire seat layout according to your requirements. You can define the overall layout structure by setting the number of rows and columns, adjusting the background image or background color, and configuring the width, height, and spacing of individual cells. Each cell can occupy one or multiple positions, allowing you to create more complex layouts such as walkways, cabins, tables, lounges, storage areas, and other custom sections.You can select a ticket type and simply click on any cell to automatically assign the selected ticket type along with the corresponding seat number. Likewise, you can select a decoration item and place it anywhere in the layout. Both seat items and decoration items support drag-and-drop functionality, allowing you to move, duplicate, or clone them easily.Every cell can have its own custom text, icon, image, emoji, color, and font size. Double-clicking on a cell allows you to modify its content and appearance individually. You can also resize cells by defining custom width and height values.Advanced selection tools are also available to speed up the design process. Use Ctrl + Click to select individual items and Shift + Click to select multiple cells within a range. Once selected, you can apply changes to all selected items simultaneously.This powerful visual editor makes it easy to create simple or highly detailed layouts for buses, trains, ferries, aircraft, theaters, stadiums, conference halls, and many other seating arrangements. ', 'abp-transport-booking'),
                    'abptb_dates' => __('Note: Set a global date configuration for your Transport  that can be reused across all posts, with options to import and customize anytime.', 'abp-transport-booking'),
                    'abptb_additional' => __('Note: Add extra services for products/equipment with your transport—import or set per Post (also usable globally); stock applies per Post, empty quantity = unlimited, empty max qty = no limit, empty/Zero price = free.', 'abp-transport-booking'),
                    'abptb_form' => __('Note: This is a flexibility global form system. Once you design the structure here, it serves as a global form. You can effortlessly import this form into any transport or use this setting at any transport,', 'abp-transport-booking'),
                    'abptb_faq' => __('Note: You can set all transport-related FAQs here and use them globally across all transports. You can also import these FAQs into any individual transport and customize them as needed.', 'abp-transport-booking'),
                    'abptb_tc' => __('Note: You can set all transport-related Term & Condition here and use them globally across all transport. You can also import these Term & Condition into any individual transport and customize them as needed.', 'abp-transport-booking'),
                    'abptb_location' => __('Note: Here, you can add all of your transport stops, which can then be used across any transport post. You can edit or delete them at any time. However, please note that deleting a stop that is already assigned to a transport may affect the existing configuration. If a stop is not currently assigned anywhere, deleting it will not cause any issues.', 'abp-transport-booking'),
                    'abptb_category' => __('Note : Here you can create and manage all transport types or categories, such as Bus, Train, Ferry, Shuttle, Taxi, AC, Non Ac, Sleeper and more. You can assign one  categories to each transport while creating or editing a post. Every category has a unique ID, which can also be used in shortcodes to display specific transport types anywhere on your website. You can edit or delete categories at any time, but removing a category that is already assigned to a transport may affect its existing configuration.', 'abp-transport-booking'),
                    'abptb_organizer' => __('Note: Here you can create and manage transport organizers, operators, or companies. You can assign one organizers to each transport while creating or editing a post. Every organizer has a unique ID that can be used in shortcodes to display transports from a specific organizer anywhere on your website. You can edit or delete organizers at any time, but deleting an organizer that is already assigned to a transport may affect the existing configuration.', 'abp-transport-booking'),
                    'abptb_brand' => __('Note : Here you can create and manage transport brands, manufacturers, or service providers. You can assign one brand to each transport while creating or editing a post. Every brand has a unique ID that can be used in shortcodes to display transports from a specific brand anywhere on your website. You can edit or delete brands at any time, but deleting a brand that is already assigned to a transport may affect the existing configuration.', 'abp-transport-booking'),
                    'abptb_feature' => __('Note : Here you can create and manage transport features that help passengers understand the facilities and services available with each transport. Examples include Wi-Fi, air conditioning, charging ports, refreshments, entertainment systems, restrooms, and more. You can assign one or multiple features to each transport while creating or editing a post. Every feature has a unique ID that can be used in shortcodes to display transports with specific features anywhere on your website. You can edit or delete features at any time, but removing a feature that is already assigned to a transport may affect the existing configuration.', 'abp-transport-booking'),
                    //=============================//
                    'sign_up_msg' => __('Please Login your account to Download/View ticket !', 'abp-transport-booking'),
                    'no_permit_msg' => __('You are not permitted to Download/View this ticket !', 'abp-transport-booking'),
                    'wrong_msg_id' => __('We see, this id are not valid !', 'abp-transport-booking'),
                    'no_order_found' => __('Sorry ! We can not find any Order in your criteria.', 'abp-transport-booking'),
                    //''          => __( '', 'abp-transport-booking' ),
                );
                $des = apply_filters('abptb_info_array_filter', $des);
                return $des[$key] ?? '';
            }
            public static function icon_svg($key): void {
                $des = [
                    'user_group_1' => '<svg  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"  d="M17 20c0-1.657-2.239-3-5-3s-5 1.343-5 3m14-3c0-1.23-1.234-2.287-3-2.75M3 17c0-1.23 1.234-2.287 3-2.75m12-4.014a3 3 0 1 0-4-4.472m-8 4.472a3 3 0 0 1 4-4.472M12 14a3 3 0 1 1 0-6a3 3 0 0 1 0 6Z"/></svg>',
                    'user_group_2' => '<svg  viewBox="0 0 24 24"> <path fill="currentColor" fill-rule="evenodd" d="M12 6a3.5 3.5 0 1 0 0 7a3.5 3.5 0 0 0 0-7m-1.5 8a4 4 0 0 0-4 4c0 1.1.9 2 2 2h7a2 2 0 0 0 2-2a4 4 0 0 0-4-4zm6.8-3.1a5.5 5.5 0 0 0-2.8-6.3c.6-.4 1.3-.6 2-.6a3.5 3.5 0 0 1 .8 6.9m2.2 7.1h.5a2 2 0 0 0 2-2a4 4 0 0 0-4-4h-1.1l-.5.8c1.9 1 3.1 3 3.1 5.2M4 7.5a3.5 3.5 0 0 1 5.5-2.9A5.5 5.5 0 0 0 6.7 11A3.5 3.5 0 0 1 4 7.5M7.1 12H6a4 4 0 0 0-4 4c0 1.1.9 2 2 2h.5a6 6 0 0 1 3-5.2z" clip-rule="evenodd"/></svg>',
                    'plus' => '<svg viewBox="0 0 16 16"><path fill="currentColor" d="M14 7H9V2H7v5H2v2h5v5h2V9h5V7z"/></svg>',
                    'minus_1' => '<svg viewBox="0 0 16 16"><path fill="currentColor" d="M2 7h12v2H2V7z"/></svg>',
                    'minus_2' => '<svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.707 10.295a2.41 2.41 0 0 0 0 3.41l7.588 7.588a2.41 2.41 0 0 0 3.41 0l7.588-7.588a2.41 2.41 0 0 0 0-3.41l-7.588-7.588a2.41 2.41 0 0 0-3.41 0zM8.5 12h7"/></svg>',
                    'minus_3' => '<svg viewBox="0 0 32 32"><path fill="currentColor" d="M16 3C8.832 3 3 8.832 3 16s5.832 13 13 13s13-5.832 13-13S23.168 3 16 3zm0 2c6.087 0 11 4.913 11 11s-4.913 11-11 11S5 22.087 5 16S9.913 5 16 5zm-6 10v2h12v-2H10z"/></svg>',
                    'minus_4' => '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M299 213H0v-42h299v42z"/></svg>',
                    'save' => '<svg  viewBox="0 0 100 100.016"><path fill="#23475F" d="M88.555 0H83v.016a2 2 0 0 1-2 2H19a2 2 0 0 1-2-2V0H4a4 4 0 0 0-4 4v92.016a4 4 0 0 0 4 4h92a4 4 0 0 0 4-4V11.525C100.049 11.436 88.564.071 88.555 0z"/><path fill="#1C3C50" d="M81.04 53.016H18.96a2 2 0 0 0-2 2v45h66.08v-45c0-1.106-.895-2-2-2zm-61.957-10h61.834a2 2 0 0 0 2-2V.555A1.993 1.993 0 0 1 81 2.015H19c-.916 0-1.681-.62-1.917-1.46v40.46a2 2 0 0 0 2 2.001z"/><path fill="#EBF0F1" d="M22 55.985h56a2 2 0 0 1 2 2v37.031a2 2 0 0 1-2 2H22c-1.104 0-2-.396-2-1.5V57.985a2 2 0 0 1 2-2z"/><path fill="#BCC4C8" d="M25 77.016h50v1H25v-1zm0 10h50v1H25v-1z"/><path fill="#1C3C50" d="M7 84.016h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm83 0h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2z"/><path fill="#BCC4C8" d="M37 1.989v36.026a2 2 0 0 0 2 2h39a2 2 0 0 0 2-2V1.989c0 .007-42.982.007-43 0zm37 29.027a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2V10.989a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v20.027z"/><path fill="#FF9D00" d="M78 55.985H22a2 2 0 0 0-2 2v10.031h60V57.985a2 2 0 0 0-2-2z"/></svg>',
                    'edit' => '<svg  viewBox="0 0 16 16"><path fill="currentColor" d="M15.49 7.3h-1.16v6.35H1.67V3.28H8V2H1.67A1.21 1.21 0 0 0 .5 3.28v10.37a1.21 1.21 0 0 0 1.17 1.25h12.66a1.21 1.21 0 0 0 1.17-1.25z"/><path fill="currentColor" d="M10.56 2.87L6.22 7.22l-.44.44l-.08.08l-1.52 3.16a1.08 1.08 0 0 0 1.45 1.45l3.14-1.53l.53-.53l.43-.43l4.34-4.36l.45-.44l.25-.25a2.18 2.18 0 0 0 0-3.08a2.17 2.17 0 0 0-1.53-.63a2.19 2.19 0 0 0-1.54.63l-.7.69l-.45.44zM5.51 11l1.18-2.43l1.25 1.26zm2-3.36l3.9-3.91l1.3 1.31L8.85 9zm5.68-5.31a.91.91 0 0 1 .65.27a.93.93 0 0 1 0 1.31l-.25.24l-1.3-1.3l.25-.25a.88.88 0 0 1 .69-.25z"/></svg>',
                    'drag' => '<svg  viewBox="0 0 16 16"><path fill="currentColor" d="m15.46 7l-3.2-2.19l-.71 1l2.29 1.57H8.62V2.16l1.57 2.29l1-.71L9 .54a1.25 1.25 0 0 0-2 0l-2.22 3.2l1 .71l1.59-2.29v5.22H2.16l2.29-1.57l-.71-1L.54 7a1.25 1.25 0 0 0 0 2l3.2 2.19l.71-1l-2.29-1.57h5.21v5.22l-1.56-2.29l-1 .71L7 15.46a1.25 1.25 0 0 0 2.06 0l2.19-3.2l-1-.71l-1.63 2.29V8.62h5.22l-2.29 1.57l.71 1L15.46 9a1.25 1.25 0 0 0 0-2z"/></svg>',
                    'order' => '<svg  viewBox="0 0 16 16"> <path fill="none" stroke="currentColor" stroke-linejoin="round" d="M5 11.5h4M5 9h6M5 6.5h6m-5.5-4h-2v12h9v-12h-2m-5-1h5l-.625 2h-3.75z"/></svg>',
                    'seat' => '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M4 21v-6h16v6h-2v-4H6v4H4Zm-1-7v-3h3v3H3Zm4 0V3h10v11H7Zm11 0v-3h3v3h-3Z"/></svg>',
                    'globe' => '<svg viewBox="0 0 100 100"><path id="gisGlobe0" fill="currentColor" fill-opacity="1" fill-rule="nonzero" stroke="none" stroke-dasharray="none" stroke-dashoffset="188.976" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="4" stroke-opacity="1" stroke-width="5" d="M52.5 5.682v20.187h17.676c-.988-2.823-2.13-5.429-3.408-7.75c-3.966-7.2-9-11.541-14.268-12.437zm-5 .197c-4.93 1.223-9.61 5.462-13.342 12.24c-1.278 2.321-2.42 4.927-3.408 7.75H47.5V5.88zM35.98 7.232C25.985 10.5 17.545 17.163 12.01 25.87h13.455c1.187-3.695 2.633-7.112 4.312-10.162c1.793-3.255 3.88-6.123 6.203-8.475zm29.41.463c2.145 2.263 4.082 4.967 5.758 8.012c1.68 3.05 3.123 6.467 4.307 10.162H87.99c-5.28-8.306-13.202-14.761-22.6-18.174zM9.257 30.87A44.79 44.79 0 0 0 5.072 47.5h16.79c.194-5.872.957-11.469 2.202-16.63H9.256zm19.974 0c-1.32 5.077-2.15 10.696-2.363 16.631H47.5V30.87H29.23zm23.27 0V47.5H74.06c-.212-5.935-1.043-11.554-2.364-16.63H52.5zm24.355 0c1.243 5.163 2.004 10.76 2.198 16.631h15.875a44.79 44.79 0 0 0-4.184-16.63h-13.89zM5.072 52.5a44.79 44.79 0 0 0 4.184 16.63h14.572c-1.174-5.176-1.865-10.774-1.994-16.63H5.072zm21.762 0c.14 5.915.901 11.53 2.146 16.63H47.5V52.5H26.834zm25.666 0v16.63h19.445c1.245-5.1 2.006-10.715 2.147-16.63H52.5zm26.576 0c-.129 5.855-.815 11.453-1.986 16.63h13.654a44.79 44.79 0 0 0 4.184-16.63H79.076zM12.01 74.13c5.285 8.313 13.214 14.772 22.62 18.183c-1.785-2.05-3.415-4.407-4.853-7.018c-1.83-3.325-3.389-7.08-4.63-11.164H12.01zm18.394 0c1.062 3.216 2.326 6.159 3.754 8.753c3.5 6.355 7.834 10.475 12.424 11.974c.306.023.61.054.918.07V74.132H30.404zm22.096 0v20.798a45.48 45.48 0 0 0 2.127-.162c4.485-1.575 8.713-5.658 12.14-11.883c1.429-2.594 2.693-5.537 3.754-8.752H52.5zm23.275 0c-1.239 4.085-2.796 7.84-4.627 11.165c-1.311 2.382-2.782 4.556-4.386 6.476a45.06 45.06 0 0 0 21.228-17.64H75.775z" color="currentColor" color-interpolation="sRGB" color-rendering="auto" display="inline" opacity="1" vector-effect="none" visibility="visible"/></svg>',
                    'setting' => '<svg viewBox="0 0 24 24"><g fill="none" fill-rule="evenodd"><path d="M24 0v24H0V0h24ZM12.594 23.258l-.012.002l-.071.035l-.02.004l-.014-.004l-.071-.036c-.01-.003-.019 0-.024.006l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.016-.018Zm.264-.113l-.014.002l-.184.093l-.01.01l-.003.011l.018.43l.005.012l.008.008l.201.092c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022Zm-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.003-.011l.018-.43l-.003-.012l-.01-.01l-.184-.092Z"/><path fill="currentColor" d="M16 15c1.306 0 2.418.835 2.83 2H20a1 1 0 1 1 0 2h-1.17a3.001 3.001 0 0 1-5.66 0H4a1 1 0 1 1 0-2h9.17A3.001 3.001 0 0 1 16 15Zm0 2a1 1 0 1 0 0 2a1 1 0 0 0 0-2ZM8 9a3 3 0 0 1 2.762 1.828l.067.172H20a1 1 0 0 1 .117 1.993L20 13h-9.17a3.001 3.001 0 0 1-5.592.172L5.17 13H4a1 1 0 0 1-.117-1.993L4 11h1.17A3.001 3.001 0 0 1 8 9Zm0 2a1 1 0 1 0 0 2a1 1 0 0 0 0-2Zm8-8c1.306 0 2.418.835 2.83 2H20a1 1 0 1 1 0 2h-1.17a3.001 3.001 0 0 1-5.66 0H4a1 1 0 0 1 0-2h9.17A3.001 3.001 0 0 1 16 3Zm0 2a1 1 0 1 0 0 2a1 1 0 0 0 0-2Z"/></g></svg>',
                    'status' => '<svg  viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M16.001 5.2q-.262.385-.445.82a7 7 0 0 0-.157.405c-.165.461-.27.881-.373 1.303l-.005.019c-.132.533-.264 1.067-.525 1.687a5 5 0 0 1-.48.88l-.006.006l-.002.004c2.325.684 4.925-.224 5.868-2.452c.58-1.368.257-3.08-1.052-3.672c-1.054-.476-2.088-.086-2.822 1M3.184 12.023q.147-.447.38-.854q.111-.194.224-.37c.264-.408.532-.742.8-1.076l.026-.031c.335-.42.672-.84.998-1.414q.234-.407.386-.854l.006-.019l.018-.058c1.897 1.55 2.71 4.266 1.52 6.362c-.729 1.289-2.258 2.021-3.487 1.268c-.99-.605-1.29-1.702-.871-2.954M21 17.251c-2.51 0-4.544-2.108-4.544-4.708h-2.654C13.802 16.662 17.025 20 21 20zm-8.663-4.708c0 4.119-3.223 7.457-7.198 7.457v-2.75c2.51 0 4.544-2.107 4.544-4.707zm0 0l-.002-8.324H9.68l.002 8.324z" clip-rule="evenodd"/></svg>',
                    'category' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="12" rx="2"/>  <path d="M3 9h18"/>  <path d="M7 16v2"/>  <path d="M17 16v2"/><circle cx="7" cy="18.5" r="0.5" fill="currentColor" stroke="none"/>  <circle cx="17" cy="18.5" r="0.5" fill="currentColor" stroke="none"/></svg>',
                    'brand_1' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l7 3v6c0 5-3 8.5-7 11-4-2.5-7-6-7-11V5z"/>  <path d="M9 12l2 2 4-4"/></svg>',
                    'brand_2' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="1"/><line x1="8" y1="7" x2="8" y2="7.01"/><line x1="12" y1="7" x2="12" y2="7.01"/><line x1="16" y1="7" x2="16" y2="7.01"/><line x1="8" y1="11" x2="8" y2="11.01"/><line x1="12" y1="11" x2="12" y2="11.01"/><line x1="16" y1="11" x2="16" y2="11.01"/><path d="M9 21v-4h6v4"/></svg>',
                    'organizer_1' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/>  <path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/>  <path d="M17.5 4.5l1 1 2-2"/></svg>',
                    'organizer_2' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h2"/><path d="M8 12h2"/><path d="M8 16h2"/><path d="M14 8l1.5 1.5L18 7"/><path d="M14 12l1.5 1.5L18 11"/><path d="M14 16l1.5 1.5L18 15"/></svg>',
                    'date_1' => '<svg viewBox="0 0 48 48">    <path fill="#CFD8DC" d="M5 38V14h38v24c0 2.2-1.8 4-4 4H9c-2.2 0-4-1.8-4-4z"/>    <path fill="#F44336" d="M43 10v6H5v-6c0-2.2 1.8-4 4-4h30c2.2 0 4 1.8 4 4z"/>    <g fill="#B71C1C">        <circle cx="33" cy="10" r="3"/>        <circle cx="15" cy="10" r="3"/>    </g>    <path fill="#B0BEC5" d="M33 3c-1.1 0-2 .9-2 2v5c0 1.1.9 2 2 2s2-.9 2-2V5c0-1.1-.9-2-2-2zM15 3c-1.1 0-2 .9-2 2v5c0 1.1.9 2 2 2s2-.9 2-2V5c0-1.1-.9-2-2-2z"/>    <path fill="#90A4AE" d="M13 20h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm-18 6h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm-18 6h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4z"/></svg>',
                    'date_2' => '<svg  viewBox="0 0 64 64">    <path fill="#ba9372" d="M58.1 19.6c-9.8-6-25-14.8-27.1-14.8c-2.1 0-16.7 8.9-26 14.8l-.5-.9C6.9 17.2 28 3.8 31 3.8s25.1 13.4 27.6 15l-.5.8"/>    <path fill="#93a2aa" d="M62 56.8c0 1.6-1.2 3-2.7 3H6.8c-1.5 0-2.7-1.3-2.7-3V21.1c0-1.6 1.2-3 2.7-3h52.5c1.5 0 2.7 1.3 2.7 3v35.7"/>    <path fill="#ed4c5c" d="M60 21.1c0-1.6-1.2-3-2.7-3H4.7c-1.5 0-2.7 1.3-2.7 3v9.5h58v-9.5z"/>    <path fill="#d9e3e8" d="M2 30.6v26.2c0 1.6 1.2 3 2.7 3h52.5c1.5 0 2.7-1.3 2.7-3V30.6H2z"/> <path fill="#93a2aa" d="M4.5 33h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zM28 37.4h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm-47 4.4h6V44h-6zm7.8 0h6V44h-6zm7.9 0h6V44h-6zm7.8 0h6V44h-6zm7.8 0h6V44h-6zm7.9 0h6V44h-6zm7.8 0h6V44h-6zm-47 4.5h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm-47 4.4h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm-47 4.4h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6z"/><ellipse cx="31.2" cy="6.2" fill="#333" rx="1.8" ry="1.9"/><ellipse cx="31" cy="6.2" fill="#93a2aa" rx="1.8" ry="1.9"/>    <path fill="#fff" d="M19.5 25.5v.2c0 .6.1 1 .2 1.3c.1.2.3.4.7.4c.4 0 .6-.1.7-.4c.1-.2.1-.4.1-.8v-5.5h1.6v5.4c0 .7-.1 1.2-.3 1.6c-.4.7-1 1-2 1s-1.6-.3-2-.8c-.3-.5-.5-1.3-.5-2.2v-.2h1.5m5-4.8h1.6v4.8c0 .5.1.9.2 1.2c.2.4.6.7 1.3.7c.6 0 1.1-.2 1.3-.7c.1-.3.1-.7.1-1.2v-4.8h1.6v4.8c0 .8-.1 1.5-.4 1.9c-.5.8-1.4 1.3-2.7 1.3c-1.3 0-2.2-.4-2.7-1.3c-.3-.5-.4-1.1-.4-1.9c.1 0 .1-4.8.1-4.8m7.7 0h1.6v6.4h3.8v1.4h-5.4v-7.8m10 0H44l-2.6 4.9v2.9h-1.6v-2.9l-2.7-4.9H39l1.6 3.4l1.6-3.4"/></svg>',
                    'clone_1' => '<svg viewBox="0 0 1792 1792"><path fill="currentColor" d="M1664 1632V544q0-13-9.5-22.5T1632 512H544q-13 0-22.5 9.5T512 544v1088q0 13 9.5 22.5t22.5 9.5h1088q13 0 22.5-9.5t9.5-22.5zm128-1088v1088q0 66-47 113t-113 47H544q-66 0-113-47t-47-113V544q0-66 47-113t113-47h1088q66 0 113 47t47 113zm-384-384v160h-128V160q0-13-9.5-22.5T1248 128H160q-13 0-22.5 9.5T128 160v1088q0 13 9.5 22.5t22.5 9.5h160v128H160q-66 0-113-47T0 1248V160Q0 94 47 47T160 0h1088q66 0 113 47t47 113z"/></svg>',
                    'clone_2' => '<svg viewBox="0 0 36 36"><path fill="currentColor" d="M24 10V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h4V12a2 2 0 0 1 2-2Z" class="clr-i-solid clr-i-solid-path-1"/>    <path fill="currentColor" d="M30 12H14a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V14a2 2 0 0 0-2-2Zm-2 11h-5v5h-2v-5h-5v-2h5v-5h2v5h5Z" class="clr-i-solid clr-i-solid-path-2"/>    <path fill="none" d="M0 0h36v36H0z"/></svg>',
                    'close_1' => '<svg viewBox="0 0 304 384"><path fill="currentColor" d="M299 73L179 192l120 119l-30 30l-120-119L30 341L0 311l119-119L0 73l30-30l119 119L269 43z"/></svg>',
                    'close_2' => '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-3.4 14L12 13.4L8.4 17L7 15.6l3.6-3.6L7 8.4L8.4 7l3.6 3.6L15.6 7L17 8.4L13.4 12l3.6 3.6l-1.4 1.4Z"/></svg>',
                    'view_1' => '<svg viewBox="0 0 24 24" stroke="currentColor"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M2.25.75h19.5s1.5 0 1.5 1.5v19.5s0 1.5-1.5 1.5H2.25s-1.5 0-1.5-1.5V2.25s0-1.5 1.5-1.5"/><path d="M4.267 10.722a1.825 1.825 0 0 0 0 2.544C5.818 14.821 8.591 17.25 12 17.25c3.41 0 6.183-2.428 7.735-3.983a1.825 1.825 0 0 0 0-2.544C18.182 9.168 15.406 6.739 12 6.739s-6.18 2.427-7.733 3.983"/><path d="M9.75 11.991a2.25 2.25 0 1 0 4.5 0a2.25 2.25 0 0 0-4.5 0"/></g></svg>',
                    'view_2' => '<svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" d="M12 21c-5 0-11-5-11-9s6-9 11-9s11 5 11 9s-6 9-11 9Zm0-14a5 5 0 1 0 0 10a5 5 0 0 0 0-10Z"/></svg>',
                    'view_3' => '<svg viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M5 12s2.545-5 7-5c4.454 0 7 5 7 5s-2.546 5-7 5c-4.455 0-7-5-7-5z"/><path d="M12 13a1 1 0 1 0 0-2a1 1 0 0 0 0 2zm9 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2M21 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2"/></g></svg>',
                    'pdf_1' => '<svg  viewBox="0 0 48 48"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M7.45 5.5a2 2 0 0 0-1.95 2v33.1a2 2 0 0 0 2 2h33.1a2 2 0 0 0 2-2V7.45a2 2 0 0 0-2-1.95Z"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M20.09 30V18h2a6 6 0 0 1 6 6h0a6 6 0 0 1-6 6Zm12.39-11.96h5.98m-5.98 5.98h3.9m-3.9-5.98V30M9.54 30V18h4a4 4 0 0 1 0 8h-4"/></svg>',
                    'property' => '<svg viewBox="0 0 120 125" fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round" stroke-linecap="round"><path d="M5 55 L 60 15 L 115 55"/><rect x="18" y="50" width="84" height="70" rx="2"/><rect x="50" y="80" width="24" height="40"/> <rect x="30" y="62" width="16" height="16"/> <rect x="74" y="62" width="16" height="16"/></svg>',
                    '' => '',
                ];
                $allowed_svg_tags = [
                    'svg' => ['class' => true, 'aria-hidden' => true, 'aria-labelledby' => true, 'role' => true, 'xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,], 'g' => ['fill' => true,], 'title' => ['title' => true,], 'path' => ['d' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true,], 'rect' => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true,],
                    'circle' => ['cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true,],
                    'line' => ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true,],
                    'polyline' => ['points' => true, 'fill' => true, 'stroke' => true,],
                ];
                $icons = $des[$key] ?? '';
                echo !empty($icons) ? wp_kses($icons, $allowed_svg_tags) : '';
            }
        }
        new ABPTB_Layout();
    }