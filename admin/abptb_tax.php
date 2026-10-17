<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Tax')) {
        class ABPTB_Tax {
            public function __construct() {
                add_action('abptb_post_content', array($this, 'post_tax'));
            }
            public function post_tax($post_infos = []): void {
                ?>
                <div class="tab_item" data-tabs="#abptb_tax">
                    <?php $this->tax($post_infos); ?>
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
        }
        new ABPTB_Tax();
    }