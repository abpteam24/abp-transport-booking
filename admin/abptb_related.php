<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Related')) {
        class ABPTB_Related {
            public function __construct() {
                add_action('abptb_post_content', array($this, 'post_related'));
            }
            public function post_related($post_infos = []): void {
                if (ABPTB_Function::on_off('related')) {
                    ?>
                    <div class="tab_item" data-tabs="#abptb_related">
                        <?php $this->related($post_infos); ?>
                    </div>
                    <?php
                }
            }
            public function related($post_infos = []): void {
                ?>
                <div class="setting_item related_item">
                    <div class="_fj_between_fa_center">
                        <span class="abp_label"><?php esc_html_e('Related Transport', 'abp-transport-booking'); ?></span>
                        <div class="selection_area">
                            <label>
                                <input class="_form_control item_search" type="text" placeholder="<?php esc_attr_e('Search Related Transport ....', 'abp-transport-booking'); ?>"/>
                            </label>
                            <div class="selection_list"></div>
                        </div>
                    </div>
                    <div class="_divider_xxs"></div>
                    <?php ABPTB_Layout::info_text('related_item'); ?>
                    <div class="_divider_xxs"></div>
                    <div class="selected_area">
                        <input type="hidden" name="related_item" value="<?php echo esc_attr($post_infos['related_item'] ?? ''); ?>"/>
                        <div class="selected_list"></div>
                    </div>
                </div>
                <?php
            }
        }
        new ABPTB_Related();
    }
