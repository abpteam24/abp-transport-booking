<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Resource')) {
        class ABPTB_Resource {
            public function __construct() {
                add_action('abptb_global_resource', array($this, 'global_resource'));
                add_action('wp_ajax_abptb_save_faq_config', array($this, 'save_faq_config'));
                add_filter('abptb_get_faq_array', array($this, 'get_faq_array'));
                add_action('abptb_post_content', array($this, 'post_resource'));
                add_action('wp_ajax_abptb_save_tc_config', array($this, 'save_tc_config'));
                add_action('wp_ajax_abptb_import_faq_content', array($this, 'import_faq_content'));
                add_action('wp_ajax_abptb_import_tc_content', array($this, 'import_tc_content'));
            }
            public function global_resource(): void {
                if (ABPTB_Function::on_off('faq')) {
                    ?>
                    <div class="setting_item faq_configuration _mar_b_xs">
                        <h5 class="abp_gap_xs" data-collapse-target="#faq_collapse">❓<?php esc_html_e('Global FAQ Configuration', 'abp-transport-booking'); ?></h5>
                        <div class="abp_active" data-collapse="#faq_collapse">
                            <div class="_divider_xxs"></div>
                            <?php ABPTB_Layout::info_text('abptb_faq'); ?>
                            <div class="faq_config _mar_t_xs">
                                <?php $this->faq_config(); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                if (ABPTB_Function::on_off('tc')) {
                    ?>
                    <div class="setting_item">
                        <h5 class="abp_gap_xs" data-collapse-target="#tc_collapse">🤝<?php esc_html_e('Global Term & Conditions Configuration', 'abp-transport-booking'); ?></h5>
                        <div class="abp_active" data-collapse="#tc_collapse">
                            <div class="_divider_xxs"></div>
                            <?php ABPTB_Layout::info_text('abptb_tc'); ?>
                            <div class="tc_config _mar_t_xs">
                                <?php $this->tc_config(); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            public function faq_config(): void {
                $faqs = ABPTB_Function::get_option('abptb_faq');
                ?>
                <div class="abp_form">
                    <?php $this->faq($faqs, true); ?>
                </div>
                <?php
            }
            public function faq($faqs = [], $global = false): void {
                ?>
                <div class="configuration_content">
                    <div class="insertable_area sortable_area">
                        <?php
                            if (!empty($faqs) && sizeof($faqs) > 0) {
                                foreach ($faqs as $faq) {
                                    $this->faq_item($faq);
                                }
                            }
                        ?>
                    </div>
                    <div class="_fj_between">
                        <?php ABPTB_Layout::button_add(__('Add New FAQ Item', 'abp-transport-booking'));
                            if ($global) {
                                ABPTB_Layout::button_global_save('faq_config', __('Save FAQs Configuration', 'abp-transport-booking'));
                            } ?>
                    </div>
                    <div class="abp_hidden">
                        <div class="hidden_content">
                            <?php $this->faq_item(); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            public function faq_item($faq = []): void {
                $title = $faq['title'] ?? __('NEW', 'abp-transport-booking');
                $description = $faq['des'] ?? '';
                $description = $description ? html_entity_decode($description) : '';
                $editor_id = uniqid('abptb_editor_');
                ?>
                <div class="delete_area faq_item _mar_b_xs <?php echo esc_attr(empty($faq) ? 'active' : ''); ?>">
                    <div class="faq_question">
                        <h6 class="abp edit_hook" data-paste="#faq_title"><?php echo esc_html($title); ?></h6>
                        <?php ABPTB_Layout::button_delete_sort_edit(); ?>
                    </div>
                    <div class="edit_area">
                        <div class="faq_answer_content">
                            <?php ABPTB_Layout::info_text('faq_item'); ?>
                            <div class="_divider_xs"></div>
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php esc_html_e('FAQ Title', 'abp-transport-booking'); ?><sup class="_color_required">*</sup></span>
                                <input type="text" class="_form_control" name="faq_title[]" data-pass="#faq_title" placeholder="<?php esc_attr_e('EX: What is the check-in time?', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($title); ?>"/>
                            </label>
                            <div class="_fd_column_mar_t_xs">
                                <span class="abp_label"><?php esc_html_e('Description', 'abp-transport-booking'); ?></span>
                                <?php
                                    wp_editor(
                                        $description,
                                        $editor_id,
                                        array(
                                            'textarea_name' => 'faq_description[]',
                                            'textarea_rows' => 6,
                                            'media_buttons' => true,
                                            'teeny' => false,
                                            'quicktags' => true
                                        )
                                    );
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            public function save_faq_config(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $abptb_faq = $this->get_faq_array();
                update_option('abptb_faq', $abptb_faq);
                ob_start();
                $this->faq_config();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('FAQs Configuration Saved Successfully..... !! ', 'abp-transport-booking'),
                    'type' => 'success'
                ]);
            }
            public function post_resource($post_infos = []): void {
                ?>
                <div class="tab_item" data-tabs="#abptb_resource">
                    <?php $this->tax($post_infos);
                        $this->post_faq($post_infos);
                        $this->post_tc($post_infos); ?>
                </div>
                <?php
            }
            public function tax($post_infos = []): void {
                $tax_status = $post_infos['_tax_status'] ?? '';
                $tax_classes = WC_Tax::get_tax_rate_classes();
                $tax_class = $post_infos['_tax_class'] ?? '';
                ?>
                <h5 class="abp_gap_xs">🧾<?php esc_html_e('Tax Configuration', 'abp-transport-booking'); ?></h5>
                <div class="_divider_xs"></div>
                <?php if (get_option('woocommerce_calc_taxes') == 'yes') { ?>
                    <div class="group_setting">
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php esc_html_e('Tax Status', 'abp-transport-booking'); ?></span>
                                <select class="_form_control" name="_tax_status">
                                    <option disabled selected><?php esc_html_e('Please Select', 'abp-transport-booking'); ?></option>
                                    <option value="taxable" <?php echo esc_attr($tax_status == 'taxable' ? 'selected' : ''); ?>><?php esc_html_e('Taxable', 'abp-transport-booking'); ?></option>
                                    <option value="shipping" <?php echo esc_attr($tax_status == 'shipping' ? 'selected' : ''); ?>><?php esc_html_e('Shipping only', 'abp-transport-booking'); ?></option>
                                    <option value="none" <?php echo esc_attr($tax_status == 'none' ? 'selected' : ''); ?>><?php esc_html_e('None', 'abp-transport-booking'); ?></option>
                                </select>
                            </label>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php esc_html_e('Tax Class', 'abp-transport-booking'); ?></span>
                                <select class="_form_control" name="_tax_class">
                                    <option disabled selected><?php esc_html_e('Please Select', 'abp-transport-booking'); ?></option>
                                    <option value="standard" <?php echo esc_attr($tax_class == 'standard' ? 'selected' : ''); ?>><?php esc_html_e('Standard', 'abp-transport-booking'); ?></option>
                                    <?php if (sizeof($tax_classes) > 0) { ?>
                                        <?php foreach ($tax_classes as $class) { ?>
                                            <option value="<?php echo esc_attr($class->slug); ?>" <?php echo esc_attr($tax_class == $class->slug ? 'selected' : ''); ?>> <?php echo esc_html($class->name); ?> </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('_tax_class'); ?>
                        </div>
                    </div>
                <?php } else {
                    ABPTB_Layout::layout_warning_info('enable_tax_msg');
                } ?>
                <?php
            }
            public function post_faq($post_infos = []): void {
                if (ABPTB_Function::on_off('faq')) {
                    $post_id = absint($post_infos['post_id'] ?? 0);
                    $display = $post_infos['display_faq'] ?? 'on';
                    $active_global_faq = $post_infos['active_global_faq'] ?? 'on';
                    $faqs = get_post_meta($post_id, 'abptb_faq', true);
                    $faqs = is_array($faqs) ? $faqs : [];
                    ?>
                    <h5 class="abp_gap_xs">❓<?php esc_html_e('FAQs Configuration', 'abp-transport-booking'); ?></h5>
                    <div class="_divider_xs"></div>
                    <div class="group_setting">
                        <div class="setting_item">
                            <div class="_f_wrap_fj_between_fa_center">
                                <div class="_gap_xs">
                                    <?php ABPTB_Layout::switch_checkbox('display_faq', $display); ?>
                                    <span class="abp_label"><?php esc_html_e('Active FAQs ?', 'abp-transport-booking'); ?></span>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('display_faq'); ?>
                        </div>
                        <div data-collapse="#display_faq" class="setting_item <?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                            <div class="_fj_between">
                                <div class="_gap_xs">
                                    <?php ABPTB_Layout::switch_checkbox('active_global_faq', $active_global_faq); ?>
                                    <span class="abp_label"><?php esc_html_e('Use Global FAQ ?', 'abp-transport-booking'); ?></span>
                                </div>
                                <div data-collapse="#active_global_faq" class=" <?php echo esc_attr($active_global_faq == 'on' ? '' : 'abp_active'); ?>">
                                    <button type="button" class="_btn_theme" onclick="abptb_import_global('faq_content')"><span class="fas fa-file-upload"></span><?php esc_html_e('Import Global FAQ', 'abp-transport-booking'); ?></button>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('active_global_faq'); ?>
                        </div>
                    </div>
                    <div data-collapse="#display_faq" class="<?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                        <div class="_mar_t_xs <?php echo esc_attr($active_global_faq == 'on' ? '' : 'abp_active'); ?>" data-collapse="#active_global_faq">
                            <div class="faq_content">
                                <?php $this->faq($faqs); ?>
                            </div>
                        </div>
                    </div>
                <?php }
            }
            public function post_tc($post_infos = []): void {
                if (ABPTB_Function::on_off('tc')) {
                    $post_id = absint($post_infos['post_id'] ?? 0);
                    $abptb_tc = get_post_meta($post_id, 'abptb_tc', true);
                    $display = $post_infos['display_tc'] ?? 'on';
                    $active_global_tc = $post_infos['active_global_tc'] ?? 'on';
                    ?>
                    <h5 class="abp_gap_xs">🤝<?php esc_html_e('Term & Conditions', 'abp-transport-booking'); ?></h5>
                    <div class="_divider_xs"></div>
                    <div class="group_setting">
                        <div class="setting_item">
                            <div class="_f_wrap_fj_between_fa_center">
                                <div class="_gap_xs">
                                    <?php ABPTB_Layout::switch_checkbox('display_tc', $display); ?>
                                    <span class="abp_label"><?php esc_html_e('Active Term & Conditions ?', 'abp-transport-booking'); ?></span>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('display_tc'); ?>
                        </div>
                        <div data-collapse="#display_tc" class="setting_item <?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                            <div class="_fj_between">
                                <div class="_gap_xs">
                                    <?php ABPTB_Layout::switch_checkbox('active_global_tc', $active_global_tc); ?>
                                    <span class="abp_label"><?php esc_html_e('Use Global Term & Conditions ?', 'abp-transport-booking'); ?></span>
                                </div>
                                <div data-collapse="#active_global_tc" class=" <?php echo esc_attr($active_global_tc == 'on' ? '' : 'abp_active'); ?>">
                                    <button type="button" class="_btn_theme_xs" onclick="abptb_import_global('tc_content')"><span class="fas fa-file-upload "></span><?php esc_html_e('Import Global Term & Conditions', 'abp-transport-booking'); ?></button>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTB_Layout::info_text('active_global_tc'); ?>
                        </div>
                        <div data-collapse="#display_tc" class="<?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                            <div class="setting_item full_width <?php echo esc_attr($active_global_tc == 'on' ? '' : 'abp_active'); ?>" data-collapse="#active_global_tc">
                                <div class="tc_content">
                                    <?php $this->tc($abptb_tc); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            public function tc_config(): void {
                $tcs = ABPTB_Function::get_option('abptb_tc', '');
                ?>
                <div class="abp_form">
                    <?php $this->tc($tcs); ?>
                    <div class="_divider_xs"></div>
                    <?php ABPTB_Layout::button_global_save('tc_config', __('Save Term & Conditions Configuration', 'abp-transport-booking')); ?>
                </div>
                <?php
            }
            public function tc($tcs = ''): void {
                $description = $tcs ? wp_kses_post($tcs) : '';
                $editor_id = 'abptb_editor_tc_' . wp_rand(0, 999);
                ?>
                <div class="edit_area">
                    <div class="_fd_column_mar_t_xs">
                        <span class="abp_label"><?php esc_html_e('Term & Conditions Content', 'abp-transport-booking'); ?></span>
                        <?php
                            wp_editor(
                                $description,
                                $editor_id,
                                array(
                                    'textarea_name' => 'tc_content',
                                    'textarea_rows' => 12,
                                    'media_buttons' => true,
                                    'teeny' => false,
                                    'quicktags' => true
                                )
                            );
                        ?>
                    </div>

                </div>
                <?php
            }
            public function save_tc_config(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_html = fn($key, $default = '') => isset($_POST[$key]) ? wp_kses_post(wp_unslash($_POST[$key])) : $default;
                update_option('abptb_tc', $post_html('tc_content'));
                ob_start();
                $this->tc_config();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Term & Conditions  Saved Successfully..... !!', 'abp-transport-booking'),
                    'type' => 'success'
                ]);
            }
            public function get_faq_array(array $abptb_faq = []): array {
                $has_post_nonce = isset($_POST['abptb_post_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abptb_post_nonce'])), 'abptb_post_nonce');
                $has_ajax_nonce = check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false);
                if (($has_post_nonce || $has_ajax_nonce) && current_user_can('manage_options')) {
                    $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                    $post_html_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('wp_kses_post', wp_unslash($_POST[$key])) : [];
                    $titles = $post_array('faq_title');
                    $descriptions = $post_html_array('faq_description');
                    if (!empty($titles)) {
                        foreach ($titles as $key => $title) {
                            if ($title && !empty($descriptions[$key])) {
                                $abptb_faq[$key] = [
                                    'title' => $title,
                                    'des' => $descriptions[$key],
                                ];
                            }
                        }
                    }
                }
                return $abptb_faq;
            }
            public function import_tc_content(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $tcs = ABPTB_Function::get_option('abptb_tc', '');
                ob_start();
                $this->tc($tcs);
                $html_content = ob_get_clean();
                wp_send_json_success(['html' => $html_content, 'msg' => __('Global Term & Conditions  Imported Successfully ..... !! ', 'abp-transport-booking'), 'type' => 'success']);
            }
            public function import_faq_content(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $faqs = ABPTB_Function::get_option('abptb_faq');
                $faqs = is_array($faqs) ? $faqs : [];
                ob_start();
                $this->faq($faqs);
                $html_content = ob_get_clean();
                wp_send_json_success(['html' => $html_content, 'msg' => __('FAQ Imported Successfully ..... !! ', 'abp-transport-booking'), 'type' => 'success']);
            }
        }
        new ABPTB_Resource();
    }