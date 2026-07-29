<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptf_client_form_template', function ($post_infos, $prefix = '') {
        $forms = ABPTF_Function::client_data($post_infos);
        if (!empty($forms) && is_array($forms)) { ?>
            <div class="client_info_area">
                <div class="item_box_1 attendee_item">
                    <h5 class=" _abp_title">
                        <?php esc_html_e('Client Info : ', 'abp-transportforge'); ?>&nbsp;<span class="_color_theme attendee_seat_name"></span>
                    </h5>
                    <?php
                        foreach ($forms as $id => $form) {
                            ABPTF_Layout::create_client_form($form, $id, $prefix);
                        }
                    ?>
                </div>
            </div>
            <?php
        }
    }, 10, 2);