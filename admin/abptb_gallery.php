<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Gallery')) {
        class ABPTB_Gallery {
            public function __construct() {
                add_action('abptb_post_content', array($this, 'post_gallery'));
            }
            public function post_gallery($post_infos = []): void {
                ?>
                <div class="tab_item" data-tabs="#abptb_gallery">
                    <?php $this->gallery($post_infos); ?>
                </div>
                <?php
            }
            public function gallery($post_infos = []): void {
                ?>
                <div class="setting_item full_width">
                    <span class="abp_label"><?php esc_html_e('Gallery', 'abp-transport-booking'); ?></span>
                    <div class="_divider_xxs"></div>
                    <?php ABPTB_Layout::info_text('display_slider'); ?>
                    <div class="_divider_xxs"></div>
                    <?php do_action('abptb_add_image_multiple', 'abptb_slider', ($post_infos['abptb_slider'] ?? '')); ?>
                </div>
                <?php
            }
        }
        new ABPTB_Gallery();
    }
