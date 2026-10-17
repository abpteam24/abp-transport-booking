<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Post_Feature')) {
        class ABPTB_Post_Feature {
            public function __construct() {
                add_action('abptb_post_content', array($this, 'post_feature_tab'));
            }
            public function post_feature_tab($post_infos = []): void {
                if (ABPTB_Function::on_off('feature')) {
                    ?>
                    <div class="tab_item" data-tabs="#abptb_post_feature">
                        <?php $this->post_feature($post_infos); ?>
                    </div>
                    <?php
                }
            }
            public function post_feature($post_infos = []): void {
                ?>
                <div class="setting_item full_width post_feature">
                    <div class="_fj_between_fa_center">
                        <span class="abp_label"><?php esc_html_e('Feature', 'abp-transport-booking'); ?></span>
                        <div class="_group_content">
                            <div class="selection_area">
                                <label>
                                    <input class="_form_control item_search" type="text" placeholder="<?php esc_attr_e('Search feature ....', 'abp-transport-booking'); ?>"/>
                                </label>
                                <div class="selection_list"></div>
                            </div>
                            <?php ABPTB_Layout::button_global_popup('option_feature', __('Add New', 'abp-transport-booking') . ' ' . ABPTB_Function::feature_label()); ?>
                        </div>
                    </div>
                    <div class="_divider_xxs"></div>
                    <?php ABPTB_Layout::info_text('post_feature'); ?>
                    <div class="_divider_xxs"></div>
                    <div class="selected_area">
                        <input type="hidden" name="post_feature" value="<?php echo esc_attr($post_infos['post_feature'] ?? ''); ?>"/>
                        <div class="selected_list"></div>
                    </div>
                </div>
                <?php
            }
        }
        new ABPTB_Post_Feature();
    }
