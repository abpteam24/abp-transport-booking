<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_TC')) {
        class ABPTB_TC {
            public function __construct() {
                add_action('abptb_global_tc', array($this, 'global_tc'));
                add_action('abptb_post_content', array($this, 'post_tc_tab'));
                add_action('wp_ajax_abptb_save_tc_config', array($this, 'save_tc_config'));
                add_action('wp_ajax_abptb_import_tc_content', array($this, 'import_tc_content'));
            }
            public function global_tc(): void {
                if (ABPTB_Function::on_off('tc')) {
                    ?>
                    <h5 class="abp_color_theme_gap_xs">🤝<?php esc_html_e('Global Term & Conditions Configuration', 'abp-transport-booking'); ?></h5>
                    <div class="_divider_xs"></div>
                    <?php ABPTB_Layout::info_text('abptb_tc'); ?>
                    <div class="tc_config _mar_t_xs">
                        <?php $this->tc_config(); ?>
                    </div>
                    <?php
                }
            }
            public function post_tc_tab($post_infos = []): void {
                if (ABPTB_Function::on_off('tc')) {
                    ?>
                    <div class="tab_item" data-tabs="#abptb_tc">
                        <?php $this->post_tc($post_infos); ?>
                    </div>
                    <?php
                }
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
        }
        new ABPTB_TC();
    }