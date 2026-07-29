<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('ABPTF_Price')) {
        class ABPTF_Price {
            public function __construct() {
                add_action('abptf_post_content', [$this, 'ticket_price']);
                add_action('wp_ajax_abptf_reload_pricing', [$this, 'reload_pricing']);
            }
            public function ticket_price($post_infos = []): void {
                ?>
                <div class="tab_item abptf_price" data-tabs="#abptf_price">
                    <?php $this->route_price($post_infos); ?>
                </div>
                <?php
            }
            public function route_price($post_infos): void {
                $types = ABPTF_Function::get_option('abptf_ticket');
                $price_infos = $post_infos['price_infos'] ?? [];
                $display_return = $post_infos['display_return'] ?? 'off';
                $display_return = ABPTF_Function::on_off('return') ? $display_return : 'off';
                $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
                $display_ticket_type = ABPTF_Function::on_off('ticket_type') ? $display_ticket_type : 'off';
                $ticket_infos = $post_infos['all_ticket_type'] ?? [];
                if (empty($ticket_infos)) {
                    if (empty($types)) {
                        $types[uniqid()]['label'] = 'Ticket/Seat';
                        update_option('abptf_ticket', $types);
                    }
                    $ticket_infos[] = array_key_first($types);
                }
                $return_price_infos = $post_infos['return_price_infos'] ?? [];
                $locations = ABPTF_Function::get_option('abptf_location');
                //echo '<pre>';                print_r($ticket_infos);                echo '</pre>';
                // echo '<pre>';                print_r($price_infos);                echo '</pre>';
                ?>
                <div class="_f_wrap _f_equal _gap_xs">
                    <div class="price_infos">
                        <?php if ( $display_return == 'on') { ?>
                            <h6 class="_abp_color_theme_mar_b_xxs "><span class="fas fa-route _mar_r_xs"></span><?php esc_html_e('Forward Price', 'abp-transportforge'); ?></h6>
                            <div class="_divider_xxs"></div>
                        <?php } ?>
                        <div class="_ov_auto">
                            <table class="_abp_fixed">
                                <thead>
                                <tr>
                                    <th class="_w_50"></th>
                                    <th><span class="fas fa-route _mar_r_xxs"></span><?php esc_html_e('From', 'abp-wc-transport-manager'); ?></th>
                                    <th><span class="fas fa-route _mar_r_xxs"></span><?php esc_html_e('To', 'abp-wc-transport-manager'); ?></th>
                                    <?php if ($display_ticket_type == 'off' || empty($ticket_infos)) { ?>
                                        <th><?php esc_html_e('Price', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                    <?php } else {
                                        foreach ($ticket_infos as $key) { ?>
                                            <th><?php echo esc_html($types[$key]['label'] ?? $key); ?></th>
                                            <?php
                                        }
                                    } ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $count = 1;
                                    if (!empty($price_infos)) {
                                        foreach ($price_infos as $bp_dp => $price_info) {
                                            [$bp, $dp] = array_map('intval', explode('_', $bp_dp));
                                            if (!empty($bp) && !empty($dp)) {
                                                ?>
                                                <tr>
                                                    <th>
                                                        <input type="hidden" name="route_id[]" value="<?php echo esc_attr($bp_dp); ?>"/>
                                                        <?php echo esc_html($count); ?>
                                                    </th>
                                                    <th><?php echo esc_html($locations[$bp]['name'] ?? $bp); ?></th>
                                                    <th><?php echo esc_html($locations[$dp]['name'] ?? $dp); ?></th>
                                                    <?php foreach ($ticket_infos as $key) {
                                                        $key = $display_ticket_type == 'on' ? $key : 'price';
                                                        ?>
                                                        <th>
                                                            <label>
                                                                <input type="text" class="_form_control validation_price" value="<?php echo esc_attr($price_info[$key] ?? 0); ?>" name="<?php echo esc_attr($key); ?>_price[]" placeholder="<?php esc_attr_e('EX:10', 'abp-wc-transport-manager') ?>">
                                                            </label>
                                                        </th>
                                                    <?php } ?>
                                                </tr>
                                                <?php
                                                $count++;
                                            }
                                        }
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ( $display_return == 'on') { ?>
                        <div class="return_price_infos">
                            <h6 class="_abp_color_theme_mar_b_xxs "><span class="fas fa-route _mar_r_xs"></span><?php esc_html_e('Return Price', 'abp-transportforge'); ?></h6>
                            <div class="_divider_xxs"></div>
                            <div class="_ov_auto">
                                <table class="_abp_fixed">
                                    <thead>
                                    <tr>
                                        <th class="_w_50"></th>
                                        <th><span class="fas fa-route _mar_r_xxs"></span><?php esc_html_e('From', 'abp-wc-transport-manager'); ?></th>
                                        <th><span class="fas fa-route _mar_r_xxs"></span><?php esc_html_e('To', 'abp-wc-transport-manager'); ?></th>
                                        <?php if ($display_ticket_type == 'off' || empty($ticket_infos)) { ?>
                                            <th><?php esc_html_e('Price', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                        <?php } else {
                                            foreach ($ticket_infos as $key) { ?>
                                                <th><?php echo esc_html($types[$key]['label'] ?? $key); ?></th>
                                                <?php
                                            }
                                        } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $count = 1;
                                        if (!empty($return_price_infos)) {
                                            foreach ($return_price_infos as $bp_dp => $price_info) {
                                                [$bp, $dp] = array_map('intval', explode('_', $bp_dp));
                                                if (!empty($bp) && !empty($dp)) {
                                                    ?>
                                                    <tr>
                                                        <th>
                                                            <input type="hidden" name="return_route_id[]" value="<?php echo esc_attr($bp_dp); ?>"/>
                                                            <?php echo esc_html($count); ?>
                                                        </th>
                                                        <th><?php echo esc_html($locations[$bp]['name'] ?? $bp); ?></th>
                                                        <th><?php echo esc_html($locations[$dp]['name'] ?? $dp); ?></th>
                                                        <?php foreach ($ticket_infos as $key) {
                                                            $key = $display_ticket_type == 'on' ? $key : 'price';
                                                            ?>
                                                            <th>
                                                                <label>
                                                                    <input type="text" class="_form_control validation_price" value="<?php echo esc_attr($price_info[$key] ?? 0); ?>" name="return_<?php echo esc_attr($key); ?>_price[]" placeholder="<?php esc_attr_e('EX:10', 'abp-wc-transport-manager') ?>">
                                                                </label>
                                                            </th>
                                                        <?php } ?>
                                                    </tr>
                                                    <?php
                                                    $count++;
                                                }
                                            }
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            public function reload_pricing(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $post_int = fn($key, $default = 0) => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_json = function ($key) {
                    if (!isset($_POST[$key])) {
                        return array();
                    }
                    $raw_data = json_decode(wp_unslash($_POST[$key]), true);
                    if (!is_array($raw_data)) {
                        return array();
                    }
                    return array_map(function ($item) {
                        return is_array($item) ? array_map('sanitize_text_field', $item) : sanitize_text_field($item);
                    }, $raw_data);
                };
                ob_start();
                $route = $post_json('routing_infos');
                $route_info = [];
                if (!empty($route)) {
                    foreach ($route as $value) {
                        $stop = $value['stop'] ?? '';
                        $type = $value['type'] ?? '';
                        if (!empty($stop) && !empty($type)) {
                            $route_info[$stop]['type'] = $type;
                        }
                    }
                }
                if (!empty($route_info)) {
                    $route_info[array_key_first($route_info)]['type'] = 'bp';
                    if (sizeof($route_info) > 1) {
                        $route_info[array_key_last($route_info)]['type'] = 'dp';
                    }
                }
                $display_return = $post_val('display_return');
                $price_infos = $post_json('price_infos');
                $ticket_infos = $post_json('all_ticket_type');
                $post_infos['post_id'] = $post_int('post_id');
                $post_infos['seat_type'] = $post_val('seat_type');
                $post_infos['display_ticket_type'] = $post_val('display_ticket_type');
                $post_infos['routing_infos'] = $route_info;
                $post_infos['all_ticket_type'] = $ticket_infos;
                $post_infos['price_infos'] = $this->get_route_array($route_info, $price_infos, $ticket_infos);
                $post_infos['display_return'] = $display_return;
                if ($display_return == 'on') {
                    $return_price_infos = $post_json('return_price_infos');
                    $return_route = $post_json('return_routing_infos');
                    $return_routing_infos = [];
                    if (!empty($return_route)) {
                        foreach ($return_route as $value) {
                            $stop = $value['stop'] ?? '';
                            $type = $value['type'] ?? '';
                            if (!empty($stop) && !empty($type)) {
                                $return_routing_infos[$stop]['type'] = $type;
                            }
                        }
                    }
                    if (!empty($return_routing_infos)) {
                        $return_routing_infos[array_key_first($return_routing_infos)]['type'] = 'bp';
                        if (sizeof($return_routing_infos) > 1) {
                            $return_routing_infos[array_key_last($return_routing_infos)]['type'] = 'dp';
                        }
                    }
                    $post_infos['return_price_infos'] = $this->get_route_array($return_routing_infos, $return_price_infos, $ticket_infos);
                }
                //echo '<pre>';                print_r($post_infos);                echo '</pre>';
                $this->route_price($post_infos);
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'msg' => __('Price Configuration Updated....!', 'abp-transportforge'), 'type' => 'success']);
            }
            public function get_route_array(array $routing_infos = [], $price_infos = [], $ticket_infos = []): array {
                $route_array = [];
                if (!empty($routing_infos)) {
                    foreach ($routing_infos as $key => $routing_info) {
                        if ($routing_info['type'] == 'bp' || $routing_info['type'] == 'both') {
                            $bp = $key;
                            $keys = array_keys($routing_infos);
                            $index = array_search($key, $keys);
                            if ($index !== false) {
                                $next_infos = array_slice($routing_infos, $index + 1, null, true);
                                if (!empty($next_infos) > 0) {
                                    foreach ($next_infos as $dp => $next_info) {
                                        if ($next_info['type'] == 'dp' || $next_info['type'] == 'both') {
                                            $path_price = [];
                                            $path_price['bp'] = $bp;
                                            $path_price['dp'] = $dp;
                                            $bp_dp = $bp . '_' . $dp;
                                            if (!empty($price_infos) && is_array($price_infos) && sizeof($price_infos) > 0) {
                                                foreach ($price_infos as $key => $price_info) {
                                                    if ($bp_dp == $key) {
                                                        if (!empty($ticket_infos) && is_array($ticket_infos) && sizeof($ticket_infos) > 0) {
                                                            foreach ($ticket_infos as $ticket_type) {
                                                                $path_price[$ticket_type] = $price_info[$ticket_type] ?? '';
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            $route_array[$bp_dp] = $path_price;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                return $route_array;
            }
        }
        new ABPTF_Price();
    }