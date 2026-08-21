<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Additional')) {
        class ABPTB_Additional {
            public function __construct() {
                add_action('abptb_global_additional', array($this, 'global_additional'));
                add_action('abptb_post_content', [$this, 'post_additional_service']);
                add_filter('abptb_get_additional_array', array($this, 'get_additional_array'));
                add_action('wp_ajax_abptb_save_global_additional', array($this, 'save_global_additional'));
                add_action('wp_ajax_abptb_import_additional_content', array($this, 'import_additional_content'));
            }
            public function global_additional(): void {
                ?>
                <div class="global_additional">
                    <?php $this->additional_config(); ?>
                </div>
                <?php
            }
            public function additional_config(): void {
                if (ABPTB_Function::on_off('additional_info')) {
                    $additional_services = ABPTB_Function::get_option('abptb_additional', ABPTB_Static::static_additional());
                    ?>
                    <div class="abp_form">
                        <h4 class="abp_color_theme_gap_xs">💰<?php esc_html_e('Global Additional services Configuration', 'abp-transport-booking'); ?></h4>
                        <?php ABPTB_Layout::info_text('abptb_additional'); ?>
                        <div class="_mar_t_xs"></div>
                        <?php $this->additional_service($additional_services, true); ?>
                    </div>
                    <?php
                }
            }
            public function post_additional_service($post_infos): void {
                if (ABPTB_Function::on_off('additional_info')) {
                    $additional_services = $post_infos['additional_services'] ?? array();
                    $display = $post_infos['display_additional_services'] ?? 'on';
                    $active_global_additional = $post_infos['active_global_additional'] ?? 'on';
                    ?>
                    <div class="tab_item additional_configuration" data-tabs="#abptb_additional_service">
                        <h4 class="abp_color_theme_gap_xs">💰<?php esc_html_e('Additional services Configuration', 'abp-transport-booking'); ?></h4>
                        <div class="group_setting _mar_t_xs">
                            <div class="setting_item">
                                <div class="_gap_xs">
                                    <?php ABPTB_Layout::switch_checkbox('display_additional_services', $display); ?>
                                    <span class="_fs_label"><?php esc_html_e('Active Additional services ?', 'abp-transport-booking'); ?></span>
                                </div>
                                <div class="_divider_xs"></div>
                                <?php ABPTB_Layout::info_text('display_additional_services'); ?>
                            </div>
                            <div data-collapse="#display_additional_services" class="setting_item <?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                                <div class="_fj_between_gap_xs">
                                    <div class="_gap_xs">
                                        <?php ABPTB_Layout::switch_checkbox('active_global_additional', $active_global_additional); ?>
                                        <span class="_fs_label"><?php esc_html_e('Use Global Additional Service ?', 'abp-transport-booking'); ?></span>
                                    </div>
                                    <div data-collapse="#active_global_additional" class=" <?php echo esc_attr($active_global_additional == 'on' ? '' : 'abp_active'); ?>">
                                        <button type="button" class="_btn_theme" onclick="abptb_import_global('additional_content')"><span class="fas fa-file-upload"></span><?php esc_html_e('Import Additional Service', 'abp-transport-booking'); ?></button>
                                    </div>
                                </div>
                                <div class="_divider_xs"></div>
                                <?php ABPTB_Layout::info_text('active_global_additional'); ?>
                            </div>
                        </div>
                        <div class="<?php echo esc_attr($active_global_additional == 'on' ? '' : 'abp_active'); ?>" data-collapse="#active_global_additional">
                            <div class="additional_content _mar_t_xs">
                                <?php $this->additional_service($additional_services); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            public function additional_service($services = [], $global = false): void {
                ?>
                <div class="configuration_content additional_service">
                    <div class="_ov_auto">
                        <table class="abp">
                            <thead>
                            <tr>
                                <th class="_w_125"><?php esc_html_e('Icon / Image', 'abp-transport-booking'); ?></th>
                                <th class="_min_200"><?php esc_html_e('Name', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></th>
                                <th class="_min_100"><?php esc_html_e('Quantity', 'abp-transport-booking'); ?></th>
                                <th class="_min_100"><?php esc_html_e('Price', 'abp-transport-booking'); ?></th>
                                <th class="_min_100"><?php esc_html_e('Max qty', 'abp-transport-booking'); ?></th>
                                <th class="_min_100"><?php esc_html_e('Returnable or Not', 'abp-transport-booking'); ?></th>
                                <th class="_min_250"><?php esc_html_e('Description', 'abp-transport-booking'); ?></th>
                                <th class="_w_75"><?php esc_html_e('Action', 'abp-transport-booking'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insertable_area sortable_area">
                            <?php
                                if ($services && is_array($services) && sizeof($services) > 0) {
                                    foreach ($services as $key => $service) {
                                        $this->service_item($key, $service);
                                    }
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fj_between">
                        <?php ABPTB_Layout::button_add(__('Add New Additional services', 'abp-transport-booking'));
                            if ($global) {
                                ABPTB_Layout::button_global_save('global_additional', __('Save Global Additional services Configuration', 'abp-transport-booking'));
                            } ?>
                    </div>
                    <div class="abp_hidden">
                        <table class="abp">
                            <tbody class="hidden_content">
                            <?php $this->service_item(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
            public function service_item($key = '', $field = array()): void {
                $field = $field ?: [];
                $name = $field['name'] ?? '';
                $qty = $field['qty'] ?? '';
                $max_qty = $field['max_qty'] ?? '';
                $price = $field['price'] ?? '';
                $returnable = $field['returnable'] ?? 'no';
                $description = $field['description'] ?? '';
                ?>
                <tr class="delete_area ">
                    <td> <?php do_action('abptb_add_image_icon', 'additional_icon[]', ($field['icon'] ?? '')); ?>  </td>
                    <td>
                        <input type="hidden" name="additional_id[]" value="<?php echo esc_attr($key); ?>"/>
                        <label>
                            <input type="text" class="_form_control validation_name" name="additional_name[]" placeholder="<?php esc_attr_e('EX: Water Bottle', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($name); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="additional_qty[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($qty); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="0.01" class="_form_control validation_price" name="additional_price[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($price); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="additional_max_qty[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($max_qty); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <select class="_form_control" name="additional_returnable[]">
                                <option value="yes" <?php echo esc_attr($returnable == 'yes' ? 'selected' : ''); ?>><?php esc_html_e('Yes', 'abp-transport-booking'); ?></option>
                                <option value="no" <?php echo esc_attr($returnable == 'no' ? 'selected' : ''); ?>><?php esc_html_e('No', 'abp-transport-booking'); ?></option>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label>
                            <textarea class="_form_control" name="additional_description[]" placeholder="<?php esc_attr_e('EX: Description', 'abp-transport-booking'); ?>"><?php echo esc_html($description); ?></textarea>
                        </label>
                    </td>
                    <td><?php ABPTB_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
            public function save_global_additional(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $additional_services = $this->get_additional_array();
                update_option('abptb_additional', $additional_services);
                ob_start();
                $this->additional_config();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Additional services Configuration Saved Successfully ..... !!', 'abp-transport-booking'),
                    'type' => 'success'
                ]);
            }
            public function get_additional_array(array $additional_services = []): array {
                $has_post_nonce = isset($_POST['abptb_post_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abptb_post_nonce'])), 'abptb_post_nonce');
                $has_ajax_nonce = check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false);
                if (($has_post_nonce || $has_ajax_nonce) && current_user_can('manage_options')) {
                    $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                    $post_textarea_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_textarea_field', wp_unslash($_POST[$key])) : [];
                    $additional_ids = $post_array('additional_id');
                    $icon = $post_array('additional_icon');
                    $names = $post_array('additional_name');
                    $qty = $post_array('additional_qty');
                    $max_qty = $post_array('additional_max_qty');
                    $returnable = $post_array('additional_returnable');
                    $price = $post_array('additional_price');
                    $description = $post_textarea_array('additional_description');
                    if (!empty($names)) {
                        foreach ($names as $key => $name) {
                            if (!empty($name)) {
                                $id = $additional_ids[$key] ?? '';
                                $id = empty($id) ? uniqid() : $id;
                                $additional_services[$id] = [
                                    'icon' => $icon[$key] ?? '',
                                    'name' => $name,
                                    'qty' => $qty[$key] ?? '',
                                    'max_qty' => $max_qty[$key] ?? '',
                                    'price' => $price[$key] ?? '',
                                    'returnable' => $returnable[$key] ?? '',
                                    'description' => $description[$key] ?? '',
                                ];
                            }
                        }
                    }
                }
                return $additional_services;
            }
            public function import_additional_content(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $additional_services = ABPTB_Function::get_option('abptb_additional', ABPTB_Static::static_additional());
                $additional_services = is_array($additional_services) ? $additional_services : [];
                ob_start();
                $this->additional_service($additional_services);
                $html_content = ob_get_clean();
                wp_send_json_success(['html' => $html_content, 'msg' => __('Additional services Imported Successfully ..... !! ', 'abp-transport-booking'), 'type' => 'success']);
            }
        }
        new ABPTB_Additional();
    }