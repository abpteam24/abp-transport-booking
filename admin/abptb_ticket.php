<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('ABPTB_Ticket')) {
        class ABPTB_Ticket {
            public function __construct() {
                add_action('abptb_post_content', [$this, 'ticket_configuration']);
                add_action('wp_ajax_abptb_type_switch', [$this, 'type_switch']);
            }
            public function ticket_configuration($post_infos = []): void {
                $seat_type = $post_infos['seat_type'] ?? 'sp';
                $seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
                $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
                $display_ticket_type = ABPTB_Function::on_off('ticket_type') ? $display_ticket_type : 'off';
                $ticket_info_key = ABPTB_Function::on_off('sp') ? 'seat_type' : 'ticket_type';
                $multi_info_key = ABPTB_Function::on_off('ticket_type') ? 'display_ticket_type' : 'single_ticket_type';
                ?>
                <div class="tab_item abptb_ticket" data-tabs="#abptb_ticket">
                    <div class="group_setting">
                        <div class="setting_item">
                            <label class="_fj_between">
                                <span class="_abp_label"><?php esc_html_e('Ticket Type', 'abp-wc-transport-manager'); ?></span>
                                <?php if (ABPTB_Function::on_off('sp')) { ?>
                                    <select class="_form_control " name="seat_type" required>
                                        <option disabled selected> <?php esc_html_e('Please Select', 'abp-wc-transport-manager'); ?></option>
                                        <option value="sp" <?php echo esc_attr($seat_type == 'sp' ? 'selected' : ''); ?>><?php echo esc_html(ABPTB_Layout::ticket_type('sp')); ?></option>
                                        <option value="ticket" <?php echo esc_attr($seat_type == 'ticket' ? 'selected' : ''); ?>><?php echo esc_html(ABPTB_Layout::ticket_type('ticket')); ?></option>
                                    </select>
                                <?php } else { ?>
                                    <input type="hidden" name="seat_type" value="<?php echo esc_attr($seat_type); ?>">
                                    <span class="_abp_label"><?php esc_html_e('Ticket', 'abp-wc-transport-manager'); ?></span>
                                <?php } ?>
                            </label>
                            <div class="_divider_xxs"></div>
                            <?php ABPTB_Layout::info_text($ticket_info_key); ?>
                        </div>
                        <div class="setting_item">
                            <?php if (ABPTB_Function::on_off('ticket_type')) { ?>
                                <div class="_fa_center">
                                    <?php ABPTB_Layout::switch_checkbox('display_ticket_type', $display_ticket_type); ?>
                                    <span class="_abp_label"><?php esc_html_e('Multiple Ticket Type?', 'abp-transport-booking'); ?></span>
                                </div>
                            <?php } else { ?>
                                <div class="_fj_between">
                                    <input type="hidden" name="display_ticket_type" value="<?php echo esc_attr($display_ticket_type); ?>">
                                    <span class="_abp_label"><?php esc_html_e('Ticket Type', 'abp-transport-booking'); ?></span>
                                    <span class="_abp_label"><?php esc_html_e('Single', 'abp-wc-transport-manager'); ?></span>
                                </div>
                            <?php } ?>
                            <div class="_divider_xxs"></div>
                            <?php ABPTB_Layout::info_text($multi_info_key); ?>
                        </div>
                        <?php if (ABPTB_Function::on_off('min_max')) { ?>
                            <div class="setting_item">
                                <label class="_fj_between _f_wrap">
                                    <span class="_abp_label"><?php esc_html_e('Min Qty/Order', 'abp-transport-booking'); ?></span>
                                    <input class="_form_control validation_number" name="min_qty" value="<?php echo esc_attr($post_infos['min_qty'] ?? 0); ?>" placeholder="<?php esc_attr_e('Ex: 1', 'abp-transport-booking'); ?>"/>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('min_qty'); ?>
                            </div>
                            <div class="setting_item">
                                <label class="_fj_between _f_wrap">
                                    <span class="_abp_label"><?php esc_html_e('Max Qty/Order', 'abp-transport-booking'); ?></span>
                                    <input class="_form_control validation_number" name="max_qty" value="<?php echo esc_attr($post_infos['max_qty'] ?? ''); ?>" placeholder="<?php esc_attr_e('Ex: 5', 'abp-transport-booking'); ?>"/>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTB_Layout::info_text('max_qty'); ?>
                            </div>
                        <?php } ?>
                        <div class="ticket_configuration configuration_content setting_item full_width">
                            <?php
                                if ($seat_type == 'sp') {
                                    $this->seat_type($post_infos);
                                } else {
                                    $this->ticket_type($post_infos);
                                } ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            public function type_switch(): void {
                if (!check_ajax_referer('abptb_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transport-booking'), 'type' => 'warn'], 403);
                }
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $post_int = fn($key, $default = 0) => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_id = $post_int('post_id');
                $seat_type = $post_val('type');
                $post_infos = ABPTB_Function::get_all_meta($post_id);
                $post_infos['display_ticket_type'] = $post_val('display_ticket_type');
                ob_start();
                if ($seat_type == 'sp') {
                    $this->seat_type($post_infos);
                } else {
                    $this->ticket_type($post_infos);
                }
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'msg' => __('Ticket Type Switched Successfully...!', 'abp-transport-booking'), 'type' => 'success']);
            }
            public function seat_type($post_infos): void {
                $sp_infos = $post_infos['sp_infos'] ?? [];
                $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
                $display_ticket_type = ABPTB_Function::on_off('ticket_type') ? $display_ticket_type : 'off';
                $sp_data = ABPTB_Query::get_sp();
                $post_id = $post_infos['post_id'] ?? '';
                //echo '<pre>';print_r(ABPTB_Ticket_SP);echo '</pre>';
                ?>
                <div class="_fj_between">
                    <h5 class="_abp"><?php esc_html_e('Seat Plan Configuration', 'abp-wc-transport-manager'); ?></h5>
                    <button type="button" class="_btn_light_active_xs">
                        <?php esc_html_e('Total Seat :', 'abp-wc-transport-manager'); ?>
                        <span class="_abp_color_theme _mar_l_xs transport_total"><?php echo esc_html(ABPTB_Function::get_total_qty($post_id, $post_infos)); ?></span>
                    </button>
                    <div class="_group_content">
                        <?php ABPTB_Layout::button_add(__('Add New Seat Label', 'abp-wc-transport-manager')); ?>
                    </div>
                </div>
                <div class="_divider_xs"></div>
                <div class="_ov_auto">
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('Seat Plan Name', 'abp-wc-transport-manager'); ?></th>
                            <th><?php esc_html_e('Frontend Name', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                            <?php if (ABPTB_Function::on_off('ticket_type') && $display_ticket_type == 'on') { ?>
                                <th><?php esc_html_e('Type Wise Quantity', 'abp-wc-transport-manager'); ?></th>
                            <?php } ?>
                            <th><?php esc_html_e('Total Quantity', 'abp-wc-transport-manager'); ?></th>
                            <th><?php esc_html_e('Action', 'abp-wc-transport-manager'); ?></th>
                        </tr>
                        </thead>
                        <tbody class="insertable_area sortable_area sp_selection_area">
                        <?php
                            if (!empty($sp_infos)) {
                                foreach ($sp_infos as $ticket_info) {
                                    $this->sp_item($sp_data, $display_ticket_type, $ticket_info);
                                }
                            } else {
                                $this->sp_item($sp_data, $display_ticket_type);
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
                <div class="abp_hidden">
                    <table class="_abp">
                        <tbody class="hidden_content">
                        <?php $this->sp_item($sp_data, $display_ticket_type); ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            public function ticket_type($post_infos): void {
                $ticket_infos = $post_infos['ticket_infos'] ?? [];
                $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
                $display_ticket_type = ABPTB_Function::on_off('ticket_type') ? $display_ticket_type : 'off';
                ?>
                <div class="_fj_between">
                    <h5 class="_abp"><?php esc_html_e('Ticket Configuration', 'abp-wc-transport-manager'); ?></h5>
                    <div class="_group_content">
                        <?php if (ABPTB_Function::on_off('ticket_type') && $display_ticket_type == 'on') {
                            ABPTB_Layout::button_add(__('Add New item', 'abp-wc-transport-manager'));
                        }
                            ABPTB_Layout::button_global_popup('ticket_type', __('Add New Ticket Type', 'abp-transport-booking')); ?>
                    </div>
                </div>
                <div class="_divider_xxs"></div>
                <div class="_ov_auto">
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('Name', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                            <th><?php esc_html_e('Quantity', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                            <th><?php esc_html_e('Reserve Quantity', 'abp-wc-transport-manager'); ?></th>
                            <th><?php esc_html_e('Min qty/Order', 'abp-wc-transport-manager'); ?></th>
                            <th><?php esc_html_e('Max qty/Order', 'abp-wc-transport-manager'); ?></th>
                            <th><?php esc_html_e('Description', 'abp-wc-transport-manager'); ?></th>
                            <th><?php esc_html_e('Action', 'abp-wc-transport-manager'); ?></th>
                        </tr>
                        </thead>
                        <tbody class="insertable_area sortable_area ticket_selection_area">
                        <?php
                            if (!empty($ticket_infos)) {
                                if ($display_ticket_type === 'off') {
                                    $key = array_key_first($ticket_infos);
                                    $this->ticket_item($ticket_infos[$key], $key);
                                } else {
                                    foreach ($ticket_infos as $key => $ticket_info) {
                                        $this->ticket_item($ticket_info, $key);
                                    }
                                }
                            } else {
                                $this->ticket_item();
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($display_ticket_type === 'on') { ?>
                    <div class="abp_hidden">
                        <table class="_abp">
                            <tbody class="hidden_content">
                            <?php $this->ticket_item(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                }
            }
            public function ticket_item($field = array(), $id = ''): void {
                ?>
                <tr class="delete_area">
                    <td>
                        <label>
                            <select name="ticket_name[]" class='_form_control' data-required>
                                <option value="" selected><?php esc_html_e('Select Ticket Name.', 'abp-wc-transport-manager'); ?></option>
                                <?php if (!empty(ABPTB_Ticket)) {
                                    foreach (ABPTB_Ticket as $key => $ticket) { ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($key == $id ? 'selected' : ''); ?>><?php echo esc_html($ticket['label'] ?? ''); ?></option>
                                        <?php
                                    }
                                } ?>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input data-required type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="ticket_qty[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($field['qty'] ?? 10); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="reserve_qty[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($field['reserve'] ?? 0); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="ticket_min_qty[]" placeholder="<?php esc_attr_e('EX: 1', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($field['min_qty'] ?? 1); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="ticket_max_qty[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($field['max_qty'] ?? ''); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <textarea class="_form_control" name="ticket_description[]" placeholder="<?php esc_attr_e('EX: Description', 'abp-transport-booking'); ?>"><?php echo esc_html($field['description'] ?? ''); ?></textarea>
                        </label>
                    </td>
                    <td><?php ABPTB_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
            public function sp_item($sp_data, $display_ticket_type, $field = array()): void {
                $field = $field ?: [];
                $id = $field['id'] ?? '';
                $total_seat = 0;
                $meta_info = [];
                ?>
                <tr class="delete_area">
                    <td>
                        <label>
                            <select name="sp_id[]" class='_form_control' data-required>
                                <option value="" selected><?php esc_html_e('Select Seat plan', 'abp-wc-transport-manager'); ?></option>
                                <?php if (!empty($sp_data)) {
                                    foreach ($sp_data as $sp_info) {
                                        $key = $sp_info['id'] ?? '';
                                        if (!empty($key)) {
                                            if ($key == $id) {
                                                $meta_info = json_decode($sp_info['seat_info'] ?? '', true) ?: [];
                                                $total_seat = $sp_info['total_seats'] ?? 0;
                                            }
                                            ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($key == $id ? 'selected' : ''); ?>><?php echo esc_html($sp_info['name'] ?? ''); ?></option>
                                            <?php
                                        }
                                    }
                                } ?>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="text" class="_form_control validation_name" name="sp_name[]" placeholder="<?php esc_attr_e('EX: Lower Deck', 'abp-transport-booking'); ?>" value="<?php echo esc_attr($field['name'] ?? ''); ?>"/>
                        </label>
                    </td>
                    <?php if (ABPTB_Function::on_off('ticket_type') && $display_ticket_type == 'on') { ?>
                        <th class="ticket_type_details">
                            <?php ABPTB_Seat_Plan::sp_seat_list(ABPTB_Ticket, $meta_info); ?>
                        </th>
                    <?php } ?>
                    <th class="row_total"><?php echo esc_html($total_seat); ?> </th>
                    <td>
                        <div class="_all_center">
                            <div class="_group_content">
                                <button type="button" class="_btn_light_theme_xxs sp_id_change" onclick="abptb_popup_open_global('view_sp','<?php echo esc_attr($id); ?>')" title="<?php esc_attr_e('View : Seat Plan', 'abp-transport-booking'); ?>">👁️</button>
                                <?php
                                    ABPTB_Layout::button_sort();
                                    ABPTB_Layout::button_delete();
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }
        new ABPTB_Ticket();
    }