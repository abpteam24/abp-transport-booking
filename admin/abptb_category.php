<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Category')) {
        class ABPTB_Category {
            public function __construct() {
                add_action('abptb_global_category', array($this, 'global_category'));
                add_action('wp_ajax_abptb_add_tax_category', array($this, 'add_tax_category'));
                add_action('wp_ajax_abptb_save_tax_category', array($this, 'save_tax_category'));
                add_action('wp_ajax_abptb_delete_tax_category', array($this, 'delete_tax_category'));
                add_action('abptb_category_update', array($this, 'update_category'));
            }
            public function global_category(): void {
                if (ABPTB_Function::on_off('category')) {
                    $label = ABPTB_Function::category_label(); ?>
                    <div class="_fj_between _mar_b_xs">
                        <h5 class="_abp"><span class="_mar_r_xs">🏘️</span><?php echo esc_html($label); ?></h5>
                        <?php ABPTB_Layout::button_global_popup('tax_category', __('Add New', 'abp-transport-booking') . ' ' . $label); ?>
                    </div>
                    <div class="tax_category _ov_auto">
                        <?php $this->category_list(); ?>
                    </div>
                    <?php
                }
            }
            public function add_tax_category(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $term_id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
                ob_start();
                $name = $slug = $des = '';
                $label = ABPTB_Function::category_label();
                $btn_label = __('Save', 'abp-transport-booking') . ' ' . $label;
                $title = __('Add new ', 'abp-transport-booking') . ' ' . $label;
                if (!empty($term_id)) {
                    $term = get_term($term_id);
                    if (!empty($term)) {
                        $name = $term->name;
                        $slug = $term->slug;
                        $des = $term->description;
                        $btn_label = __('Update', 'abp-transport-booking') . ' ' . $label . ' ' . $name;
                        $title = __('Edit ', 'abp-transport-booking') . ' ' . $label . ' ' . $name;
                    }
                }
                ?>
                <div class="abp_form">
                    <h5 class="_abp"><span class="_mar_r_xs">🏘️</span><?php echo esc_html($title); ?></h5>
                    <div class="_divider_xs"></div>
                    <input type="hidden" name="id" value="<?php echo esc_attr($term_id); ?>"/>
                    <div class="group_setting">
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html__('Name', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></span>
                                <input class="_form_control" name="name" value="<?php echo esc_attr($name); ?>" placeholder="<?php esc_attr_e('Name', 'abp-transport-booking'); ?>" required/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('cat_name'); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html__('Slug (Optional)', 'abp-transport-booking'); ?></span>
                                <input class="_form_control" name="slug" value="<?php echo esc_attr($slug); ?>" placeholder="<?php esc_attr_e('Slug', 'abp-transport-booking'); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('cat_slug'); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html__('Description(Optional)', 'abp-transport-booking'); ?></span>
                                <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e('Description', 'abp-transport-booking'); ?>"><?php echo esc_html($des); ?></textarea>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('cat_des'); ?>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php ABPTB_Layout::button_global_save('tax_category', $btn_label); ?>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __('Form Loaded Successfully .....! ', 'abp-transport-booking')]);
            }
            public function save_tax_category(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_int = fn($key, $default = 0) => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $post_textarea = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_textarea_field(wp_unslash($_POST[$key])) : $default;
                $post_slug = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_title(wp_unslash($_POST[$key])) : $default;
                $tax_id = $post_int('id');
                $name = $post_val('name');
                $slug = $post_slug('slug');
                $description = $post_textarea('description');
                $post_id = $post_int('post_id');
                $label = ABPTB_Function::category_label();
                if (empty($name)) {
                    ob_start();
                    if ($post_id > 0) {
                        $_category = ABPTB_Function::get_post_info($post_id, 'abptb_category');
                        self::category_selection($_category);
                    } else {
                        $this->category_list();
                    }
                    $html = ob_get_clean();
                    wp_send_json_error(['html' => $html, 'type' => 'warn', 'msg' => $label . ' ' . __('Name cannot be blank!', 'abp-transport-booking')], 400);
                }
                if ($tax_id > 0) {
                    $result = wp_update_term($tax_id, 'abptb_category', [
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $description,
                    ]);
                } else {
                    $result = wp_insert_term($name, 'abptb_category', [
                        'slug' => $slug,
                        'description' => $description,
                    ]);
                }
                $this->update_category();
                ob_start();
                if ($post_id > 0) {
                    $_category = ABPTB_Function::get_post_info($post_id, 'abptb_category');
                    self::category_selection($_category);
                } else {
                    $this->category_list();
                }
                $html = ob_get_clean();
                if (is_wp_error($result)) {
                    wp_send_json_error(['html' => $html, 'type' => 'warn', 'msg' => $result->get_error_message()], 400);
                }
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __('Saved Successfully !', 'abp-transport-booking'),]);
            }
            public function delete_tax_category(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $tax_id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : '';
                $label = ABPTB_Function::category_label();
                if (empty($tax_id) || !is_numeric($tax_id)) {
                    ob_start();
                    $this->category_list();
                    $html = ob_get_clean();
                    wp_send_json_error(['html' => $html, 'msg' => $label . ' ' . __('id Invalid...!', 'abp-transport-booking'), 'type' => 'warn']);
                }
                $result = wp_delete_term($tax_id, 'abptb_category');
                $this->update_category();
                ob_start();
                $this->category_list();
                $html = ob_get_clean();
                if (is_wp_error($result)) {
                    wp_send_json_error(['html' => $html, 'msg' => $result->get_error_message(), 'type' => 'warn']);
                }
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => ABPTB_Function::category_label() . ' ' . __('Deleted Successfully !', 'abp-transport-booking')]);
            }
            public function update_category(): void {
                $taxonomies = ABPTB_Function::get_taxonomy('abptb_category');
                $tax = [];
                if (!empty($taxonomies) && is_array($taxonomies) && sizeof($taxonomies) > 0) {
                    foreach ($taxonomies as $taxonomy) {
                        $tax[$taxonomy->term_id]['name'] = $taxonomy->name;
                        $tax[$taxonomy->term_id]['description'] = $taxonomy->description;
                    }
                }
                ksort($tax);
                update_option('abptb_category', $tax);
            }
            public function category_list(): void {
                $all_categories = ABPTB_Function::get_option('abptb_category');
                $count = 1;
                if (!empty($all_categories) && is_array($all_categories) && sizeof($all_categories) > 0) { ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('SI', 'abp-transport-booking'); ?></th>
                            <th class="_min_150"><?php echo esc_html(ABPTB_Function::category_label()); ?></th>
                            <th><?php esc_html_e('ID', 'abp-transport-booking'); ?></th>
                            <th class="_min_150"><?php esc_html_e('Description', 'abp-transport-booking'); ?></th>
                            <th class="_w_250"><?php esc_html_e('Shortcode', 'abp-transport-booking'); ?></th>
                            <th class="_w_100"><?php esc_html_e('Action', 'abp-transport-booking'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($all_categories as $term_id => $category) {
                            $name = $category['name'] ?? ''; ?>
                            <tr>
                                <th><?php echo esc_html($count); ?>.</th>
                                <th class="_text_left"><a href="<?php echo esc_url(get_term_link($term_id)); ?>" target="_blank" class="_abp_fs_h5_color_theme"><?php echo esc_html($name); ?></a></th>
                                <th><?php echo esc_html($term_id); ?></th>
                                <td><?php echo esc_html($category['description'] ?? ''); ?></td>
                                <th><code> [abptb-post cat_id="<?php echo esc_attr($term_id); ?>"]</code></th>
                                <th>
                                    <div class="_fj_center">
                                        <div class="_group_content">
                                            <button type="button" class="_btn_light_yellow_xxs" onclick="abptb_popup_open_global('tax_category','<?php echo esc_attr($term_id); ?>')" title="<?php echo esc_attr__('Edit : ', 'abp-transport-booking') . ' ' . esc_attr($name); ?>">✍️</button>
                                            <button type="button" class="_btn_light_danger_xxs" onclick="abptb_delete_global('tax_category','<?php echo esc_attr($term_id); ?>')" title="<?php echo esc_attr__('Trash : ', 'abp-transport-booking') . ' ' . esc_attr($name); ?>">❌</button>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <?php $count++;
                        } ?>
                        </tbody>
                    </table>
                <?php } else {
                    ABPTB_Layout::layout_warning_info('no_category');
                }
            }
            public static function category_selection($value = ''): void {
                $options = ABPTB_Function::get_option('abptb_category');
                if (!empty($options) && is_array($options) && sizeof($options) > 0) { ?>
                    <label>
                        <select class="_form_control" name="abptb_category">
                            <option value="" selected><?php echo esc_html__('Please Select', 'abp-transport-booking') . ' ' . esc_html(ABPTB_Function::category_label()); ?></option>
                            <?php foreach ($options as $key => $option) { ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($key == $value ? 'selected' : ''); ?>><?php echo esc_html($option['name'] ?? ''); ?></option>
                            <?php } ?>
                        </select>
                    </label>
                <?php } else {
                    ABPTB_Layout::layout_info_xs('no_category');
                }
                ABPTB_Layout::button_global_popup('tax_category', __('Add New', 'abp-transport-booking') . ' ' . ABPTB_Function::category_label());
            }
        }
        new ABPTB_Category();
    }